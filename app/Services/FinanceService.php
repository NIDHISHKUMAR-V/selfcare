<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\ExpenseCategory;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FinanceService
{
    /**
     * Get high-level monthly financial metrics for a user.
     *
     * @return array{
     *     month_year: string,
     *     month_label: string,
     *     total_income: float,
     *     total_expenses: float,
     *     net_balance: float,
     *     savings_rate: int,
     *     today_expenses: float,
     *     week_expenses: float,
     *     monthly_budget_limit: float,
     *     budget_spent_percent: int,
     *     budget_remaining: float,
     *     budget_status: string
     * }
     */
    public function getMonthlySummary(User $user, ?string $monthYear = null): array
    {
        $monthYear = $monthYear ?: Carbon::today()->format('Y-m');
        $date = Carbon::createFromFormat('Y-m', $monthYear)->startOfMonth();
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $today = Carbon::today();
        $startOfWeek = Carbon::today()->startOfWeek();

        $monthlyIncome = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->sum('amount');

        $monthlyExpenses = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->sum('amount');

        $todayExpenses = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereDate('transaction_date', $today)
            ->sum('amount');

        $weekExpenses = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startOfWeek->toDateString(), $today->toDateString()])
            ->sum('amount');

        $netBalance = $monthlyIncome - $monthlyExpenses;
        $savingsRate = $monthlyIncome > 0 ? (int) max(0, round(($netBalance / $monthlyIncome) * 100)) : 0;

        // Check for overall budget
        $overallBudget = Budget::where('user_id', $user->id)
            ->whereNull('expense_category_id')
            ->where('month_year', $monthYear)
            ->first();

        $budgetLimit = $overallBudget ? (float) $overallBudget->amount_limit : (float) ($user->monthly_budget_limit ?: 0.0);
        $budgetPercent = $budgetLimit > 0 ? (int) round(($monthlyExpenses / $budgetLimit) * 100) : 0;
        $budgetRemaining = max(0.0, $budgetLimit - $monthlyExpenses);

        $threshold = $overallBudget ? $overallBudget->alert_threshold_percentage : 80;
        $budgetStatus = 'normal';
        if ($budgetLimit > 0) {
            if ($monthlyExpenses > $budgetLimit) {
                $budgetStatus = 'exceeded';
            } elseif ($budgetPercent >= $threshold) {
                $budgetStatus = 'warning';
            }
        }

        return [
            'month_year' => $monthYear,
            'month_label' => $date->format('F Y'),
            'total_income' => $monthlyIncome,
            'total_expenses' => $monthlyExpenses,
            'net_balance' => $netBalance,
            'savings_rate' => $savingsRate,
            'today_expenses' => $todayExpenses,
            'week_expenses' => $weekExpenses,
            'monthly_budget_limit' => $budgetLimit,
            'budget_spent_percent' => $budgetPercent,
            'budget_remaining' => $budgetRemaining,
            'budget_status' => $budgetStatus,
        ];
    }

    /**
     * Get category-wise budgets and their actual spending for a given month.
     *
     * @return Collection<int, array{
     *     category_id: int,
     *     category_name: string,
     *     category_color: string,
     *     category_icon: string,
     *     budget_id: ?int,
     *     amount_limit: float,
     *     spent: float,
     *     remaining: float,
     *     percent_used: int,
     *     status: string
     * }>
     */
    public function getBudgetsWithSpending(User $user, string $monthYear): Collection
    {
        $date = Carbon::createFromFormat('Y-m', $monthYear);
        $startOfMonth = $date->copy()->startOfMonth()->toDateString();
        $endOfMonth = $date->copy()->endOfMonth()->toDateString();

        $categories = ExpenseCategory::where('user_id', $user->id)
            ->orWhereNull('user_id')
            ->orderBy('name')
            ->get();

        $budgets = Budget::where('user_id', $user->id)
            ->where('month_year', $monthYear)
            ->whereNotNull('expense_category_id')
            ->get()
            ->keyBy('expense_category_id');

        $expensesByCategory = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->whereNotNull('expense_category_id')
            ->selectRaw('expense_category_id, SUM(amount) as total_spent')
            ->groupBy('expense_category_id')
            ->pluck('total_spent', 'expense_category_id');

        return $categories->map(function (ExpenseCategory $cat) use ($budgets, $expensesByCategory): array {
            $budget = $budgets->get($cat->id);
            $limit = $budget ? (float) $budget->amount_limit : 0.0;
            $spent = (float) ($expensesByCategory->get($cat->id) ?? 0.0);
            $remaining = max(0.0, $limit - $spent);
            $percent = $limit > 0 ? (int) round(($spent / $limit) * 100) : 0;

            $threshold = $budget ? $budget->alert_threshold_percentage : 80;
            $status = 'normal';
            if ($limit > 0) {
                if ($spent > $limit) {
                    $status = 'exceeded';
                } elseif ($percent >= $threshold) {
                    $status = 'warning';
                }
            }

            return [
                'category_id' => $cat->id,
                'category_name' => $cat->name,
                'category_color' => $cat->color,
                'category_icon' => $cat->icon,
                'budget_id' => $budget?->id,
                'amount_limit' => $limit,
                'spent' => $spent,
                'remaining' => $remaining,
                'percent_used' => $percent,
                'status' => $status,
            ];
        });
    }

    /**
     * Get multi-month trends and category distributions for Chart.js reports.
     *
     * @return array{
     *     months_trend: array<int, array{month: string, label: string, income: float, expense: float, net: float}>,
     *     category_distribution: array{labels: array<int, string>, data: array<int, float>, colors: array<int, string>},
     *     daily_expenses: array<int, array{day: string, amount: float}>
     * }
     */
    public function getFinancialReportsData(User $user, int $monthsCount = 6, ?string $monthYear = null): array
    {
        $selectedMonth = $monthYear ? Carbon::createFromFormat('Y-m', $monthYear) : Carbon::today();

        // 1. Multi-month Income vs Expense Trend
        $monthsTrend = [];
        for ($i = $monthsCount - 1; $i >= 0; $i--) {
            $m = Carbon::today()->subMonths($i);
            $start = $m->copy()->startOfMonth()->toDateString();
            $end = $m->copy()->endOfMonth()->toDateString();

            $inc = (float) Transaction::where('user_id', $user->id)
                ->where('type', 'income')
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('amount');

            $exp = (float) Transaction::where('user_id', $user->id)
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('amount');

            $monthsTrend[] = [
                'month' => $m->format('Y-m'),
                'label' => $m->format('M Y'),
                'income' => $inc,
                'expense' => $exp,
                'net' => $inc - $exp,
            ];
        }

        // 2. Category-wise expense distribution for selected month
        $startSelected = $selectedMonth->copy()->startOfMonth()->toDateString();
        $endSelected = $selectedMonth->copy()->endOfMonth()->toDateString();

        $categoryExpenses = Transaction::where('transactions.user_id', $user->id)
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.transaction_date', [$startSelected, $endSelected])
            ->join('expense_categories', 'transactions.expense_category_id', '=', 'expense_categories.id')
            ->selectRaw('expense_categories.name, expense_categories.color, SUM(transactions.amount) as total')
            ->groupBy('expense_categories.name', 'expense_categories.color')
            ->orderByDesc('total')
            ->get();

        $catLabels = [];
        $catData = [];
        $catColors = [];
        foreach ($categoryExpenses as $row) {
            $catLabels[] = $row->name;
            $catData[] = (float) $row->total;
            $catColors[] = $row->color ?: '#8E8CD8';
        }

        // 3. Daily expense trend for selected month
        $daysInMonth = $selectedMonth->daysInMonth;
        $dailyExpenses = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dayDate = $selectedMonth->copy()->day($d);
            if ($dayDate->gt(Carbon::today()) && $selectedMonth->isCurrentMonth()) {
                break;
            }

            $amount = (float) Transaction::where('user_id', $user->id)
                ->where('type', 'expense')
                ->whereDate('transaction_date', $dayDate)
                ->sum('amount');

            $dailyExpenses[] = [
                'day' => $dayDate->format('d M'),
                'amount' => $amount,
            ];
        }

        return [
            'months_trend' => $monthsTrend,
            'category_distribution' => [
                'labels' => $catLabels,
                'data' => $catData,
                'colors' => $catColors,
            ],
            'daily_expenses' => $dailyExpenses,
        ];
    }

    /**
     * Get savings goals summary.
     *
     * @return array{
     *     goals: Collection<int, SavingsGoal>,
     *     total_target: float,
     *     total_saved: float,
     *     overall_progress: int
     * }
     */
    public function getSavingsSummary(User $user): array
    {
        $goals = SavingsGoal::where('user_id', $user->id)
            ->orderBy('status')
            ->orderBy('target_date')
            ->get();

        $totalTarget = (float) $goals->where('status', '!=', 'cancelled')->sum('target_amount');
        $totalSaved = (float) $goals->where('status', '!=', 'cancelled')->sum('current_saved_amount');
        $overallProgress = $totalTarget > 0 ? (int) min(100, round(($totalSaved / $totalTarget) * 100)) : 0;

        return [
            'goals' => $goals,
            'total_target' => $totalTarget,
            'total_saved' => $totalSaved,
            'overall_progress' => $overallProgress,
        ];
    }
}
