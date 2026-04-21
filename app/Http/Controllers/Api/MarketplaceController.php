<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Interfaces\Services\MarketplaceServiceInterface;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    use ApiResponse;

    public function __construct(
        private MarketplaceServiceInterface $marketplaceService
    ) {}

    // GET /api/marketplace/{slug}
    // بيانات السوق + كاتيجوريز + فيلدات + مميزات
    // + بانرات + شركاء مميزون + إعلانات مميزة
    public function show(string $slug)
    {
        try {
            $data = $this->marketplaceService->getMarketplacePage($slug);
            return $this->success($data);
        } catch (\Exception $e) {
            return $this->notFound($e->getMessage());
        }
    }

    // GET /api/marketplace/{slug}/ads?city_id=1&category_id=2&price_min=500&fields[rooms]=3
    public function ads(string $slug, Request $request)
    {
        try {
            $ads = $this->marketplaceService->getAds($slug, $request->all());
            return $this->paginated($ads);
        } catch (\Exception $e) {
            return $this->notFound($e->getMessage());
        }
    }
}
