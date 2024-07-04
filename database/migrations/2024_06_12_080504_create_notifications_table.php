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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->enum('action_type', ['add', 'delete', 'update', 'trace', 'cancel', 'change_price', 'start_trip', 'late'])->nullable();
            $table->nullableMorphs('actionable');
            //who do this action
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
