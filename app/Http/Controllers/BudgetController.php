<?php

namespace App\Http\Controllers;

use App\Http\Requests\BudgetRequest;
use App\Models\Budget;
use App\Models\ExpenseCategory;
use App\Services\FinanceService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function __construct(
        protected FinanceService $financeService
    ) {}

    /**
     * Display budget management overview.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $month = $request->query('month', Carbon::today()->format('Y-m'));

        $summary = $this->financeService->getMonthlySummary($user, $month);
        $categoryBudgets = $this->financeService->getBudgetsWithSpending($user, $month);
        $categories = ExpenseCategory::where('user_id', $user->id)->orWhereNull('user_id')->get();

        return view('finance.budgets', [
            'summary' => $summary,
            'categoryBudgets' => $categoryBudgets,
            'categories' => $categories,
            'currentMonth' => $month,
        ]);
    }

    /**
     * Store or update a budget limit.
     */
    public function store(BudgetRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = auth()->user();

        Budget::updateOrCreate(
            [
                'user_id' => $user->id,
                'expense_category_id' => $data['expense_category_id'] ?? null,
                'month_year' => $data['month_year'],
            ],
            [
                'amount_limit' => $data['amount_limit'],
                'alert_threshold_percentage' => $data['alert_threshold_percentage'] ?? 80,
            ]
        );

        // If setting overall monthly budget limit, synchronize with user's profile limit
        if (empty($data['expense_category_id'])) {
            $user->update(['monthly_budget_limit' => $data['amount_limit']]);
        }

        return redirect()->route('budgets.index', ['month' => $data['month_year']])
            ->with('success', 'Budget limit saved successfully!');
    }

    /**
     * Delete a budget limit.
     */
    public function destroy(Budget $budget): RedirectResponse
    {
        if ($budget->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to budget.');
        }

        $month = $budget->month_year;
        $budget->delete();

        return redirect()->route('budgets.index', ['month' => $month])
            ->with('success', 'Budget limit removed.');
    }
}
