<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Insert default categories
        DB::table('categories')->insert([
            ['id' => 1, 'name' => 'Tổng hợp', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Quần áo', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Mỹ phẩm', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Xe', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
