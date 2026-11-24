@extends('layouts.app')

@section('title', 'Money Manager')
@section('page-title', 'Money Manager')
@section('page-subtitle', 'Track your income, expenses, and personal cash flow')

@section('content')

{{-- Monthly Financial KPI Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:1rem;margin-bottom:1.5rem;">
    {{-- Income --}}
    <div class="stat-card sage">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.4rem;">
            <div class="stat-label" style="margin:0;font-size:0.8rem;">Monthly Income</div>
            <i data-lucide="arrow-down-left" style="width:18px;height:18px;color:var(--sage);"></i>
        </div>
        <div class="stat-value" style="color:var(--sage);">₹{{ number_format($summary['total_income'], 2) }}</div>
        <div class="stat-change" style="color:var(--sage);">
            {{ $summary['month_label'] }}
        </div>
    </div>

    {{-- Expenses --}}
    <div class="stat-card peach">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.4rem;">
            <div class="stat-label" style="margin:0;font-size:0.8rem;">Monthly Expenses</div>
            <i data-lucide="arrow-up-right" style="width:18px;height:18px;color:#C2500C;"></i>
        </div>
        <div class="stat-value" style="color:#C2500C;">₹{{ number_format($summary['total_expenses'], 2) }}</div>
        <div class="stat-change" style="color:#C2500C;">
            Today: ₹{{ number_format($summary['today_expenses'], 2) }} · Week: ₹{{ number_format($summary['week_expenses'], 2) }}
        </div>
    </div>

    {{-- Net Balance --}}
    <div class="stat-card {{ $summary['net_balance'] >= 0 ? 'lavender' : '' }}" style="{{ $summary['net_balance'] < 0 ? 'background:#FEF2F2;border-color:#FECACA;' : '' }}">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.4rem;">
            <div class="stat-label" style="margin:0;font-size:0.8rem;">Net Balance</div>
            <i data-lucide="wallet" style="width:18px;height:18px;color:{{ $summary['net_balance'] >= 0 ? 'var(--primary)' : '#DC2626' }};"></i>
        </div>
        <div class="stat-value" style="color:{{ $summary['net_balance'] >= 0 ? 'var(--primary)' : '#DC2626' }};">
            {{ $summary['net_balance'] < 0 ? '-' : '' }}₹{{ number_format(abs($summary['net_balance']), 2) }}
        </div>
        <div class="stat-change" style="color:{{ $summary['net_balance'] >= 0 ? 'var(--primary)' : '#DC2626' }};">
            Savings Rate: {{ $summary['savings_rate'] }}%
        </div>
    </div>

    {{-- Budget Overview --}}
    <div class="stat-card {{ $summary['budget_status'] === 'exceeded' ? '' : ($summary['budget_status'] === 'warning' ? 'peach' : 'sage') }}"
         style="{{ $summary['budget_status'] === 'exceeded' ? 'background:#FEF2F2;border-color:#FECACA;' : '' }}">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.4rem;">
            <div class="stat-label" style="margin:0;font-size:0.8rem;">Budget Remaining</div>
            <i data-lucide="pie-chart" style="width:18px;height:18px;color:{{ $summary['budget_status'] === 'exceeded' ? '#DC2626' : 'var(--sage)' }};"></i>
        </div>
        <div class="stat-value" style="{{ $summary['budget_status'] === 'exceeded' ? 'color:#DC2626;' : '' }}">
            ₹{{ number_format($summary['budget_remaining'], 2) }}
        </div>
        <div style="margin-top:0.4rem;">
            <div class="progress-bar">
                <div class="progress-fill" style="width:{{ min(100, $summary['budget_spent_percent']) }}%;background:{{ $summary['budget_status'] === 'exceeded' ? '#DC2626' : ($summary['budget_status'] === 'warning' ? '#F4A261' : 'var(--sage)') }};"></div>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:0.7rem;color:var(--text-muted);margin-top:0.25rem;">
                <span>{{ $summary['budget_spent_percent'] }}% spent</span>
                <span>Limit: ₹{{ number_format($summary['monthly_budget_limit'], 0) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Budget Alert Banner --}}
@if($summary['budget_status'] === 'exceeded')
    <div class="alert alert-error" style="margin-bottom:1.5rem;">
        <i data-lucide="alert-triangle" style="width:18px;height:18px;flex-shrink:0;"></i>
        <div>
            <strong>Budget Warning:</strong> You have exceeded your monthly spending limit by
            <strong>₹{{ number_format($summary['total_expenses'] - $summary['monthly_budget_limit'], 2) }}</strong>!
            Review your expenses to stay on track.
        </div>
    </div>
@elseif($summary['budget_status'] === 'warning')
    <div class="alert" style="background:#FFFBEB;border:1px solid #FDE68A;color:#B45309;margin-bottom:1.5rem;">
        <i data-lucide="alert-circle" style="width:18px;height:18px;flex-shrink:0;"></i>
        <div>
            <strong>Budget Notice:</strong> You have consumed <strong>{{ $summary['budget_spent_percent'] }}%</strong>
            of your monthly budget limit. Only <strong>₹{{ number_format($summary['budget_remaining'], 2) }}</strong> remaining.
        </div>
    </div>
@endif

{{-- Action Toolbar & Filters --}}
<div class="card" style="margin-bottom:1.5rem;padding:1rem 1.25rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">

        {{-- Type Tabs --}}
        <div style="display:flex;align-items:center;gap:0.35rem;background:var(--bg);padding:3px;border-radius:10px;border:1px solid var(--border);">
            <a href="{{ route('finance.index', array_merge(request()->query(), ['type' => 'all'])) }}"
               class="btn btn-sm {{ $currentType === 'all' ? 'btn-primary' : 'btn-outline' }}"
               style="border:none;border-radius:8px;font-size:0.8rem;padding:0.35rem 0.8rem;">
                All
            </a>
            <a href="{{ route('finance.index', array_merge(request()->query(), ['type' => 'expense'])) }}"
               class="btn btn-sm {{ $currentType === 'expense' ? 'btn-primary' : 'btn-outline' }}"
               style="border:none;border-radius:8px;font-size:0.8rem;padding:0.35rem 0.8rem;">
                Expenses
            </a>
            <a href="{{ route('finance.index', array_merge(request()->query(), ['type' => 'income'])) }}"
               class="btn btn-sm {{ $currentType === 'income' ? 'btn-primary' : 'btn-outline' }}"
               style="border:none;border-radius:8px;font-size:0.8rem;padding:0.35rem 0.8rem;">
                Income
            </a>
        </div>

        {{-- Filters (Month, Search, Categories) --}}
        <form method="GET" action="{{ route('finance.index') }}" style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
            <input type="hidden" name="type" value="{{ $currentType }}">

            {{-- Month Picker --}}
            <input type="month" name="month" value="{{ $currentMonth }}" class="form-control"
                   style="padding:0.35rem 0.65rem;font-size:0.8rem;width:145px;border-radius:8px;" onchange="this.form.submit()">

            {{-- Search --}}
            <input type="text" name="search" value="{{ $search }}" placeholder="Search title or notes..." class="form-control"
                   style="padding:0.35rem 0.65rem;font-size:0.8rem;width:170px;border-radius:8px;">

            <button type="submit" class="btn btn-outline btn-sm">Filter</button>
            @if(request()->hasAny(['search', 'category_id']) || $currentType !== 'all')
                <a href="{{ route('finance.index', ['month' => $currentMonth]) }}" class="btn btn-outline btn-sm" title="Clear Filters">
                    <i data-lucide="x" style="width:13px;height:13px;"></i>
                </a>
            @endif
        </form>

        {{-- Action Buttons --}}
        <div style="display:flex;align-items:center;gap:0.5rem;">
            <a href="{{ route('finance.export-csv', ['month' => $currentMonth]) }}" class="btn btn-outline btn-sm" title="Export CSV">
                <i data-lucide="download" style="width:13px;height:13px;"></i> CSV
            </a>
            <a href="{{ route('finance.create', ['type' => 'expense']) }}" class="btn btn-primary btn-sm">
                <i data-lucide="plus" style="width:14px;height:14px;"></i> Add Transaction
            </a>
        </div>
    </div>
</div>

{{-- Transactions List Table --}}
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
        <div>
            <div class="section-title">Transactions Ledger</div>
            <div class="section-subtitle">Showing {{ $transactions->total() }} records for {{ $summary['month_label'] }}</div>
        </div>
    </div>

    @if($transactions->isEmpty())
        <div style="text-align:center;padding:3rem 1rem;">
            <i data-lucide="receipt" style="width:44px;height:44px;color:var(--border);margin:0 auto 0.75rem;display:block;"></i>
            <h4 style="font-size:0.95rem;font-weight:600;color:var(--text);margin:0 0 0.25rem;">No transactions found</h4>
            <p style="font-size:0.82rem;color:var(--text-muted);margin:0 0 1rem;">Start tracking your cash flow by recording an expense or income entry.</p>
            <a href="{{ route('finance.create') }}" class="btn btn-primary btn-sm">
                <i data-lucide="plus" style="width:14px;height:14px;"></i> Record First Transaction
            </a>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.86rem;">
                <thead>
                    <tr style="border-bottom:1.5px solid var(--border);text-align:left;">
                        <th style="padding:0.75rem;color:var(--text-muted);font-weight:600;">Date</th>
                        <th style="padding:0.75rem;color:var(--text-muted);font-weight:600;">Title & Notes</th>
                        <th style="padding:0.75rem;color:var(--text-muted);font-weight:600;">Category</th>
                        <th style="padding:0.75rem;color:var(--text-muted);font-weight:600;">Payment</th>
                        <th style="padding:0.75rem;color:var(--text-muted);font-weight:600;text-align:right;">Amount</th>
                        <th style="padding:0.75rem;color:var(--text-muted);font-weight:600;text-align:center;">Receipt</th>
                        <th style="padding:0.75rem;color:var(--text-muted);font-weight:600;text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $t)
                        <tr style="border-bottom:1px solid var(--border);">
                            {{-- Date --}}
                            <td style="padding:0.75rem;font-weight:500;color:var(--text);white-space:nowrap;">
                                {{ $t->transaction_date->format('M d, Y') }}
                            </td>

                            {{-- Title & Notes --}}
                            <td style="padding:0.75rem;max-width:260px;">
                                <div style="font-weight:600;color:var(--text);">{{ $t->title }}</div>
                                @if($t->notes)
                                    <div style="font-size:0.72rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $t->notes }}
                                    </div>
                                @endif
                            </td>

                            {{-- Category --}}
                            <td style="padding:0.75rem;">
                                @php
                                    $cat = $t->type === 'income' ? $t->incomeCategory : $t->expenseCategory;
                                @endphp
                                <span class="badge" style="background:{{ $t->type === 'income' ? 'var(--sage-light)' : 'var(--peach-light)' }};color:{{ $t->type === 'income' ? 'var(--sage)' : '#C2500C' }};">
                                    {{ $cat?->name ?? 'General' }}
                                </span>
                            </td>

                            {{-- Payment Method --}}
                            <td style="padding:0.75rem;color:var(--text-muted);font-size:0.8rem;white-space:nowrap;">
                                {{ $t->payment_method }}
                            </td>

                            {{-- Amount --}}
                            <td style="padding:0.75rem;text-align:right;font-weight:700;white-space:nowrap;color:{{ $t->type === 'income' ? 'var(--sage)' : '#DC2626' }};">
                                {{ $t->type === 'income' ? '+' : '-' }}₹{{ number_format($t->amount, 2) }}
                            </td>

                            {{-- Receipt --}}
                            <td style="padding:0.75rem;text-align:center;">
                                @if($t->receipt_path)
                                    <a href="{{ Storage::disk('public')->url($t->receipt_path) }}" target="_blank"
                                       class="btn btn-outline btn-sm" style="padding:0.25rem 0.45rem;border-radius:6px;" title="View Receipt">
                                        <i data-lucide="file-text" style="width:13px;height:13px;color:var(--primary);"></i>
                                    </a>
                                @else
                                    <span style="color:var(--border);">—</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td style="padding:0.75rem;text-align:right;white-space:nowrap;">
                                <div style="display:inline-flex;gap:0.3rem;">
                                    <a href="{{ route('finance.edit', $t) }}" class="btn btn-outline btn-sm" style="padding:0.25rem 0.45rem;" title="Edit">
                                        <i data-lucide="edit-2" style="width:13px;height:13px;"></i>
                                    </a>
                                    <form method="POST" action="{{ route('finance.destroy', $t) }}" onsubmit="return confirm('Delete this transaction?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline btn-sm" style="padding:0.25rem 0.45rem;color:#DC2626;" title="Delete">
                                            <i data-lucide="trash-2" style="width:13px;height:13px;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div style="margin-top:1.25rem;">
            {{ $transactions->links() }}
        </div>
    @endif
</div>

@endsection
