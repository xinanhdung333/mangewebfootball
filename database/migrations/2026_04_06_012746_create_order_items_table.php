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
        Schema::create('order_items', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('order_id')->index('idx_order');
            $table->integer('service_id')->index('idx_service');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 12);
            $table->dateTime('created_at')->useCurrent();
            $table->enum('status', ['pending', 'confirmed', 'processing', 'completed', 'cancelled'])->nullable()->default('pending');
            $table->dateTime('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
