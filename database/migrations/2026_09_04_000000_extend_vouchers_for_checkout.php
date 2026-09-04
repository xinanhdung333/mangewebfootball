<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('discount_type', 20)->default('fixed')->after('code');
            $table->decimal('max_discount_amount', 12, 2)->nullable()->after('discount_amount');
            $table->unsignedInteger('usage_limit')->nullable()->after('min_order_amount');
            $table->unsignedInteger('usage_limit_per_user')->nullable()->after('usage_limit');
            $table->boolean('first_order_only')->default(false)->after('usage_limit_per_user');
            $table->dateTime('starts_at')->nullable()->after('is_active');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_voucher_code', 50)->nullable()->after('voucher_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('shipping_voucher_code');
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn([
                'discount_type', 'max_discount_amount', 'usage_limit',
                'usage_limit_per_user', 'first_order_only', 'starts_at',
            ]);
        });
    }
};
