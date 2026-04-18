<?php

namespace App\Interfaces;

interface PlanRepositoryInterface
{
    public function getAllActive(): object;
    public function findById(int $id): ?object;
}
