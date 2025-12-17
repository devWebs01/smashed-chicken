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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_address')->nullable();
            $table->dateTime('order_date_time')->nullable()->default(now());
            $table->string('payment_method')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled', 'draft', 'confirm'])->default('draft');
            $table->decimal('total_price', 10, 2);
            $table->enum('delivery_method', ['dine_in', 'takeaway', 'delivery']);
            $table->foreignId('device_id')->nullable()->constrained('devices')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
