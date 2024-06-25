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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->decimal('amount', 8, 2)->nullable(); // For the weight value
            $table->string('amount_unit'); // For the weight unit (kg, lb, etc.)
            $table->bigInteger('wholesale_price')->default(0);
            $table->bigInteger('retail_price')->default(0);
            $table->text('image')->nullable();
            $table->integer('status')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
          Schema::table('products', function (Blueprint $table) {
        $table->dropIndex('category_id');
    });
        Schema::dropIfExists('products');
    }
};
