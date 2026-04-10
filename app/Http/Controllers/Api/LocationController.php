<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Interfaces\LocationRepositoryInterface;

class LocationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private LocationRepositoryInterface $locationRepository
    ) {}

    // GET /api/cities
    public function cities()
    {
        return $this->success($this->locationRepository->getAllCities());
    }

    // GET /api/cities/{cityId}/areas
    public function areas(int $cityId)
    {
        return $this->success($this->locationRepository->getAreasByCity($cityId));
    }
}
