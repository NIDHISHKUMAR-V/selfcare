<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Exercise;
use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\SleepLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ActivityService
{
    /**
     * Get tasks & activities overview for a specific day.
     *
     * @return array{
     *     date: string,
     *     activities: Collection<int, Activity>,
     *     total: int,
     *     completed: int,
     *     pending: int,
     *     in_progress: int,
     *     completion_rate: int
     * }
     */
    public function getDailyActivities(User $user, Carbon $date): array
    {
        $activities = Activity::where('user_id', $user->id)
            ->whereDate('activity_date', $date)
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END")
            ->orderBy('start_time')
            ->get();

        $total = $activities->count();
        $completed = $activities->where('status', 'completed')->count();
        $pending = $activities->where('status', 'pending')->count();
        $inProgress = $activities->where('status', 'in_progress')->count();

        $rate = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return [
            'date' => $date->toDateString(),
            'activities' => $activities,
            'total' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'completion_rate' => $rate,
        ];
    }

    /**
     * Get habits overview: today's checklist, weekly completion %, and monthly calendar grid.
     *
     * @return array{
     *     habits: Collection<int, Habit>,
     *     today_completed_ids: array<int, int>,
     *     today_completed_count: int,
     *     total_habits_count: int,
     *     today_percent: int,
     *     weekly_percent: int,
     *     calendar_days: array<int, array{date: string, day: int, completed_count: int, total_count: int, is_today: bool, is_future: bool}>
     * }
     */
    public function getHabitDashboard(User $user, Carbon $date, ?string $monthYear = null): array
    {
        $monthYear = $monthYear ?: $date->format('Y-m');
        $calendarMonth = Carbon::createFromFormat('Y-m', $monthYear);

        $habits = Habit::where('user_id', $user->id)
            ->where('is_archived', false)
            ->get();

        $totalHabitsCount = $habits->count();

        // 1. Today's completed habit IDs
        $todayCompletedIds = HabitLog::where('user_id', $user->id)
            ->whereDate('completed_date', $date)
            ->where('is_completed', true)
            ->pluck('habit_id')
            ->toArray();

        $todayCompletedCount = count($todayCompletedIds);
        $todayPercent = $totalHabitsCount > 0 ? (int) round(($todayCompletedCount / $totalHabitsCount) * 100) : 0;

        // 2. Weekly completion percentage (last 7 days)
        $startOfWeek = Carbon::today()->subDays(6)->startOfDay();
        $endOfWeek = Carbon::today()->endOfDay();

        $weeklyCompletedCount = HabitLog::where('user_id', $user->id)
            ->whereBetween('completed_date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->where('is_completed', true)
            ->count();

        $weeklyPossibleCount = $totalHabitsCount * 7;
        $weeklyPercent = $weeklyPossibleCount > 0 ? (int) round(($weeklyCompletedCount / $weeklyPossibleCount) * 100) : 0;

        // 3. Monthly habit calendar data
        $startOfMonth = $calendarMonth->copy()->startOfMonth();
        $endOfMonth = $calendarMonth->copy()->endOfMonth();

        $monthLogs = HabitLog::where('user_id', $user->id)
            ->whereBetween('completed_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->where('is_completed', true)
            ->get()
            ->groupBy(fn ($log) => Carbon::parse($log->completed_date)->toDateString());

        $calendarDays = [];
        $current = $startOfMonth->copy();
        $todayStr = Carbon::today()->toDateString();

        while ($current->lte($endOfMonth)) {
            $dateStr = $current->toDateString();
            $completedCount = isset($monthLogs[$dateStr]) ? $monthLogs[$dateStr]->count() : 0;

            $calendarDays[] = [
                'date' => $dateStr,
                'day' => $current->day,
                'completed_count' => $completedCount,
                'total_count' => $totalHabitsCount,
                'is_today' => $dateStr === $todayStr,
                'is_future' => $current->gt(Carbon::today()),
            ];

            $current->addDay();
        }

        return [
            'habits' => $habits,
            'today_completed_ids' => $todayCompletedIds,
            'today_completed_count' => $todayCompletedCount,
            'total_habits_count' => $totalHabitsCount,
            'today_percent' => $todayPercent,
            'weekly_percent' => $weeklyPercent,
            'calendar_days' => $calendarDays,
            'month_label' => $calendarMonth->format('F Y'),
            'current_month' => $monthYear,
        ];
    }

    /**
     * Get exercise & workouts overview.
     *
     * @return array{
     *     today_workouts: Collection<int, Exercise>,
     *     today_duration: int,
     *     today_calories: float,
     *     today_distance: float,
     *     week_duration: int,
     *     week_calories: float,
     *     week_workouts_count: int,
     *     history: Collection<int, Exercise>,
     *     trend: array<int, array{day: string, duration: int, calories: float}>
     * }
     */
    public function getExerciseStats(User $user, Carbon $date): array
    {
        $todayWorkouts = Exercise::where('user_id', $user->id)
            ->whereDate('exercise_date', $date)
            ->orderBy('exercise_time')
            ->get();

        $todayDuration = (int) $todayWorkouts->sum('duration_minutes');
        $todayCalories = (float) $todayWorkouts->sum('calories_burned');
        $todayDistance = (float) $todayWorkouts->sum('distance_km');

        $startOfWeek = Carbon::today()->subDays(6)->toDateString();
        $endOfWeek = Carbon::today()->toDateString();

        $weekWorkouts = Exercise::where('user_id', $user->id)
            ->whereBetween('exercise_date', [$startOfWeek, $endOfWeek])
            ->get();

        $weekDuration = (int) $weekWorkouts->sum('duration_minutes');
        $weekCalories = (float) $weekWorkouts->sum('calories_burned');
        $weekCount = $weekWorkouts->count();

        // 7-day trend for Chart.js
        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $dayWorkouts = $weekWorkouts->filter(fn ($w) => Carbon::parse($w->exercise_date)->toDateString() === $d->toDateString());

            $trend[] = [
                'day' => $d->format('D, M d'),
                'duration' => (int) $dayWorkouts->sum('duration_minutes'),
                'calories' => (float) $dayWorkouts->sum('calories_burned'),
            ];
        }

        $history = Exercise::where('user_id', $user->id)
            ->orderByDesc('exercise_date')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return [
            'today_workouts' => $todayWorkouts,
            'today_duration' => $todayDuration,
            'today_calories' => $todayCalories,
            'today_distance' => $todayDistance,
            'week_duration' => $weekDuration,
            'week_calories' => $weekCalories,
            'week_workouts_count' => $weekCount,
            'history' => $history,
            'trend' => $trend,
        ];
    }

    /**
     * Get sleep tracking statistics.
     *
     * @return array{
     *     last_sleep: ?SleepLog,
     *     avg_duration_minutes: int,
     *     avg_duration_formatted: string,
     *     avg_quality: float,
     *     sleep_history: Collection<int, SleepLog>,
     *     trend: array<int, array{date: string, label: string, hours: float, quality: int}>
     * }
     */
    public function getSleepStats(User $user): array
    {
        $lastSleep = SleepLog::where('user_id', $user->id)
            ->orderByDesc('sleep_date')
            ->orderByDesc('id')
            ->first();

        $last7DaysLogs = SleepLog::where('user_id', $user->id)
            ->where('sleep_date', '>=', Carbon::today()->subDays(6)->toDateString())
            ->orderBy('sleep_date')
            ->get();

        $avgMinutes = $last7DaysLogs->isNotEmpty() ? (int) round($last7DaysLogs->avg('duration_minutes')) : 0;
        $avgHours = intdiv($avgMinutes, 60);
        $avgMinsRemainder = $avgMinutes % 60;
        $avgFormatted = $avgMinutes > 0 ? "{$avgHours}h {$avgMinsRemainder}m" : "0h 0m";

        $avgQuality = $last7DaysLogs->isNotEmpty() ? round($last7DaysLogs->avg('quality_rating'), 1) : 0.0;

        // 7-day trend
        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $log = $last7DaysLogs->first(fn ($s) => Carbon::parse($s->sleep_date)->toDateString() === $d->toDateString());

            $trend[] = [
                'date' => $d->toDateString(),
                'label' => $d->format('D, M d'),
                'hours' => $log ? round($log->duration_minutes / 60, 1) : 0.0,
                'quality' => $log ? $log->quality_rating : 0,
            ];
        }

        $history = SleepLog::where('user_id', $user->id)
            ->orderByDesc('sleep_date')
            ->limit(20)
            ->get();

        return [
            'last_sleep' => $lastSleep,
            'avg_duration_minutes' => $avgMinutes,
            'avg_duration_formatted' => $avgFormatted,
            'avg_quality' => $avgQuality,
            'sleep_history' => $history,
            'trend' => $trend,
        ];
    }
}
