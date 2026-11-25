@extends('layouts.app')

@section('title', 'Budget Planner')
@section('page-title', 'Budget Management')
@section('page-subtitle', 'Set monthly and category budgets, track spending limits, and avoid overspending')

@section('content')

{{-- Month Selector Bar --}}
<div class="card" style="margin-bottom:1.5rem;padding:0.9rem 1.25rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
        <div style="display:flex;align-items:center;gap:0.75rem;">
            <i data-lucide="pie-chart" style="width:20px;height:20px;color:var(--primary);"></i>
            <div>
                <h3 style="font-size:0.95rem;font-weight:700;color:var(--text);margin:0;">Budget for {{ $summary['month_label'] }}</h3>
                <p style="font-size:0.72rem;color:var(--text-muted);margin:0;">Spending controls & category allocations</p>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:0.6rem;">
            <form method="GET" action="{{ route('budgets.index') }}">
                <input type="month" name="month" value="{{ $currentMonth }}" class="form-control"
                       style="padding:0.35rem 0.65rem;font-size:0.8rem;width:150px;border-radius:8px;" onchange="this.form.submit()">
            </form>
            <button onclick="openBudgetModal(null, '', {{ $summary['monthly_budget_limit'] }}, 80)" class="btn btn-primary btn-sm">
                <i data-lucide="plus" style="width:14px;height:14px;"></i> Set Budget Limit
            </button>
        </div>
    </div>
</div>

{{-- Overall Budget Hero Card --}}
<div class="card" style="background:linear-gradient(135deg,#EDE9FE 0%,#FEF3E8 100%);border:none;margin-bottom:1.5rem;padding:1.5rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
        <div style="flex:1;min-width:240px;">
            <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.25rem;">
                <span class="badge" style="background:var(--primary);color:#fff;">Overall Monthly Budget</span>
                @if($summary['budget_status'] === 'exceeded')
                    <span class="badge badge-red">Limit Exceeded!</span>
                @elseif($summary['budget_status'] === 'warning')
                    <span class="badge badge-orange">80%+ Alert</span>
                @else
                    <span class="badge badge-green">On Track</span>
                @endif
            </div>

            <div style="display:flex;align-items:baseline;gap:0.4rem;margin:0.75rem 0 0.5rem;">
                <span style="font-size:2.2rem;font-weight:800;color:var(--text);line-height:1;">
                    ₹{{ number_format($summary['total_expenses'], 2) }}
                </span>
                <span style="font-size:1.1rem;font-weight:600;color:var(--text-muted);">
                    / ₹{{ number_format($summary['monthly_budget_limit'], 2) }} limit
                </span>
            </div>

            {{-- Progress Bar --}}
            <div class="progress-bar" style="height:10px;background:rgba(255,255,255,0.8);margin-top:0.75rem;">
                <div class="progress-fill" style="width:{{ min(100, $summary['budget_spent_percent']) }}%;background:{{ $summary['budget_status'] === 'exceeded' ? '#DC2626' : ($summary['budget_status'] === 'warning' ? '#F4A261' : 'var(--sage)') }};"></div>
            </div>

            <div style="display:flex;justify-content:space-between;font-size:0.75rem;font-weight:600;color:var(--text-muted);margin-top:0.4rem;">
                <span>{{ $summary['budget_spent_percent'] }}% Utilized</span>
                <span>
                    @if($summary['budget_status'] === 'exceeded')
                        <span style="color:#DC2626;">₹{{ number_format($summary['total_expenses'] - $summary['monthly_budget_limit'], 2) }} over limit</span>
                    @else
                        ₹{{ number_format($summary['budget_remaining'], 2) }} left to spend
                    @endif
                </span>
            </div>
        </div>

        <div style="background:rgba(255,255,255,0.85);backdrop-filter:blur(8px);padding:1.25rem;border-radius:16px;text-align:center;min-width:180px;">
            <div style="font-size:0.72rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;">Daily Safe Spend</div>
            @php
                $daysRemaining = max(1, \Carbon\Carbon::createFromFormat('Y-m', $currentMonth)->endOfMonth()->day - \Carbon\Carbon::today()->day + 1);
                $safeDaily = max(0, $summary['budget_remaining'] / $daysRemaining);
            @endphp
            <div style="font-size:1.4rem;font-weight:700;color:var(--text);margin:0.25rem 0;">
                ₹{{ number_format($safeDaily, 0) }}<span style="font-size:0.75rem;font-weight:500;color:var(--text-muted);">/day</span>
            </div>
            <div style="font-size:0.7rem;color:var(--text-muted);">For remaining {{ $daysRemaining }} days</div>
        </div>
    </div>
</div>

{{-- Category-wise Budgets --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
    <div>
        <div class="section-title">Category Spending Limits</div>
        <div class="section-subtitle">Set boundaries for specific expenses like Food, Shopping, or Bills</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1rem;">
    @foreach($categoryBudgets as $cb)
        <div class="card" style="display:flex;flex-direction:column;justify-content:space-between;">
            <div>
                {{-- Category Header --}}
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem;">
                    <div style="display:flex;align-items:center;gap:0.6rem;">
                        <div style="width:34px;height:34px;border-radius:10px;background:{{ $cb['category_color'] ? $cb['category_color'].'20' : 'var(--primary-light)' }};display:flex;align-items:center;justify-content:center;">
                            <i data-lucide="{{ $cb['category_icon'] ?: 'tag' }}" style="width:16px;height:16px;color:{{ $cb['category_color'] ?: 'var(--primary)' }};"></i>
                        </div>
                        <div>
                            <div style="font-size:0.9rem;font-weight:700;color:var(--text);">{{ $cb['category_name'] }}</div>
                            <div style="font-size:0.7rem;color:var(--text-muted);">
                                Spent: ₹{{ number_format($cb['spent'], 2) }}
                            </div>
                        </div>
                    </div>

                    @if($cb['amount_limit'] > 0)
                        @if($cb['status'] === 'exceeded')
                            <span class="badge badge-red">Exceeded</span>
                        @elseif($cb['status'] === 'warning')
                            <span class="badge badge-orange">Near Limit</span>
                        @else
                            <span class="badge badge-green">Good</span>
                        @endif
                    @else
                        <span class="badge" style="background:var(--bg);color:var(--text-muted);">No Limit</span>
                    @endif
                </div>

                {{-- Progress Bar if Budget Set --}}
                @if($cb['amount_limit'] > 0)
                    <div style="margin:0.5rem 0;">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width:{{ min(100, $cb['percent_used']) }}%;background:{{ $cb['status'] === 'exceeded' ? '#DC2626' : ($cb['status'] === 'warning' ? '#F4A261' : 'var(--sage)') }};"></div>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:0.7rem;color:var(--text-muted);margin-top:0.3rem;">
                            <span>{{ $cb['percent_used'] }}% used of ₹{{ number_format($cb['amount_limit'], 0) }}</span>
                            <span>₹{{ number_format($cb['remaining'], 2) }} left</span>
                        </div>
                    </div>
                @else
                    <p style="font-size:0.75rem;color:var(--text-muted);margin:0.5rem 0;">No spending limit defined for this category.</p>
                @endif
            </div>

            {{-- Actions --}}
            <div style="margin-top:0.75rem;border-top:1px solid var(--border);padding-top:0.75rem;display:flex;align-items:center;justify-content:space-between;">
                <button type="button"
                        onclick="openBudgetModal({{ $cb['category_id'] }}, '{{ $cb['category_name'] }}', {{ $cb['amount_limit'] ?: "''" }}, 80)"
                        class="btn btn-outline btn-sm" style="font-size:0.75rem;padding:0.25rem 0.6rem;">
                    <i data-lucide="edit-3" style="width:12px;height:12px;"></i>
                    {{ $cb['amount_limit'] > 0 ? 'Edit Limit' : 'Set Limit' }}
                </button>

                @if($cb['budget_id'])
                    <form method="POST" action="{{ route('budgets.destroy', $cb['budget_id']) }}" onsubmit="return confirm('Remove budget limit for {{ $cb['category_name'] }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline btn-sm" style="padding:0.25rem 0.5rem;color:#DC2626;" title="Remove Limit">
                            <i data-lucide="trash-2" style="width:12px;height:12px;"></i>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @endforeach
</div>

{{-- Set/Edit Budget Modal --}}
<div id="budgetModal" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="budgetModalTitle">Set Budget Limit</h3>
                <p class="modal-subtitle">Control monthly spending limits and warnings</p>
            </div>
            <button type="button" onclick="window.closeModal('budgetModal')" class="modal-close-btn">
                <i data-lucide="x" style="width:18px;height:18px;"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('budgets.store') }}">
            @csrf
            <input type="hidden" name="month_year" value="{{ $currentMonth }}">
            <input type="hidden" name="expense_category_id" id="modal_category_id" value="">

            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required" for="modal_amount_limit">Amount Limit ($)</label>
                    <input type="number" id="modal_amount_limit" name="amount_limit" class="form-control"
                           step="1" min="1" placeholder="e.g. 500" required autofocus>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label" for="modal_alert_threshold">Warning Alert Threshold (%)</label>
                    <input type="number" id="modal_alert_threshold" name="alert_threshold_percentage" class="form-control"
                           min="10" max="100" value="80">
                    <div class="form-hint">
                        Show a warning badge when spending reaches this percentage of the limit (default 80%).
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="window.closeModal('budgetModal')" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Budget Limit</button>
            </div>
        </form>
    </div>
</div>
@endsection
