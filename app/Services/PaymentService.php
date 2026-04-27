<?php

namespace App\Services;

use App\Interfaces\PaymentRepositoryInterface;
use App\Interfaces\PlanRepositoryInterface;
use App\Interfaces\Services\PaymentServiceInterface;
use App\Models\User;
use App\Models\ServicePrice;
use App\Models\Ad;
use App\Models\Banner;
use App\Models\FeaturedPartner;
use App\Models\FeaturedPurchase;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PaymentService implements PaymentServiceInterface
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepository,
        private PlanRepositoryInterface    $planRepository,
    ) {}

    // GET /api/plans
    public function getPlans(): object
    {
        return $this->planRepository->getAllActive();
    }

    // POST /api/subscribe
    public function subscribe(array $data, int $userId): array
    {
        $user   = User::find($userId);
        $vendor = $user->vendorProfile;

        if (!$vendor) {
            throw new \Exception('ليس لديك ملف معلن', 403);
        }

        $plan = $this->planRepository->findById($data['plan_id']);
        if (!$plan) {
            throw new \Exception('الباقة غير موجودة', 404);
        }

        $method = $data['method'];

        // إنشاء transaction بـ pending
        $transaction = $this->paymentRepository->createTransaction([
            'vendor_profile_id' => $vendor->id,
            'plan_id'           => $plan->id,
            'amount'            => $plan->price,
            'type'              => 'subscription',
            'method'            => $method,
            'status'            => 'pending',
        ]);

        return match ($method) {
            'paymob'   => $this->handlePaymob($transaction, $plan, $user),
            'fawry'    => $this->handleFawry($transaction, $plan, $user),
            default    => $this->handleManual($transaction, $plan),
        };
    }

    // ── أسعار الخدمات الفردية ─────────────────────────────────
    public function getServicePricing(): array
    {
        $prices = ServicePrice::where('is_active', true)
            ->orderBy('service_type')
            ->orderBy('duration_days')
            ->get();

        $labels = [
            'feature_ad'      => 'تمييز إعلان',
            'feature_company' => 'تمييز شركة',
            'add_banner'      => 'إضافة بانر',
        ];

        $grouped = [];
        foreach ($prices as $p) {
            $grouped[$p->service_type][] = [
                'id'            => $p->id,
                'duration_days' => $p->duration_days,
                'price'         => $p->price,
            ];
        }

        $result = [];
        foreach ($grouped as $type => $options) {
            $result[] = [
                'service_type' => $type,
                'label'        => $labels[$type] ?? $type,
                'options'      => $options,
            ];
        }

        return $result;
    }

    // ── شراء خدمة فردية ───────────────────────────────────────
    public function purchaseService(array $data, int $userId): array
    {
        $user   = User::find($userId);
        $vendor = $user->vendorProfile;

        if (!$vendor) {
            throw new \Exception('ليس لديك ملف معلن', 403);
        }

        $servicePrice = ServicePrice::where('id', $data['service_price_id'])
            ->where('is_active', true)
            ->first();

        if (!$servicePrice) {
            throw new \Exception('خطة السعر غير متاحة', 404);
        }

        // تحقق من نوع الخدمة
        if ($servicePrice->service_type !== $data['service_type']) {
            throw new \Exception('نوع الخدمة لا يتطابق مع خطة السعر', 422);
        }

        $method  = $data['method'];
        $extraData = [];

        // ── تجهيز البيانات حسب نوع الخدمة ──
        if ($data['service_type'] === 'feature_ad') {
            $ad = Ad::where('id', $data['ad_id'])->where('user_id', $userId)->first();
            if (!$ad) throw new \Exception('الإعلان غير موجود أو ليس ملكك', 404);
            if ($ad->is_featured) throw new \Exception('الإعلان مميز بالفعل', 422);
            $extraData['ad_id'] = $ad->id;
        }

        if ($data['service_type'] === 'add_banner') {
            // رفع الصورة وحفظ البانر كغير مفعل (قيد المراجعة)
            $imagePath = $data['image']->store('banners', 'public');
            $banner = Banner::create([
                'marketplace_id'    => $data['marketplace_id'] ?? null,
                'vendor_profile_id' => $vendor->id,
                'city_id'           => $data['city_id'] ?? null,
                'image'             => $imagePath,
                'link'              => $data['link'] ?? null,
                'position'          => $data['position'],
                'price'             => $servicePrice->price,
                'starts_at'         => now(),
                'expires_at'        => $servicePrice->duration_days > 0
                    ? now()->addDays($servicePrice->duration_days)
                    : null,
                'is_active'         => false, // قيد المراجعة
            ]);
            $extraData['banner_id'] = $banner->id;
        }

        if ($data['service_type'] === 'feature_company') {
            $extraData['featured_partner_id'] = null; // سيُنشأ بعد الدفع
        }

        $transactionType = match ($data['service_type']) {
            'feature_ad'      => 'featured',
            'feature_company' => 'featured_partner',
            'add_banner'      => 'banner',
        };

        // إنشاء الـ Transaction
        $transaction = $this->paymentRepository->createTransaction([
            'vendor_profile_id'  => $vendor->id,
            'ad_id'              => $extraData['ad_id'] ?? null,
            'banner_id'          => $extraData['banner_id'] ?? null,
            'featured_partner_id' => $extraData['featured_partner_id'] ?? null,
            'amount'             => $servicePrice->price,
            'type'               => $transactionType,
            'method'             => $method,
            'status'             => 'pending',
            'notes'              => json_encode(['duration_days' => $servicePrice->duration_days]),
        ]);

        // ← نمرر الـ $servicePrice بدل الـ $plan عشان الـ name والـ price
        $fakePlan = (object) [
            'id'    => $servicePrice->id,
            'name'  => match ($data['service_type']) {
                'feature_ad'      => 'تمييز إعلان',
                'feature_company' => 'تمييز شركة',
                'add_banner'      => 'إضافة بانر',
            },
            'price' => $servicePrice->price,
        ];

        return match ($method) {
            'paymob'   => $this->handlePaymob($transaction, $fakePlan, $user),
            'fawry'    => $this->handleFawry($transaction, $fakePlan, $user),
            default    => $this->handleManual($transaction, $fakePlan),
        };
    }

    // ── Paymob ───────────────────────────────────────────────
    private function handlePaymob(object $transaction, object $plan, User $user): array
    {
        try {
            // Step 1: Auth token
            $authResponse = Http::post('https://accept.paymob.com/api/auth/tokens', [
                'api_key' => config('services.paymob.api_key'),
            ]);
            $authToken = $authResponse->json('token');

            // Step 2: Order registration
            $orderResponse = Http::post('https://accept.paymob.com/api/ecommerce/orders', [
                'auth_token'     => $authToken,
                'delivery_needed' => false,
                'amount_cents'   => $plan->price * 100,
                'currency'       => 'EGP',
                'merchant_order_id' => $transaction->id,
                'items'          => [[
                    'name'        => $plan->name,
                    'amount_cents' => $plan->price * 100,
                    'description' => "باقة {$plan->name}",
                    'quantity'    => 1,
                ]],
            ]);
            $orderId = $orderResponse->json('id');

            // Step 3: Payment key
            $paymentKeyResponse = Http::post('https://accept.paymob.com/api/acceptance/payment_keys', [
                'auth_token'     => $authToken,
                'amount_cents'   => $plan->price * 100,
                'expiration'     => 3600,
                'order_id'       => $orderId,
                'billing_data'   => [
                    'apartment'       => 'NA',
                    'email'           => $user->email ?? 'NA',
                    'floor'           => 'NA',
                    'first_name'      => $user->name,
                    'street'          => 'NA',
                    'building'        => 'NA',
                    'phone_number'    => $user->phone,
                    'shipping_method' => 'NA',
                    'postal_code'     => 'NA',
                    'city'            => 'NA',
                    'country'         => 'EG',
                    'last_name'       => 'NA',
                    'state'           => 'NA',
                ],
                'currency'           => 'EGP',
                'integration_id'     => config('services.paymob.integration_id'),
            ]);
            $paymentKey = $paymentKeyResponse->json('token');

            // تحديث الـ transaction بـ reference
            $this->paymentRepository->updateTransaction($transaction->id, [
                'reference' => (string) $orderId,
            ]);

            return [
                'method'      => 'paymob',
                'payment_url' => "https://accept.paymob.com/api/acceptance/iframes/" . config('services.paymob.iframe_id') . "?payment_token={$paymentKey}",
                'transaction_id' => $transaction->id,
            ];
        } catch (\Exception $e) {
            Log::error('Paymob error: ' . $e->getMessage());
            $this->paymentRepository->updateTransaction($transaction->id, ['status' => 'failed']);
            throw new \Exception('حدث خطأ في بوابة الدفع', 500);
        }
    }

    // ── Fawry ────────────────────────────────────────────────
    private function handleFawry(object $transaction, object $plan, User $user): array
    {
        try {
            $merchantCode = config('services.fawry.merchant_code');
            $secureKey    = config('services.fawry.secure_key');
            $refNumber    = 'EJK-' . $transaction->id . '-' . time();

            // توليد الـ signature
            $signature = hash(
                'sha256',
                $merchantCode .
                    $refNumber .
                    $user->phone .
                    $transaction->id .
                    number_format($plan->price, 2, '.', '') .
                    $secureKey
            );

            $response = Http::post(config('services.fawry.base_url') . '/ECommerceWeb/Fawry/payments/charge', [
                'merchantCode'        => $merchantCode,
                'merchantRefNum'      => $refNumber,
                'customerMobile'      => $user->phone,
                'customerEmail'       => $user->email ?? '',
                'paymentMethod'       => 'PAYATFAWRY',
                'amount'              => $plan->price,
                'currencyCode'        => 'EGP',
                'description'         => "باقة {$plan->name} - إيجاركو",
                'chargeItems'         => [[
                    'itemId'          => (string) $plan->id,
                    'description'     => $plan->name,
                    'price'           => $plan->price,
                    'quantity'        => 1,
                ]],
                'signature'           => $signature,
            ]);

            $fawryRefNum = $response->json('referenceNumber');

            $this->paymentRepository->updateTransaction($transaction->id, [
                'reference' => $fawryRefNum ?? $refNumber,
            ]);

            return [
                'method'           => 'fawry',
                'reference_number' => $fawryRefNum ?? $refNumber,
                'amount'           => $plan->price,
                'expires_in'       => '24 ساعة',
                'transaction_id'   => $transaction->id,
                'message'          => 'ادفع في أي فرع فوري برقم المرجع',
            ];
        } catch (\Exception $e) {
            Log::error('Fawry error: ' . $e->getMessage());
            $this->paymentRepository->updateTransaction($transaction->id, ['status' => 'failed']);
            throw new \Exception('حدث خطأ في بوابة فوري', 500);
        }
    }

    // ── يدوي (فودافون كاش / إنستاباي / كاش) ─────────────────
    private function handleManual(object $transaction, object $plan): array
    {
        return [
            'method'         => $transaction->method,
            'transaction_id' => $transaction->id,
            'amount'         => $plan->price,
            'message'        => 'سيتم تفعيل باقتك بعد تأكيد الدفع من الأدمن',
            'payment_details' => [
                'vodafone_cash' => config('services.payment.vodafone_number'),
                'instapay'      => config('services.payment.instapay_number'),
            ],
        ];
    }

    // ── Paymob Callback (POST /api/payment/paymob/callback) ──
    public function paymobCallback(array $data): void
    {
        // التحقق من الـ HMAC
        $hmac       = $data['hmac'] ?? '';
        $secretKey  = config('services.paymob.hmac_secret');

        $fields = [
            'amount_cents',
            'created_at',
            'currency',
            'error_occured',
            'has_parent_transaction',
            'id',
            'integration_id',
            'is_3d_secure',
            'is_auth',
            'is_capture',
            'is_refunded',
            'is_standalone_payment',
            'is_voided',
            'order.id',
            'owner',
            'pending',
            'source_data.pan',
            'source_data.sub_type',
            'source_data.type',
            'success',
        ];

        $concatenated = '';
        foreach ($fields as $field) {
            $keys  = explode('.', $field);
            $value = $data;
            foreach ($keys as $key) {
                $value = $value[$key] ?? '';
            }
            $concatenated .= $value;
        }

        $calculatedHmac = hash('sha512', $concatenated . $secretKey);

        if ($calculatedHmac !== $hmac) {
            Log::warning('Paymob invalid HMAC');
            return;
        }

        if ($data['success'] !== 'true') return;

        $orderId     = $data['order']['id'] ?? null;
        $transaction = $this->paymentRepository->findTransactionByReference((string) $orderId);

        if (!$transaction || $transaction->status === 'completed') return;

        $this->activateTransaction($transaction);
    }

    // ── Fawry Callback (POST /api/payment/fawry/callback) ────
    public function fawryCallback(array $data): void
    {
        $reference   = $data['merchantRefNumber'] ?? '';
        $status      = $data['paymentStatus']     ?? '';
        $transaction = $this->paymentRepository->findTransactionByReference($reference);

        if (!$transaction || $transaction->status === 'completed') return;
        if ($status !== 'PAID') return;

        $this->activateTransaction($transaction);
    }

    // ── تأكيد يدوي من الأدمن ─────────────────────────────────
    public function confirmManual(int $transactionId): void
    {
        $transaction = $this->paymentRepository->findTransaction($transactionId);

        if (!$transaction || $transaction->status === 'completed') {
            throw new \Exception('العملية غير موجودة أو مكتملة مسبقاً');
        }

        $this->activateTransaction($transaction);
    }

    // ── تفعيل العملية — مشترك بين كل وسائل الدفع ─────────────
    private function activateTransaction(object $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            // تحديث الـ transaction
            $this->paymentRepository->updateTransaction($transaction->id, [
                'status'     => 'completed',
                'updated_at' => now(),
            ]);

            $vendor = DB::table('vendor_profiles')
                ->where('id', $transaction->vendor_profile_id)
                ->select(['user_id', 'display_name', 'marketplace_id'])
                ->first();

            // ── التوزيع حسب نوع العملية ──
            match ($transaction->type) {
                'subscription'    => $this->handleSubscriptionActivation($transaction),
                'feature_ad'      => $this->handleFeatureAdActivation($transaction),
                'feature_company' => $this->handleFeatureCompanyActivation($transaction, $vendor),
                'add_banner'      => $this->handleBannerActivation($transaction, $vendor),
                default           => null,
            };

            // إشعار المعلن
            if ($vendor) {
                $title = match ($transaction->type) {
                    'subscription'    => 'تم تفعيل باقتك ✅',
                    'feature_ad'      => 'تم تمييز إعلانك ⭐',
                    'feature_company' => 'تم تمييز شركتك ⭐',
                    'add_banner'      => 'تم استلام طلب البانر 📢',
                    default           => 'تم معالجة الدفع ✅',
                };

                $body = match ($transaction->type) {
                    'subscription'    => 'تم تفعيل باقتك بنجاح',
                    'feature_ad'      => 'إعلانك أصبح مميزاً الآن',
                    'feature_company' => 'شركتك مميزة الآن وستظهر للمستخدمين',
                    'add_banner'      => 'تم استلام بانرك وهو قيد المراجعة من الإدارة',
                    default           => 'تم معالجة الدفع بنجاح',
                };

                UserNotification::create([
                    'user_id' => $vendor->user_id,
                    'type'    => 'payment_confirmed',
                    'title'   => $title,
                    'body'    => $body,
                    'data'    => json_encode(['transaction_id' => $transaction->id, 'type' => $transaction->type]),
                ]);
            }

            // clear cache
            Cache::forget("dashboard_stats_{$vendor?->user_id}");
            Cache::forget("vendor_profile_{$vendor?->user_id}");
        });
    }

    // ── تفعيل الباقة ─────────────────────────────────────────
    private function handleSubscriptionActivation(object $transaction): void
    {
        $plan = $this->planRepository->findById($transaction->plan_id);

        $this->paymentRepository->cancelActiveSubscriptions($transaction->vendor_profile_id);

        $this->paymentRepository->createSubscription([
            'vendor_profile_id' => $transaction->vendor_profile_id,
            'plan_id'           => $transaction->plan_id,
            'starts_at'         => now(),
            'expires_at'        => now()->addDays($plan->duration_days),
            'status'            => 'active',
        ]);
    }

    // ── تمييز إعلان ──────────────────────────────────────────
    private function handleFeatureAdActivation(object $transaction): void
    {
        $notes = json_decode($transaction->notes, true);
        $durationDays = $notes['duration_days'] ?? 7;

        $ad = Ad::find($transaction->ad_id);
        if ($ad) {
            $ad->update([
                'is_featured'    => true,
                'featured_until' => $durationDays > 0 ? now()->addDays($durationDays) : null,
            ]);

            FeaturedPurchase::create([
                'ad_id'             => $ad->id,
                'vendor_profile_id' => $transaction->vendor_profile_id,
                'price'             => $transaction->amount,
                'duration'          => $durationDays,
                'starts_at'         => now(),
                'expires_at'        => $durationDays > 0 ? now()->addDays($durationDays) : null,
            ]);
        }
    }

    // ── تمييز شركة ───────────────────────────────────────────
    private function handleFeatureCompanyActivation(object $transaction, ?object $vendor): void
    {
        $notes = json_decode($transaction->notes, true);
        $durationDays = $notes['duration_days'] ?? 7;

        $fp = FeaturedPartner::updateOrCreate(
            ['vendor_profile_id' => $transaction->vendor_profile_id],
            [
                'marketplace_id' => $vendor->marketplace_id ?? null,
                'name'           => $vendor->display_name ?: 'شركة',
                'logo'           => 'default_partner.png',
                'website'        => null,
                'price'          => $transaction->amount,
                'is_active'      => true,
                'starts_at'      => now(),
                'expires_at'     => $durationDays > 0 ? now()->addDays($durationDays) : null,
            ]
        );

        // ربط الـ transaction بالـ featured_partner
        $this->paymentRepository->updateTransaction($transaction->id, [
            'featured_partner_id' => $fp->id,
        ]);
    }

    // ── تفعيل البانر (قيد المراجعة) ──────────────────────────
    private function handleBannerActivation(object $transaction, ?object $vendor): void
    {
        // البانر محفوظ بالفعل وغير مفعل — نبقيه كذلك لحد ما الأدمن يوافق
        // بس نحدث الـ notes إن الدفع تم
        $this->paymentRepository->updateTransaction($transaction->id, [
            'notes' => json_encode(array_merge(
                json_decode($transaction->notes, true) ?? [],
                ['payment_confirmed' => true, 'awaiting_review' => true]
            )),
        ]);
    }
}
