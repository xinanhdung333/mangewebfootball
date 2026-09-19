<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('to_province_id')->nullable()->after('user_address_id');
            $table->unsignedInteger('to_district_id')->nullable()->after('to_province_id');
            $table->string('to_ward_code', 20)->nullable()->after('to_district_id');
            $table->string('detail_address', 255)->nullable()->after('to_ward_code');
            $table->string('ghn_code', 50)->nullable()->after('detail_address')->index();
            $table->string('ghn_status', 50)->nullable()->after('ghn_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['ghn_code']);
            $table->dropColumn([
                'to_province_id',
                'to_district_id',
                'to_ward_code',
                'detail_address',
                'ghn_code',
                'ghn_status',
            ]);
        });
    }
};
