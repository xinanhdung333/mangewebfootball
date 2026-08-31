<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Thêm tọa độ GPS vào địa chỉ người dùng
        Schema::table('user_addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('user_addresses', 'lat')) {
                $table->decimal('lat', 10, 7)->nullable()->after('postal_code');
            }
            if (!Schema::hasColumn('user_addresses', 'lng')) {
                $table->decimal('lng', 10, 7)->nullable()->after('lat');
            }
        });

        // Thêm phí ship và khoảng cách vào orders
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'shipping_fee')) {
                $table->decimal('shipping_fee', 12, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('orders', 'shipping_distance_km')) {
                $table->decimal('shipping_distance_km', 8, 3)->nullable()->after('shipping_fee');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_fee', 'shipping_distance_km']);
        });
    }
};
