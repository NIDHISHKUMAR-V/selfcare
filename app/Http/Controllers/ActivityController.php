<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivityRequest;
use App\Models\Activity;
use App\Services\ActivityService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function __construct(
        protected ActivityService $activityService
    ) {}

    /**
     * Display daily tasks and activities.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $dateInput = $request->query('date', Carbon::today()->toDateString());
        $date = Carbon::parse($dateInput);

        $overview = $this->activityService->getDailyActivities($user, $date);

        // Categories available for tasks
        $categories = ['General', 'Work', 'Self-Care', 'Personal', 'Study', 'Health', 'Fitness', 'Home'];

        return view('activities.index', [
            'date' => $date,
            'prevDate' => $date->copy()->subDay()->toDateString(),
            'nextDate' => $date->copy()->addDay()->toDateString(),
            'overview' => $overview,
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created activity / task.
     */
    public function store(ActivityRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['status'] = $data['status'] ?? 'pending';

        if ($data['status'] === 'completed') {
            $data['completed_at'] = now();
        }

        Activity::create($data);

        return redirect()->route('activities.index', ['date' => $data['activity_date']])
            ->with('success', 'Task added successfully!');
    }

    /**
     * Update an existing task.
     */
    public function update(ActivityRequest $request, Activity $activity): RedirectResponse
    {
        if ($activity->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to task.');
        }

        $data = $request->validated();

        if (isset($data['status'])) {
            if ($data['status'] === 'completed' && $activity->status !== 'completed') {
                $data['completed_at'] = now();
            } elseif ($data['status'] !== 'completed') {
                $data['completed_at'] = null;
            }
        }

        $activity->update($data);

        return redirect()->route('activities.index', ['date' => $activity->activity_date->toDateString()])
            ->with('success', 'Task updated successfully!');
    }

    /**
     * Toggle status between pending and completed.
     */
    public function toggleStatus(Activity $activity): RedirectResponse
    {
        if ($activity->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to task.');
        }

        $newStatus = $activity->status === 'completed' ? 'pending' : 'completed';
        $activity->update([
            'status' => $newStatus,
            'completed_at' => $newStatus === 'completed' ? now() : null,
        ]);

        $msg = $newStatus === 'completed' ? 'Task marked as completed! 🎉' : 'Task marked as pending.';

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Delete an activity task.
     */
    public function destroy(Activity $activity): RedirectResponse
    {
        if ($activity->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to task.');
        }

        $date = $activity->activity_date->toDateString();
        $activity->delete();

        return redirect()->route('activities.index', ['date' => $date])
            ->with('success', 'Task deleted.');
    }
}
