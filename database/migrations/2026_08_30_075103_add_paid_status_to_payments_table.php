<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'paid',
                'success',
                'failed',
                'refunded',
            ])->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'success',
                'failed',
                'refunded',
            ])->default('pending')->change();
        });
    }
};