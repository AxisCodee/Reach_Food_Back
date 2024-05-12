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
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->integer('stock_quantity')->nullable();
            $table->decimal('weight', 8, 2); // For the weight value
            $table->string('weight_unit'); // For the weight unit (kg, lb, etc.)
            $table->bigInteger('wholesale_price')->default(0);
            $table->bigInteger('retail_price')->default(0);
            $table->text('image')->nullable();
            $table->string('color')->nullable();
            $table->double('size')->nullable();
            $table->integer('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
