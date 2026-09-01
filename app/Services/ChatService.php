<?php

namespace App\Services;

use App\Interfaces\ChatRepositoryInterface;
use App\Interfaces\Services\ChatServiceInterface;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatService implements ChatServiceInterface
{
    public function __construct(
        private ChatRepositoryInterface $chatRepository
    ) {}

    /**
     * Feature 1: البحث الذكي بالذكاء الاصطناعي (Public)
     */
    public function smartSearch(string $message, ?int $marketplaceId, ?int $cityId): array
    {
        // 1. فحص هل ميزة الذكاء الاصطناعي مفعلة من لوحة التحكم
        $aiEnabled = Setting::get('ai_enabled', true);
        
        // 2. استخراج كلمات مفتاحية من رسالة المستخدم
        $keywords = $this->extractKeywords($message);

        // 3. جلب الإعلانات المطابقة كبطاقات مرئية
        $ads = $this->chatRepository->searchAdsByKeywords($keywords, $cityId, $marketplaceId, 6);

        if (!$aiEnabled) {
            return [
                'reply' => 'إليك الإعلانات المطابقة لبحثك في منصة إيجاركو.',
                'ads'   => $ads,
            ];
        }

        // 4. جلب سياق الإعلانات النشطة
        $contextAds = $this->chatRepository->getAdsContext($marketplaceId, $cityId, 20);
        $adsContextText = $this->formatAdsContext($contextAds);

        // 5. تجهيز الـ System Prompt
        $systemPrompt = "أنت مساعد ذكي ومستشار خبير في منصة 'إيجاركو' (منصة إيجارات رائدة في مصر للعقارات والسيارات والفنادق والخدمات).\n"
            . "مهمتك مساعدة المستخدم في العثور على أفضل الإيجارات المناسبة لطلبه.\n"
            . "قواعد الرد الإلزامية:\n"
            . "1. تحدث باللغة العربية بأسلوب ودود، محترف وواضح.\n"
            . "2. اعتمد على بيانات الإعلانات المتاحة لديك في السياق.\n"
            . "3. اذكر تفاصيل مهمة مثل السعر، المنطقة، والمميزات باختصار.\n"
            . "4. إذا لم تجد طلباً مطابقاً تماماً، اقترح بدائل قريبة متوفرة وشجع المستخدم على تصفح المنصة.\n\n"
            . "قائمة الإعلانات المتاحة حالياً في المنصة:\n" . $adsContextText;

        // 6. استدعاء GLM-4 API
        $reply = $this->callGlm($systemPrompt, $message);

        return [
            'reply' => $reply,
            'ads'   => $ads,
        ];
    }

    /**
     * Feature 2: تحليل أداء التاجر بالذكاء الاصطناعي (Protected)
     */
    public function vendorAnalytics(string $message, int $userId): array
    {
        // 1. جلب إحصائيات التاجر
        $stats = $this->chatRepository->getVendorStats($userId);

        // 2. فحص هل ميزة الذكاء الاصطناعي مفعلة
        $aiEnabled = Setting::get('ai_enabled', true);
        if (!$aiEnabled) {
            return [
                'reply' => 'تم جلب إحصائياتك بنجاح. ميزة التحليل الذكي معطلة حالياً.',
                'stats' => $stats,
            ];
        }

        // 3. تحويل الإحصائيات إلى نص سياق
        $statsContext = $this->formatVendorStatsContext($stats);

        // 4. بناء الـ System Prompt
        $systemPrompt = "أنت خبير تسويق واستشاري تطوير أعمال في منصة 'إيجاركو'.\n"
            . "مهمتك تحليل أداء متجر وإعلانات التاجر وتقديم رؤى عملية ونصائح واضحة لزيادة المشاهدات والتواصل والأرباح.\n"
            . "قواعد التحليل الإلزامية:\n"
            . "1. تحدث باللغة العربية بأسلوب احترافي ومشجع للتاجر.\n"
            . "2. حلل الأرقام بدقة (نسبة التواصل للمشاهدات، حالة الإعلانات، قنوات التواصل الأكثر تفاعلاً).\n"
            . "3. قدم 3 إلى 4 نصائح عملية وقابلة للتطبيق الفوري (مثل تحسين الصور، العناوين، التسعير، تجديد الإعلانات المنتهية).\n\n"
            . "بيانات أداء التاجر الحالية:\n" . $statsContext;

        // 5. استدعاء GLM-4 API
        $reply = $this->callGlm($systemPrompt, $message);

        return [
            'reply' => $reply,
            'stats' => $stats,
        ];
    }

    /**
     * استدعاء GLM-4 API (Zhipu AI)
     */
    private function callGlm(string $systemPrompt, string $userMessage): string
    {
        // الأولوية لإعدادات لوحة التحكم، وإذا لم تكن موجودة نستخدم config
        $apiKey      = Setting::get('glm_api_key') ?: config('services.glm.api_key');
        $baseUrl     = config('services.glm.base_url', 'https://open.bigmodel.cn/api/paas/v4/chat/completions');
        $model       = Setting::get('glm_model', config('services.glm.model', 'glm-4'));
        $temperature = (float) Setting::get('ai_temperature', 0.7);
        $maxTokens   = (int) Setting::get('ai_max_tokens', 1000);

        if (empty($apiKey)) {
            Log::warning('GLM API Key is not configured.');
            return 'مساعد إيجاركو الذكي جاهز لخدمتك، يرجى تفعيل مفتاح الـ API من إعدادات النظام.';
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])
            ->timeout(30)
            ->post($baseUrl, [
                'model'       => $model,
                'messages'    => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $userMessage],
                ],
                'temperature' => $temperature,
                'max_tokens'  => $maxTokens,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? 'تم استلام طلبك بنجاح.';
            }

            Log::error('GLM API Error: ' . $response->body(), [
                'status' => $response->status(),
            ]);

            return 'أهلاً بك في إيجاركو! لقد راجعنا طلبك ويمكنك الاطلاع على أفضل العروض المقترحة أدناه.';
        } catch (\Exception $e) {
            Log::error('GLM API Connection Failed: ' . $e->getMessage());
            return 'أهلاً بك! يمكنك تصفح الإعلانات المتاحة والمقترحة لك حالياً بالأسفل.';
        }
    }

    /**
     * تنسيق الإعلانات كنص سياق لنموذج الذكاء الاصطناعي
     */
    private function formatAdsContext($ads): string
    {
        if ($ads->isEmpty()) {
            return "لا توجد إعلانات متاحة حالياً في قاعدة البيانات.\n";
        }

        $text = "";
        foreach ($ads as $index => $ad) {
            $num = $index + 1;
            $priceUnit = match ($ad->price_unit) {
                'daily'   => 'يومياً',
                'weekly'  => 'أسبوعياً',
                'monthly' => 'شهرياً',
                'yearly'  => 'سنوياً',
                default   => '',
            };

            $text .= "{$num}. [إعلان #{$ad->id}] العنوان: {$ad->title} | السعر: {$ad->price} {$priceUnit} | الموقع: {$ad->city_name} - {$ad->area_name} | القسم: {$ad->category_name} ({$ad->marketplace_name})\n";
        }

        return $text;
    }

    /**
     * تنسيق إحصائيات التاجر كنص سياق للذكاء الاصطناعي
     */
    private function formatVendorStatsContext(array $stats): string
    {
        $breakdownText = "";
        if (!empty($stats['contacts_breakdown'])) {
            foreach ($stats['contacts_breakdown'] as $type => $count) {
                $breakdownText .= "- {$type}: {$count} تواصل\n";
            }
        } else {
            $breakdownText = "لا توجد تفاصيل تواصل بعد.\n";
        }

        $topAdsText = "";
        if (!empty($stats['top_performing_ads'])) {
            foreach ($stats['top_performing_ads'] as $ad) {
                $topAdsText .= "- إعلان (#{$ad->id}) '{$ad->title}': {$ad->views_count} مشاهدة، {$ad->contacts_count} طلب تواصل (الحالة: {$ad->status})\n";
            }
        } else {
            $topAdsText = "لا توجد إعلانات منشورة بعد.\n";
        }

        return "إجمالي الإعلانات: {$stats['total_ads']}\n"
            . "الإعلانات النشطة: {$stats['active_ads']}\n"
            . "الإعلانات المعلقة للمراجعة: {$stats['pending_ads']}\n"
            . "الإعلانات المنتهية: {$stats['expired_ads']}\n"
            . "إجمالي المشاهدات: {$stats['total_views']}\n"
            . "إجمالي طلبات التواصل: {$stats['total_contacts']}\n\n"
            . "قنوات التواصل الأكثر استخداماً:\n" . $breakdownText . "\n"
            . "أفضل الإعلانات أداءً:\n" . $topAdsText;
    }

    /**
     * استخراج الكلمات المفتاحية من جملة المستخدم
     */
    private function extractKeywords(string $message): array
    {
        // حذف حروف الجر والكلمات الشائعة
        $stopWords = ['عايز', 'اريد', 'أريد', 'ابحث', 'أبحث', 'عن', 'في', 'من', 'الى', 'إلى', 'مع', 'على', 'هل', 'يوجد', 'لو', 'سمحت', 'كام', 'بكام', 'ممكن'];
        
        $cleaned = preg_replace('/[إأآا]/u', 'ا', mb_strtolower($message, 'UTF-8'));
        $cleaned = preg_replace('/[ة]/u', 'ه', $cleaned);
        $cleaned = preg_replace('/[ى]/u', 'ي', $cleaned);
        $cleaned = preg_replace('/[ًٌٍَُِّْ]/u', '', $cleaned);
        $cleaned = preg_replace('/[^\p{Arabic}\p{L}\p{N}\s]/u', ' ', $cleaned);

        $words = array_filter(explode(' ', $cleaned));

        $filtered = array_filter($words, function ($word) use ($stopWords) {
            return mb_strlen($word) >= 2 && !in_array($word, $stopWords);
        });

        return array_values($filtered);
    }
}
