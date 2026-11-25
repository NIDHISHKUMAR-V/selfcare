@extends('layouts.app')

@section('title', 'Log Meal')
@section('page-title', 'Log Meal')
@section('page-subtitle', 'Add food consumption record with calories and macronutrients')

@section('content')
<div style="max-width:760px;margin:0 auto;">
    <div class="card" style="padding:1.75rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid var(--border);">
            <div>
                <h2 class="section-title" style="font-size:1.15rem;">Record Food Consumption</h2>
                <p class="section-subtitle">Track breakfast, lunch, dinner, or healthy snacks</p>
            </div>
            <a href="{{ route('food.index', ['date' => $defaultDate]) }}" class="btn btn-outline btn-sm">
                <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Back to Food Log
            </a>
        </div>

        @if($errors->any())
            <div class="alert alert-error">
                <i data-lucide="alert-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
                <div>
                    <ul style="margin:0;padding-left:1rem;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('food.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- Meal Type Selection --}}
            <div class="form-group" style="margin-bottom:1.5rem;">
                <label class="form-label required">Meal Category</label>
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.75rem;">
                    @php
                        $mealTypes = [
                            'breakfast' => ['label' => 'Breakfast', 'icon' => 'sun'],
                            'lunch'     => ['label' => 'Lunch', 'icon' => 'utensils'],
                            'dinner'    => ['label' => 'Dinner', 'icon' => 'moon'],
                            'snack'     => ['label' => 'Snack', 'icon' => 'apple'],
                        ];
                    @endphp
                    @foreach($mealTypes as $type => $info)
                        <label style="cursor:pointer;position:relative;">
                            <input type="radio" name="meal_type" value="{{ $type }}" class="meal-type-radio"
                                   {{ old('meal_type', $defaultType) === $type ? 'checked' : '' }} style="position:absolute;opacity:0;"
                                   onchange="window.updateMealSelection(this)">
                            <div class="meal-card-selector {{ old('meal_type', $defaultType) === $type ? 'selected' : '' }}" style="padding:0.75rem 0.5rem;text-align:center;border-radius:12px;border:1.5px solid var(--border);transition:all 0.15s ease;display:flex;flex-direction:column;align-items:center;gap:0.35rem;background:#FAF9FD;">
                                <i data-lucide="{{ $info['icon'] }}" style="width:18px;height:18px;"></i>
                                <span style="font-size:0.82rem;font-weight:600;">{{ $info['label'] }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="form-grid-2">
                {{-- Food Item Name --}}
                <div class="form-group">
                    <label class="form-label required" for="food_name">Food Name</label>
                    <input type="text" id="food_name" name="food_name" class="form-control"
                           placeholder="e.g. Oatmeal with Almonds, Chicken Salad"
                           value="{{ old('food_name') }}" required autofocus>
                </div>

                {{-- Serving Size --}}
                <div class="form-group">
                    <label class="form-label" for="serving_size">Serving Size</label>
                    <input type="text" id="serving_size" name="serving_size" class="form-control"
                           placeholder="e.g. 1 bowl, 100g, 1 piece"
                           value="{{ old('serving_size', '1 serving') }}">
                </div>
            </div>

            {{-- Quantity & Calories --}}
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label required" for="quantity">Quantity / Servings</label>
                    <input type="number" id="quantity" name="quantity" class="form-control"
                           step="0.1" min="0.1" value="{{ old('quantity', 1) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="calories">Calories (kcal)</label>
                    <input type="number" id="calories" name="calories" class="form-control"
                           step="1" min="0" placeholder="e.g. 350" value="{{ old('calories') }}" required>
                </div>
            </div>

            {{-- Macronutrients: Protein, Carbs, Fat --}}
            <div style="background:#FAF9FD;padding:1.25rem;border-radius:14px;margin-bottom:1.25rem;border:1px solid var(--border);">
                <div style="font-size:0.82rem;font-weight:700;color:var(--text);margin-bottom:0.75rem;display:flex;align-items:center;gap:0.4rem;">
                    <i data-lucide="pie-chart" style="width:16px;height:16px;color:var(--primary);"></i>
                    Macronutrients (Optional, grams)
                </div>

                <div class="form-grid-3">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label" for="protein" style="color:var(--sage);">Protein (g)</label>
                        <input type="number" id="protein" name="protein" class="form-control"
                               step="0.1" min="0" placeholder="0" value="{{ old('protein', 0) }}">
                    </div>

                    <div class="form-group" style="margin:0;">
                        <label class="form-label" for="carbohydrates" style="color:var(--peach);">Carbs (g)</label>
                        <input type="number" id="carbohydrates" name="carbohydrates" class="form-control"
                               step="0.1" min="0" placeholder="0" value="{{ old('carbohydrates', 0) }}">
                    </div>

                    <div class="form-group" style="margin:0;">
                        <label class="form-label" for="fat" style="color:#EC4899;">Fats (g)</label>
                        <input type="number" id="fat" name="fat" class="form-control"
                               step="0.1" min="0" placeholder="0" value="{{ old('fat', 0) }}">
                    </div>
                </div>
            </div>

            {{-- Date & Time --}}
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label required" for="meal_date">Meal Date</label>
                    <input type="date" id="meal_date" name="meal_date" class="form-control"
                           value="{{ old('meal_date', $defaultDate) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="meal_time">Meal Time</label>
                    <input type="time" id="meal_time" name="meal_time" class="form-control"
                           value="{{ old('meal_time', now()->format('H:i')) }}">
                </div>
            </div>

            {{-- Food Image Upload Dropzone --}}
            <div class="form-group">
                <label class="form-label">Food Photo (Optional)</label>
                <label for="image" class="image-upload-dropzone" style="display:block;">
                    <i data-lucide="camera" class="upload-icon"></i>
                    <div class="upload-text">Click to choose or take a food picture</div>
                    <div class="upload-subtext">PNG, JPG, or WEBP up to 3MB</div>
                    <input type="file" id="image" name="image" accept="image/*" style="display:none;" onchange="window.SelfCare.previewImage(this, 'foodImagePreview')">
                </label>
                <div style="margin-top:0.75rem;text-align:center;">
                    <img id="foodImagePreview" src="" alt="Preview" style="display:none;max-height:160px;margin:0 auto;border-radius:12px;border:1px solid var(--border);">
                </div>
            </div>

            {{-- Notes --}}
            <div class="form-group">
                <label class="form-label" for="notes">Notes & Reflection</label>
                <textarea id="notes" name="notes" class="form-control" rows="2"
                          placeholder="How did this meal make you feel? Any specific ingredients?">{{ old('notes') }}</textarea>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--border);">
                <a href="{{ route('food.index', ['date' => $defaultDate]) }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary" style="padding:0.6rem 1.75rem;">
                    <i data-lucide="check" style="width:15px;height:15px;"></i> Save Meal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
