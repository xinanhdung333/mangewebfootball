<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('user_spending', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('total_booking', 12, 2)->default(0);
            $table->decimal('total_services', 12, 2)->default(0);
            $table->timestamps();
            
            $table->unique('user_id');
            $table->index(['user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_spending');
    }
};
