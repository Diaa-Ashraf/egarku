<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\ChatSearchRequest;
use App\Http\Requests\Chat\ChatVendorRequest;
use App\Interfaces\Services\ChatServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ChatServiceInterface $chatService
    ) {}

    /**
     * POST /api/chat
     * البحث الذكي وتوصيات الإعلانات بالذكاء الاصطناعي (عام)
     */
    public function search(ChatSearchRequest $request): JsonResponse
    {
        try {
            $data = $this->chatService->smartSearch(
                message: $request->validated('message'),
                marketplaceId: $request->validated('marketplace_id'),
                cityId: $request->validated('city_id')
            );

            return $this->success($data);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * POST /api/chat/vendor
     * تحليل أداء التاجر وتقديم النصائح بالذكاء الاصطناعي (محمي - auth:sanctum)
     */
    public function vendorAnalytics(ChatVendorRequest $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            
            if (!$userId) {
                return $this->unauthorized('يجب تسجيل الدخول للوصول لهذه الخدمة');
            }

            $data = $this->chatService->vendorAnalytics(
                message: $request->validated('message'),
                userId: $userId
            );

            return $this->success($data);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}
