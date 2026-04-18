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
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id')->index('idx_user');
            $table->integer('booking_id')->nullable()->index('idx_booking');
            $table->integer('service_id')->nullable()->index('idx_service');
            $table->text('message');
            $table->tinyInteger('rating')->nullable()->default(0);
            $table->dateTime('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
