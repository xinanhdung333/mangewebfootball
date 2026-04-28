<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_discounts', function (Blueprint $table) {
            $table->string('note')->nullable()->after('multiplier');
        });
    }

    public function down(): void
    {
        Schema::table('service_discounts', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
};