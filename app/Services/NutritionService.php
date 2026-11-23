<?php

namespace App\Services;

use App\Models\FoodLog;
use App\Models\User;
use App\Models\WaterLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class NutritionService
{
    /**
     * Get aggregated daily nutrition metrics for a specific user and date.
     *
     * @return array{
     *     date: string,
     *     total_calories: float,
     *     calorie_goal: int,
     *     calorie_percentage: int,
     *     total_protein: float,
     *     total_carbs: float,
     *     total_fat: float,
     *     meals_count: int,
     *     macro_percentages: array{protein: int, carbs: int, fat: int}
     * }
     */
    public function getDailyNutritionSummary(User $user, Carbon $date): array
    {
        $logs = FoodLog::where('user_id', $user->id)
            ->whereDate('meal_date', $date)
            ->get();

        $totalCalories = (float) $logs->sum('calories');
        $totalProtein = (float) $logs->sum('protein');
        $totalCarbs = (float) $logs->sum('carbohydrates');
        $totalFat = (float) $logs->sum('fat');
        $mealsCount = $logs->count();

        $calorieGoal = $user->daily_calorie_goal ?: 2000;
        $caloriePercentage = $calorieGoal > 0 ? (int) min(100, round(($totalCalories / $calorieGoal) * 100)) : 0;

        // Macro percentages based on grams
        $macroGrams = $totalProtein + $totalCarbs + $totalFat;
        $macroPercentages = [
            'protein' => $macroGrams > 0 ? (int) round(($totalProtein / $macroGrams) * 100) : 0,
            'carbs' => $macroGrams > 0 ? (int) round(($totalCarbs / $macroGrams) * 100) : 0,
            'fat' => $macroGrams > 0 ? (int) round(($totalFat / $macroGrams) * 100) : 0,
        ];

        return [
            'date' => $date->toDateString(),
            'total_calories' => $totalCalories,
            'calorie_goal' => $calorieGoal,
            'calorie_percentage' => $caloriePercentage,
            'total_protein' => $totalProtein,
            'total_carbs' => $totalCarbs,
            'total_fat' => $totalFat,
            'meals_count' => $mealsCount,
            'macro_percentages' => $macroPercentages,
        ];
    }

    /**
     * Get daily water metrics for a specific user and date.
     *
     * @return array{
     *     date: string,
     *     total_water_ml: int,
     *     water_goal_ml: int,
     *     remaining_ml: int,
     *     progress_percentage: int,
     *     glasses_count: float
     * }
     */
    public function getDailyWaterSummary(User $user, Carbon $date): array
    {
        $totalWater = (int) WaterLog::where('user_id', $user->id)
            ->whereDate('log_date', $date)
            ->sum('amount_ml');

        $waterGoal = $user->daily_water_goal_ml ?: 2500;
        $remaining = max(0, $waterGoal - $totalWater);
        $progress = $waterGoal > 0 ? (int) min(100, round(($totalWater / $waterGoal) * 100)) : 0;
        $glasses = round($totalWater / 250, 1);

        return [
            'date' => $date->toDateString(),
            'total_water_ml' => $totalWater,
            'water_goal_ml' => $waterGoal,
            'remaining_ml' => $remaining,
            'progress_percentage' => $progress,
            'glasses_count' => $glasses,
        ];
    }

    /**
     * Get food logs grouped by meal type for a specific day.
     *
     * @return Collection<string, Collection<int, FoodLog>>
     */
    public function getMealsGroupedByType(User $user, Carbon $date): Collection
    {
        $logs = FoodLog::where('user_id', $user->id)
            ->whereDate('meal_date', $date)
            ->orderBy('meal_time', 'asc')
            ->get();

        $types = collect(['breakfast', 'lunch', 'dinner', 'snack']);

        return $types->mapWithKeys(function (string $type) use ($logs): array {
            return [$type => $logs->where('meal_type', $type)->values()];
        });
    }

    /**
     * Generate weekly and monthly analytics reports.
     *
     * @return array{
     *     period: string,
     *     start_date: string,
     *     end_date: string,
     *     average_daily_calories: float,
     *     total_meals_logged: int,
     *     highest_calorie_day: array{date: string, calories: float},
     *     lowest_calorie_day: array{date: string, calories: float},
     *     total_protein: float,
     *     total_carbs: float,
     *     total_fat: float,
     *     daily_calorie_trend: array<int, array{date: string, label: string, calories: float}>,
     *     macro_breakdown: array{protein: int, carbs: int, fat: int}
     * }
     */
    public function getNutritionReport(User $user, string $period = 'weekly'): array
    {
        $endDate = Carbon::today();
        $startDate = $period === 'monthly'
            ? Carbon::today()->subDays(29)
            : Carbon::today()->subDays(6);

        $logs = FoodLog::where('user_id', $user->id)
            ->whereBetween('meal_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $daysCount = $startDate->diffInDays($endDate) + 1;

        // Group by date
        $dailyTrend = [];
        $current = $startDate->copy();
        $highest = ['date' => 'N/A', 'calories' => 0.0];
        $lowest = ['date' => 'N/A', 'calories' => PHP_FLOAT_MAX];

        while ($current->lte($endDate)) {
            $dateString = $current->toDateString();
            $dayLogs = $logs->filter(fn (FoodLog $log) => $log->meal_date->toDateString() === $dateString);
            $dayCalories = (float) $dayLogs->sum('calories');

            $dailyTrend[] = [
                'date' => $dateString,
                'label' => $current->format('D, M d'),
                'calories' => $dayCalories,
            ];

            if ($dayCalories > $highest['calories']) {
                $highest = ['date' => $current->format('M d, Y'), 'calories' => $dayCalories];
            }

            if ($dayCalories > 0 && $dayCalories < $lowest['calories']) {
                $lowest = ['date' => $current->format('M d, Y'), 'calories' => $dayCalories];
            }

            $current->addDay();
        }

        if ($lowest['calories'] === PHP_FLOAT_MAX) {
            $lowest['calories'] = 0.0;
        }

        $totalCalories = (float) $logs->sum('calories');
        $averageCalories = round($totalCalories / max(1, $daysCount), 1);
        $totalProtein = (float) $logs->sum('protein');
        $totalCarbs = (float) $logs->sum('carbohydrates');
        $totalFat = (float) $logs->sum('fat');

        $totalMacros = $totalProtein + $totalCarbs + $totalFat;
        $macroBreakdown = [
            'protein' => $totalMacros > 0 ? (int) round(($totalProtein / $totalMacros) * 100) : 0,
            'carbs' => $totalMacros > 0 ? (int) round(($totalCarbs / $totalMacros) * 100) : 0,
            'fat' => $totalMacros > 0 ? (int) round(($totalFat / $totalMacros) * 100) : 0,
        ];

        return [
            'period' => $period,
            'start_date' => $startDate->format('M d, Y'),
            'end_date' => $endDate->format('M d, Y'),
            'average_daily_calories' => $averageCalories,
            'total_meals_logged' => $logs->count(),
            'highest_calorie_day' => $highest,
            'lowest_calorie_day' => $lowest,
            'total_protein' => $totalProtein,
            'total_carbs' => $totalCarbs,
            'total_fat' => $totalFat,
            'daily_calorie_trend' => $dailyTrend,
            'macro_breakdown' => $macroBreakdown,
        ];
    }
}
