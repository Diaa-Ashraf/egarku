<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AdminUser;

use function Symfony\Component\String\b;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      $admin = AdminUser::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@ejarku.com',
            'password' => bcrypt('password'),
        ]);
    }
}
