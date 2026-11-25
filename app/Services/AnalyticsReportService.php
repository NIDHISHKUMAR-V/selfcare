<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Exercise;
use App\Models\FoodLog;
use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\PersonalGoal;
use App\Models\SleepLog;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WaterLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AnalyticsReportService
{
    /**
     * Generate comprehensive lifestyle report for a user across a given date range.
     *
     * @return array{
     *     period: string,
     *     start_date: string,
     *     end_date: string,
     *     days_count: int,
     *     food: array{total_meals: int, total_calories: float, avg_daily_calories: float, avg_protein: float, avg_carbs: float, avg_fat: float},
     *     water: array{total_ml: int, avg_daily_ml: int, target_achievement_rate: int},
     *     finance: array{total_income: float, total_expense: float, net_savings: float, top_expenses: Collection<int, mixed>},
     *     activities: array{total_tasks: int, completed_tasks: int, completion_rate: int},
     *     habits: array{total_habits: int, total_completions: int, consistency_rate: int},
     *     exercise: array{total_workouts: int, total_duration_mins: int, total_calories_burned: float, avg_workout_mins: int},
     *     sleep: array{logged_nights: int, avg_duration_hours: float, avg_quality_score: float},
     *     goals: array{total_goals: int, completed_goals: int, in_progress_goals: int, avg_progress: int},
     *     chart_timeline: array<int, array{date: string, label: string, calories: float, water: int, expense: float, exercise: int, sleep: float}>
     * }
     */
    public function getPerformanceReport(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $startStr = $startDate->toDateString();
        $endStr = $endDate->toDateString();
        $daysCount = max(1, $startDate->diffInDays($endDate) + 1);

        // 1. Food & Nutrition
        $foodLogs = FoodLog::where('user_id', $user->id)
            ->whereBetween('meal_date', [$startStr, $endStr])
            ->get();

        $totalMeals = $foodLogs->count();
        $totalCalories = (float) $foodLogs->sum('calories');
        $avgDailyCalories = round($totalCalories / $daysCount, 1);
        $avgProtein = round($foodLogs->sum('protein') / $daysCount, 1);
        $avgCarbs = round($foodLogs->sum('carbohydrates') / $daysCount, 1);
        $avgFat = round($foodLogs->sum('fat') / $daysCount, 1);

        // 2. Water
        $waterLogs = WaterLog::where('user_id', $user->id)
            ->whereBetween('log_date', [$startStr, $endStr])
            ->get();
        $totalWater = (int) $waterLogs->sum('amount_ml');
        $avgWater = (int) round($totalWater / $daysCount);
        $waterTarget = $user->daily_water_goal_ml ?: 2500;
        $waterDaysMet = $waterLogs->groupBy(fn ($w) => Carbon::parse($w->log_date)->toDateString())
            ->filter(fn ($dayGroup) => $dayGroup->sum('amount_ml') >= $waterTarget)
            ->count();
        $waterAchievementRate = (int) round(($waterDaysMet / $daysCount) * 100);

        // 3. Finances
        $transactions = Transaction::where('user_id', $user->id)
            ->whereBetween('transaction_date', [$startStr, $endStr])
            ->get();
        $totalIncome = (float) $transactions->where('type', 'income')->sum('amount');
        $totalExpense = (float) $transactions->where('type', 'expense')->sum('amount');
        $netSavings = $totalIncome - $totalExpense;
        $topExpenses = $transactions->where('type', 'expense')
            ->groupBy('category_id')
            ->map(fn ($group) => [
                'category' => $group->first()->category?->name ?? 'General',
                'amount' => (float) $group->sum('amount'),
            ])
            ->sortByDesc('amount')
            ->values()
            ->take(5);

        // 4. Activities & Tasks
        $activities = Activity::where('user_id', $user->id)
            ->whereBetween('activity_date', [$startStr, $endStr])
            ->get();
        $totalTasks = $activities->count();
        $completedTasks = $activities->where('status', 'completed')->count();
        $taskRate = $totalTasks > 0 ? (int) round(($completedTasks / $totalTasks) * 100) : 0;

        // 5. Habits
        $habitsCount = Habit::where('user_id', $user->id)->where('is_archived', false)->count();
        $habitLogsCount = HabitLog::where('user_id', $user->id)
            ->whereBetween('completed_date', [$startStr, $endStr])
            ->where('is_completed', true)
            ->count();
        $totalPossibleHabits = $habitsCount * $daysCount;
        $habitConsistencyRate = $totalPossibleHabits > 0 ? (int) round(($habitLogsCount / $totalPossibleHabits) * 100) : 0;

        // 6. Exercise
        $exercises = Exercise::where('user_id', $user->id)
            ->whereBetween('exercise_date', [$startStr, $endStr])
            ->get();
        $totalWorkouts = $exercises->count();
        $totalWorkoutMins = (int) $exercises->sum('duration_minutes');
        $totalWorkoutCals = (float) $exercises->sum('calories_burned');
        $avgWorkoutMins = $totalWorkouts > 0 ? (int) round($totalWorkoutMins / $totalWorkouts) : 0;

        // 7. Sleep
        $sleepLogs = SleepLog::where('user_id', $user->id)
            ->whereBetween('sleep_date', [$startStr, $endStr])
            ->get();
        $loggedNights = $sleepLogs->count();
        $avgSleepHours = $loggedNights > 0 ? round($sleepLogs->avg('duration_minutes') / 60, 1) : 0.0;
        $avgSleepQuality = $loggedNights > 0 ? round($sleepLogs->avg('quality_rating'), 1) : 0.0;

        // 8. Goals
        $goals = PersonalGoal::where('user_id', $user->id)->get();
        $totalGoals = $goals->count();
        $completedGoals = $goals->where('status', 'completed')->count();
        $inProgressGoals = $goals->where('status', 'in_progress')->count();
        $avgGoalProgress = $totalGoals > 0 ? (int) round($goals->avg('progress_percentage')) : 0;

        // Daily Chart Timeline aggregation
        $timeline = [];
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dStr = $curr->toDateString();
            $dFood = (float) $foodLogs->filter(fn ($f) => Carbon::parse($f->meal_date)->toDateString() === $dStr)->sum('calories');
            $dWater = (int) $waterLogs->filter(fn ($w) => Carbon::parse($w->log_date)->toDateString() === $dStr)->sum('amount_ml');
            $dExp = (float) $transactions->filter(fn ($t) => $t->type === 'expense' && Carbon::parse($t->transaction_date)->toDateString() === $dStr)->sum('amount');
            $dExer = (int) $exercises->filter(fn ($e) => Carbon::parse($e->exercise_date)->toDateString() === $dStr)->sum('duration_minutes');
            $dSleep = $sleepLogs->first(fn ($s) => Carbon::parse($s->sleep_date)->toDateString() === $dStr);

            $timeline[] = [
                'date' => $dStr,
                'label' => $curr->format('M d'),
                'calories' => $dFood,
                'water' => $dWater,
                'expense' => $dExp,
                'exercise' => $dExer,
                'sleep' => $dSleep ? round($dSleep->duration_minutes / 60, 1) : 0.0,
            ];

            $curr->addDay();
        }

        return [
            'period' => $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y'),
            'start_date' => $startStr,
            'end_date' => $endStr,
            'days_count' => $daysCount,
            'food' => [
                'total_meals' => $totalMeals,
                'total_calories' => $totalCalories,
                'avg_daily_calories' => $avgDailyCalories,
                'avg_protein' => $avgProtein,
                'avg_carbs' => $avgCarbs,
                'avg_fat' => $avgFat,
            ],
            'water' => [
                'total_ml' => $totalWater,
                'avg_daily_ml' => $avgWater,
                'target_achievement_rate' => $waterAchievementRate,
            ],
            'finance' => [
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'net_savings' => $netSavings,
                'top_expenses' => $topExpenses,
            ],
            'activities' => [
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'completion_rate' => $taskRate,
            ],
            'habits' => [
                'total_habits' => $habitsCount,
                'total_completions' => $habitLogsCount,
                'consistency_rate' => $habitConsistencyRate,
            ],
            'exercise' => [
                'total_workouts' => $totalWorkouts,
                'total_duration_mins' => $totalWorkoutMins,
                'total_calories_burned' => $totalWorkoutCals,
                'avg_workout_mins' => $avgWorkoutMins,
            ],
            'sleep' => [
                'logged_nights' => $loggedNights,
                'avg_duration_hours' => $avgSleepHours,
                'avg_quality_score' => $avgSleepQuality,
            ],
            'goals' => [
                'total_goals' => $totalGoals,
                'completed_goals' => $completedGoals,
                'in_progress_goals' => $inProgressGoals,
                'avg_progress' => $avgGoalProgress,
            ],
            'chart_timeline' => $timeline,
        ];
    }
}
