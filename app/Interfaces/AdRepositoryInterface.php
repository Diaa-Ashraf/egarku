<?php

namespace App\Interfaces;

interface AdRepositoryInterface
{
    public function findById(int $id): ?object;
    public function create(array $data): object;
    public function update(int $id, array $data): object;
    public function delete(int $id): bool;
    public function incrementViews(int $id): void;
    public function incrementContacts(int $id): void;
}
