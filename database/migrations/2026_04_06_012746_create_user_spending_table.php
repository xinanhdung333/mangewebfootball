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
        Schema::create('user_spending', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('user_id')->index('idx_user');
            $table->decimal('total_booking', 10)->nullable()->default(0);
            $table->decimal('total_services', 10)->nullable()->default(0);
            $table->decimal('total_spent', 10)->nullable()->storedAs('`total_booking` + `total_services`');
            $table->timestamp('last_update')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_spending');
    }
};
