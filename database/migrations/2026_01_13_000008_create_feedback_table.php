<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained('services')->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('cascade');
            $table->text('message');
            $table->integer('rating')->default(5);
            $table->timestamps();
            
            $table->index(['user_id']);
            $table->index(['service_id']);
            $table->index(['booking_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('feedback');
    }
};
