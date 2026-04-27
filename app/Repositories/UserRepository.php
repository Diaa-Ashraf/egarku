<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?object
    {
        return User::findOrFail($id);
    }

    public function update(int $id, array $data): object
    {
        User::where('id', $id)->update($data);
        return User::findOrFail($id);
    }

    public function delete(int $id): bool
    {
        return User::findOrFail($id)->delete(); // softDelete
    }

    
}
