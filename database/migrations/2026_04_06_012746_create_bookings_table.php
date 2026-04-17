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
        Schema::create('bookings', function (Blueprint $table) {
$table->id();            $table->integer('user_id')->index('idx_user');
            $table->integer('field_id')->index('idx_field');
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('total_price', 12)->nullable();
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'expired'])->default('pending');
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
