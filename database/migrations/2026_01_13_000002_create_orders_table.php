<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->enum('status', ['pending', 'paid', 'delivered', 'cancelled'])->default('pending');
            $table->json('meta')->nullable();
            $table->timestamps();
            
            $table->index(['user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
