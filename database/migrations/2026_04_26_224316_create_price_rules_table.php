<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up()
{
    Schema::create('price_rules', function (Blueprint $table) {
        $table->id();

$table->integer('field_id')->nullable(); // bỏ unsigned        
      $table->foreign('field_id')
    ->references('id')
    ->on('fields')
    ->nullOnDelete();

        $table->integer('start_hour'); // 0-23
        $table->integer('end_hour');   // 1-24

        $table->decimal('multiplier', 5, 2)->default(1);

        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_rules');
    }
};
