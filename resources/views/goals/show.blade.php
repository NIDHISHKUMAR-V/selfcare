@extends('layouts.app')

@section('title', $goal->title . ' - My Self-Care')
@section('page-title', 'Goal Details')
@section('page-subtitle', 'Milestones and progress tracker')

@section('content')
<div style="display:flex;flex-direction:column;gap:1.5rem;">
    <!-- Back Button -->
    <div>
        <a href="{{ route('goals.index') }}" class="btn btn-sm btn-outline" style="display:inline-flex;align-items:center;gap:0.35rem;">
            <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Back to Goals
        </a>
    </div>

    <!-- Goal Hero Card -->
    <div class="card" style="padding:1.5rem;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;">
            <div>
                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem;">
                    <span class="badge" style="background:var(--primary-light);color:var(--primary);">
                        {{ $goal->category }}
                    </span>
                    <span class="badge" style="background:{{ $goal->status === 'completed' ? 'var(--sage-light)' : '#EFF6FF' }};color:{{ $goal->status === 'completed' ? 'var(--secondary)' : '#3B82F6' }};">
                        {{ ucfirst(str_replace('_', ' ', $goal->status)) }}
                    </span>
                    <span style="font-size:0.75rem;color:var(--text-muted);">
                        {{ $goal->goal_type === 'short_term' ? 'Short Term' : 'Long Term' }}
                    </span>
                </div>
                <h2 style="font-size:1.4rem;font-weight:700;color:var(--text);margin:0 0 0.5rem 0;">{{ $goal->title }}</h2>
                @if($goal->description)
                    <p style="font-size:0.85rem;color:var(--text-muted);margin:0;max-width:700px;line-height:1.5;">
                        {{ $goal->description }}
                    </p>
                @endif
            </div>

            <div style="text-align:right;">
                @if($goal->target_date)
                    <div style="font-size:0.75rem;color:var(--text-muted);">Target Deadline</div>
                    <div style="font-size:0.95rem;font-weight:700;color:var(--text);">{{ $goal->target_date->format('M d, Y') }}</div>
                @endif
            </div>
        </div>

        <!-- Overall Progress Bar -->
        <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border);">
            <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.8rem;font-weight:600;margin-bottom:0.5rem;">
                <span>Overall Completion</span>
                <span style="color:var(--primary);font-size:0.9rem;">{{ $goal->progress_percentage }}%</span>
            </div>
            <div style="height:10px;background:var(--border);border-radius:999px;overflow:hidden;">
                <div style="height:100%;width:{{ $goal->progress_percentage }}%;background:{{ $goal->progress_percentage === 100 ? 'var(--secondary)' : 'var(--primary)' }};border-radius:999px;transition:width 0.4s ease;"></div>
            </div>
        </div>
    </div>

    <!-- Milestones Section -->
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;">
            <div>
                <div class="section-title">Milestones & Action Steps</div>
                <div style="font-size:0.75rem;color:var(--text-muted);">Completing milestones automatically advances your goal progress.</div>
            </div>
            <button onclick="window.openModal('milestoneModal')" class="btn btn-primary btn-sm">
                <i data-lucide="plus" style="width:14px;height:14px;"></i> Add Milestone
            </button>
        </div>

        @if($goal->milestones->isNotEmpty())
            <div style="display:flex;flex-direction:column;gap:0.6rem;">
                @foreach($goal->milestones as $milestone)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:0.75rem 1rem;border-radius:12px;border:1px solid var(--border);background:{{ $milestone->is_completed ? 'var(--sage-light)' : '#fff' }};gap:0.75rem;">
                        <div style="display:flex;align-items:center;gap:0.75rem;flex:1;">
                            <form method="POST" action="{{ route('goals.milestones.toggle', $milestone) }}" style="display:inline;">
                                @csrf
                                <button type="submit" style="background:none;border:none;cursor:pointer;padding:0;display:flex;align-items:center;">
                                    @if($milestone->is_completed)
                                        <i data-lucide="check-circle-2" style="width:22px;height:22px;color:var(--secondary);fill:#fff;"></i>
                                    @else
                                        <i data-lucide="circle" style="width:22px;height:22px;color:var(--text-muted);"></i>
                                    @endif
                                </button>
                            </form>

                            <div style="flex:1;">
                                <span style="font-size:0.9rem;font-weight:600;color:var(--text);{{ $milestone->is_completed ? 'text-decoration:line-through;color:var(--text-muted);' : '' }}">
                                    {{ $milestone->title }}
                                </span>
                                @if($milestone->due_date)
                                    <div style="font-size:0.72rem;color:var(--text-muted);margin-top:0.15rem;">
                                        Due: {{ $milestone->due_date->format('M d, Y') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <form method="POST" action="{{ route('goals.milestones.destroy', $milestone) }}" onsubmit="return confirm('Delete this milestone?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-ghost" style="color:#DC2626;padding:0.3rem;" title="Delete">
                                <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align:center;padding:2.5rem 1rem;">
                <i data-lucide="list-plus" style="width:40px;height:40px;color:var(--border);margin-bottom:0.5rem;"></i>
                <div style="font-weight:600;color:var(--text);margin-bottom:0.25rem;">No milestones created yet</div>
                <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:1rem;">Break this goal down into bite-sized actionable steps.</div>
                <button onclick="window.openModal('milestoneModal')" class="btn btn-primary btn-sm">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> Add First Milestone
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Add Milestone Modal -->
<div id="milestoneModal" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Add Goal Milestone</h3>
                <p class="modal-subtitle">Break your goal down into clear actionable steps</p>
            </div>
            <button type="button" onclick="window.closeModal('milestoneModal')" class="modal-close-btn">
                <i data-lucide="x" style="width:18px;height:18px;"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('goals.milestones.store', $goal) }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required" for="milestone_title">Milestone Title</label>
                    <input type="text" id="milestone_title" name="title" required placeholder="e.g. Complete module 1, Run 5K nonstop" class="form-control" value="{{ old('title') }}" autofocus>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" for="milestone_due_date">Target Due Date (Optional)</label>
                    <input type="date" id="milestone_due_date" name="due_date" value="{{ old('due_date') }}" class="form-control">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="window.closeModal('milestoneModal')" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="check" style="width:14px;height:14px;"></i> Save Milestone
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
