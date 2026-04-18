<?php

namespace App\Interfaces;

interface UserRepositoryInterface
{
    public function findById(int $id): ?object;
    public function update(int $id, array $data): object;
    public function delete(int $id): bool;
}
