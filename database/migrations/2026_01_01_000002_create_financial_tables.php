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
        // 1. Income Categories
        Schema::create('income_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('icon')->default('wallet');
            $table->string('color')->default('#7FA68E');
            $table->timestamps();
        });

        // 2. Expense Categories
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('icon')->default('tag');
            $table->string('color')->default('#8E8CD8');
            $table->timestamps();
        });

        // 3. Transactions (Income and Expenses)
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['income', 'expense'])->index();
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->foreignId('income_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('expense_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payment_method')->default('Cash'); // Cash, Card, Bank Transfer, Digital Wallet, Other
            $table->date('transaction_date')->index();
            $table->text('notes')->nullable();
            $table->string('receipt_path')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // 4. Budgets
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expense_category_id')->nullable()->constrained()->nullOnDelete(); // null = overall monthly budget
            $table->string('month_year', 7)->index(); // YYYY-MM
            $table->decimal('amount_limit', 12, 2);
            $table->unsignedInteger('alert_threshold_percentage')->default(80);
            $table->timestamps();
        });

        // 5. Savings Goals
        Schema::create('savings_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('goal_name');
            $table->decimal('target_amount', 12, 2);
            $table->decimal('current_saved_amount', 12, 2)->default(0.00);
            $table->date('target_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'reached', 'cancelled'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_goals');
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('income_categories');
    }
};
