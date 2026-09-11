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
        // 1. bookings table
        Schema::table('bookings', function (Blueprint $table) {
            try { $table->index(['field_id', 'booking_date', 'status'], 'bookings_field_date_status_index'); } catch (\Exception $e) {}
            try { $table->index(['user_id', 'status'], 'bookings_user_status_index'); } catch (\Exception $e) {}
            try { $table->index(['status', 'created_at'], 'bookings_status_created_index'); } catch (\Exception $e) {}
        });

        // 2. orders table
        Schema::table('orders', function (Blueprint $table) {
            try { $table->index(['user_id', 'status'], 'orders_user_status_index'); } catch (\Exception $e) {}
            try { $table->index(['status', 'created_at'], 'orders_status_created_index'); } catch (\Exception $e) {}
        });

        // 3. order_items table
        Schema::table('order_items', function (Blueprint $table) {
            try { $table->index(['order_id', 'service_id'], 'order_items_order_service_index'); } catch (\Exception $e) {}
        });

        // 4. cart_items table
        Schema::table('cart_items', function (Blueprint $table) {
            try { $table->index(['cart_id', 'service_id'], 'cart_items_cart_service_index'); } catch (\Exception $e) {}
        });

        // 5. services table
        Schema::table('services', function (Blueprint $table) {
            try { $table->index(['status', 'category_id'], 'services_status_category_index'); } catch (\Exception $e) {}
            try { $table->index(['status', 'created_at'], 'services_status_created_index'); } catch (\Exception $e) {}
        });

        // 6. feedbacks table
        Schema::table('feedbacks', function (Blueprint $table) {
            try { $table->index(['service_id', 'rating'], 'feedbacks_service_rating_index'); } catch (\Exception $e) {}
            try { $table->index(['booking_id'], 'feedbacks_booking_index'); } catch (\Exception $e) {}
        });

        // 7. payments table
        Schema::table('payments', function (Blueprint $table) {
            try { $table->index(['order_id', 'status'], 'payments_order_status_index'); } catch (\Exception $e) {}
        });

        // 8. booking_payments table
        Schema::table('booking_payments', function (Blueprint $table) {
            try { $table->index(['booking_id', 'status'], 'booking_payments_booking_status_index'); } catch (\Exception $e) {}
        });

        // 9. booking_services table
        Schema::table('booking_services', function (Blueprint $table) {
            try { $table->index(['booking_id', 'service_id'], 'booking_services_booking_service_index'); } catch (\Exception $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            try { $table->dropIndex('bookings_field_date_status_index'); } catch (\Exception $e) {}
            try { $table->dropIndex('bookings_user_status_index'); } catch (\Exception $e) {}
            try { $table->dropIndex('bookings_status_created_index'); } catch (\Exception $e) {}
        });

        Schema::table('orders', function (Blueprint $table) {
            try { $table->dropIndex('orders_user_status_index'); } catch (\Exception $e) {}
            try { $table->dropIndex('orders_status_created_index'); } catch (\Exception $e) {}
        });

        Schema::table('order_items', function (Blueprint $table) {
            try { $table->dropIndex('order_items_order_service_index'); } catch (\Exception $e) {}
        });

        Schema::table('cart_items', function (Blueprint $table) {
            try { $table->dropIndex('cart_items_cart_service_index'); } catch (\Exception $e) {}
        });

        Schema::table('services', function (Blueprint $table) {
            try { $table->dropIndex('services_status_category_index'); } catch (\Exception $e) {}
            try { $table->dropIndex('services_status_created_index'); } catch (\Exception $e) {}
        });

        Schema::table('feedbacks', function (Blueprint $table) {
            try { $table->dropIndex('feedbacks_service_rating_index'); } catch (\Exception $e) {}
            try { $table->dropIndex('feedbacks_booking_index'); } catch (\Exception $e) {}
        });

        Schema::table('payments', function (Blueprint $table) {
            try { $table->dropIndex('payments_order_status_index'); } catch (\Exception $e) {}
        });

        Schema::table('booking_payments', function (Blueprint $table) {
            try { $table->dropIndex('booking_payments_booking_status_index'); } catch (\Exception $e) {}
        });

        Schema::table('booking_services', function (Blueprint $table) {
            try { $table->dropIndex('booking_services_booking_service_index'); } catch (\Exception $e) {}
        });
    }
};
