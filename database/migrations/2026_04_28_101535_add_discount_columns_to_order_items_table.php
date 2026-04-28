<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {

            $table->decimal('original_price', 10, 2)->nullable()->after('price');

            $table->integer('discount_percent')->default(0)->after('original_price');

        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {

            $table->dropColumn(['original_price', 'discount_percent']);

        });
    }
};