<?php

namespace App\Http\Controllers;

use App\Http\Requests\HabitRequest;
use App\Models\Habit;
use App\Models\HabitLog;
use App\Services\ActivityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HabitController extends Controller
{
    public function __construct(
        protected ActivityService $activityService
    ) {}

    /**
     * Display habits tracker dashboard.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $dateInput = $request->query('date', Carbon::today()->toDateString());
        $date = Carbon::parse($dateInput);
        $month = $request->query('month', $date->format('Y-m'));

        $dashboard = $this->activityService->getHabitDashboard($user, $date, $month);

        $suggestedHabits = [
            ['name' => 'Wake up early', 'category' => 'Mindset', 'icon' => 'sunrise', 'color' => '#F4A261'],
            ['name' => 'Exercise 30 mins', 'category' => 'Fitness', 'icon' => 'dumbbell', 'color' => '#7FA68E'],
            ['name' => 'Read books (20 mins)', 'category' => 'Productivity', 'icon' => 'book-open', 'color' => '#8E8CD8'],
            ['name' => 'Drink enough water', 'category' => 'Health', 'icon' => 'droplets', 'color' => '#3B82F6'],
            ['name' => 'Meditate (10 mins)', 'category' => 'Mindset', 'icon' => 'smile', 'color' => '#EC4899'],
            ['name' => 'Sleep on time', 'category' => 'Health', 'icon' => 'moon', 'color' => '#A78BFA'],
            ['name' => 'Avoid junk food', 'category' => 'Health', 'icon' => 'utensils', 'color' => '#10B981'],
            ['name' => 'Practice gratitude', 'category' => 'Mindset', 'icon' => 'heart', 'color' => '#E8A598'],
        ];

        return view('habits.index', [
            'date' => $date,
            'prevDate' => $date->copy()->subDay()->toDateString(),
            'nextDate' => $date->copy()->addDay()->toDateString(),
            'dashboard' => $dashboard,
            'suggestedHabits' => $suggestedHabits,
            'selectedMonth' => $month,
        ]);
    }

    /**
     * Store a newly created habit.
     */
    public function store(HabitRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['color'] = $data['color'] ?? '#7FA68E';
        $data['icon'] = $data['icon'] ?? 'check-circle';

        Habit::create($data);

        return redirect()->route('habits.index')->with('success', 'New habit created successfully!');
    }

    /**
     * Toggle habit completion status for a given date.
     */
    public function toggle(Request $request, Habit $habit): RedirectResponse|JsonResponse
    {
        if ($habit->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to habit.');
        }

        $dateStr = $request->input('date', Carbon::today()->toDateString());

        $log = HabitLog::where('habit_id', $habit->id)
            ->where('user_id', auth()->id())
            ->whereDate('completed_date', $dateStr)
            ->first();

        if ($log) {
            $isCompleted = !$log->is_completed;
            $log->update(['is_completed' => $isCompleted]);
        } else {
            $isCompleted = true;
            HabitLog::create([
                'habit_id' => $habit->id,
                'user_id' => auth()->id(),
                'completed_date' => $dateStr,
                'is_completed' => true,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_completed' => $isCompleted,
                'current_streak' => $habit->current_streak,
            ]);
        }

        $statusMsg = $isCompleted ? "Marked '{$habit->name}' as completed today! Keep the streak going 🔥" : "Unmarked '{$habit->name}'.";

        return redirect()->back()->with('success', $statusMsg);
    }

    /**
     * Update an existing habit.
     */
    public function update(HabitRequest $request, Habit $habit): RedirectResponse
    {
        if ($habit->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to habit.');
        }

        $habit->update($request->validated());

        return redirect()->route('habits.index')->with('success', 'Habit updated successfully!');
    }

    /**
     * Delete a habit.
     */
    public function destroy(Habit $habit): RedirectResponse
    {
        if ($habit->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to habit.');
        }

        $habit->delete();

        return redirect()->route('habits.index')->with('success', 'Habit removed.');
    }
}
