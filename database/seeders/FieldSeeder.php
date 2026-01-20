<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FieldSeeder extends Seeder
{
    public function run()
    {
        DB::table('fields')->insert([
            [
                'name' => 'Sân A',
                'description' => 'Sân cỏ nhân tạo 7 người, có đèn chiếu sáng.',
                'location' => 'Hà Nội',
                'price_per_hour' => 200000,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'Sân B',
                'description' => 'Sân cỏ tự nhiên, thoáng mát.',
                'location' => 'Hà Nội',
                'price_per_hour' => 150000,
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);
    }
}
