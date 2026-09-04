<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_rules', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('multiplier');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('price_rules', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropColumn('is_active');
        });
    }
};
