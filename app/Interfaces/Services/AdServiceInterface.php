<?php

namespace App\Interfaces\Services;

interface AdServiceInterface
{
    public function show(int $id, ?int $userId): array;
    public function store(array $data, int $userId): array;
    public function update(int $id, array $data, int $userId): object;
    public function destroy(int $id, int $userId): void;
    public function contact(int $adId, string $type, ?int $userId, string $ip): array;
    public function toggleSave(int $adId, int $userId): array;
    public function getSaved(int $userId): object;
}
