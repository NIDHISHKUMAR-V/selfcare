<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhaseThreeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;
    private ExpenseCategory $expenseCategory;
    private IncomeCategory $incomeCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Finance Tester',
            'email' => 'finance@example.com',
            'password' => Hash::make('password123'),
            'monthly_budget_limit' => 20000.00,
        ]);

        $this->otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->expenseCategory = ExpenseCategory::create([
            'user_id' => $this->user->id,
            'name' => 'Food & Dining',
            'icon' => 'utensils',
            'color' => '#F4A261',
        ]);

        $this->incomeCategory = IncomeCategory::create([
            'user_id' => $this->user->id,
            'name' => 'Software Consulting',
            'icon' => 'laptop',
            'color' => '#7FA68E',
        ]);
    }

    public function test_guest_cannot_access_finance_routes(): void
    {
        $this->get('/finance')->assertRedirect(route('login'));
        $this->get('/finance/create')->assertRedirect(route('login'));
        $this->get('/finance/budgets')->assertRedirect(route('login'));
        $this->get('/finance/savings')->assertRedirect(route('login'));
        $this->get('/finance/reports')->assertRedirect(route('login'));
        $this->get('/finance/export-csv')->assertRedirect(route('login'));
    }

    public function test_user_can_view_transactions_ledger(): void
    {
        Transaction::create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'title' => 'Healthy Groceries',
            'amount' => 450.00,
            'expense_category_id' => $this->expenseCategory->id,
            'payment_method' => 'UPI',
            'transaction_date' => Carbon::today(),
        ]);

        $response = $this->actingAs($this->user)->get('/finance');

        $response->assertStatus(200);
        $response->assertSee('Healthy Groceries');
        $response->assertSee('450.00');
        $response->assertSee('Food & Dining');
    }

    public function test_user_can_record_expense_with_receipt(): void
    {
        Storage::fake('public');

        $receipt = UploadedFile::fake()->image('receipt.jpg', 400, 400);

        $response = $this->actingAs($this->user)->post('/finance', [
            'type' => 'expense',
            'title' => 'Farmer Market',
            'amount' => 320.50,
            'expense_category_id' => $this->expenseCategory->id,
            'payment_method' => 'Card',
            'transaction_date' => Carbon::today()->toDateString(),
            'notes' => 'Organic vegetables and fruits',
            'receipt' => $receipt,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'expense',
            'title' => 'Farmer Market',
            'amount' => 320.50,
        ]);

        $tx = Transaction::where('title', 'Farmer Market')->first();
        $this->assertNotNull($tx->receipt_path);
        Storage::disk('public')->assertExists($tx->receipt_path);
    }

    public function test_user_can_record_income(): void
    {
        $response = $this->actingAs($this->user)->post('/finance', [
            'type' => 'income',
            'title' => 'Consulting Invoice #104',
            'amount' => 15000.00,
            'income_category_id' => $this->incomeCategory->id,
            'payment_method' => 'Bank Transfer',
            'transaction_date' => Carbon::today()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'income',
            'title' => 'Consulting Invoice #104',
            'amount' => 15000.00,
        ]);
    }

    public function test_user_can_edit_and_update_transaction(): void
    {
        $transaction = Transaction::create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'title' => 'Books',
            'amount' => 200.00,
            'expense_category_id' => $this->expenseCategory->id,
            'payment_method' => 'Cash',
            'transaction_date' => Carbon::today(),
        ]);

        $response = $this->actingAs($this->user)->get("/finance/{$transaction->id}/edit");
        $response->assertStatus(200);
        $response->assertSee('Books');

        $updateResponse = $this->actingAs($this->user)->put("/finance/{$transaction->id}", [
            'type' => 'expense',
            'title' => 'Self-Care & Nutrition Books',
            'amount' => 250.00,
            'expense_category_id' => $this->expenseCategory->id,
            'payment_method' => 'Card',
            'transaction_date' => Carbon::today()->toDateString(),
        ]);

        $updateResponse->assertRedirect();
        $transaction->refresh();

        $this->assertEquals('Self-Care & Nutrition Books', $transaction->title);
        $this->assertEquals(250.00, $transaction->amount);
    }

    public function test_user_can_delete_transaction(): void
    {
        $transaction = Transaction::create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'title' => 'Unwanted Sub',
            'amount' => 199.00,
            'payment_method' => 'UPI',
            'transaction_date' => Carbon::today(),
        ]);

        $response = $this->actingAs($this->user)->delete("/finance/{$transaction->id}");
        $response->assertRedirect();

        $this->assertSoftDeleted('transactions', ['id' => $transaction->id]);
    }

    public function test_user_cannot_modify_or_delete_another_users_transaction(): void
    {
        $otherTx = Transaction::create([
            'user_id' => $this->otherUser->id,
            'type' => 'expense',
            'title' => 'Private Expense',
            'amount' => 5000.00,
            'payment_method' => 'Cash',
            'transaction_date' => Carbon::today(),
        ]);

        $this->actingAs($this->user)->get("/finance/{$otherTx->id}/edit")->assertStatus(403);

        $this->actingAs($this->user)->put("/finance/{$otherTx->id}", [
            'type' => 'expense',
            'title' => 'Tampered',
            'amount' => 1.00,
            'expense_category_id' => $this->expenseCategory->id,
            'payment_method' => 'Cash',
            'transaction_date' => Carbon::today()->toDateString(),
        ])->assertStatus(403);

        $this->actingAs($this->user)->delete("/finance/{$otherTx->id}")->assertStatus(403);
    }

    public function test_user_can_manage_budgets_and_view_warning_status(): void
    {
        $currentMonth = Carbon::today()->format('Y-m');

        // Set budget for category
        $response = $this->actingAs($this->user)->post('/finance/budgets', [
            'expense_category_id' => $this->expenseCategory->id,
            'month_year' => $currentMonth,
            'amount_limit' => 2000.00,
            'alert_threshold_percentage' => 80,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('budgets', [
            'user_id' => $this->user->id,
            'expense_category_id' => $this->expenseCategory->id,
            'amount_limit' => 2000.00,
        ]);

        // Spend 1800 (90%, above 80% threshold -> warning)
        Transaction::create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'title' => 'Big Dinner',
            'amount' => 1800.00,
            'expense_category_id' => $this->expenseCategory->id,
            'payment_method' => 'Card',
            'transaction_date' => Carbon::today(),
        ]);

        $indexResponse = $this->actingAs($this->user)->get("/finance/budgets?month={$currentMonth}");
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Food & Dining');
        $indexResponse->assertSee('Near Limit');
    }

    public function test_user_can_manage_savings_goals_and_add_funds(): void
    {
        $response = $this->actingAs($this->user)->post('/finance/savings', [
            'goal_name' => 'Wellness Retreat',
            'target_amount' => 25000.00,
            'current_saved_amount' => 5000.00,
            'target_date' => Carbon::today()->addMonths(6)->toDateString(),
            'notes' => 'Mental health and rejuvenation trip',
        ]);

        $response->assertRedirect();
        $goal = SavingsGoal::where('goal_name', 'Wellness Retreat')->first();
        $this->assertNotNull($goal);
        $this->assertEquals(5000.00, $goal->current_saved_amount);

        // Add funds to reach target
        $fundResponse = $this->actingAs($this->user)->post("/finance/savings/{$goal->id}/add-funds", [
            'amount' => 20000.00,
        ]);

        $fundResponse->assertRedirect();
        $goal->refresh();

        $this->assertEquals(25000.00, $goal->current_saved_amount);
        $this->assertEquals('reached', $goal->status);
    }

    public function test_user_can_view_financial_reports(): void
    {
        $response = $this->actingAs($this->user)->get('/finance/reports');
        $response->assertStatus(200);
        $response->assertSee('Financial Performance Overview');
        $response->assertSee('6-Month Income vs Expense Trend');
    }

    public function test_user_can_export_transactions_as_csv(): void
    {
        Transaction::create([
            'user_id' => $this->user->id,
            'type' => 'income',
            'title' => 'Salary Deposit',
            'amount' => 45000.00,
            'payment_method' => 'Bank Transfer',
            'transaction_date' => Carbon::today(),
        ]);

        $response = $this->actingAs($this->user)->get('/finance/export-csv');

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
        $this->assertTrue(str_contains($response->headers->get('content-disposition'), 'transactions_'));
    }
}
