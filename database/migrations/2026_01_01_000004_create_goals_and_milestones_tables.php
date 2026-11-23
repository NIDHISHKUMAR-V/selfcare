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
        // 1. Personal Goals
        Schema::create('personal_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('category', ['Health', 'Finance', 'Fitness', 'Career', 'Education', 'Personal Development'])->default('Personal Development');
            $table->enum('goal_type', ['short_term', 'long_term'])->default('short_term');
            $table->date('target_date')->nullable();
            $table->unsignedTinyInteger('progress_percentage')->default(0);
            $table->enum('status', ['in_progress', 'completed', 'on_hold'])->default('in_progress')->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // 2. Goal Milestones
        Schema::create('goal_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personal_goal_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->boolean('is_completed')->default(false);
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goal_milestones');
        Schema::dropIfExists('personal_goals');
    }
};
