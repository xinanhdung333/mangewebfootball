<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('vouchers', 'discount_type')) {
                $table->string('discount_type', 30)->default('fixed')->after('code');
            }

            if (!Schema::hasColumn('vouchers', 'max_discount_amount')) {
                $table->decimal('max_discount_amount', 12, 2)->nullable()->after('discount_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('vouchers', 'max_discount_amount')) {
                $table->dropColumn('max_discount_amount');
            }

            if (Schema::hasColumn('vouchers', 'discount_type')) {
                $table->dropColumn('discount_type');
            }
        });
    }
};
