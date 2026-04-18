<?php

namespace App\Interfaces\Services;

interface UserServiceInterface
{
    public function getProfile(int $userId): array;
    public function updateProfile(array $data, int $userId): object;
    public function updatePassword(array $data, int $userId): void;
    public function updateAvatar($file, int $userId): object;
    public function deleteAccount(int $userId): void;
}
