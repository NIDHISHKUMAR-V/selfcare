@extends('layouts.app')

@section('title', 'Food & Nutrition Reports')
@section('page-title', 'Nutrition Analytics & Reports')
@section('page-subtitle', 'Understand your dietary patterns, daily averages, and macronutrient balance')

@section('content')

{{-- Controls & Period Selector --}}
<div class="card" style="margin-bottom:1.5rem;padding:0.9rem 1.25rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;">
        <div>
            <div style="font-size:0.95rem;font-weight:700;color:var(--text);">
                {{ $period === 'monthly' ? 'Monthly Nutrition Overview (Last 30 Days)' : 'Weekly Nutrition Overview (Last 7 Days)' }}
            </div>
            <div style="font-size:0.75rem;color:var(--text-muted);">
                {{ $report['start_date'] }} — {{ $report['end_date'] }}
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:0.5rem;">
            <div style="display:flex;background:var(--bg);border-radius:10px;padding:3px;border:1px solid var(--border);">
                <a href="{{ route('food.reports', ['period' => 'weekly']) }}"
                   class="btn btn-sm {{ $period === 'weekly' ? 'btn-primary' : 'btn-outline' }}"
                   style="border:none;padding:0.35rem 0.8rem;border-radius:8px;">
                    Weekly
                </a>
                <a href="{{ route('food.reports', ['period' => 'monthly']) }}"
                   class="btn btn-sm {{ $period === 'monthly' ? 'btn-primary' : 'btn-outline' }}"
                   style="border:none;padding:0.35rem 0.8rem;border-radius:8px;">
                    Monthly
                </a>
            </div>

            <a href="{{ route('food.index') }}" class="btn btn-outline btn-sm">
                <i data-lucide="calendar" style="width:14px;height:14px;"></i> Daily Diary
            </a>
        </div>
    </div>
</div>

{{-- KPI Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;">

    <div class="stat-card lavender">
        <div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;">Daily Average</div>
        <div class="stat-value" style="color:var(--primary);margin:0.3rem 0;">{{ number_format($report['average_daily_calories'], 0) }} <span style="font-size:0.9rem;font-weight:500;">kcal/day</span></div>
        <div class="stat-change" style="color:var(--primary);">
            Based on {{ $report['period'] === 'monthly' ? '30 days' : '7 days' }}
        </div>
    </div>

    <div class="stat-card sage">
        <div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;">Total Meals Logged</div>
        <div class="stat-value" style="color:var(--sage);margin:0.3rem 0;">{{ $report['total_meals_logged'] }}</div>
        <div class="stat-change" style="color:var(--sage);">
            Breakfast, lunch, dinner & snacks
        </div>
    </div>

    <div class="stat-card peach">
        <div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;">Highest Calorie Day</div>
        <div class="stat-value" style="color:#C2500C;margin:0.3rem 0;">{{ number_format($report['highest_calorie_day']['calories'], 0) }} <span style="font-size:0.9rem;font-weight:500;">kcal</span></div>
        <div class="stat-change" style="color:#C2500C;">
            {{ $report['highest_calorie_day']['date'] }}
        </div>
    </div>

    <div class="stat-card pink">
        <div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;">Lowest Calorie Day</div>
        <div class="stat-value" style="color:#EC4899;margin:0.3rem 0;">{{ number_format($report['lowest_calorie_day']['calories'], 0) }} <span style="font-size:0.9rem;font-weight:500;">kcal</span></div>
        <div class="stat-change" style="color:#EC4899;">
            {{ $report['lowest_calorie_day']['date'] }}
        </div>
    </div>

</div>

{{-- Charts Section --}}
@php
    $trendData = $report['daily_calorie_trend'];
    $foodTrendJson = json_encode([
        'labels' => array_column($trendData, 'label'),
        'data' => array_column($trendData, 'calories'),
    ]);
    $foodMacroJson = json_encode($report['macro_breakdown']);
@endphp
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.25rem;margin-bottom:1.5rem;">

    {{-- Daily Calorie Trend Bar Chart --}}
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <div>
                <div class="section-title">Daily Calorie Consumption Trend</div>
                <div class="section-subtitle">Energy intake recorded over time</div>
            </div>
            <span class="badge badge-purple">{{ ucfirst($period) }}</span>
        </div>
        <div style="height:280px;position:relative;">
            <canvas id="dailyCalorieTrendChart" data-chart="food-calorie-trend" data-chart-data="{!! htmlspecialchars($foodTrendJson, ENT_QUOTES) !!}"></canvas>
        </div>
    </div>

    {{-- Macronutrient Breakdown Doughnut Chart --}}
    <div class="card" style="display:flex;flex-direction:column;">
        <div style="margin-bottom:1rem;">
            <div class="section-title">Macronutrient Breakdown</div>
            <div class="section-subtitle">Proportion of protein, carbs, and fat</div>
        </div>

        <div style="flex:1;position:relative;display:flex;align-items:center;justify-content:center;">
            <canvas id="macroDonutChart" data-chart="food-macro-donut" data-chart-data="{!! htmlspecialchars($foodMacroJson, ENT_QUOTES) !!}" style="max-height:200px;"></canvas>
        </div>

        <div style="margin-top:1rem;border-top:1px solid var(--border);padding-top:0.75rem;display:grid;grid-template-columns:repeat(3,1fr);gap:0.5rem;text-align:center;">
            <div>
                <div style="font-size:0.7rem;color:var(--text-muted);">Protein</div>
                <div style="font-size:0.85rem;font-weight:700;color:var(--sage);">{{ $report['macro_breakdown']['protein'] }}%</div>
                <div style="font-size:0.68rem;color:var(--text-muted);">{{ number_format($report['total_protein'], 0) }}g</div>
            </div>
            <div>
                <div style="font-size:0.7rem;color:var(--text-muted);">Carbs</div>
                <div style="font-size:0.85rem;font-weight:700;color:#C2500C;">{{ $report['macro_breakdown']['carbs'] }}%</div>
                <div style="font-size:0.68rem;color:var(--text-muted);">{{ number_format($report['total_carbs'], 0) }}g</div>
            </div>
            <div>
                <div style="font-size:0.7rem;color:var(--text-muted);">Fat</div>
                <div style="font-size:0.85rem;font-weight:700;color:#EC4899;">{{ $report['macro_breakdown']['fat'] }}%</div>
                <div style="font-size:0.68rem;color:var(--text-muted);">{{ number_format($report['total_fat'], 0) }}g</div>
            </div>
        </div>
    </div>

</div>

{{-- Consumption History Table --}}
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
        <div>
            <div class="section-title">Daily Intake History</div>
            <div class="section-subtitle">Detailed day-by-day record of consumed calories</div>
        </div>
    </div>

    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:0.85rem;">
            <thead>
                <tr style="border-bottom:1.5px solid var(--border);text-align:left;">
                    <th style="padding:0.75rem;color:var(--text-muted);font-weight:600;">Date</th>
                    <th style="padding:0.75rem;color:var(--text-muted);font-weight:600;">Calories</th>
                    <th style="padding:0.75rem;color:var(--text-muted);font-weight:600;">Target</th>
                    <th style="padding:0.75rem;color:var(--text-muted);font-weight:600;">Status</th>
                    <th style="padding:0.75rem;text-align:right;color:var(--text-muted);font-weight:600;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_reverse($report['daily_calorie_trend']) as $day)
                    @php
                        $target = auth()->user()->daily_calorie_goal ?: 2000;
                        $pct = $target > 0 ? round(($day['calories'] / $target) * 100) : 0;
                    @endphp
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:0.75rem;font-weight:600;color:var(--text);">
                            {{ $day['label'] }}
                        </td>
                        <td style="padding:0.75rem;font-weight:700;color:var(--text);">
                            {{ number_format($day['calories'], 0) }} kcal
                        </td>
                        <td style="padding:0.75rem;color:var(--text-muted);">
                            {{ number_format($target) }} kcal
                        </td>
                        <td style="padding:0.75rem;">
                            @if($day['calories'] == 0)
                                <span class="badge" style="background:#F3F4F6;color:#6B7280;">No Logs</span>
                            @elseif($day['calories'] <= $target)
                                <span class="badge badge-green">Within Goal ({{ $pct }}%)</span>
                            @else
                                <span class="badge badge-orange">Over Target ({{ $pct }}%)</span>
                            @endif
                        </td>
                        <td style="padding:0.75rem;text-align:right;">
                            <a href="{{ route('food.index', ['date' => $day['date']]) }}" class="btn btn-outline btn-sm" style="font-size:0.75rem;padding:0.25rem 0.6rem;">
                                View Day <i data-lucide="chevron-right" style="width:12px;height:12px;"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
