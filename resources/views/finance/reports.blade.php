@extends('layouts.app')

@section('title', 'Financial Reports')
@section('page-title', 'Financial Analytics')
@section('page-subtitle', 'Understand your cash flow, spending patterns, and balance over time')

@section('content')

{{-- Controls Bar --}}
<div class="card" style="margin-bottom:1.5rem;padding:0.9rem 1.25rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
        <div>
            <div style="font-size:0.95rem;font-weight:700;color:var(--text);">Financial Performance Overview</div>
            <div style="font-size:0.75rem;color:var(--text-muted);">Report for {{ $summary['month_label'] }}</div>
        </div>

        <div style="display:flex;align-items:center;gap:0.6rem;">
            <form method="GET" action="{{ route('finance.reports') }}">
                <input type="month" name="month" value="{{ $currentMonth }}" class="form-control"
                       style="padding:0.35rem 0.65rem;font-size:0.8rem;width:150px;border-radius:8px;" onchange="this.form.submit()">
            </form>
            <a href="{{ route('finance.export-csv', ['month' => $currentMonth]) }}" class="btn btn-outline btn-sm">
                <i data-lucide="download" style="width:14px;height:14px;"></i> Export CSV
            </a>
            <a href="{{ route('finance.index') }}" class="btn btn-primary btn-sm">
                <i data-lucide="list" style="width:14px;height:14px;"></i> View Ledger
            </a>
        </div>
    </div>
</div>

{{-- KPI Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:1rem;margin-bottom:1.5rem;">
    <div class="stat-card sage">
        <div class="stat-label">Total Income</div>
        <div class="stat-value" style="color:var(--sage);margin:0.25rem 0;">₹{{ number_format($summary['total_income'], 2) }}</div>
        <div class="stat-change" style="color:var(--sage);">Earned in {{ $summary['month_label'] }}</div>
    </div>

    <div class="stat-card peach">
        <div class="stat-label">Total Expenses</div>
        <div class="stat-value" style="color:#C2500C;margin:0.25rem 0;">₹{{ number_format($summary['total_expenses'], 2) }}</div>
        <div class="stat-change" style="color:#C2500C;">Spent in {{ $summary['month_label'] }}</div>
    </div>

    <div class="stat-card {{ $summary['net_balance'] >= 0 ? 'lavender' : '' }}" style="{{ $summary['net_balance'] < 0 ? 'background:#FEF2F2;border-color:#FECACA;' : '' }}">
        <div class="stat-label">Net Savings</div>
        <div class="stat-value" style="color:{{ $summary['net_balance'] >= 0 ? 'var(--primary)' : '#DC2626' }};margin:0.25rem 0;">
            {{ $summary['net_balance'] < 0 ? '-' : '' }}₹{{ number_format(abs($summary['net_balance']), 2) }}
        </div>
        <div class="stat-change" style="color:{{ $summary['net_balance'] >= 0 ? 'var(--primary)' : '#DC2626' }};">
            {{ $summary['savings_rate'] }}% savings rate
        </div>
    </div>

    <div class="stat-card sage">
        <div class="stat-label">Budget Left</div>
        <div class="stat-value" style="margin:0.25rem 0;">₹{{ number_format($summary['budget_remaining'], 2) }}</div>
        <div class="stat-change" style="color:var(--text-muted);">
            {{ $summary['budget_spent_percent'] }}% utilized of limit
        </div>
    </div>
</div>

{{-- Charts Row 1: 6-Month Income vs Expenses & Category Doughnut --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">

    {{-- Multi-month Bar Chart --}}
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <div>
                <div class="section-title">6-Month Income vs Expense Trend</div>
                <div class="section-subtitle">Monthly cash flow and savings trajectory</div>
            </div>
            <span class="badge badge-purple">Last 6 Months</span>
        </div>
        <div style="height:270px;position:relative;">
            <canvas id="incomeExpenseChart"></canvas>
        </div>
    </div>

    {{-- Category Spending Distribution --}}
    <div class="card" style="display:flex;flex-direction:column;">
        <div style="margin-bottom:1rem;">
            <div class="section-title">Category Spending</div>
            <div class="section-subtitle">Breakdown for {{ $summary['month_label'] }}</div>
        </div>

        @if(empty($chartsData['category_distribution']['labels']))
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;color:var(--text-muted);padding:2rem 0;">
                <i data-lucide="pie-chart" style="width:36px;height:36px;color:var(--border);margin-bottom:0.5rem;"></i>
                <p style="font-size:0.82rem;margin:0;">No expenses logged for this month.</p>
            </div>
        @else
            <div style="flex:1;position:relative;display:flex;align-items:center;justify-content:center;">
                <canvas id="categoryChart" style="max-height:220px;"></canvas>
            </div>
        @endif
    </div>

</div>

{{-- Daily Expense Trend for Selected Month --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
        <div>
            <div class="section-title">Daily Expense Breakdown</div>
            <div class="section-subtitle">Daily expenditure recorded across {{ $summary['month_label'] }}</div>
        </div>
        <span class="badge badge-orange">Daily Spend</span>
    </div>
    <div style="height:220px;position:relative;">
        <canvas id="dailyExpenseChart"></canvas>
    </div>
</div>

{{-- Monthly History Table --}}
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
        <div>
            <div class="section-title">Historical Balance Table</div>
            <div class="section-subtitle">Month-by-month financial summary</div>
        </div>
    </div>

    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:0.86rem;">
            <thead>
                <tr style="border-bottom:1.5px solid var(--border);text-align:left;">
                    <th style="padding:0.75rem;color:var(--text-muted);font-weight:600;">Month</th>
                    <th style="padding:0.75rem;color:var(--text-muted);font-weight:600;text-align:right;">Total Income</th>
                    <th style="padding:0.75rem;color:var(--text-muted);font-weight:600;text-align:right;">Total Expenses</th>
                    <th style="padding:0.75rem;color:var(--text-muted);font-weight:600;text-align:right;">Net Savings</th>
                    <th style="padding:0.75rem;color:var(--text-muted);font-weight:600;text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_reverse($chartsData['months_trend']) as $m)
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:0.75rem;font-weight:700;color:var(--text);">
                            {{ $m['label'] }}
                        </td>
                        <td style="padding:0.75rem;text-align:right;font-weight:600;color:var(--sage);">
                            +₹{{ number_format($m['income'], 2) }}
                        </td>
                        <td style="padding:0.75rem;text-align:right;font-weight:600;color:#DC2626;">
                            -₹{{ number_format($m['expense'], 2) }}
                        </td>
                        <td style="padding:0.75rem;text-align:right;font-weight:700;color:{{ $m['net'] >= 0 ? 'var(--primary)' : '#DC2626' }};">
                            {{ $m['net'] >= 0 ? '+' : '' }}₹{{ number_format($m['net'], 2) }}
                        </td>
                        <td style="padding:0.75rem;text-align:center;">
                            <a href="{{ route('finance.index', ['month' => $m['month']]) }}" class="btn btn-outline btn-sm" style="font-size:0.75rem;padding:0.25rem 0.6rem;">
                                View Ledger <i data-lucide="chevron-right" style="width:12px;height:12px;"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // 1. Multi-Month Income vs Expense Bar Chart
    const trendCtx = document.getElementById('incomeExpenseChart').getContext('2d');
    const trendData = @json($chartsData['months_trend']);

    new Chart(trendCtx, {
        type: 'bar',
        data: {
            labels: trendData.map(d => d.label),
            datasets: [
                {
                    label: 'Income',
                    data: trendData.map(d => d.income),
                    backgroundColor: 'rgba(127, 166, 142, 0.6)',
                    borderColor: '#7FA68E',
                    borderWidth: 1.5,
                    borderRadius: 6,
                },
                {
                    label: 'Expense',
                    data: trendData.map(d => d.expense),
                    backgroundColor: 'rgba(244, 162, 97, 0.6)',
                    borderColor: '#F4A261',
                    borderWidth: 1.5,
                    borderRadius: 6,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.dataset.label}: ₹${ctx.parsed.y.toLocaleString('en-IN')}`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: {
                        callback: v => '₹' + v.toLocaleString('en-IN')
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // 2. Category Spending Doughnut Chart
    @if(!empty($chartsData['category_distribution']['labels']))
        const catCtx = document.getElementById('categoryChart').getContext('2d');
        const catData = @json($chartsData['category_distribution']);

        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: catData.labels,
                datasets: [{
                    data: catData.data,
                    backgroundColor: catData.colors,
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    hoverOffset: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { size: 10 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ₹${ctx.parsed.toLocaleString('en-IN')}`
                        }
                    }
                },
                cutout: '62%',
            }
        });
    @endif

    // 3. Daily Expense Trend Chart
    const dailyCtx = document.getElementById('dailyExpenseChart').getContext('2d');
    const dailyData = @json($chartsData['daily_expenses']);

    new Chart(dailyCtx, {
        type: 'bar',
        data: {
            labels: dailyData.map(d => d.day),
            datasets: [{
                label: 'Expense (₹)',
                data: dailyData.map(d => d.amount),
                backgroundColor: 'rgba(244, 162, 97, 0.5)',
                borderColor: '#F4A261',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ₹${ctx.parsed.y.toLocaleString('en-IN')}`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: {
                        callback: v => '₹' + v
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 }, maxRotation: 45 }
                }
            }
        }
    });
</script>
@endpush
