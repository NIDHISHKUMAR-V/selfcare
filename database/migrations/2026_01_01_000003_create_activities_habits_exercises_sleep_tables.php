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
        // 1. Daily Activities & Tasks
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->default('General'); // Work, Self-Care, Personal, Study, Health
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending')->index();
            $table->date('activity_date')->index();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // 2. Habits
        Schema::create('habits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->default('Health'); // Health, Mindset, Productivity, Fitness
            $table->string('frequency')->default('daily'); // daily, weekdays, weekends
            $table->unsignedTinyInteger('target_days_per_week')->default(7);
            $table->string('color')->default('#7FA68E');
            $table->string('icon')->default('check-circle');
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });

        // 3. Habit Logs
        Schema::create('habit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('completed_date')->index();
            $table->boolean('is_completed')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['habit_id', 'completed_date']);
        });

        // 4. Exercises
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('activity_type')->index(); // Walking, Running, Cycling, Gym, Yoga, Swimming, etc.
            $table->unsignedInteger('duration_minutes');
            $table->decimal('distance_km', 6, 2)->nullable();
            $table->decimal('calories_burned', 8, 2)->nullable();
            $table->date('exercise_date')->index();
            $table->time('exercise_time')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Sleep Logs
        Schema::create('sleep_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('sleep_date')->index();
            $table->dateTime('sleep_time');
            $table->dateTime('wake_time');
            $table->unsignedInteger('duration_minutes');
            $table->unsignedTinyInteger('quality_rating')->default(3); // 1-5 scale (1: Poor, 3: Good, 5: Excellent)
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sleep_logs');
        Schema::dropIfExists('exercises');
        Schema::dropIfExists('habit_logs');
        Schema::dropIfExists('habits');
        Schema::dropIfExists('activities');
    }
};
