<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('ghn_province_id')->nullable()->after('city');
            $table->unsignedBigInteger('ghn_district_id')->nullable()->after('ghn_province_id');
            $table->string('ghn_ward_code', 20)->nullable()->after('ghn_district_id');
        });
    }

    public function down(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn(['ghn_province_id', 'ghn_district_id', 'ghn_ward_code']);
        });
    }
};
