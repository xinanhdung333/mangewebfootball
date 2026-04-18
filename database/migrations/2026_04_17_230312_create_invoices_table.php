<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {

            $table->id();

            // booking.id = bigint unsigned
            $table->unsignedBigInteger('booking_id')->nullable();

            // services.id = integer
            $table->integer('service_id')->nullable();

            $table->string('invoice_code')->unique();

            $table->decimal('total_amount', 10, 2);

            $table->timestamp('issued_at')->nullable();

            $table->timestamps();


            // foreign key booking
            $table->foreign('booking_id')
                  ->references('id')
                  ->on('bookings')
                  ->cascadeOnDelete();


            // foreign key service
            $table->foreign('service_id')
                  ->references('id')
                  ->on('services')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};