<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_discounts', function (Blueprint $table) {

            $table->id();

            // null = áp dụng tất cả service
$table->integer('service_id')->nullable();
            // khung giờ
            $table->time('start_time');
            $table->time('end_time');

            // hệ số giảm/tăng (0.8 = giảm 20%)
            $table->decimal('multiplier', 4, 2);

            // bật/tắt rule
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // foreign key
            $table->foreign('service_id')
                ->references('id')->on('services')
                ->onDelete('cascade');

            // index
            $table->index(['service_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_discounts');
    }
};