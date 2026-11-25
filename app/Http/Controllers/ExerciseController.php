<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExerciseRequest;
use App\Models\Exercise;
use App\Services\ActivityService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExerciseController extends Controller
{
    public function __construct(
        protected ActivityService $activityService
    ) {}

    /**
     * Display the exercise & workouts tracker dashboard.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $dateInput = $request->query('date', Carbon::today()->toDateString());
        $date = Carbon::parse($dateInput);

        $stats = $this->activityService->getExerciseStats($user, $date);

        $activityTypes = [
            'Walking' => ['icon' => 'footprints', 'color' => '#7FA68E', 'bg' => '#E8F5ED'],
            'Running' => ['icon' => 'flame', 'color' => '#F4A261', 'bg' => '#FEF3E8'],
            'Cycling' => ['icon' => 'bike', 'color' => '#3B82F6', 'bg' => '#EFF6FF'],
            'Gym'     => ['icon' => 'dumbbell', 'color' => '#8E8CD8', 'bg' => '#EDE9FE'],
            'Yoga'    => ['icon' => 'heart', 'color' => '#EC4899', 'bg' => '#FDF2F8'],
            'Swimming'=> ['icon' => 'waves', 'color' => '#06B6D4', 'bg' => '#ECFEFF'],
            'Other'   => ['icon' => 'activity', 'color' => '#6B7280', 'bg' => '#F3F4F6'],
        ];

        return view('exercise.index', [
            'date' => $date,
            'prevDate' => $date->copy()->subDay()->toDateString(),
            'nextDate' => $date->copy()->addDay()->toDateString(),
            'stats' => $stats,
            'activityTypes' => $activityTypes,
        ]);
    }

    /**
     * Store a newly recorded workout.
     */
    public function store(ExerciseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        if (empty($data['exercise_time'])) {
            $data['exercise_time'] = now()->format('H:i');
        }

        Exercise::create($data);

        return redirect()->route('exercise.index', ['date' => $data['exercise_date']])
            ->with('success', "Logged {$data['activity_type']} workout successfully!");
    }

    /**
     * Update an existing workout record.
     */
    public function update(ExerciseRequest $request, Exercise $exercise): RedirectResponse
    {
        if ($exercise->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to workout record.');
        }

        $exercise->update($request->validated());

        return redirect()->route('exercise.index', ['date' => $exercise->exercise_date->toDateString()])
            ->with('success', 'Workout updated successfully!');
    }

    /**
     * Delete a workout record.
     */
    public function destroy(Exercise $exercise): RedirectResponse
    {
        if ($exercise->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to workout record.');
        }

        $date = $exercise->exercise_date->toDateString();
        $exercise->delete();

        return redirect()->route('exercise.index', ['date' => $date])
            ->with('success', 'Workout record removed.');
    }
}
