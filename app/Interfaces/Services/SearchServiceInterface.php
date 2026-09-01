<?php

namespace App\Interfaces\Services;

interface SearchServiceInterface
{
    public function search(string $query, int $perPage = 20): array;
}
