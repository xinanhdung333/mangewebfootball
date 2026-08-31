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
        Schema::table('orders', function (Blueprint $table) {
            $table->text('note')->nullable()->after('payment_method');
            $table->string('shipping_service', 50)->nullable()->after('note');
            $table->string('voucher_code', 50)->nullable()->after('shipping_service');
            $table->decimal('voucher_discount', 12, 2)->default(0)->after('voucher_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['note', 'shipping_service', 'voucher_code', 'voucher_discount']);
        });
    }
};
