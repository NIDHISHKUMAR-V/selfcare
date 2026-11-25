@extends('layouts.app')

@section('title', 'Savings Goals')
@section('page-title', 'Savings Goals')
@section('page-subtitle', 'Set financial targets, track your progress, and build your safety net')

@section('content')

{{-- Overview Hero Card --}}
<div class="card" style="background:linear-gradient(135deg,#E8F5ED 0%,#EDE9FE 100%);border:none;margin-bottom:1.5rem;padding:1.5rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1.5rem;">
        <div style="flex:1;min-width:220px;">
            <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.4rem;">
                <div style="width:34px;height:34px;border-radius:10px;background:var(--sage);color:#fff;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="piggy-bank" style="width:18px;height:18px;"></i>
                </div>
                <div style="font-size:0.8rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;">Savings Milestone</div>
            </div>

            <div style="display:flex;align-items:baseline;gap:0.4rem;margin:0.75rem 0 0.5rem;">
                <span style="font-size:2.2rem;font-weight:800;color:var(--text);line-height:1;">
                    ₹{{ number_format($totalSaved, 2) }}
                </span>
                <span style="font-size:1.1rem;font-weight:600;color:var(--text-muted);">
                    / ₹{{ number_format($totalTarget, 2) }} target
                </span>
            </div>

            <div class="progress-bar" style="height:10px;background:rgba(255,255,255,0.8);margin-top:0.75rem;">
                <div class="progress-fill" style="width:{{ $overallProgress }}%;background:linear-gradient(90deg,var(--sage),var(--primary));"></div>
            </div>

            <div style="display:flex;justify-content:space-between;font-size:0.75rem;font-weight:600;color:var(--text-muted);margin-top:0.35rem;">
                <span>{{ $overallProgress }}% of total goals reached</span>
                <span>₹{{ number_format(max(0, $totalTarget - $totalSaved), 2) }} remaining</span>
            </div>
        </div>

        <button onclick="window.openModal('newGoalModal')" class="btn btn-primary">
            <i data-lucide="plus" style="width:16px;height:16px;"></i> Create New Goal
        </button>
    </div>
</div>

{{-- Goals Grid --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
    <div>
        <div class="section-title">Your Savings Goals</div>
        <div class="section-subtitle">{{ $goals->count() }} goal{{ $goals->count() != 1 ? 's' : '' }} tracked</div>
    </div>
</div>

@if($goals->isEmpty())
    <div class="card" style="text-align:center;padding:3rem 1rem;">
        <i data-lucide="piggy-bank" style="width:48px;height:48px;color:var(--border);margin:0 auto 0.75rem;display:block;"></i>
        <h4 style="font-size:1rem;font-weight:600;color:var(--text);margin:0 0 0.25rem;">No savings goals set yet</h4>
        <p style="font-size:0.82rem;color:var(--text-muted);margin:0 0 1rem;">Start saving for an emergency fund, dream vacation, gadget, or future investment.</p>
        <button onclick="window.openModal('newGoalModal')" class="btn btn-primary btn-sm">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> Create Your First Goal
        </button>
    </div>
@else
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(290px,1fr));gap:1rem;">
        @foreach($goals as $goal)
            @php
                $pct = $goal->progress_percentage;
                $isReached = $goal->status === 'reached' || $pct >= 100;
            @endphp
            <div class="card" style="display:flex;flex-direction:column;justify-content:space-between;">
                <div>
                    {{-- Header --}}
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.5rem;margin-bottom:0.75rem;">
                        <div>
                            <div style="font-size:1rem;font-weight:700;color:var(--text);">{{ $goal->goal_name }}</div>
                            @if($goal->target_date)
                                <div style="font-size:0.72rem;color:var(--text-muted);display:flex;align-items:center;gap:0.3rem;margin-top:2px;">
                                    <i data-lucide="calendar" style="width:12px;height:12px;"></i>
                                    Target: {{ $goal->target_date->format('M d, Y') }}
                                    @if(!$isReached)
                                        @php $daysLeft = Carbon\Carbon::today()->diffInDays($goal->target_date, false); @endphp
                                        <span>· {{ $daysLeft > 0 ? "{$daysLeft} days left" : 'Past target date' }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div>
                            @if($isReached)
                                <span class="badge" style="background:#DCFCE7;color:#16A34A;">🏆 Reached!</span>
                            @elseif($goal->status === 'cancelled')
                                <span class="badge" style="background:#F3F4F6;color:#6B7280;">Cancelled</span>
                            @else
                                <span class="badge badge-purple">{{ $pct }}%</span>
                            @endif
                        </div>
                    </div>

                    {{-- Amounts --}}
                    <div style="display:flex;align-items:baseline;gap:0.3rem;margin-bottom:0.5rem;">
                        <span style="font-size:1.4rem;font-weight:700;color:var(--text);">
                            ₹{{ number_format($goal->current_saved_amount, 2) }}
                        </span>
                        <span style="font-size:0.85rem;color:var(--text-muted);">
                            / ₹{{ number_format($goal->target_amount, 2) }}
                        </span>
                    </div>

                    {{-- Progress Meter --}}
                    <div class="progress-bar" style="height:8px;">
                        <div class="progress-fill" style="width:{{ $pct }}%;background:{{ $isReached ? 'var(--sage)' : 'var(--primary)' }};"></div>
                    </div>

                    <div style="display:flex;justify-content:space-between;font-size:0.7rem;color:var(--text-muted);margin-top:0.3rem;">
                        <span>{{ $pct }}% saved</span>
                        <span>
                            @if($isReached)
                                Completed!
                            @else
                                ₹{{ number_format(max(0, $goal->target_amount - $goal->current_saved_amount), 2) }} to go
                            @endif
                        </span>
                    </div>

                    @if($goal->notes)
                        <p style="font-size:0.75rem;color:var(--text-muted);margin-top:0.6rem;font-style:italic;">
                            "{{ $goal->notes }}"
                        </p>
                    @endif
                </div>

                {{-- Action Bar --}}
                <div style="margin-top:1rem;border-top:1px solid var(--border);padding-top:0.75rem;display:flex;align-items:center;justify-content:space-between;">
                    @if(!$isReached && $goal->status !== 'cancelled')
                        <button type="button"
                                onclick="openAddFundsModal({{ $goal->id }}, '{{ addslashes($goal->goal_name) }}')"
                                class="btn btn-sage btn-sm" style="font-size:0.75rem;padding:0.3rem 0.65rem;">
                            <i data-lucide="plus" style="width:12px;height:12px;"></i> Add Funds
                        </button>
                    @else
                        <span style="font-size:0.75rem;color:var(--sage);font-weight:600;">Goal Achieved!</span>
                    @endif

                    <div style="display:flex;gap:0.3rem;">
                        <button type="button"
                                onclick="openEditGoalModal({{ $goal->id }}, '{{ addslashes($goal->goal_name) }}', {{ $goal->target_amount }}, {{ $goal->current_saved_amount }}, '{{ $goal->target_date ? $goal->target_date->toDateString() : '' }}', '{{ addslashes($goal->notes ?? '') }}', '{{ $goal->status }}')"
                                class="btn btn-outline btn-sm" style="padding:0.25rem 0.45rem;" title="Edit Goal">
                            <i data-lucide="edit-2" style="width:12px;height:12px;"></i>
                        </button>

                        <form method="POST" action="{{ route('savings.destroy', $goal) }}" onsubmit="return confirm('Remove savings goal?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline btn-sm" style="padding:0.25rem 0.45rem;color:#DC2626;" title="Delete">
                                <i data-lucide="trash-2" style="width:12px;height:12px;"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

{{-- Create Goal Modal --}}
<div id="newGoalModal" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">New Savings Goal</h3>
                <p class="modal-subtitle">Set a target, track progress, and reach financial milestones</p>
            </div>
            <button type="button" onclick="window.closeModal('newGoalModal')" class="modal-close-btn">
                <i data-lucide="x" style="width:18px;height:18px;"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('savings.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required" for="goal_name">Goal Name</label>
                    <input type="text" id="goal_name" name="goal_name" class="form-control"
                           placeholder="e.g. Emergency Fund, New Laptop, Holiday" required>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label required" for="target_amount">Target Amount (₹)</label>
                        <input type="number" id="target_amount" name="target_amount" class="form-control"
                               step="1" min="1" placeholder="50000" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="current_saved_amount">Already Saved (₹)</label>
                        <input type="number" id="current_saved_amount" name="current_saved_amount" class="form-control"
                               step="1" min="0" value="0">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="target_date">Target Date</label>
                    <input type="date" id="target_date" name="target_date" class="form-control"
                           min="{{ now()->toDateString() }}">
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="form-control" rows="2"
                              placeholder="Why is this goal important?"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="window.closeModal('newGoalModal')" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="piggy-bank" style="width:14px;height:14px;"></i> Create Goal
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Add Funds Modal --}}
<div id="addFundsModal" class="modal-overlay">
    <div class="modal-card" style="max-width:420px;">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="addFundsTitle">Deposit Savings</h3>
                <p class="modal-subtitle">Add funds towards this savings goal</p>
            </div>
            <button type="button" onclick="window.closeModal('addFundsModal')" class="modal-close-btn">
                <i data-lucide="x" style="width:18px;height:18px;"></i>
            </button>
        </div>

        <form id="addFundsForm" method="POST" action="">
            @csrf
            <div class="modal-body">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label required" for="deposit_amount">Amount to Deposit (₹)</label>
                    <input type="number" id="deposit_amount" name="amount" class="form-control"
                           step="1" min="1" placeholder="e.g. 1000" required autofocus>
                    <div class="form-hint">Enter the amount you want to add to this goal today.</div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="window.closeModal('addFundsModal')" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-sage">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i> Deposit Funds
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Goal Modal --}}
<div id="editGoalModal" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Edit Savings Goal</h3>
                <p class="modal-subtitle">Update the details of your savings goal</p>
            </div>
            <button type="button" onclick="window.closeModal('editGoalModal')" class="modal-close-btn">
                <i data-lucide="x" style="width:18px;height:18px;"></i>
            </button>
        </div>

        <form id="editGoalForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required" for="edit_goal_name">Goal Name</label>
                    <input type="text" id="edit_goal_name" name="goal_name" class="form-control" required>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label required" for="edit_target_amount">Target Amount (₹)</label>
                        <input type="number" id="edit_target_amount" name="target_amount" class="form-control" step="1" min="1" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="edit_current_saved_amount">Current Saved (₹)</label>
                        <input type="number" id="edit_current_saved_amount" name="current_saved_amount" class="form-control" step="1" min="0">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="edit_target_date">Target Date</label>
                        <input type="date" id="edit_target_date" name="target_date" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="edit_status">Status</label>
                        <select id="edit_status" name="status" class="form-control">
                            <option value="active">Active</option>
                            <option value="reached">Reached</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" for="edit_notes">Notes</label>
                    <textarea id="edit_notes" name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="window.closeModal('editGoalModal')" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="check" style="width:14px;height:14px;"></i> Update Goal
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
