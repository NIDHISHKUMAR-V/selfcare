@extends('layouts.app')

@section('title', 'Sleep Tracker - My Self-Care')
@section('page-title', 'Sleep & Rest Recovery')
@section('page-subtitle', 'Monitor restful sleep and daily mental restoration')

@section('content')
<div style="display:flex;flex-direction:column;gap:1.5rem;">
    <!-- Top Action -->
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
        <div>
            <div style="font-size:0.85rem;color:var(--text-muted);">Track your sleep schedule, sleep duration, and restfulness rating.</div>
        </div>
        <button onclick="window.openModal('sleepModal')" class="btn btn-primary btn-sm">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> Log Sleep
        </button>
    </div>

    <!-- Summary Statistics Cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:1rem;">
        <div class="stat-card lavender">
            <div class="stat-icon purple"><i data-lucide="moon" style="width:20px;height:20px;"></i></div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--text);">
                {{ $stats['last_sleep'] ? $stats['last_sleep']->formatted_duration : 'No record' }}
            </div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem;">
                Last Night: {{ $stats['last_sleep'] ? $stats['last_sleep']->quality_label : '—' }}
            </div>
        </div>

        <div class="stat-card sage">
            <div class="stat-icon" style="background:var(--sage);color:#fff;"><i data-lucide="clock" style="width:20px;height:20px;"></i></div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--text);">{{ $stats['avg_duration_formatted'] }}</div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem;">7-Day Average Duration</div>
        </div>

        <div class="stat-card peach">
            <div class="stat-icon" style="background:var(--peach);color:#fff;"><i data-lucide="star" style="width:20px;height:20px;"></i></div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--text);">{{ $stats['avg_quality'] }} <span style="font-size:0.9rem;font-weight:400;color:var(--text-muted);">/ 5.0</span></div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem;">Average Rest Quality</div>
        </div>
    </div>

    <!-- 7-Day Trend Chart -->
    <div class="card">
        <div class="section-title" style="margin-bottom:0.25rem;">7-Day Sleep Duration Trend</div>
        <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:1rem;">Hours of sleep per night</div>
        @php $sleepChartData = json_encode(['labels' => array_column($stats['trend'], 'label'), 'data' => array_column($stats['trend'], 'hours')]); @endphp
        <canvas id="sleepTrendChart" data-chart="sleep-trend" data-chart-data="{!! htmlspecialchars($sleepChartData, ENT_QUOTES) !!}" style="max-height:220px;"></canvas>
    </div>

    <!-- Sleep History Table -->
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <div class="section-title">Sleep History</div>
            <span style="font-size:0.75rem;color:var(--text-muted);">Last 20 entries</span>
        </div>

        @if($stats['sleep_history']->isNotEmpty())
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:0.85rem;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--border);text-align:left;color:var(--text-muted);font-size:0.75rem;">
                            <th style="padding:0.6rem 0.75rem;">Date</th>
                            <th style="padding:0.6rem 0.75rem;">Bedtime</th>
                            <th style="padding:0.6rem 0.75rem;">Wake Up</th>
                            <th style="padding:0.6rem 0.75rem;">Duration</th>
                            <th style="padding:0.6rem 0.75rem;">Quality</th>
                            <th style="padding:0.6rem 0.75rem;">Notes</th>
                            <th style="padding:0.6rem 0.75rem;text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['sleep_history'] as $log)
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:0.75rem;font-weight:600;color:var(--text);">
                                    {{ $log->sleep_date->format('M d, Y') }}
                                </td>
                                <td style="padding:0.75rem;color:var(--text-muted);">
                                    {{ $log->sleep_time->format('g:i A') }}
                                </td>
                                <td style="padding:0.75rem;color:var(--text-muted);">
                                    {{ $log->wake_time->format('g:i A') }}
                                </td>
                                <td style="padding:0.75rem;font-weight:600;color:var(--primary);">
                                    {{ $log->formatted_duration }}
                                </td>
                                <td style="padding:0.75rem;">
                                    <span class="badge" style="background:#EFF6FF;color:#3B82F6;">
                                        {{ $log->quality_label }} ({{ $log->quality_rating }}/5)
                                    </span>
                                </td>
                                <td style="padding:0.75rem;color:var(--text-muted);font-size:0.75rem;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    {{ $log->notes ?: '—' }}
                                </td>
                                <td style="padding:0.75rem;text-align:right;">
                                    <form method="POST" action="{{ route('sleep.destroy', $log) }}" onsubmit="return confirm('Delete this sleep record?');" style="display:inline;">
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
                <i data-lucide="moon" style="width:48px;height:48px;color:var(--border);margin-bottom:0.75rem;"></i>
                <div style="font-weight:600;color:var(--text);margin-bottom:0.25rem;">No sleep records logged yet</div>
                <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:1rem;">Track your nightly sleep to understand your recovery patterns.</div>
                <button onclick="window.openModal('sleepModal')" class="btn btn-primary btn-sm">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> Log First Sleep
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Log Sleep Modal -->
<div id="sleepModal" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Log Sleep Record</h3>
                <p class="modal-subtitle">Track your bedtime, wake time, and restful recovery quality</p>
            </div>
            <button type="button" onclick="window.closeModal('sleepModal')" class="modal-close-btn">
                <i data-lucide="x" style="width:18px;height:18px;"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('sleep.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required" for="sleep_date">Sleep Date</label>
                    <input type="date" id="sleep_date" name="sleep_date" required value="{{ old('sleep_date', now()->subDay()->toDateString()) }}" class="form-control">
                    <div class="form-hint">Date when you went to bed</div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label required" for="sleep_time">Bedtime</label>
                        <input type="datetime-local" id="sleep_time" name="sleep_time" required value="{{ old('sleep_time', now()->subDay()->setTime(23, 0)->format('Y-m-d\TH:i')) }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label required" for="wake_time">Wake Time</label>
                        <input type="datetime-local" id="wake_time" name="wake_time" required value="{{ old('wake_time', now()->setTime(7, 30)->format('Y-m-d\TH:i')) }}" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="quality_rating">Rest Quality</label>
                    <select id="quality_rating" name="quality_rating" required class="form-control">
                        <option value="5">🌟 Excellent - Completely refreshed & energized</option>
                        <option value="4" selected>😊 Very Good - Rested well</option>
                        <option value="3">😌 Good - Normal, adequate sleep</option>
                        <option value="2">😴 Fair - Woke up slightly tired</option>
                        <option value="1">😫 Poor - Restless / Interrupted</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" for="sleep_notes">Sleep Notes</label>
                    <textarea id="sleep_notes" name="notes" rows="2" class="form-control" placeholder="Room temperature, dreams, interruptions...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="window.closeModal('sleepModal')" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="check" style="width:14px;height:14px;"></i> Save Sleep Log
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
