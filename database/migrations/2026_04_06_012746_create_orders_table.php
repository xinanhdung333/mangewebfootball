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
        Schema::create('orders', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id')->index('idx_user');
            $table->integer('cart_id')->nullable()->index('idx_cart');
            $table->decimal('total_amount', 12)->default(0);
            $table->string('payment_method', 50)->nullable();
            $table->enum('status', ['pending', 'confirmed', 'processing', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
