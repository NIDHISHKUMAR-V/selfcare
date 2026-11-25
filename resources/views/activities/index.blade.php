@extends('layouts.app')

@section('title', 'Daily Activities - My Self-Care')
@section('page-title', 'Daily Activities')
@section('page-subtitle', 'Manage your daily tasks and routines')

@section('content')
<div style="display:flex;flex-direction:column;gap:1.5rem;">
    <!-- Date Navigator and Actions -->
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
        <div style="display:flex;align-items:center;gap:0.5rem;">
            <a href="{{ route('activities.index', ['date' => $prevDate]) }}" class="btn btn-sm btn-outline">
                <i data-lucide="chevron-left" style="width:14px;height:14px;"></i>
            </a>
            <form method="GET" action="{{ route('activities.index') }}" style="display:inline-flex;align-items:center;">
                <input type="date" name="date" value="{{ $date->toDateString() }}" onchange="this.form.submit()"
                       class="form-control" style="padding:0.35rem 0.65rem;font-size:0.85rem;font-weight:600;width:auto;cursor:pointer;">
            </form>
            <a href="{{ route('activities.index', ['date' => $nextDate]) }}" class="btn btn-sm btn-outline">
                <i data-lucide="chevron-right" style="width:14px;height:14px;"></i>
            </a>
            @if(!$date->isToday())
                <a href="{{ route('activities.index') }}" class="btn btn-sm btn-ghost" style="font-size:0.75rem;">Today</a>
            @endif
        </div>

        <button onclick="window.openModal('taskModal')" class="btn btn-primary btn-sm">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> Add Task
        </button>
    </div>

    <!-- Summary Statistics Cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:1rem;">
        <div class="stat-card lavender">
            <div class="stat-icon purple"><i data-lucide="list-checks" style="width:20px;height:20px;"></i></div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--text);">{{ $overview['total'] }}</div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem;">Total Tasks</div>
        </div>

        <div class="stat-card sage">
            <div class="stat-icon" style="background:var(--sage);color:#fff;"><i data-lucide="check-circle" style="width:20px;height:20px;"></i></div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--text);">{{ $overview['completed'] }}</div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem;">Completed</div>
        </div>

        <div class="stat-card peach">
            <div class="stat-icon" style="background:var(--peach);color:#fff;"><i data-lucide="clock" style="width:20px;height:20px;"></i></div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--text);">{{ $overview['in_progress'] }}</div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem;">In Progress</div>
        </div>

        <div class="stat-card blue">
            <div class="stat-icon" style="background:#3B82F6;color:#fff;"><i data-lucide="percent" style="width:20px;height:20px;"></i></div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--text);">{{ $overview['completion_rate'] }}%</div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem;">Completion Rate</div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="card" style="padding:1rem 1.25rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;font-size:0.8rem;font-weight:600;">
            <span>Daily Progress</span>
            <span style="color:var(--primary);">{{ $overview['completed'] }} of {{ $overview['total'] }} tasks completed</span>
        </div>
        <div style="height:8px;background:var(--border);border-radius:999px;overflow:hidden;">
            <div style="height:100%;width:{{ $overview['completion_rate'] }}%;background:var(--primary);border-radius:999px;transition:width 0.3s ease;"></div>
        </div>
    </div>

    <!-- Tasks List -->
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <div class="section-title">Tasks for {{ $date->format('l, F j, Y') }}</div>
            <span style="font-size:0.75rem;color:var(--text-muted);">{{ $overview['total'] }} tasks scheduled</span>
        </div>

        @if($overview['activities']->isNotEmpty())
            <div style="display:flex;flex-direction:column;gap:0.6rem;">
                @foreach($overview['activities'] as $act)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:0.75rem 1rem;border-radius:12px;border:1px solid var(--border);background:{{ $act->status === 'completed' ? '#F9FAFB' : '#fff' }};gap:0.75rem;">
                        <div style="display:flex;align-items:center;gap:0.75rem;flex:1;overflow:hidden;">
                            <!-- Checkbox Toggle -->
                            <form method="POST" action="{{ route('activities.toggle', $act) }}" style="display:inline;">
                                @csrf
                                <button type="submit" style="background:none;border:none;cursor:pointer;padding:0;display:flex;align-items:center;color:{{ $act->status === 'completed' ? 'var(--secondary)' : 'var(--text-muted)' }};">
                                    @if($act->status === 'completed')
                                        <i data-lucide="check-circle-2" style="width:20px;height:20px;color:var(--secondary);fill:var(--sage-light);"></i>
                                    @else
                                        <i data-lucide="circle" style="width:20px;height:20px;"></i>
                                    @endif
                                </button>
                            </form>

                            <div style="flex:1;overflow:hidden;">
                                <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                                    <span style="font-size:0.88rem;font-weight:600;color:var(--text);{{ $act->status === 'completed' ? 'text-decoration:line-through;color:var(--text-muted);' : '' }}">
                                        {{ $act->title }}
                                    </span>
                                    <span class="badge" style="background:var(--primary-light);color:var(--primary);">
                                        {{ $act->category }}
                                    </span>
                                    @if($act->priority === 'high')
                                        <span class="badge" style="background:#FEE2E2;color:#DC2626;">High</span>
                                    @elseif($act->priority === 'medium')
                                        <span class="badge" style="background:var(--peach-light);color:#D97706;">Medium</span>
                                    @else
                                        <span class="badge" style="background:var(--sage-light);color:var(--secondary);">Low</span>
                                    @endif
                                </div>
                                @if($act->description)
                                    <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.2rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                        {{ $act->description }}
                                    </div>
                                @endif
                                @if($act->start_time || $act->end_time)
                                    <div style="font-size:0.7rem;color:var(--text-muted);margin-top:0.2rem;display:flex;align-items:center;gap:0.25rem;">
                                        <i data-lucide="clock" style="width:11px;height:11px;"></i>
                                        <span>
                                            {{ $act->start_time ? \Carbon\Carbon::parse($act->start_time)->format('g:i A') : '' }}
                                            {{ $act->end_time ? ' - ' . \Carbon\Carbon::parse($act->end_time)->format('g:i A') : '' }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div style="display:flex;align-items:center;gap:0.25rem;">
                            <form method="POST" action="{{ route('activities.destroy', $act) }}" onsubmit="return confirm('Delete this task?');">
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
                <i data-lucide="calendar-check" style="width:48px;height:48px;color:var(--border);margin-bottom:0.75rem;"></i>
                <div style="font-weight:600;color:var(--text);margin-bottom:0.25rem;">No tasks scheduled for today</div>
                <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:1rem;">Start your day with peaceful clarity by adding a daily task.</div>
                <button onclick="window.openModal('taskModal')" class="btn btn-primary btn-sm">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> Add First Task
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Add Task Modal -->
<div id="taskModal" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Add Daily Task</h3>
                <p class="modal-subtitle">Schedule a routine or actionable item for your day</p>
            </div>
            <button type="button" onclick="window.closeModal('taskModal')" class="modal-close-btn">
                <i data-lucide="x" style="width:18px;height:18px;"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('activities.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required" for="title">Task Title</label>
                    <input type="text" id="title" name="title" required placeholder="e.g. Morning meditation, Review design docs" class="form-control" value="{{ old('title') }}" autofocus>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label required" for="category">Category</label>
                        <select id="category" name="category" required class="form-control">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label required" for="priority">Priority</label>
                        <select id="priority" name="priority" required class="form-control">
                            <option value="low">Low Priority</option>
                            <option value="medium" selected>Medium Priority</option>
                            <option value="high">High Priority</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label required" for="activity_date">Date</label>
                        <input type="date" id="activity_date" name="activity_date" required value="{{ old('activity_date', $date->toDateString()) }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="status">Initial Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="pending" selected>Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="start_time">Start Time (Optional)</label>
                        <input type="time" id="start_time" name="start_time" value="{{ old('start_time') }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="end_time">End Time (Optional)</label>
                        <input type="time" id="end_time" name="end_time" value="{{ old('end_time') }}" class="form-control">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" for="description">Notes / Details</label>
                    <textarea id="description" name="description" rows="2" class="form-control" placeholder="Add helpful details or checklists...">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="window.closeModal('taskModal')" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="check" style="width:14px;height:14px;"></i> Save Task
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
