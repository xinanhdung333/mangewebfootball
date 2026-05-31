<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
          Schema::table('booking_payments', function (Blueprint $table) {
       $table->enum('payment_method', [
    'momo',
    'vnpay',
    'cash',
    'bank_transfer'
])->default('cash');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_payments', function (Blueprint $table) {
            //
        });
    }
};
