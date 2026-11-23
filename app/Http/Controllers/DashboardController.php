<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\FoodLog;
use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\SleepLog;
use App\Models\Transaction;
use App\Models\WaterLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $today = Carbon::today();
        $currentMonth = $today->format('Y-m');

        // ---- Food Overview ----
        $todayFoodLogs = FoodLog::where('user_id', $user->id)
            ->where('meal_date', $today)
            ->get();

        $todayCalories = $todayFoodLogs->sum('calories');
        $todayMealsCount = $todayFoodLogs->count();

        // ---- Water Intake ----
        $todayWaterMl = WaterLog::where('user_id', $user->id)
            ->where('log_date', $today)
            ->sum('amount_ml');

        $waterGoalMl = $user->daily_water_goal_ml ?: 2500;
        $waterProgressPercent = $waterGoalMl > 0
            ? min(100, round(($todayWaterMl / $waterGoalMl) * 100))
            : 0;

        // ---- Financial Overview ----
        $monthlyIncome = Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereYear('transaction_date', $today->year)
            ->whereMonth('transaction_date', $today->month)
            ->sum('amount');

        $monthlyExpenses = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereYear('transaction_date', $today->year)
            ->whereMonth('transaction_date', $today->month)
            ->sum('amount');

        $todayExpenses = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->where('transaction_date', $today)
            ->sum('amount');

        $currentBalance = $monthlyIncome - $monthlyExpenses;

        // ---- Daily Activities ----
        $todayActivities = Activity::where('user_id', $user->id)
            ->where('activity_date', $today)
            ->get();

        $completedTasksCount = $todayActivities->where('status', 'completed')->count();
        $pendingTasksCount = $todayActivities->whereIn('status', ['pending', 'in_progress'])->count();

        // ---- Habits ----
        $userHabits = Habit::where('user_id', $user->id)
            ->where('is_archived', false)
            ->get();

        $completedHabitIds = HabitLog::where('user_id', $user->id)
            ->where('completed_date', $today)
            ->where('is_completed', true)
            ->pluck('habit_id');

        $completedHabitsCount = $completedHabitIds->count();
        $totalHabitsCount = $userHabits->count();
        $habitCompletionPercent = $totalHabitsCount > 0
            ? round(($completedHabitsCount / $totalHabitsCount) * 100)
            : 0;

        // ---- Sleep ----
        $lastSleep = SleepLog::where('user_id', $user->id)
            ->orderByDesc('sleep_date')
            ->first();

        // ---- Recent Transactions ----
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->with(['incomeCategory', 'expenseCategory'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // ---- Weekly calorie chart data (last 7 days) ----
        $weeklyCalories = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $calories = FoodLog::where('user_id', $user->id)
                ->where('meal_date', $date)
                ->sum('calories');
            $weeklyCalories->push(['date' => $date->format('D'), 'calories' => (float) $calories]);
        }

        // ---- Motivational Quote ----
        $quotes = [
            ['quote' => 'Take care of your body. It\'s the only place you have to live.', 'author' => 'Jim Rohn'],
            ['quote' => 'Health is not about the weight you lose, but the life you gain.', 'author' => 'Unknown'],
            ['quote' => 'The secret of getting ahead is getting started.', 'author' => 'Mark Twain'],
            ['quote' => 'An investment in knowledge pays the best interest.', 'author' => 'Benjamin Franklin'],
            ['quote' => 'You are never too old to set another goal or to dream a new dream.', 'author' => 'C.S. Lewis'],
            ['quote' => 'Small steps in the right direction can turn out to be the biggest step of your life.', 'author' => 'Unknown'],
            ['quote' => 'Your health is your wealth. Invest in it daily.', 'author' => 'Unknown'],
        ];
        $dailyQuote = $quotes[array_key_first($quotes) + ($today->dayOfWeek % count($quotes))];

        return view('dashboard.index', compact(
            'todayCalories',
            'todayMealsCount',
            'todayWaterMl',
            'waterGoalMl',
            'waterProgressPercent',
            'monthlyIncome',
            'monthlyExpenses',
            'todayExpenses',
            'currentBalance',
            'completedTasksCount',
            'pendingTasksCount',
            'completedHabitsCount',
            'totalHabitsCount',
            'habitCompletionPercent',
            'lastSleep',
            'recentTransactions',
            'weeklyCalories',
            'dailyQuote',
            'user',
        ));
    }
}
