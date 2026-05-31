<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('momo','vnpay','cash','bank_transfer') DEFAULT 'cash'");
        DB::statement("ALTER TABLE booking_payments MODIFY payment_method ENUM('momo','vnpay','cash','bank_transfer') DEFAULT 'cash'");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("UPDATE payments SET payment_method = 'cash' WHERE payment_method = 'bank_transfer'");
        DB::statement("UPDATE booking_payments SET payment_method = 'cash' WHERE payment_method = 'bank_transfer'");
        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('momo','vnpay','cash') DEFAULT 'cash'");
        DB::statement("ALTER TABLE booking_payments MODIFY payment_method ENUM('momo','vnpay','cash') DEFAULT 'cash'");
    }
};
