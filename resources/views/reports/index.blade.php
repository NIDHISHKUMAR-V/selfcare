@extends('layouts.app')

@section('title', 'Reports & Analytics - My Self-Care')
@section('page-title', 'Life Analytics & Reports')
@section('page-subtitle', 'Holistic insights across your health, finances, routines, and personal development')

@section('content')
<div style="display:flex;flex-direction:column;gap:1.5rem;">
    <!-- Date Range Filter & Preset Switcher -->
    <div class="card" style="padding:1rem 1.25rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
            <!-- Presets -->
            <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                <span style="font-size:0.75rem;font-weight:600;color:var(--text-muted);margin-right:0.25rem;">Time Range:</span>
                <a href="{{ route('reports.index', ['preset' => 'today']) }}" class="btn btn-sm {{ $preset === 'today' ? 'btn-primary' : 'btn-outline' }}">Today</a>
                <a href="{{ route('reports.index', ['preset' => 'last_7_days']) }}" class="btn btn-sm {{ $preset === 'last_7_days' ? 'btn-primary' : 'btn-outline' }}">Last 7 Days</a>
                <a href="{{ route('reports.index', ['preset' => 'this_week']) }}" class="btn btn-sm {{ $preset === 'this_week' ? 'btn-primary' : 'btn-outline' }}">This Week</a>
                <a href="{{ route('reports.index', ['preset' => 'this_month']) }}" class="btn btn-sm {{ $preset === 'this_month' ? 'btn-primary' : 'btn-outline' }}">This Month</a>
                <a href="{{ route('reports.index', ['preset' => 'last_30_days']) }}" class="btn btn-sm {{ $preset === 'last_30_days' ? 'btn-primary' : 'btn-outline' }}">Last 30 Days</a>
            </div>

            <!-- Custom Date Range Form -->
            <form method="GET" action="{{ route('reports.index') }}" style="display:flex;align-items:center;gap:0.5rem;">
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-control" style="padding:0.3rem 0.5rem;font-size:0.75rem;width:auto;">
                <span style="font-size:0.75rem;color:var(--text-muted);">to</span>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-control" style="padding:0.3rem 0.5rem;font-size:0.75rem;width:auto;">
                <button type="submit" class="btn btn-sm btn-outline">Apply</button>
            </form>
        </div>

        <div style="margin-top:0.75rem;padding-top:0.75rem;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;font-size:0.75rem;color:var(--text-muted);">
            <span>Reporting period: <strong>{{ $report['period'] }}</strong> ({{ $report['days_count'] }} days)</span>
            <button onclick="window.print()" class="btn btn-sm btn-ghost" style="font-size:0.72rem;padding:0.2rem 0.5rem;">
                <i data-lucide="printer" style="width:13px;height:13px;"></i> Print / Save PDF
            </button>
        </div>
    </div>

    <!-- Holistic Executive Summary Cards (6 Pillars) -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:1rem;">
        <!-- Nutrition -->
        <div class="stat-card lavender">
            <div class="stat-icon purple"><i data-lucide="utensils" style="width:18px;height:18px;"></i></div>
            <div style="font-size:1.4rem;font-weight:700;color:var(--text);">{{ number_format($report['food']['avg_daily_calories']) }} <span style="font-size:0.75rem;font-weight:400;color:var(--text-muted);">kcal/day</span></div>
            <div style="font-size:0.72rem;color:var(--text-muted);margin-top:0.25rem;">{{ $report['food']['total_meals'] }} meals tracked</div>
        </div>

        <!-- Water -->
        <div class="stat-card sage">
            <div class="stat-icon" style="background:var(--sage);color:#fff;"><i data-lucide="droplet" style="width:18px;height:18px;"></i></div>
            <div style="font-size:1.4rem;font-weight:700;color:var(--text);">{{ number_format($report['water']['avg_daily_ml']) }} <span style="font-size:0.75rem;font-weight:400;color:var(--text-muted);">ml/day</span></div>
            <div style="font-size:0.72rem;color:var(--text-muted);margin-top:0.25rem;">{{ $report['water']['target_achievement_rate'] }}% target days</div>
        </div>

        <!-- Finances -->
        <div class="stat-card peach">
            <div class="stat-icon" style="background:var(--peach);color:#fff;"><i data-lucide="wallet" style="width:18px;height:18px;"></i></div>
            <div style="font-size:1.4rem;font-weight:700;color:var(--text);">${{ number_format($report['finance']['net_savings'], 2) }}</div>
            <div style="font-size:0.72rem;color:var(--text-muted);margin-top:0.25rem;">Net Savings</div>
        </div>

        <!-- Daily Tasks -->
        <div class="stat-card blue">
            <div class="stat-icon" style="background:#3B82F6;color:#fff;"><i data-lucide="check-square" style="width:18px;height:18px;"></i></div>
            <div style="font-size:1.4rem;font-weight:700;color:var(--text);">{{ $report['activities']['completion_rate'] }}%</div>
            <div style="font-size:0.72rem;color:var(--text-muted);margin-top:0.25rem;">{{ $report['activities']['completed_tasks'] }} / {{ $report['activities']['total_tasks'] }} tasks</div>
        </div>

        <!-- Habits -->
        <div class="stat-card pink">
            <div class="stat-icon" style="background:#EC4899;color:#fff;"><i data-lucide="repeat" style="width:18px;height:18px;"></i></div>
            <div style="font-size:1.4rem;font-weight:700;color:var(--text);">{{ $report['habits']['consistency_rate'] }}%</div>
            <div style="font-size:0.72rem;color:var(--text-muted);margin-top:0.25rem;">{{ $report['habits']['total_completions'] }} logs completed</div>
        </div>

        <!-- Sleep -->
        <div class="stat-card lavender">
            <div class="stat-icon purple"><i data-lucide="moon" style="width:18px;height:18px;"></i></div>
            <div style="font-size:1.4rem;font-weight:700;color:var(--text);">{{ $report['sleep']['avg_duration_hours'] }} <span style="font-size:0.75rem;font-weight:400;color:var(--text-muted);">hours/night</span></div>
            <div style="font-size:0.72rem;color:var(--text-muted);margin-top:0.25rem;">Quality: {{ $report['sleep']['avg_quality_score'] }}/5.0</div>
        </div>
    </div>

    <!-- Interactive Multimodal Charts -->
    @php
        $timeline = $report['chart_timeline'];
        $chartLabels = array_column($timeline, 'label');
        $energyData = json_encode([
            'labels' => $chartLabels,
            'calories' => array_column($timeline, 'calories'),
            'exercise' => array_column($timeline, 'exercise'),
        ]);
        $sleepWaterData = json_encode([
            'labels' => $chartLabels,
            'sleep' => array_column($timeline, 'sleep'),
            'water' => array_column($timeline, 'water'),
        ]);
    @endphp
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
        <!-- Calorie vs Exercise Burn Chart -->
        <div class="card">
            <div class="section-title" style="margin-bottom:0.25rem;">Daily Energy Balance</div>
            <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:1rem;">Calories Consumed vs. Exercise Burn</div>
            <canvas id="energyBalanceChart" data-chart="energy-balance" data-chart-data="{!! htmlspecialchars($energyData, ENT_QUOTES) !!}" style="max-height:220px;"></canvas>
        </div>

        <!-- Sleep and Hydration Chart -->
        <div class="card">
            <div class="section-title" style="margin-bottom:0.25rem;">Sleep Duration & Hydration</div>
            <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:1rem;">Nightly rest hours (bar) & daily ml water (line)</div>
            <canvas id="sleepWaterChart" data-chart="sleep-water" data-chart-data="{!! htmlspecialchars($sleepWaterData, ENT_QUOTES) !!}" style="max-height:220px;"></canvas>
        </div>
    </div>

    <!-- Deep-Dive Breakdown Grid -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:1.5rem;">
        <!-- Nutrition Macronutrients -->
        <div class="card">
            <div class="section-title" style="margin-bottom:1rem;">Macronutrient Averages</div>
            <div style="display:flex;flex-direction:column;gap:0.75rem;">
                <div>
                    <div style="display:flex;justify-content:space-between;font-size:0.78rem;font-weight:600;margin-bottom:0.25rem;">
                        <span>Protein ({{ $report['food']['avg_protein'] }}g/day)</span>
                        <span style="color:#3B82F6;">{{ round($report['food']['avg_protein'] * 4) }} kcal</span>
                    </div>
                    <div style="height:6px;background:var(--border);border-radius:999px;overflow:hidden;">
                        <div style="height:100%;width:{{ min(100, round(($report['food']['avg_protein'] / 150) * 100)) }}%;background:#3B82F6;border-radius:999px;"></div>
                    </div>
                </div>

                <div>
                    <div style="display:flex;justify-content:space-between;font-size:0.78rem;font-weight:600;margin-bottom:0.25rem;">
                        <span>Carbohydrates ({{ $report['food']['avg_carbs'] }}g/day)</span>
                        <span style="color:var(--accent);">{{ round($report['food']['avg_carbs'] * 4) }} kcal</span>
                    </div>
                    <div style="height:6px;background:var(--border);border-radius:999px;overflow:hidden;">
                        <div style="height:100%;width:{{ min(100, round(($report['food']['avg_carbs'] / 300) * 100)) }}%;background:var(--accent);border-radius:999px;"></div>
                    </div>
                </div>

                <div>
                    <div style="display:flex;justify-content:space-between;font-size:0.78rem;font-weight:600;margin-bottom:0.25rem;">
                        <span>Healthy Fats ({{ $report['food']['avg_fat'] }}g/day)</span>
                        <span style="color:#10B981;">{{ round($report['food']['avg_fat'] * 9) }} kcal</span>
                    </div>
                    <div style="height:6px;background:var(--border);border-radius:999px;overflow:hidden;">
                        <div style="height:100%;width:{{ min(100, round(($report['food']['avg_fat'] / 80) * 100)) }}%;background:#10B981;border-radius:999px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Flow Overview -->
        <div class="card">
            <div class="section-title" style="margin-bottom:1rem;">Financial Summary</div>
            <div style="display:flex;justify-content:space-around;text-align:center;margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid var(--border);">
                <div>
                    <div style="font-size:0.72rem;color:var(--text-muted);">Total Inflows</div>
                    <div style="font-size:1.1rem;font-weight:700;color:var(--secondary);">${{ number_format($report['finance']['total_income'], 2) }}</div>
                </div>
                <div>
                    <div style="font-size:0.72rem;color:var(--text-muted);">Total Expenses</div>
                    <div style="font-size:1.1rem;font-weight:700;color:#DC2626;">${{ number_format($report['finance']['total_expense'], 2) }}</div>
                </div>
                <div>
                    <div style="font-size:0.72rem;color:var(--text-muted);">Net Position</div>
                    <div style="font-size:1.1rem;font-weight:700;color:{{ $report['finance']['net_savings'] >= 0 ? 'var(--secondary)' : '#DC2626' }};">
                        ${{ number_format($report['finance']['net_savings'], 2) }}
                    </div>
                </div>
            </div>

            @if($report['finance']['top_expenses']->isNotEmpty())
                <div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);margin-bottom:0.5rem;">Top Expense Categories:</div>
                <div style="display:flex;flex-direction:column;gap:0.35rem;">
                    @foreach($report['finance']['top_expenses'] as $exp)
                        <div style="display:flex;justify-content:space-between;font-size:0.75rem;">
                            <span>{{ $exp['category'] }}</span>
                            <span style="font-weight:600;">${{ number_format($exp['amount'], 2) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Goals & Aspirations -->
        <div class="card">
            <div class="section-title" style="margin-bottom:1rem;">Personal Goals & Milestones</div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
                <span style="font-size:0.8rem;color:var(--text-muted);">Active Goals:</span>
                <span style="font-size:0.9rem;font-weight:700;color:var(--text);">{{ $report['goals']['in_progress_goals'] }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
                <span style="font-size:0.8rem;color:var(--text-muted);">Goals Completed:</span>
                <span style="font-size:0.9rem;font-weight:700;color:var(--secondary);">{{ $report['goals']['completed_goals'] }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
                <span style="font-size:0.8rem;color:var(--text-muted);">Average Goal Progress:</span>
                <span style="font-size:0.9rem;font-weight:700;color:var(--primary);">{{ $report['goals']['avg_progress'] }}%</span>
            </div>
            <div style="height:8px;background:var(--border);border-radius:999px;overflow:hidden;margin-top:0.5rem;">
                <div style="height:100%;width:{{ $report['goals']['avg_progress'] }}%;background:var(--primary);border-radius:999px;"></div>
            </div>
        </div>
    </div>
</div>

@endsection
