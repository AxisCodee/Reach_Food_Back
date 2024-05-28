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
        Schema::create('trip_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->nullable()->constrained('trips')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('address_id')->constrained('addresses')->cascadeOnDelete()->cascadeOnUpdate();
            $table->date('start_date')->nullable();
            $table->time('start_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_dates');
    }
};
