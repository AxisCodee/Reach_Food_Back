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
        Schema::create('trip_traces', function (Blueprint $table) {
            $table->id();
            $table->time('duration')->nullable();
            $table->enum('status', ['start', 'pause', 'resume', 'stop'])->nullable();
            $table->foreignId('trip_date_id')->unique()->constrained('trip_dates')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_traces');
    }
};
