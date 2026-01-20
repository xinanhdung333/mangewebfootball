<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => Hash::make('password'), 'role' => 'admin']
        );

        // Boss
        User::updateOrCreate(
            ['email' => 'boss@example.com'],
            ['name' => 'Boss', 'password' => Hash::make('password'), 'role' => 'boss']
        );

        // Regular user
        User::updateOrCreate(
            ['email' => 'user@example.com'],
            ['name' => 'User', 'password' => Hash::make('password'), 'role' => 'user']
        );
    }
}
