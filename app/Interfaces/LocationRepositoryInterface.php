<?php

namespace App\Interfaces;

interface LocationRepositoryInterface
{
    public function getAllCities(): object;
    public function getAreasByCity(int $cityId): object;
}
