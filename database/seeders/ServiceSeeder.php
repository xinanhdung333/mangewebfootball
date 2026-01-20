<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        DB::table('services')->insert([
            [
                'name' => 'Huấn luyện cá nhân',
                'description' => 'Huấn luyện viên chuyên nghiệp.',
                'price' => 300000,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'Sửa chữa sân',
                'description' => 'Dịch vụ bảo trì sân.',
                'price' => 500000,
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);
    }
}
