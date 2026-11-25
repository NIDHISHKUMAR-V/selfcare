@extends('layouts.app')

@section('title', 'Goals & Growth - My Self-Care')
@section('page-title', 'Goals & Aspirations')
@section('page-subtitle', 'Set intentional milestones for health, career, and personal growth')

@section('content')
<div style="display:flex;flex-direction:column;gap:1.5rem;">
    <!-- Top Action and Filters -->
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
        <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
            <a href="{{ route('goals.index', ['category' => 'All', 'type' => $selectedType]) }}"
               class="btn btn-sm {{ $selectedCategory === 'All' ? 'btn-primary' : 'btn-outline' }}">
                All Categories ({{ $data['total'] }})
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('goals.index', ['category' => $cat, 'type' => $selectedType]) }}"
                   class="btn btn-sm {{ $selectedCategory === $cat ? 'btn-primary' : 'btn-outline' }}">
                    {{ $cat }} {{ isset($data['categories'][$cat]) ? '(' . $data['categories'][$cat] . ')' : '' }}
                </a>
            @endforeach
        </div>

        <button onclick="window.openModal('goalModal')" class="btn btn-primary btn-sm">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> New Goal
        </button>
    </div>

    <!-- Summary Statistics Cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:1rem;">
        <div class="stat-card lavender">
            <div class="stat-icon purple"><i data-lucide="target" style="width:20px;height:20px;"></i></div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--text);">{{ $data['total'] }}</div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem;">Total Goals</div>
        </div>

        <div class="stat-card sage">
            <div class="stat-icon" style="background:var(--sage);color:#fff;"><i data-lucide="check-circle" style="width:20px;height:20px;"></i></div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--text);">{{ $data['completed'] }}</div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem;">Achieved Goals</div>
        </div>

        <div class="stat-card peach">
            <div class="stat-icon" style="background:var(--peach);color:#fff;"><i data-lucide="clock" style="width:20px;height:20px;"></i></div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--text);">{{ $data['in_progress'] }}</div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem;">In Progress</div>
        </div>

        <div class="stat-card blue">
            <div class="stat-icon" style="background:#3B82F6;color:#fff;"><i data-lucide="trending-up" style="width:20px;height:20px;"></i></div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--text);">{{ $data['avg_progress'] }}%</div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem;">Average Progress</div>
        </div>
    </div>

    <!-- Goals Grid -->
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;">
            <div class="section-title">
                {{ $selectedCategory === 'All' ? 'All Active Goals' : $selectedCategory . ' Goals' }}
            </div>
            <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.75rem;">
                <span style="color:var(--text-muted);">Filter Type:</span>
                <a href="{{ route('goals.index', ['category' => $selectedCategory, 'type' => 'All']) }}" style="color:{{ $selectedType === 'All' ? 'var(--primary)' : 'var(--text-muted)' }};font-weight:600;text-decoration:none;">All</a>
                <span>•</span>
                <a href="{{ route('goals.index', ['category' => $selectedCategory, 'type' => 'short_term']) }}" style="color:{{ $selectedType === 'short_term' ? 'var(--primary)' : 'var(--text-muted)' }};font-weight:600;text-decoration:none;">Short-term</a>
                <span>•</span>
                <a href="{{ route('goals.index', ['category' => $selectedCategory, 'type' => 'long_term']) }}" style="color:{{ $selectedType === 'long_term' ? 'var(--primary)' : 'var(--text-muted)' }};font-weight:600;text-decoration:none;">Long-term</a>
            </div>
        </div>

        @if($data['goals']->isNotEmpty())
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:1.25rem;">
                @foreach($data['goals'] as $goal)
                    @php
                        $milestonesCount = $goal->milestones->count();
                        $completedMilestones = $goal->milestones->where('is_completed', true)->count();
                    @endphp
                    <div style="border-radius:14px;border:1px solid var(--border);padding:1.25rem;background:#fff;display:flex;flex-direction:column;justify-content:space-between;gap:1rem;transition:box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 4px 14px rgba(0,0,0,0.06)'" onmouseout="this.style.boxShadow='none'">
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:0.5rem;margin-bottom:0.5rem;">
                                <span class="badge" style="background:var(--primary-light);color:var(--primary);">
                                    {{ $goal->category }}
                                </span>
                                <span class="badge" style="background:{{ $goal->status === 'completed' ? 'var(--sage-light)' : ($goal->status === 'on_hold' ? '#FEF3C7' : '#EFF6FF') }};color:{{ $goal->status === 'completed' ? 'var(--secondary)' : ($goal->status === 'on_hold' ? '#D97706' : '#3B82F6') }};">
                                    {{ ucfirst(str_replace('_', ' ', $goal->status)) }}
                                </span>
                            </div>

                            <a href="{{ route('goals.show', $goal) }}" style="text-decoration:none;color:inherit;">
                                <h3 style="font-size:1rem;font-weight:700;color:var(--text);margin-bottom:0.35rem;line-height:1.3;">
                                    {{ $goal->title }}
                                </h3>
                            </a>

                            @if($goal->description)
                                <p style="font-size:0.78rem;color:var(--text-muted);margin-bottom:0.75rem;line-clamp:2;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
                                    {{ $goal->description }}
                                </p>
                            @endif

                            <div style="display:flex;align-items:center;gap:0.75rem;font-size:0.72rem;color:var(--text-muted);margin-bottom:1rem;">
                                <div style="display:flex;align-items:center;gap:0.25rem;">
                                    <i data-lucide="tag" style="width:12px;height:12px;"></i>
                                    <span>{{ $goal->goal_type === 'short_term' ? 'Short Term' : 'Long Term' }}</span>
                                </div>
                                @if($goal->target_date)
                                    <div style="display:flex;align-items:center;gap:0.25rem;">
                                        <i data-lucide="calendar" style="width:12px;height:12px;"></i>
                                        <span>Target: {{ $goal->target_date->format('M d, Y') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div>
                            <!-- Progress Bar -->
                            <div style="margin-bottom:0.75rem;">
                                <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.75rem;font-weight:600;margin-bottom:0.35rem;">
                                    <span>Progress</span>
                                    <span style="color:var(--primary);">{{ $goal->progress_percentage }}%</span>
                                </div>
                                <div style="height:6px;background:var(--border);border-radius:999px;overflow:hidden;">
                                    <div style="height:100%;width:{{ $goal->progress_percentage }}%;background:{{ $goal->progress_percentage === 100 ? 'var(--secondary)' : 'var(--primary)' }};border-radius:999px;"></div>
                                </div>
                            </div>

                            <!-- Footer with Milestones info & Actions -->
                            <div style="display:flex;justify-content:space-between;align-items:center;border-top:1px solid var(--border);padding-top:0.75rem;font-size:0.75rem;">
                                <span style="color:var(--text-muted);">
                                    <i data-lucide="check-square" style="width:12px;height:12px;display:inline-block;vertical-align:middle;"></i>
                                    {{ $completedMilestones }} / {{ $milestonesCount }} milestones
                                </span>

                                <div style="display:flex;align-items:center;gap:0.5rem;">
                                    <a href="{{ route('goals.show', $goal) }}" class="btn btn-sm btn-outline" style="font-size:0.72rem;padding:0.25rem 0.6rem;">
                                        Manage
                                    </a>
                                    <form method="POST" action="{{ route('goals.destroy', $goal) }}" onsubmit="return confirm('Delete this goal and all its milestones?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-ghost" style="color:#DC2626;padding:0.25rem;" title="Delete">
                                            <i data-lucide="trash-2" style="width:13px;height:13px;"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align:center;padding:3rem 1rem;">
                <i data-lucide="target" style="width:48px;height:48px;color:var(--border);margin-bottom:0.75rem;"></i>
                <div style="font-weight:600;color:var(--text);margin-bottom:0.25rem;">No goals found in this view</div>
                <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:1rem;">Turn your dreams into achievable action plans with milestones.</div>
                <button onclick="window.openModal('goalModal')" class="btn btn-primary btn-sm">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> Set New Goal
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Create Goal Modal -->
<div id="goalModal" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Create New Goal</h3>
                <p class="modal-subtitle">Define an intentional vision for your lifestyle and development</p>
            </div>
            <button type="button" onclick="window.closeModal('goalModal')" class="modal-close-btn">
                <i data-lucide="x" style="width:18px;height:18px;"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('goals.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required" for="goal_title">Goal Title</label>
                    <input type="text" id="goal_title" name="title" required placeholder="e.g. Run a 10K marathon, Learn French B1" class="form-control" value="{{ old('title') }}" autofocus>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label required" for="category">Category</label>
                        <select id="category" name="category" required class="form-control">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label required" for="goal_type">Time Horizon</label>
                        <select id="goal_type" name="goal_type" required class="form-control">
                            <option value="short_term" selected>Short-Term (&lt; 6 months)</option>
                            <option value="long_term">Long-Term (1+ years)</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="target_date">Target Deadline</label>
                        <input type="date" id="target_date" name="target_date" value="{{ old('target_date') }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="status">Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="in_progress" selected>In Progress</option>
                            <option value="on_hold">On Hold</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" for="goal_description">Why this goal matters</label>
                    <textarea id="goal_description" name="description" rows="2" class="form-control" placeholder="Outline your motivation and vision...">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="window.closeModal('goalModal')" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="check" style="width:14px;height:14px;"></i> Save Goal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
