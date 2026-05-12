<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
    $table->decimal('original_price', 15, 2)
          ->nullable()
          ->change();
});
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {

            // rollback về như cũ (nếu cần)
            $table->decimal('original_price', 10, 2)->change();

        });
    }
};