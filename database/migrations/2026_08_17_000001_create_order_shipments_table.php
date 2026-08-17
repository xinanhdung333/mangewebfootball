<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_shipments', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')->unique();
            $table->string('provider', 30)->default('demo');
            $table->string('tracking_code', 80)->unique();
            $table->string('client_order_code', 80)->unique();
            $table->string('status', 40)->default('created');
            $table->decimal('pickup_lat', 10, 7);
            $table->decimal('pickup_lng', 10, 7);
            $table->decimal('delivery_lat', 10, 7);
            $table->decimal('delivery_lng', 10, 7);
            $table->decimal('shipper_lat', 10, 7);
            $table->decimal('shipper_lng', 10, 7);
            $table->json('route_points')->nullable();
            $table->json('provider_response')->nullable();
            $table->text('provider_error')->nullable();
            $table->timestamp('last_status_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_shipments');
    }
};
