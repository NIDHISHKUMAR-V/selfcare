@extends('layouts.app')

@section('title', 'Food & Nutrition Tracker')
@section('page-title', 'Food & Nutrition')
@section('page-subtitle', 'Track your daily meals, calories, and macronutrients')

@section('content')

{{-- Date Navigator --}}
<div class="card" style="margin-bottom:1.5rem;padding:0.9rem 1.25rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;">
        <div style="display:flex;align-items:center;gap:0.5rem;">
            <a href="{{ route('food.index', ['date' => $prevDate]) }}" class="btn btn-outline btn-sm" title="Previous Day">
                <i data-lucide="chevron-left" style="width:14px;height:14px;"></i>
            </a>
            <div style="display:flex;align-items:center;gap:0.5rem;">
                <i data-lucide="calendar" style="width:16px;height:16px;color:var(--primary);"></i>
                <span style="font-size:0.95rem;font-weight:700;color:var(--text);">
                    {{ $date->isToday() ? 'Today, ' : ($date->isYesterday() ? 'Yesterday, ' : '') }}{{ $date->format('l, M d, Y') }}
                </span>
            </div>
            <a href="{{ route('food.index', ['date' => $nextDate]) }}" class="btn btn-outline btn-sm" title="Next Day">
                <i data-lucide="chevron-right" style="width:14px;height:14px;"></i>
            </a>
            @if(!$date->isToday())
                <a href="{{ route('food.index') }}" class="btn btn-outline btn-sm" style="font-size:0.75rem;padding:0.25rem 0.6rem;">
                    Jump to Today
                </a>
            @endif
        </div>

        <div style="display:flex;align-items:center;gap:0.6rem;">
            <form method="GET" action="{{ route('food.index') }}" style="display:flex;align-items:center;gap:0.4rem;">
                <input type="date" name="date" value="{{ $date->toDateString() }}" class="form-control"
                       style="padding:0.35rem 0.65rem;font-size:0.8rem;border-radius:8px;" onchange="this.form.submit()">
            </form>
            <a href="{{ route('food.create', ['date' => $date->toDateString()]) }}" class="btn btn-primary btn-sm">
                <i data-lucide="plus" style="width:14px;height:14px;"></i> Log Meal
            </a>
            <a href="{{ route('food.reports') }}" class="btn btn-outline btn-sm">
                <i data-lucide="pie-chart" style="width:14px;height:14px;"></i> Reports
            </a>
        </div>
    </div>
</div>

{{-- Nutrition Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:1rem;margin-bottom:1.5rem;">

    {{-- Calories Card --}}
    <div class="stat-card lavender">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.4rem;">
            <div class="stat-label" style="margin:0;font-size:0.8rem;">Calories Consumed</div>
            <i data-lucide="flame" style="width:18px;height:18px;color:var(--primary);"></i>
        </div>
        <div style="display:flex;align-items:baseline;gap:0.3rem;">
            <div class="stat-value">{{ number_format($dailySummary['total_calories'], 0) }}</div>
            <div style="font-size:0.85rem;color:var(--text-muted);">/ {{ number_format($dailySummary['calorie_goal']) }} kcal</div>
        </div>
        <div style="margin-top:0.6rem;">
            <div class="progress-bar">
                <div class="progress-fill" style="width:{{ $dailySummary['calorie_percentage'] }}%;background:var(--primary);"></div>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:0.7rem;color:var(--text-muted);margin-top:0.3rem;">
                <span>{{ $dailySummary['calorie_percentage'] }}% of target</span>
                <span>{{ max(0, $dailySummary['calorie_goal'] - $dailySummary['total_calories']) }} kcal left</span>
            </div>
        </div>
    </div>

    {{-- Protein Card --}}
    <div class="stat-card sage">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.4rem;">
            <div class="stat-label" style="margin:0;font-size:0.8rem;">Protein</div>
            <span class="badge badge-green">{{ $dailySummary['macro_percentages']['protein'] }}%</span>
        </div>
        <div class="stat-value" style="color:var(--sage);">{{ number_format($dailySummary['total_protein'], 1) }} <span style="font-size:0.9rem;font-weight:500;">g</span></div>
        <div class="stat-change" style="color:var(--sage);">
            {{ number_format($dailySummary['total_protein'] * 4, 0) }} kcal from protein
        </div>
    </div>

    {{-- Carbohydrates Card --}}
    <div class="stat-card peach">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.4rem;">
            <div class="stat-label" style="margin:0;font-size:0.8rem;">Carbohydrates</div>
            <span class="badge badge-orange">{{ $dailySummary['macro_percentages']['carbs'] }}%</span>
        </div>
        <div class="stat-value" style="color:#C2500C;">{{ number_format($dailySummary['total_carbs'], 1) }} <span style="font-size:0.9rem;font-weight:500;">g</span></div>
        <div class="stat-change" style="color:#C2500C;">
            {{ number_format($dailySummary['total_carbs'] * 4, 0) }} kcal from carbs
        </div>
    </div>

    {{-- Fat Card --}}
    <div class="stat-card pink">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.4rem;">
            <div class="stat-label" style="margin:0;font-size:0.8rem;">Fat</div>
            <span class="badge" style="background:#FDF2F8;color:#EC4899;">{{ $dailySummary['macro_percentages']['fat'] }}%</span>
        </div>
        <div class="stat-value" style="color:#EC4899;">{{ number_format($dailySummary['total_fat'], 1) }} <span style="font-size:0.9rem;font-weight:500;">g</span></div>
        <div class="stat-change" style="color:#EC4899;">
            {{ number_format($dailySummary['total_fat'] * 9, 0) }} kcal from fat
        </div>
    </div>

</div>

{{-- Meals Sections --}}
@php
    $mealTypes = [
        'breakfast' => ['title' => 'Breakfast', 'icon' => 'coffee', 'color' => '#F4A261', 'bg' => '#FEF3E8'],
        'lunch'     => ['title' => 'Lunch', 'icon' => 'sun', 'color' => '#8E8CD8', 'bg' => '#EDE9FE'],
        'dinner'    => ['title' => 'Dinner', 'icon' => 'moon', 'color' => '#7FA68E', 'bg' => '#E8F5ED'],
        'snack'     => ['title' => 'Snacks', 'icon' => 'apple', 'color' => '#EC4899', 'bg' => '#FDF2F8'],
    ];
@endphp

<div style="display:flex;flex-direction:column;gap:1.25rem;">
    @foreach($mealTypes as $typeKey => $meta)
        @php
            $items = $mealsByType->get($typeKey, collect());
            $typeCalories = $items->sum('calories');
            $typeProtein = $items->sum('protein');
            $typeCarbs = $items->sum('carbohydrates');
            $typeFat = $items->sum('fat');
        @endphp

        <div class="card">
            {{-- Header --}}
            <div style="display:flex;align-items:center;justify-content:space-between;padding-bottom:0.75rem;border-bottom:1px solid var(--border);margin-bottom:0.75rem;">
                <div style="display:flex;align-items:center;gap:0.65rem;">
                    <div style="width:34px;height:34px;border-radius:10px;background:{{ $meta['bg'] }};display:flex;align-items:center;justify-content:center;">
                        <i data-lucide="{{ $meta['icon'] }}" style="width:16px;height:16px;color:{{ $meta['color'] }};"></i>
                    </div>
                    <div>
                        <h3 style="font-size:0.95rem;font-weight:700;color:var(--text);margin:0;">{{ $meta['title'] }}</h3>
                        <p style="font-size:0.72rem;color:var(--text-muted);margin:0;">
                            {{ $items->count() }} item{{ $items->count() != 1 ? 's' : '' }}
                            @if($items->isNotEmpty())
                                · P: {{ number_format($typeProtein, 1) }}g | C: {{ number_format($typeCarbs, 1) }}g | F: {{ number_format($typeFat, 1) }}g
                            @endif
                        </p>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <div style="font-size:0.95rem;font-weight:700;color:var(--text);">
                        {{ number_format($typeCalories, 0) }} <span style="font-size:0.75rem;font-weight:500;color:var(--text-muted);">kcal</span>
                    </div>
                    <a href="{{ route('food.create', ['type' => $typeKey, 'date' => $date->toDateString()]) }}"
                       class="btn btn-outline btn-sm" style="font-size:0.78rem;">
                        <i data-lucide="plus" style="width:13px;height:13px;"></i> Add {{ $meta['title'] }}
                    </a>
                </div>
            </div>

            {{-- Items List --}}
            @if($items->isEmpty())
                <div style="padding:1rem 0;text-align:center;color:var(--text-muted);font-size:0.82rem;">
                    No {{ strtolower($meta['title']) }} logged yet for this day.
                </div>
            @else
                <div style="display:flex;flex-direction:column;gap:0.5rem;">
                    @foreach($items as $food)
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:0.65rem 0.75rem;border-radius:10px;background:var(--bg);gap:0.75rem;">
                            <div style="display:flex;align-items:center;gap:0.75rem;min-width:0;flex:1;">
                                @if($food->image_path)
                                    <img src="{{ Storage::disk('public')->url($food->image_path) }}" alt="{{ $food->food_name }}"
                                         style="width:40px;height:40px;border-radius:8px;object-fit:cover;flex-shrink:0;">
                                @else
                                    <div style="width:40px;height:40px;border-radius:8px;background:var(--card);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i data-lucide="utensils" style="width:16px;height:16px;color:var(--text-muted);"></i>
                                    </div>
                                @endif

                                <div style="min-width:0;">
                                    <div style="font-size:0.86rem;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $food->food_name }}
                                        @if($food->serving_size)
                                            <span style="font-size:0.75rem;font-weight:400;color:var(--text-muted);">({{ $food->serving_size }})</span>
                                        @endif
                                    </div>
                                    <div style="font-size:0.72rem;color:var(--text-muted);display:flex;gap:0.5rem;flex-wrap:wrap;">
                                        <span>Qty: {{ $food->quantity }}</span>
                                        <span>•</span>
                                        <span>P: {{ number_format($food->protein, 1) }}g</span>
                                        <span>C: {{ number_format($food->carbohydrates, 1) }}g</span>
                                        <span>F: {{ number_format($food->fat, 1) }}g</span>
                                        @if($food->meal_time)
                                            <span>•</span>
                                            <span>{{ \Carbon\Carbon::parse($food->meal_time)->format('h:i A') }}</span>
                                        @endif
                                    </div>
                                    @if($food->notes)
                                        <div style="font-size:0.7rem;color:var(--text-muted);font-style:italic;margin-top:2px;">
                                            "{{ $food->notes }}"
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div style="display:flex;align-items:center;gap:0.75rem;flex-shrink:0;">
                                <div style="font-size:0.9rem;font-weight:700;color:var(--text);text-align:right;">
                                    {{ number_format($food->calories, 0) }} <span style="font-size:0.7rem;font-weight:400;color:var(--text-muted);">kcal</span>
                                </div>
                                <div style="display:flex;gap:0.25rem;">
                                    <a href="{{ route('food.edit', $food) }}" class="btn btn-outline btn-sm" style="padding:0.3rem 0.5rem;" title="Edit">
                                        <i data-lucide="edit-2" style="width:13px;height:13px;"></i>
                                    </a>
                                    <form method="POST" action="{{ route('food.destroy', $food) }}" onsubmit="return confirm('Remove this food item?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline btn-sm" style="padding:0.3rem 0.5rem;color:#DC2626;" title="Delete">
                                            <i data-lucide="trash-2" style="width:13px;height:13px;"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</div>

@endsection
