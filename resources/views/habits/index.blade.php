@extends('layouts.app')

@section('title', 'Habit Tracker - My Self-Care')
@section('page-title', 'Habit Tracker')
@section('page-subtitle', 'Build positive daily rituals and maintain consistency')

@section('content')
<div style="display:flex;flex-direction:column;gap:1.5rem;">
    <!-- Date Navigator and Actions -->
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
        <div style="display:flex;align-items:center;gap:0.5rem;">
            <a href="{{ route('habits.index', ['date' => $prevDate]) }}" class="btn btn-sm btn-outline">
                <i data-lucide="chevron-left" style="width:14px;height:14px;"></i>
            </a>
            <form method="GET" action="{{ route('habits.index') }}" style="display:inline-flex;align-items:center;">
                <input type="date" name="date" value="{{ $date->toDateString() }}" onchange="this.form.submit()"
                       class="form-control" style="padding:0.35rem 0.65rem;font-size:0.85rem;font-weight:600;width:auto;cursor:pointer;">
            </form>
            <a href="{{ route('habits.index', ['date' => $nextDate]) }}" class="btn btn-sm btn-outline">
                <i data-lucide="chevron-right" style="width:14px;height:14px;"></i>
            </a>
            @if(!$date->isToday())
                <a href="{{ route('habits.index') }}" class="btn btn-sm btn-ghost" style="font-size:0.75rem;">Today</a>
            @endif
        </div>

        <button onclick="window.openModal('habitModal')" class="btn btn-primary btn-sm">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> New Habit
        </button>
    </div>

    <!-- Summary Statistics Cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:1rem;">
        <div class="stat-card lavender">
            <div class="stat-icon purple"><i data-lucide="repeat" style="width:20px;height:20px;"></i></div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--text);">{{ $dashboard['total_habits_count'] }}</div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem;">Active Habits</div>
        </div>

        <div class="stat-card sage">
            <div class="stat-icon" style="background:var(--sage);color:#fff;"><i data-lucide="check-circle" style="width:20px;height:20px;"></i></div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--text);">{{ $dashboard['today_completed_count'] }}</div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem;">Completed Today</div>
        </div>

        <div class="stat-card peach">
            <div class="stat-icon" style="background:var(--peach);color:#fff;"><i data-lucide="target" style="width:20px;height:20px;"></i></div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--text);">{{ $dashboard['today_percent'] }}%</div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem;">Today's Rate</div>
        </div>

        <div class="stat-card blue">
            <div class="stat-icon" style="background:#3B82F6;color:#fff;"><i data-lucide="trending-up" style="width:20px;height:20px;"></i></div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--text);">{{ $dashboard['weekly_percent'] }}%</div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem;">7-Day Average</div>
        </div>
    </div>

    <!-- Main Content: Daily Checklist + Monthly Calendar -->
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;align-items:start;">
        <!-- Daily Checklist -->
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                <div class="section-title">Habit Checklist for {{ $date->format('l, F j, Y') }}</div>
                <span style="font-size:0.75rem;color:var(--text-muted);">{{ $dashboard['today_completed_count'] }} / {{ $dashboard['total_habits_count'] }} done</span>
            </div>

            @if($dashboard['habits']->isNotEmpty())
                <div style="display:flex;flex-direction:column;gap:0.75rem;">
                    @foreach($dashboard['habits'] as $habit)
                        @php
                            $isDone = in_array($habit->id, $dashboard['today_completed_ids']);
                            $streak = $habit->current_streak;
                        @endphp
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:0.9rem 1rem;border-radius:12px;border:1px solid {{ $isDone ? 'var(--secondary)' : 'var(--border)' }};background:{{ $isDone ? 'var(--sage-light)' : '#fff' }};gap:0.75rem;">
                            <div style="display:flex;align-items:center;gap:0.75rem;flex:1;">
                                <form method="POST" action="{{ route('habits.toggle', $habit) }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                                    <button type="submit" style="background:none;border:none;cursor:pointer;padding:0;display:flex;align-items:center;">
                                        @if($isDone)
                                            <i data-lucide="check-circle-2" style="width:24px;height:24px;color:var(--secondary);fill:#fff;"></i>
                                        @else
                                            <i data-lucide="circle" style="width:24px;height:24px;color:var(--text-muted);"></i>
                                        @endif
                                    </button>
                                </form>

                                <div style="flex:1;">
                                    <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                                        <span style="font-size:0.92rem;font-weight:600;color:var(--text);{{ $isDone ? 'text-decoration:line-through;color:var(--text-muted);' : '' }}">
                                            {{ $habit->name }}
                                        </span>
                                        <span class="badge" style="background:var(--primary-light);color:var(--primary);">
                                            {{ $habit->category }}
                                        </span>
                                    </div>
                                    @if($habit->description)
                                        <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.2rem;">
                                            {{ $habit->description }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div style="display:flex;align-items:center;gap:0.75rem;">
                                <!-- Streak badge -->
                                <div style="display:flex;align-items:center;gap:0.25rem;font-size:0.75rem;font-weight:700;padding:0.25rem 0.5rem;border-radius:8px;background:{{ $streak > 0 ? '#FEF3C7' : '#F3F4F6' }};color:{{ $streak > 0 ? '#D97706' : '#9CA3AF' }};">
                                    <i data-lucide="flame" style="width:13px;height:13px;{{ $streak > 0 ? 'color:#D97706;fill:#D97706;' : '' }}"></i>
                                    <span>{{ $streak }}d streak</span>
                                </div>

                                <form method="POST" action="{{ route('habits.destroy', $habit) }}" onsubmit="return confirm('Delete this habit and all its logs?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-ghost" style="color:#DC2626;padding:0.3rem;" title="Delete">
                                        <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align:center;padding:3rem 1rem;">
                    <i data-lucide="sparkles" style="width:48px;height:48px;color:var(--border);margin-bottom:0.75rem;"></i>
                    <div style="font-weight:600;color:var(--text);margin-bottom:0.25rem;">No habits created yet</div>
                    <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:1rem;">Pick a suggested habit below or create your own custom habit.</div>
                    <button onclick="window.openModal('habitModal')" class="btn btn-primary btn-sm">
                        <i data-lucide="plus" style="width:14px;height:14px;"></i> Create Habit
                    </button>
                </div>
            @endif

            <!-- Suggested Habits -->
            <div style="margin-top:2rem;border-top:1px solid var(--border);padding-top:1.25rem;">
                <div style="font-size:0.85rem;font-weight:700;color:var(--text);margin-bottom:0.75rem;">Suggested Healthy Habits</div>
                <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));gap:0.5rem;">
                    @foreach($suggestedHabits as $sug)
                        <button onclick="prefillHabit('{{ $sug['name'] }}', '{{ $sug['category'] }}')" class="btn btn-sm btn-outline" style="justify-content:flex-start;font-size:0.78rem;padding:0.5rem 0.65rem;">
                            <i data-lucide="plus" style="width:13px;height:13px;color:var(--primary);"></i>
                            <span>{{ $sug['name'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Monthly Consistency Calendar -->
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                <div class="section-title">Consistency Calendar</div>
                <span style="font-size:0.78rem;font-weight:600;color:var(--primary);">{{ $dashboard['month_label'] }}</span>
            </div>

            <!-- Weekdays header -->
            <div style="display:grid;grid-template-columns:repeat(7, 1fr);gap:4px;text-align:center;font-size:0.7rem;font-weight:700;color:var(--text-muted);margin-bottom:0.5rem;">
                <div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div>
            </div>

            <!-- Days grid -->
            <div style="display:grid;grid-template-columns:repeat(7, 1fr);gap:4px;text-align:center;">
                @php
                    $firstDay = \Carbon\Carbon::parse($dashboard['calendar_days'][0]['date'] ?? now());
                    $leadEmpty = $firstDay->dayOfWeek;
                @endphp

                @for($i = 0; $i < $leadEmpty; $i++)
                    <div style="height:32px;"></div>
                @endfor

                @foreach($dashboard['calendar_days'] as $cDay)
                    @php
                        $hasCompletions = $cDay['completed_count'] > 0;
                        $isSelected = $cDay['date'] === $date->toDateString();
                    @endphp
                    <a href="{{ route('habits.index', ['date' => $cDay['date']]) }}"
                       style="height:32px;display:flex;align-items:center;justify-content:center;border-radius:8px;font-size:0.75rem;text-decoration:none;font-weight:{{ $isSelected ? '700' : '500' }};
                              background:{{ $isSelected ? 'var(--primary)' : ($hasCompletions ? 'var(--sage-light)' : 'transparent') }};
                              color:{{ $isSelected ? '#fff' : ($hasCompletions ? 'var(--secondary)' : 'var(--text)') }};
                              border:{{ $cDay['is_today'] ? '1px solid var(--primary)' : '1px solid transparent' }};"
                       title="{{ $cDay['completed_count'] }} habits completed on {{ $cDay['date'] }}">
                        {{ $cDay['day'] }}
                    </a>
                @endforeach
            </div>

            <div style="margin-top:1.25rem;padding-top:0.75rem;border-top:1px solid var(--border);display:flex;justify-content:space-between;font-size:0.72rem;color:var(--text-muted);">
                <div style="display:flex;align-items:center;gap:0.35rem;">
                    <span style="width:10px;height:10px;border-radius:2px;background:var(--sage-light);border:1px solid var(--secondary);display:inline-block;"></span>
                    <span>Completed</span>
                </div>
                <div style="display:flex;align-items:center;gap:0.35rem;">
                    <span style="width:10px;height:10px;border-radius:2px;background:var(--primary);display:inline-block;"></span>
                    <span>Selected Date</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Habit Modal -->
<div id="habitModal" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Create New Habit</h3>
                <p class="modal-subtitle">Commit to daily consistency and track your streaks</p>
            </div>
            <button type="button" onclick="window.closeModal('habitModal')" class="modal-close-btn">
                <i data-lucide="x" style="width:18px;height:18px;"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('habits.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required" for="habit_name">Habit Name</label>
                    <input type="text" id="habit_name" name="name" required placeholder="e.g. Morning 10-min meditation" class="form-control" value="{{ old('name') }}" autofocus>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label required" for="habit_category">Category</label>
                        <select id="habit_category" name="category" required class="form-control">
                            <option value="Health">Health</option>
                            <option value="Mindset">Mindset</option>
                            <option value="Fitness">Fitness</option>
                            <option value="Productivity">Productivity</option>
                            <option value="Lifestyle">Lifestyle</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label required" for="frequency">Frequency</label>
                        <select id="frequency" name="frequency" required class="form-control">
                            <option value="daily" selected>Daily</option>
                            <option value="weekdays">Weekdays</option>
                            <option value="weekends">Weekends</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="target_days_per_week">Target Days Per Week (1 to 7)</label>
                    <input type="number" id="target_days_per_week" name="target_days_per_week" min="1" max="7" value="{{ old('target_days_per_week', 7) }}" class="form-control">
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" for="habit_description">Purpose / Motivation</label>
                    <textarea id="habit_description" name="description" rows="2" class="form-control" placeholder="Why is this habit meaningful to you?">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="window.closeModal('habitModal')" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="check" style="width:14px;height:14px;"></i> Save Habit
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
