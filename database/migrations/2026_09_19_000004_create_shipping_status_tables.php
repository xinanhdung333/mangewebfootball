<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('label', 100);
            $table->string('shipment_status', 40)->default('created');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_terminal')->default(false);
            $table->timestamps();
        });

        Schema::create('order_shipping_status_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id');
            $table->unsignedBigInteger('shipping_status_id');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'created_at']);
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('shipping_status_id')->references('id')->on('shipping_statuses')->restrictOnDelete();
        });

        $statuses = [
            ['ready_to_pick', 'Chờ lấy hàng', 'created', 10, false],
            ['picking', 'Đang lấy hàng', 'created', 20, false],
            ['picked_up', 'Đã lấy hàng', 'picked_up', 30, false],
            ['storing', 'Đang lưu kho', 'transporting', 40, false],
            ['transporting', 'Đang trung chuyển', 'transporting', 50, false],
            ['sorting', 'Đang phân loại', 'transporting', 60, false],
            ['delivering', 'Đang giao hàng', 'delivering', 70, false],
            ['delivered', 'Đã giao thành công', 'delivered', 80, true],
            ['returning', 'Đang hoàn hàng', 'delivering', 90, false],
            ['returned', 'Đã hoàn hàng', 'delivered', 100, true],
            ['cancelled', 'Đã hủy', 'delivered', 110, true],
        ];

        $now = now();
        DB::table('shipping_statuses')->insert(array_map(
            fn (array $status) => [
                'code' => $status[0],
                'label' => $status[1],
                'shipment_status' => $status[2],
                'sort_order' => $status[3],
                'is_terminal' => $status[4],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $statuses
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('order_shipping_status_histories');
        Schema::dropIfExists('shipping_statuses');
    }
};
