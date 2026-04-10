<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Interfaces\Services\HomeServiceInterface;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use ApiResponse;

    public function __construct(
        private HomeServiceInterface $homeService
    ) {}

    // GET /api/home?city_id=1
    public function index(Request $request)
    {
        $data = $this->homeService->getHomeData($request->city_id);
        return $this->success($data);
    }
}
