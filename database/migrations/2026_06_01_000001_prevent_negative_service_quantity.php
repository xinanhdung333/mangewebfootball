<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('services')
            ->where('quantity', '<', 0)
            ->update(['quantity' => 0]);

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE services ADD CONSTRAINT services_quantity_non_negative CHECK (quantity >= 0)');
            } catch (\Throwable $e) {
                // Older MySQL/MariaDB versions may not support CHECK constraints.
            }
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE services DROP CHECK services_quantity_non_negative');
            } catch (\Throwable $e) {
                //
            }
        }
    }
};
