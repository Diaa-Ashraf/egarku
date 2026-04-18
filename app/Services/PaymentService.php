<?php

namespace App\Services;

use App\Interfaces\PaymentRepositoryInterface;
use App\Interfaces\PlanRepositoryInterface;
use App\Interfaces\Services\PaymentServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

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
                'delivery_needed'=> false,
                'amount_cents'   => $plan->price * 100,
                'currency'       => 'EGP',
                'merchant_order_id' => $transaction->id,
                'items'          => [[
                    'name'        => $plan->name,
                    'amount_cents'=> $plan->price * 100,
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
            $signature = hash('sha256',
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
            'payment_details'=> [
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
            'amount_cents', 'created_at', 'currency', 'error_occured',
            'has_parent_transaction', 'id', 'integration_id', 'is_3d_secure',
            'is_auth', 'is_capture', 'is_refunded', 'is_standalone_payment',
            'is_voided', 'order.id', 'owner', 'pending', 'source_data.pan',
            'source_data.sub_type', 'source_data.type', 'success',
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

        $this->activateSubscription($transaction);
    }

    // ── Fawry Callback (POST /api/payment/fawry/callback) ────
    public function fawryCallback(array $data): void
    {
        $reference   = $data['merchantRefNumber'] ?? '';
        $status      = $data['paymentStatus']     ?? '';
        $transaction = $this->paymentRepository->findTransactionByReference($reference);

        if (!$transaction || $transaction->status === 'completed') return;
        if ($status !== 'PAID') return;

        $this->activateSubscription($transaction);
    }

    // ── تأكيد يدوي من الأدمن ─────────────────────────────────
    public function confirmManual(int $transactionId): void
    {
        $transaction = $this->paymentRepository->findTransaction($transactionId);

        if (!$transaction || $transaction->status === 'completed') {
            throw new \Exception('العملية غير موجودة أو مكتملة مسبقاً');
        }

        $this->activateSubscription($transaction);
    }

    // ── تفعيل الباقة — مشترك بين كل وسائل الدفع ─────────────
    private function activateSubscription(object $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            // تحديث الـ transaction
            $this->paymentRepository->updateTransaction($transaction->id, [
                'status'     => 'completed',
                'updated_at' => now(),
            ]);

            $plan = $this->planRepository->findById($transaction->plan_id);

            // إلغاء الاشتراكات القديمة
            $this->paymentRepository->cancelActiveSubscriptions($transaction->vendor_profile_id);

            // إنشاء اشتراك جديد
            $this->paymentRepository->createSubscription([
                'vendor_profile_id' => $transaction->vendor_profile_id,
                'plan_id'           => $transaction->plan_id,
                'starts_at'         => now(),
                'expires_at'        => now()->addDays($plan->duration_days),
                'status'            => 'active',
            ]);

            // notification للمعلن
            $vendor = DB::table('vendor_profiles')
                ->where('id', $transaction->vendor_profile_id)
                ->select(['user_id'])
                ->first();

            if ($vendor) {
                Notification::create([
                    'user_id' => $vendor->user_id,
                    'type'    => 'payment_confirmed',
                    'title'   => 'تم تفعيل باقتك ✅',
                    'body'    => "تم تفعيل باقة {$plan->name} بنجاح",
                    'data'    => json_encode(['plan_id' => $plan->id]),
                ]);
            }

            // clear cache
            Cache::forget("dashboard_stats_{$vendor?->user_id}");
            Cache::forget("vendor_profile_{$vendor?->user_id}");
        });
    }
}
