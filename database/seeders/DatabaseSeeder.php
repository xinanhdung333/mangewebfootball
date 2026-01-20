<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // call seeders
        $this->call([
            UserSeeder::class,
            FieldSeeder::class,
            ServiceSeeder::class,
        ]);
    }
}
