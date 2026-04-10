<?php

namespace App\Interfaces\Services;

interface HomeServiceInterface
{
    public function getHomeData(?int $cityId): array;
}
