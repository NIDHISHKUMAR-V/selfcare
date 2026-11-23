@extends('layouts.app')

@section('title', 'Water Intake Tracker')
@section('page-title', 'Water Tracker')
@section('page-subtitle', 'Stay hydrated and track your daily water consumption')

@section('content')

{{-- Date Navigator --}}
<div class="card" style="margin-bottom:1.5rem;padding:0.9rem 1.25rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;">
        <div style="display:flex;align-items:center;gap:0.5rem;">
            <a href="{{ route('water.index', ['date' => $prevDate]) }}" class="btn btn-outline btn-sm" title="Previous Day">
                <i data-lucide="chevron-left" style="width:14px;height:14px;"></i>
            </a>
            <div style="display:flex;align-items:center;gap:0.5rem;">
                <i data-lucide="calendar" style="width:16px;height:16px;color:var(--sage);"></i>
                <span style="font-size:0.95rem;font-weight:700;color:var(--text);">
                    {{ $date->isToday() ? 'Today, ' : ($date->isYesterday() ? 'Yesterday, ' : '') }}{{ $date->format('l, M d, Y') }}
                </span>
            </div>
            <a href="{{ route('water.index', ['date' => $nextDate]) }}" class="btn btn-outline btn-sm" title="Next Day">
                <i data-lucide="chevron-right" style="width:14px;height:14px;"></i>
            </a>
            @if(!$date->isToday())
                <a href="{{ route('water.index') }}" class="btn btn-outline btn-sm" style="font-size:0.75rem;padding:0.25rem 0.6rem;">
                    Jump to Today
                </a>
            @endif
        </div>

        <div style="display:flex;align-items:center;gap:0.6rem;">
            <form method="GET" action="{{ route('water.index') }}" style="display:flex;align-items:center;gap:0.4rem;">
                <input type="date" name="date" value="{{ $date->toDateString() }}" class="form-control"
                       style="padding:0.35rem 0.65rem;font-size:0.8rem;border-radius:8px;" onchange="this.form.submit()">
            </form>
            <button onclick="document.getElementById('goalModal').style.display = 'flex'" class="btn btn-outline btn-sm">
                <i data-lucide="target" style="width:14px;height:14px;"></i> Set Goal
            </button>
        </div>
    </div>
</div>

{{-- Top Row: Main Progress Ring & Quick Add Buttons --}}
<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">

    {{-- Main Water Display Card --}}
    <div class="card" style="background:linear-gradient(135deg,#EFF6FF 0%,#E8F5ED 100%);border:none;display:flex;flex-direction:column;justify-content:center;padding:1.75rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1.5rem;">
            <div style="flex:1;min-width:200px;">
                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem;">
                    <div style="width:36px;height:36px;border-radius:10px;background:#3B82F6;color:#fff;display:flex;align-items:center;justify-content:center;">
                        <i data-lucide="droplets" style="width:20px;height:20px;"></i>
                    </div>
                    <div>
                        <div style="font-size:0.8rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;">Daily Consumption</div>
                        <div style="font-size:1.1rem;font-weight:700;color:var(--text);">Hydration Level</div>
                    </div>
                </div>

                <div style="display:flex;align-items:baseline;gap:0.4rem;margin:1rem 0 0.5rem;">
                    <span style="font-size:2.8rem;font-weight:800;color:var(--text);line-height:1;">
                        {{ number_format($summary['total_water_ml']) }}
                    </span>
                    <span style="font-size:1.2rem;font-weight:600;color:var(--text-muted);">/ {{ number_format($summary['water_goal_ml']) }} ml</span>
                </div>

                <p style="font-size:0.85rem;color:var(--text-muted);margin:0 0 1.25rem;">
                    Approx. <strong>{{ $summary['glasses_count'] }}</strong> standard glasses (250ml each)
                </p>

                {{-- Progress Bar --}}
                <div class="progress-bar" style="height:12px;background:rgba(255,255,255,0.7);">
                    <div class="progress-fill" style="width:{{ $summary['progress_percentage'] }}%;background:linear-gradient(90deg,#3B82F6,#7FA68E);"></div>
                </div>

                <div style="display:flex;justify-content:space-between;font-size:0.75rem;font-weight:600;color:var(--text-muted);margin-top:0.4rem;">
                    <span>{{ $summary['progress_percentage'] }}% Completed</span>
                    <span>
                        @if($summary['remaining_ml'] > 0)
                            {{ number_format($summary['remaining_ml']) }} ml remaining
                        @else
                            🎉 Goal reached! Well done!
                        @endif
                    </span>
                </div>
            </div>

            {{-- Quick Hydration Badge --}}
            <div style="text-align:center;background:rgba(255,255,255,0.8);backdrop-filter:blur(10px);padding:1.5rem;border-radius:20px;border:1px solid rgba(255,255,255,0.9);box-shadow:0 4px 20px rgba(0,0,0,0.03);">
                <div style="font-size:2.5rem;margin-bottom:0.25rem;">
                    @if($summary['progress_percentage'] >= 100) 🏆 @elseif($summary['progress_percentage'] >= 75) 💧 @elseif($summary['progress_percentage'] >= 40) 🥤 @else 🌱 @endif
                </div>
                <div style="font-size:1.2rem;font-weight:700;color:var(--text);">
                    @if($summary['progress_percentage'] >= 100)
                        Super Hydrated!
                    @elseif($summary['progress_percentage'] >= 75)
                        Almost There!
                    @elseif($summary['progress_percentage'] >= 40)
                        Good Progress
                    @else
                        Time to Drink!
                    @endif
                </div>
                <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.25rem;">
                    Target: {{ number_format($summary['water_goal_ml']) }} ml/day
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Add Card --}}
    <div class="card" style="display:flex;flex-direction:column;justify-content:space-between;">
        <div>
            <div class="section-title">Quick Log Water</div>
            <div class="section-subtitle">Click a preset to log instantly</div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-top:1rem;">
                <form method="POST" action="{{ route('water.quick-add') }}">
                    @csrf
                    <input type="hidden" name="amount_ml" value="250">
                    <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                    <button type="submit" class="btn btn-outline" style="width:100%;flex-direction:column;gap:0.2rem;padding:0.75rem;border-radius:12px;background:var(--bg);">
                        <i data-lucide="coffee" style="width:20px;height:20px;color:#3B82F6;"></i>
                        <span style="font-size:0.9rem;font-weight:700;color:var(--text);">+250 ml</span>
                        <span style="font-size:0.68rem;color:var(--text-muted);">1 Glass</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('water.quick-add') }}">
                    @csrf
                    <input type="hidden" name="amount_ml" value="500">
                    <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                    <button type="submit" class="btn btn-outline" style="width:100%;flex-direction:column;gap:0.2rem;padding:0.75rem;border-radius:12px;background:var(--bg);">
                        <i data-lucide="droplet" style="width:20px;height:20px;color:var(--sage);"></i>
                        <span style="font-size:0.9rem;font-weight:700;color:var(--text);">+500 ml</span>
                        <span style="font-size:0.68rem;color:var(--text-muted);">Small Bottle</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('water.quick-add') }}">
                    @csrf
                    <input type="hidden" name="amount_ml" value="750">
                    <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                    <button type="submit" class="btn btn-outline" style="width:100%;flex-direction:column;gap:0.2rem;padding:0.75rem;border-radius:12px;background:var(--bg);">
                        <i data-lucide="glass-water" style="width:20px;height:20px;color:var(--primary);"></i>
                        <span style="font-size:0.9rem;font-weight:700;color:var(--text);">+750 ml</span>
                        <span style="font-size:0.68rem;color:var(--text-muted);">Large Bottle</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('water.quick-add') }}">
                    @csrf
                    <input type="hidden" name="amount_ml" value="1000">
                    <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                    <button type="submit" class="btn btn-outline" style="width:100%;flex-direction:column;gap:0.2rem;padding:0.75rem;border-radius:12px;background:var(--bg);">
                        <i data-lucide="flask-conical" style="width:20px;height:20px;color:#C2500C;"></i>
                        <span style="font-size:0.9rem;font-weight:700;color:var(--text);">+1000 ml</span>
                        <span style="font-size:0.68rem;color:var(--text-muted);">1 Litre Flask</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Custom Amount Input Form --}}
        <div style="margin-top:1rem;border-top:1px solid var(--border);padding-top:1rem;">
            <form method="POST" action="{{ route('water.store') }}" style="display:flex;gap:0.5rem;">
                @csrf
                <input type="hidden" name="log_date" value="{{ $date->toDateString() }}">
                <input type="number" name="amount_ml" class="form-control" placeholder="Custom ml (e.g. 350)" min="1" max="5000" required
                       style="padding:0.45rem 0.75rem;font-size:0.85rem;">
                <button type="submit" class="btn btn-sage btn-sm" style="flex-shrink:0;">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> Log
                </button>
            </form>
        </div>
    </div>

</div>

{{-- Middle Row: 7-Day History Chart + Today's Logs --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">

    {{-- 7-Day History Chart --}}
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <div>
                <div class="section-title">7-Day Water Consumption History</div>
                <div class="section-subtitle">Daily intake vs your {{ number_format($summary['water_goal_ml']) }}ml goal</div>
            </div>
            <span class="badge badge-green">Last 7 Days</span>
        </div>
        <div style="height:240px;position:relative;">
            <canvas id="waterHistoryChart"></canvas>
        </div>
    </div>

    {{-- Today's Water Log Entries --}}
    <div class="card" style="display:flex;flex-direction:column;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <div>
                <div class="section-title">Today's Logs</div>
                <div class="section-subtitle">{{ $logs->count() }} drink{{ $logs->count() != 1 ? 's' : '' }} recorded</div>
            </div>
        </div>

        @if($logs->isEmpty())
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;color:var(--text-muted);padding:2rem 0;">
                <i data-lucide="droplets" style="width:36px;height:36px;color:var(--border);margin-bottom:0.5rem;"></i>
                <p style="font-size:0.82rem;margin:0;">No water logged for this day yet.</p>
                <span style="font-size:0.75rem;color:var(--text-muted);">Use quick-add above to log your first drink!</span>
            </div>
        @else
            <div style="display:flex;flex-direction:column;gap:0.5rem;max-height:260px;overflow-y:auto;padding-right:0.25rem;">
                @foreach($logs as $entry)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:0.6rem 0.75rem;border-radius:10px;background:var(--bg);">
                        <div style="display:flex;align-items:center;gap:0.65rem;">
                            <div style="width:30px;height:30px;border-radius:8px;background:#EFF6FF;color:#3B82F6;display:flex;align-items:center;justify-content:center;">
                                <i data-lucide="droplet" style="width:14px;height:14px;"></i>
                            </div>
                            <div>
                                <div style="font-size:0.86rem;font-weight:700;color:var(--text);">+{{ number_format($entry->amount_ml) }} ml</div>
                                <div style="font-size:0.7rem;color:var(--text-muted);">
                                    {{ $entry->log_time ? \Carbon\Carbon::parse($entry->log_time)->format('h:i A') : 'Recorded' }}
                                    @if($entry->notes) · {{ $entry->notes }} @endif
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('water.destroy', $entry) }}" onsubmit="return confirm('Delete this water entry?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline btn-sm" style="padding:0.25rem 0.45rem;color:#DC2626;" title="Delete">
                                <i data-lucide="trash-2" style="width:12px;height:12px;"></i>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

{{-- Set Daily Goal Modal --}}
<div id="goalModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;align-items:center;justify-content:center;backdrop-filter:blur(3px);">
    <div class="card" style="width:100%;max-width:420px;margin:1rem;box-shadow:0 10px 40px rgba(0,0,0,0.15);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;padding-bottom:0.75rem;border-bottom:1px solid var(--border);">
            <div class="section-title">Set Daily Water Goal</div>
            <button onclick="document.getElementById('goalModal').style.display='none'" style="background:none;border:none;cursor:pointer;color:var(--text-muted);">
                <i data-lucide="x" style="width:18px;height:18px;"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('water.update-goal') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label" for="daily_water_goal_ml">Target Water Intake (ml/day)</label>
                <input type="number" id="daily_water_goal_ml" name="daily_water_goal_ml" class="form-control"
                       value="{{ $summary['water_goal_ml'] }}" min="500" max="10000" step="100" required>
                <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.35rem;">
                    Recommended: 2000ml to 3000ml (approx. 8–12 standard glasses per day).
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:0.5rem;margin-top:1.25rem;">
                <button type="button" onclick="document.getElementById('goalModal').style.display='none'" class="btn btn-outline btn-sm">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary btn-sm">
                    Save Goal
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // 7-Day Water Intake Chart
    const waterCtx = document.getElementById('waterHistoryChart').getContext('2d');
    const waterHistoryData = @json($history);
    const goalVal = {{ $summary['water_goal_ml'] }};

    new Chart(waterCtx, {
        type: 'bar',
        data: {
            labels: waterHistoryData.map(d => d.label),
            datasets: [
                {
                    label: 'Water Logged (ml)',
                    data: waterHistoryData.map(d => d.amount),
                    backgroundColor: 'rgba(59, 130, 246, 0.45)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1.5,
                    borderRadius: 6,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => `${ctx.parsed.y} ml`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: {
                        font: { size: 11 },
                        callback: v => v + ' ml'
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 } }
                }
            }
        }
    });
</script>
@endpush
