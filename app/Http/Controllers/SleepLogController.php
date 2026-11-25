<?php

namespace App\Http\Controllers;

use App\Http\Requests\SleepLogRequest;
use App\Models\SleepLog;
use App\Services\ActivityService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SleepLogController extends Controller
{
    public function __construct(
        protected ActivityService $activityService
    ) {}

    /**
     * Display sleep tracking journal and stats.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $stats = $this->activityService->getSleepStats($user);

        return view('sleep.index', [
            'stats' => $stats,
        ]);
    }

    /**
     * Store a newly recorded sleep entry.
     */
    public function store(SleepLogRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $sleepTime = Carbon::parse($data['sleep_time']);
        $wakeTime = Carbon::parse($data['wake_time']);

        // Handle case if wake time is recorded as next day
        if ($wakeTime->lt($sleepTime)) {
            $wakeTime->addDay();
        }

        $durationMinutes = $sleepTime->diffInMinutes($wakeTime);
        $data['duration_minutes'] = $durationMinutes;

        SleepLog::create($data);

        return redirect()->route('sleep.index')->with('success', 'Sleep log recorded successfully!');
    }

    /**
     * Update an existing sleep log entry.
     */
    public function update(SleepLogRequest $request, SleepLog $sleep): RedirectResponse
    {
        if ($sleep->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to sleep record.');
        }

        $data = $request->validated();

        $sleepTime = Carbon::parse($data['sleep_time']);
        $wakeTime = Carbon::parse($data['wake_time']);

        if ($wakeTime->lt($sleepTime)) {
            $wakeTime->addDay();
        }

        $data['duration_minutes'] = $sleepTime->diffInMinutes($wakeTime);
        $sleep->update($data);

        return redirect()->route('sleep.index')->with('success', 'Sleep log updated successfully!');
    }

    /**
     * Delete a sleep log entry.
     */
    public function destroy(SleepLog $sleep): RedirectResponse
    {
        if ($sleep->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to sleep record.');
        }

        $sleep->delete();

        return redirect()->route('sleep.index')->with('success', 'Sleep record removed.');
    }
}
