<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('trip_id')->constrained('trips')->cascadeOnDelete()->cascadeOnUpdate();
            $table->enum('status', ['pending', 'accepted', 'cancelled', 'delivered'])->default('pending');
            $table->dateTime('order_date')->nullable();
            $table->dateTime('delivery_date')->nullable();
            $table->bigInteger('total_price')->nullable();
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
