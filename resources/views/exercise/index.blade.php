@extends('layouts.app')

@section('title', 'Exercise Tracker - My Self-Care')
@section('page-title', 'Exercise & Movement')
@section('page-subtitle', 'Log your workouts and monitor physical vitality')

@section('content')
<div style="display:flex;flex-direction:column;gap:1.5rem;">
    <!-- Date Navigator and Actions -->
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
        <div style="display:flex;align-items:center;gap:0.5rem;">
            <a href="{{ route('exercise.index', ['date' => $prevDate]) }}" class="btn btn-sm btn-outline">
                <i data-lucide="chevron-left" style="width:14px;height:14px;"></i>
            </a>
            <form method="GET" action="{{ route('exercise.index') }}" style="display:inline-flex;align-items:center;">
                <input type="date" name="date" value="{{ $date->toDateString() }}" onchange="this.form.submit()"
                       class="form-control" style="padding:0.35rem 0.65rem;font-size:0.85rem;font-weight:600;width:auto;cursor:pointer;">
            </form>
            <a href="{{ route('exercise.index', ['date' => $nextDate]) }}" class="btn btn-sm btn-outline">
                <i data-lucide="chevron-right" style="width:14px;height:14px;"></i>
            </a>
            @if(!$date->isToday())
                <a href="{{ route('exercise.index') }}" class="btn btn-sm btn-ghost" style="font-size:0.75rem;">Today</a>
            @endif
        </div>

        <button onclick="window.openModal('exerciseModal')" class="btn btn-primary btn-sm">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> Log Workout
        </button>
    </div>

    <!-- Summary Statistics Cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:1rem;">
        <div class="stat-card sage">
            <div class="stat-icon" style="background:var(--sage);color:#fff;"><i data-lucide="timer" style="width:20px;height:20px;"></i></div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--text);">{{ $stats['today_duration'] }} <span style="font-size:0.9rem;font-weight:400;color:var(--text-muted);">mins</span></div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem;">Today's Active Time</div>
        </div>

        <div class="stat-card peach">
            <div class="stat-icon" style="background:var(--peach);color:#fff;"><i data-lucide="flame" style="width:20px;height:20px;"></i></div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--text);">{{ number_format($stats['today_calories']) }} <span style="font-size:0.9rem;font-weight:400;color:var(--text-muted);">kcal</span></div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem;">Calories Burned</div>
        </div>

        <div class="stat-card blue">
            <div class="stat-icon" style="background:#3B82F6;color:#fff;"><i data-lucide="map-pin" style="width:20px;height:20px;"></i></div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--text);">{{ number_format($stats['today_distance'], 1) }} <span style="font-size:0.9rem;font-weight:400;color:var(--text-muted);">km</span></div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem;">Distance Covered</div>
        </div>

        <div class="stat-card lavender">
            <div class="stat-icon purple"><i data-lucide="dumbbell" style="width:20px;height:20px;"></i></div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--text);">{{ $stats['week_duration'] }} <span style="font-size:0.9rem;font-weight:400;color:var(--text-muted);">mins</span></div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem;">7-Day Total Duration</div>
        </div>
    </div>

    <!-- 7-Day Trend Chart & Categories -->
    @php
        $exerciseChartData = json_encode(['labels' => array_column($stats['trend'], 'day'), 'data' => array_column($stats['trend'], 'duration')]);
    @endphp
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;align-items:start;">
        <div class="card">
            <div class="section-title" style="margin-bottom:0.25rem;">7-Day Workout Trend</div>
            <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:1rem;">Daily active exercise minutes</div>
            <canvas id="exerciseTrendChart" data-chart="exercise-trend" data-chart-data="{!! htmlspecialchars($exerciseChartData, ENT_QUOTES) !!}" style="max-height:220px;"></canvas>
        </div>

        <div class="card">
            <div class="section-title" style="margin-bottom:0.25rem;">Quick Workouts</div>
            <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:1rem;">Click to log a session</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
                @foreach($activityTypes as $tName => $tInfo)
                    <button onclick="prefillWorkout('{{ $tName }}')" class="btn btn-outline" style="flex-direction:column;padding:0.75rem 0.5rem;gap:0.35rem;border-radius:12px;">
                        <i data-lucide="{{ $tInfo['icon'] }}" style="width:20px;height:20px;color:{{ $tInfo['color'] }};"></i>
                        <span style="font-size:0.75rem;font-weight:600;">{{ $tName }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Workout History Table -->
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <div class="section-title">Recent Workouts</div>
            <span style="font-size:0.75rem;color:var(--text-muted);">Last 20 entries</span>
        </div>

        @if($stats['history']->isNotEmpty())
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:0.85rem;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--border);text-align:left;color:var(--text-muted);font-size:0.75rem;">
                            <th style="padding:0.6rem 0.75rem;">Activity</th>
                            <th style="padding:0.6rem 0.75rem;">Date & Time</th>
                            <th style="padding:0.6rem 0.75rem;">Duration</th>
                            <th style="padding:0.6rem 0.75rem;">Calories</th>
                            <th style="padding:0.6rem 0.75rem;">Distance</th>
                            <th style="padding:0.6rem 0.75rem;text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['history'] as $item)
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:0.75rem;font-weight:600;color:var(--text);">
                                    <div style="display:flex;align-items:center;gap:0.5rem;">
                                        <div style="width:28px;height:28px;border-radius:8px;background:var(--sage-light);display:flex;align-items:center;justify-content:center;color:var(--secondary);">
                                            <i data-lucide="dumbbell" style="width:14px;height:14px;"></i>
                                        </div>
                                        <span>{{ $item->activity_type }}</span>
                                    </div>
                                    @if($item->notes)
                                        <div style="font-size:0.7rem;color:var(--text-muted);font-weight:400;margin-top:0.15rem;">{{ $item->notes }}</div>
                                    @endif
                                </td>
                                <td style="padding:0.75rem;color:var(--text-muted);font-size:0.78rem;">
                                    {{ $item->exercise_date->format('M d, Y') }} {{ $item->exercise_time ? \Carbon\Carbon::parse($item->exercise_time)->format('g:i A') : '' }}
                                </td>
                                <td style="padding:0.75rem;font-weight:600;color:var(--text);">{{ $item->duration_minutes }} mins</td>
                                <td style="padding:0.75rem;color:var(--accent);font-weight:600;">{{ $item->calories_burned ? number_format($item->calories_burned) . ' kcal' : '—' }}</td>
                                <td style="padding:0.75rem;color:var(--text-muted);">{{ $item->distance_km ? $item->distance_km . ' km' : '—' }}</td>
                                <td style="padding:0.75rem;text-align:right;">
                                    <form method="POST" action="{{ route('exercise.destroy', $item) }}" onsubmit="return confirm('Delete this workout?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-ghost" style="color:#DC2626;padding:0.25rem;" title="Delete">
                                            <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align:center;padding:3rem 1rem;">
                <i data-lucide="dumbbell" style="width:48px;height:48px;color:var(--border);margin-bottom:0.75rem;"></i>
                <div style="font-weight:600;color:var(--text);margin-bottom:0.25rem;">No workouts recorded yet</div>
                <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:1rem;">Track your runs, yoga, swimming, or gym routines.</div>
                <button onclick="window.openModal('exerciseModal')" class="btn btn-primary btn-sm">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> Log First Workout
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Log Exercise Modal -->
<div id="exerciseModal" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Log Workout</h3>
                <p class="modal-subtitle">Record your activity, duration, and calories burned</p>
            </div>
            <button type="button" onclick="window.closeModal('exerciseModal')" class="modal-close-btn">
                <i data-lucide="x" style="width:18px;height:18px;"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('exercise.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required" for="workout_type">Activity Type</label>
                    <input type="text" id="workout_type" name="activity_type" required placeholder="Walking, Gym, Running, Yoga..." class="form-control" value="{{ old('activity_type') }}" autofocus>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label required" for="duration_minutes">Duration (Minutes)</label>
                        <input type="number" id="duration_minutes" name="duration_minutes" required min="1" max="1440" placeholder="e.g. 45" class="form-control" value="{{ old('duration_minutes', 30) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="calories_burned">Calories Burned (kcal)</label>
                        <input type="number" id="calories_burned" name="calories_burned" min="0" placeholder="e.g. 250" class="form-control" value="{{ old('calories_burned') }}">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="distance_km">Distance (km)</label>
                        <input type="number" id="distance_km" step="0.01" name="distance_km" min="0" placeholder="e.g. 4.5" class="form-control" value="{{ old('distance_km') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label required" for="exercise_date">Date</label>
                        <input type="date" id="exercise_date" name="exercise_date" required value="{{ old('exercise_date', $date->toDateString()) }}" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="exercise_time">Time of Day</label>
                    <input type="time" id="exercise_time" name="exercise_time" value="{{ old('exercise_time', now()->format('H:i')) }}" class="form-control">
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" for="exercise_notes">Workout Notes</label>
                    <textarea id="exercise_notes" name="notes" rows="2" class="form-control" placeholder="How did this workout feel? Cadence, energy level...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="window.closeModal('exerciseModal')" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="check" style="width:14px;height:14px;"></i> Save Workout
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
