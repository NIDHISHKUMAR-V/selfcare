<?php

namespace App\Http\Controllers;

use App\Http\Requests\WaterLogRequest;
use App\Models\WaterLog;
use App\Services\NutritionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaterLogController extends Controller
{
    public function __construct(
        protected NutritionService $nutritionService
    ) {}

    /**
     * Display the water intake tracker.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $dateInput = $request->query('date', Carbon::today()->toDateString());
        $date = Carbon::parse($dateInput);

        $waterSummary = $this->nutritionService->getDailyWaterSummary($user, $date);

        $todayLogs = WaterLog::where('user_id', $user->id)
            ->whereDate('log_date', $date)
            ->orderByDesc('log_time')
            ->orderByDesc('id')
            ->get();

        // 7-day history for Chart.js
        $history = collect();
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $dayMl = (int) WaterLog::where('user_id', $user->id)
                ->whereDate('log_date', $day)
                ->sum('amount_ml');

            $history->push([
                'label' => $day->format('D, M d'),
                'amount' => $dayMl,
            ]);
        }

        return view('water.index', [
            'date' => $date,
            'prevDate' => $date->copy()->subDay()->toDateString(),
            'nextDate' => $date->copy()->addDay()->toDateString(),
            'summary' => $waterSummary,
            'logs' => $todayLogs,
            'history' => $history,
        ]);
    }

    /**
     * Store a water intake record.
     */
    public function store(WaterLogRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        if (empty($data['log_time'])) {
            $data['log_time'] = now()->format('H:i:s');
        }

        $log = WaterLog::create($data);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logged ' . $log->amount_ml . 'ml of water!',
                'log' => $log,
            ]);
        }

        return redirect()->route('water.index', ['date' => $data['log_date']])
            ->with('success', 'Logged ' . $log->amount_ml . 'ml of water!');
    }

    /**
     * Quick-add specific amount (+250ml, +500ml, etc.).
     */
    public function quickAdd(Request $request): RedirectResponse
    {
        $request->validate([
            'amount_ml' => ['required', 'integer', 'min:50', 'max:2000'],
            'date' => ['nullable', 'date'],
        ]);

        $date = $request->input('date', Carbon::today()->toDateString());

        WaterLog::create([
            'user_id' => auth()->id(),
            'amount_ml' => (int) $request->input('amount_ml'),
            'log_date' => $date,
            'log_time' => now()->format('H:i:s'),
            'notes' => 'Quick added ' . $request->input('amount_ml') . 'ml',
        ]);

        return redirect()->back()->with('success', '+' . $request->input('amount_ml') . 'ml added to your water intake!');
    }

    /**
     * Delete a water log entry.
     */
    public function destroy(WaterLog $waterLog): RedirectResponse
    {
        if ($waterLog->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to water record.');
        }

        $date = $waterLog->log_date->toDateString();
        $waterLog->delete();

        return redirect()->route('water.index', ['date' => $date])
            ->with('success', 'Water entry removed.');
    }

    /**
     * Update user's daily water goal.
     */
    public function updateGoal(Request $request): RedirectResponse
    {
        $request->validate([
            'daily_water_goal_ml' => ['required', 'integer', 'min:500', 'max:10000'],
        ]);

        auth()->user()->update([
            'daily_water_goal_ml' => (int) $request->input('daily_water_goal_ml'),
        ]);

        return redirect()->back()->with('success', 'Daily water goal updated successfully!');
    }
}
