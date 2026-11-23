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
        // 1. Food Items (Predefined or user custom food items)
        Schema::create('food_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name')->index();
            $table->string('serving_unit')->default('g'); // e.g., g, serving, cup, slice
            $table->decimal('serving_size', 8, 2)->default(100.00);
            $table->decimal('calories', 8, 2);
            $table->decimal('protein', 8, 2)->default(0.00);
            $table->decimal('carbohydrates', 8, 2)->default(0.00);
            $table->decimal('fat', 8, 2)->default(0.00);
            $table->decimal('fiber', 8, 2)->nullable()->default(0.00);
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        // 2. Food Logs
        Schema::create('food_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('food_item_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner', 'snack'])->default('snack')->index();
            $table->string('food_name');
            $table->decimal('quantity', 8, 2)->default(1.00);
            $table->string('serving_size')->nullable();
            $table->decimal('calories', 8, 2);
            $table->decimal('protein', 8, 2)->default(0.00);
            $table->decimal('carbohydrates', 8, 2)->default(0.00);
            $table->decimal('fat', 8, 2)->default(0.00);
            $table->date('meal_date')->index();
            $table->time('meal_time')->nullable();
            $table->text('notes')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        // 3. Water Logs
        Schema::create('water_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount_ml');
            $table->date('log_date')->index();
            $table->time('log_time')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('water_logs');
        Schema::dropIfExists('food_logs');
        Schema::dropIfExists('food_items');
    }
};
