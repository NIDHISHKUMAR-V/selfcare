@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Here\'s your self-care overview for today')

@section('content')

{{-- Motivational Quote Banner --}}
<div class="card" style="background:linear-gradient(135deg,#EDE9FE,#E8F5ED);border:none;margin-bottom:1.5rem;display:flex;align-items:center;gap:1rem;">
    <div style="font-size:1.75rem;">✨</div>
    <div>
        <p style="font-size:0.9rem;font-weight:600;color:var(--text);margin:0;">"{{ $dailyQuote['quote'] }}"</p>
        <p style="font-size:0.75rem;color:var(--text-muted);margin:0.25rem 0 0;">— {{ $dailyQuote['author'] }}</p>
    </div>
</div>

{{-- Top Stat Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:1rem;margin-bottom:1.5rem;">

    {{-- Calories --}}
    <div class="stat-card lavender">
        <div class="stat-icon purple">
            <i data-lucide="utensils" style="width:20px;height:20px;"></i>
        </div>
        <div class="stat-value">{{ number_format($todayCalories, 0) }}</div>
        <div class="stat-label">Calories Today</div>
        <div class="stat-change" style="color:var(--primary);">
            {{ $todayMealsCount }} meal{{ $todayMealsCount != 1 ? 's' : '' }} logged
            @if($user->daily_calorie_goal)
                · Goal: {{ number_format($user->daily_calorie_goal) }}
            @endif
        </div>
    </div>

    {{-- Water --}}
    <div class="stat-card sage">
        <div class="stat-icon green">
            <i data-lucide="droplets" style="width:20px;height:20px;"></i>
        </div>
        <div class="stat-value">{{ number_format($todayWaterMl) }} <span style="font-size:1rem;font-weight:500;">ml</span></div>
        <div class="stat-label">Water Intake Today</div>
        <div style="margin-top:0.5rem;">
            <div class="progress-bar">
                <div class="progress-fill" style="width:{{ $waterProgressPercent }}%;background:var(--sage);"></div>
            </div>
            <div style="font-size:0.7rem;color:var(--text-muted);margin-top:0.3rem;">{{ $waterProgressPercent }}% of {{ number_format($waterGoalMl) }}ml goal</div>
        </div>
    </div>

    {{-- Today Expenses --}}
    <div class="stat-card peach">
        <div class="stat-icon orange">
            <i data-lucide="credit-card" style="width:20px;height:20px;"></i>
        </div>
        <div class="stat-value">₹{{ number_format($todayExpenses, 2) }}</div>
        <div class="stat-label">Spent Today</div>
        <div class="stat-change" style="color:#C2500C;">
            Monthly: ₹{{ number_format($monthlyExpenses, 2) }}
        </div>
    </div>

    {{-- Balance --}}
    <div class="stat-card {{ $currentBalance >= 0 ? 'sage' : '' }}" style="{{ $currentBalance < 0 ? 'background:#FEF2F2;border-color:#FECACA;' : '' }}">
        <div class="stat-icon {{ $currentBalance >= 0 ? 'green' : '' }}" style="{{ $currentBalance < 0 ? 'background:#EF4444;color:#fff;' : '' }}">
            <i data-lucide="trending-{{ $currentBalance >= 0 ? 'up' : 'down' }}" style="width:20px;height:20px;"></i>
        </div>
        <div class="stat-value" style="{{ $currentBalance < 0 ? 'color:#DC2626;' : '' }}">₹{{ number_format(abs($currentBalance), 2) }}</div>
        <div class="stat-label">Monthly Balance</div>
        <div class="stat-change" style="color:{{ $currentBalance >= 0 ? 'var(--sage)' : '#DC2626' }};">
            Income: ₹{{ number_format($monthlyIncome, 2) }}
        </div>
    </div>

    {{-- Tasks --}}
    <div class="stat-card lavender">
        <div class="stat-icon purple">
            <i data-lucide="list-checks" style="width:20px;height:20px;"></i>
        </div>
        <div class="stat-value">{{ $completedTasksCount }}</div>
        <div class="stat-label">Tasks Done Today</div>
        <div class="stat-change" style="color:var(--text-muted);">
            {{ $pendingTasksCount }} pending
        </div>
    </div>

    {{-- Habits --}}
    <div class="stat-card pink">
        <div class="stat-icon pink">
            <i data-lucide="repeat" style="width:20px;height:20px;"></i>
        </div>
        <div class="stat-value">{{ $completedHabitsCount }}/{{ $totalHabitsCount }}</div>
        <div class="stat-label">Habits Completed</div>
        <div style="margin-top:0.5rem;">
            <div class="progress-bar">
                <div class="progress-fill" style="width:{{ $habitCompletionPercent }}%;background:#EC4899;"></div>
            </div>
            <div style="font-size:0.7rem;color:var(--text-muted);margin-top:0.3rem;">{{ $habitCompletionPercent }}% completion rate</div>
        </div>
    </div>

</div>

{{-- Middle Row: Chart + Recent Transactions --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1rem;margin-bottom:1.5rem;">

    {{-- Weekly Calorie Chart --}}
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <div>
                <div class="section-title">Weekly Calories</div>
                <div class="section-subtitle">Your calorie intake over the last 7 days</div>
            </div>
            <span class="badge badge-purple">Last 7 Days</span>
        </div>
        @php $dashboardCalChartData = json_encode(['labels' => $weeklyCalories->pluck('date')->toArray(), 'data' => $weeklyCalories->pluck('calories')->toArray()]); @endphp
        <canvas id="weeklyCaloriesChart" data-chart="weekly-calories" data-chart-data="{!! htmlspecialchars($dashboardCalChartData, ENT_QUOTES) !!}" style="max-height:220px;"></canvas>
    </div>

    {{-- Sleep Card --}}
    <div class="card" style="display:flex;flex-direction:column;">
        <div style="margin-bottom:1rem;">
            <div class="section-title">Last Sleep</div>
            <div class="section-subtitle">Your most recent sleep record</div>
        </div>

        @if($lastSleep)
            <div style="text-align:center;flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:0.5rem;">
                <div style="font-size:2rem;">{{ ['😴','😴','😌','😊','🌟'][$lastSleep->quality_rating - 1] ?? '😴' }}</div>
                <div style="font-size:1.8rem;font-weight:700;color:var(--text);">{{ $lastSleep->formatted_duration }}</div>
                <div style="font-size:0.78rem;color:var(--text-muted);">{{ $lastSleep->sleep_date->format('D, d M') }}</div>
                <span class="badge" style="background:#EFF6FF;color:#3B82F6;">
                    {{ $lastSleep->quality_label }}
                </span>
            </div>
        @else
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:0.5rem;text-align:center;">
                <i data-lucide="moon" style="width:40px;height:40px;color:var(--border);"></i>
                <p style="font-size:0.82rem;color:var(--text-muted);">No sleep records yet.<br>Add your first sleep entry!</p>
                <a href="#" class="btn btn-sm btn-outline" style="margin-top:0.25rem;">
                    <i data-lucide="plus" style="width:13px;height:13px;"></i> Log Sleep
                </a>
            </div>
        @endif
    </div>
</div>

{{-- Bottom Row: Transactions + Quick Actions --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1rem;">

    {{-- Recent Transactions --}}
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <div>
                <div class="section-title">Recent Transactions</div>
                <div class="section-subtitle">Your latest financial activity</div>
            </div>
            <a href="{{ route('finance.index') }}" class="btn btn-sm btn-outline">View All</a>
        </div>

        @if($recentTransactions->isEmpty())
            <div style="text-align:center;padding:2rem 0;">
                <i data-lucide="wallet" style="width:40px;height:40px;color:var(--border);margin:0 auto;display:block;"></i>
                <p style="font-size:0.82rem;color:var(--text-muted);margin-top:0.5rem;">No transactions yet.<br>Start by adding your income or expenses.</p>
            </div>
        @else
            <div style="display:flex;flex-direction:column;gap:0.6rem;">
                @foreach($recentTransactions as $tx)
                    <div style="display:flex;align-items:center;gap:0.75rem;padding:0.6rem;border-radius:10px;background:var(--bg);">
                        <div style="width:36px;height:36px;border-radius:10px;background:{{ $tx->type === 'income' ? 'var(--sage-light)' : 'var(--peach-light)' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i data-lucide="{{ $tx->type === 'income' ? 'arrow-down-left' : 'arrow-up-right' }}"
                               style="width:16px;height:16px;color:{{ $tx->type === 'income' ? 'var(--sage)' : 'var(--peach)' }};"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:0.83rem;font-weight:500;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $tx->title }}</div>
                            <div style="font-size:0.72rem;color:var(--text-muted);">
                                {{ $tx->transaction_date->format('d M') }}
                                · {{ $tx->type === 'income' ? ($tx->incomeCategory?->name ?? 'Income') : ($tx->expenseCategory?->name ?? 'Expense') }}
                            </div>
                        </div>
                        <div style="font-size:0.9rem;font-weight:600;color:{{ $tx->type === 'income' ? 'var(--sage)' : '#DC2626' }};flex-shrink:0;">
                            {{ $tx->type === 'income' ? '+' : '-' }}₹{{ number_format($tx->amount, 2) }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Quick Actions --}}
    <div class="card">
        <div style="margin-bottom:1rem;">
            <div class="section-title">Quick Actions</div>
            <div class="section-subtitle">Log your day instantly</div>
        </div>
        <div style="display:flex;flex-direction:column;gap:0.6rem;">
            <a href="{{ route('food.create') }}" class="btn btn-primary" style="justify-content:flex-start;">
                <i data-lucide="utensils" style="width:15px;height:15px;"></i> Log Food
            </a>
            <a href="{{ route('water.index') }}" class="btn btn-sage" style="justify-content:flex-start;">
                <i data-lucide="droplets" style="width:15px;height:15px;"></i> Add Water
            </a>
            <a href="#" class="btn btn-outline" style="justify-content:flex-start;">
                <i data-lucide="plus-circle" style="width:15px;height:15px;"></i> Add Activity
            </a>
            <a href="{{ route('finance.create', ['type' => 'expense']) }}" class="btn btn-outline" style="justify-content:flex-start;">
                <i data-lucide="wallet" style="width:15px;height:15px;"></i> Record Expense
            </a>
            <a href="#" class="btn btn-outline" style="justify-content:flex-start;">
                <i data-lucide="dumbbell" style="width:15px;height:15px;"></i> Log Exercise
            </a>
            <a href="#" class="btn btn-outline" style="justify-content:flex-start;">
                <i data-lucide="moon" style="width:15px;height:15px;"></i> Log Sleep
            </a>
        </div>
    </div>

</div>

@endsection
