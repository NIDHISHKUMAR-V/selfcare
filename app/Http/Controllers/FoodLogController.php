<?php

namespace App\Http\Controllers;

use App\Http\Requests\FoodLogRequest;
use App\Models\FoodItem;
use App\Models\FoodLog;
use App\Services\NutritionService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FoodLogController extends Controller
{
    public function __construct(
        protected NutritionService $nutritionService
    ) {}

    /**
     * Display the daily food tracking journal.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $dateInput = $request->query('date', Carbon::today()->toDateString());
        $date = Carbon::parse($dateInput);

        $dailySummary = $this->nutritionService->getDailyNutritionSummary($user, $date);
        $mealsByType = $this->nutritionService->getMealsGroupedByType($user, $date);
        $recentFoods = FoodItem::where('user_id', $user->id)
            ->orWhereNull('user_id')
            ->limit(10)
            ->get();

        return view('food.index', [
            'date' => $date,
            'prevDate' => $date->copy()->subDay()->toDateString(),
            'nextDate' => $date->copy()->addDay()->toDateString(),
            'dailySummary' => $dailySummary,
            'mealsByType' => $mealsByType,
            'recentFoods' => $recentFoods,
        ]);
    }

    /**
     * Show form to log a meal.
     */
    public function create(Request $request): View
    {
        $defaultType = $request->query('type', 'breakfast');
        $defaultDate = $request->query('date', Carbon::today()->toDateString());

        return view('food.create', [
            'defaultType' => $defaultType,
            'defaultDate' => $defaultDate,
        ]);
    }

    /**
     * Store a newly created food log entry.
     */
    public function store(FoodLogRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('food_logs', 'public');
        }

        FoodLog::create($data);

        return redirect()->route('food.index', ['date' => $data['meal_date']])
            ->with('success', 'Meal logged successfully!');
    }

    /**
     * Display a single food record.
     */
    public function show(FoodLog $food): RedirectResponse
    {
        return redirect()->route('food.index', ['date' => $food->meal_date->toDateString()]);
    }

    /**
     * Show form to edit an existing food record.
     */
    public function edit(FoodLog $food): View
    {
        if ($food->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to food record.');
        }

        return view('food.edit', ['foodLog' => $food]);
    }

    /**
     * Update an existing food log entry.
     */
    public function update(FoodLogRequest $request, FoodLog $food): RedirectResponse
    {
        if ($food->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to food record.');
        }

        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($food->image_path && Storage::disk('public')->exists($food->image_path)) {
                Storage::disk('public')->delete($food->image_path);
            }
            $data['image_path'] = $request->file('image')->store('food_logs', 'public');
        }

        $food->update($data);

        return redirect()->route('food.index', ['date' => $food->meal_date->toDateString()])
            ->with('success', 'Food log updated successfully!');
    }

    /**
     * Delete a food log entry.
     */
    public function destroy(FoodLog $food): RedirectResponse
    {
        if ($food->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to food record.');
        }

        if ($food->image_path && Storage::disk('public')->exists($food->image_path)) {
            Storage::disk('public')->delete($food->image_path);
        }

        $date = $food->meal_date->toDateString();
        $food->delete();

        return redirect()->route('food.index', ['date' => $date])
            ->with('success', 'Food item removed successfully.');
    }

    /**
     * Display food & nutrition analytics and reports.
     */
    public function reports(Request $request): View
    {
        $user = auth()->user();
        $period = $request->query('period', 'weekly');
        if (!in_array($period, ['weekly', 'monthly'], true)) {
            $period = 'weekly';
        }

        $report = $this->nutritionService->getNutritionReport($user, $period);

        return view('food.reports', [
            'report' => $report,
            'period' => $period,
        ]);
    }
}
