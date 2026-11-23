@extends('layouts.app')

@section('title', 'Edit Meal')
@section('page-title', 'Edit Meal')
@section('page-subtitle', 'Update food record and nutritional information')

@section('content')
<div style="max-width:800px;margin:0 auto;">
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;padding-bottom:0.75rem;border-bottom:1px solid var(--border);">
            <div class="section-title">Edit Food Record</div>
            <a href="{{ route('food.index', ['date' => $foodLog->meal_date->toDateString()]) }}" class="btn btn-outline btn-sm">
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

        <form method="POST" action="{{ route('food.update', $foodLog) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Meal Type Selection --}}
            <div class="form-group" style="margin-bottom:1.25rem;">
                <label class="form-label">Meal Type</label>
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.5rem;">
                    @foreach(['breakfast' => 'Breakfast', 'lunch' => 'Lunch', 'dinner' => 'Dinner', 'snack' => 'Snack'] as $type => $label)
                        <label style="cursor:pointer;">
                            <input type="radio" name="meal_type" value="{{ $type }}"
                                   {{ old('meal_type', $foodLog->meal_type) === $type ? 'checked' : '' }} style="display:none;"
                                   onchange="updateMealSelection(this)">
                            <div class="meal-type-badge {{ old('meal_type', $foodLog->meal_type) === $type ? 'selected' : '' }}"
                                 id="badge_{{ $type }}"
                                 style="padding:0.6rem;text-align:center;border-radius:10px;border:1.5px solid var(--border);font-size:0.85rem;font-weight:600;transition:all 0.15s ease;">
                                {{ $label }}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div style="display:grid;grid-template-columns:2fr 1fr;gap:1rem;">
                {{-- Food Item Name --}}
                <div class="form-group">
                    <label class="form-label" for="food_name">Food Item Name <span style="color:#DC2626;">*</span></label>
                    <input type="text" id="food_name" name="food_name" class="form-control {{ $errors->has('food_name') ? 'is-invalid' : '' }}"
                           value="{{ old('food_name', $foodLog->food_name) }}" required autofocus>
                </div>

                {{-- Serving Size --}}
                <div class="form-group">
                    <label class="form-label" for="serving_size">Serving Size</label>
                    <input type="text" id="serving_size" name="serving_size" class="form-control"
                           value="{{ old('serving_size', $foodLog->serving_size) }}">
                </div>
            </div>

            {{-- Quantity & Calories --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label" for="quantity">Quantity <span style="color:#DC2626;">*</span></label>
                    <input type="number" id="quantity" name="quantity" class="form-control {{ $errors->has('quantity') ? 'is-invalid' : '' }}"
                           step="0.1" min="0.1" value="{{ old('quantity', $foodLog->quantity) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="calories">Calories (kcal) <span style="color:#DC2626;">*</span></label>
                    <input type="number" id="calories" name="calories" class="form-control {{ $errors->has('calories') ? 'is-invalid' : '' }}"
                           step="1" min="0" value="{{ old('calories', $foodLog->calories) }}" required>
                </div>
            </div>

            {{-- Macronutrients: Protein, Carbs, Fat --}}
            <div style="background:var(--bg);padding:1rem;border-radius:12px;margin-bottom:1rem;border:1px solid var(--border);">
                <div style="font-size:0.8rem;font-weight:700;color:var(--text);margin-bottom:0.75rem;display:flex;align-items:center;gap:0.4rem;">
                    <i data-lucide="activity" style="width:14px;height:14px;color:var(--primary);"></i>
                    Macronutrients (in grams)
                </div>

                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label" for="protein" style="color:var(--sage);">Protein (g)</label>
                        <input type="number" id="protein" name="protein" class="form-control"
                               step="0.1" min="0" value="{{ old('protein', $foodLog->protein) }}">
                    </div>

                    <div class="form-group" style="margin:0;">
                        <label class="form-label" for="carbohydrates" style="color:#C2500C;">Carbohydrates (g)</label>
                        <input type="number" id="carbohydrates" name="carbohydrates" class="form-control"
                               step="0.1" min="0" value="{{ old('carbohydrates', $foodLog->carbohydrates) }}">
                    </div>

                    <div class="form-group" style="margin:0;">
                        <label class="form-label" for="fat" style="color:#EC4899;">Fat (g)</label>
                        <input type="number" id="fat" name="fat" class="form-control"
                               step="0.1" min="0" value="{{ old('fat', $foodLog->fat) }}">
                    </div>
                </div>
            </div>

            {{-- Date & Time --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label" for="meal_date">Meal Date <span style="color:#DC2626;">*</span></label>
                    <input type="date" id="meal_date" name="meal_date" class="form-control"
                           value="{{ old('meal_date', $foodLog->meal_date->toDateString()) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="meal_time">Meal Time</label>
                    <input type="time" id="meal_time" name="meal_time" class="form-control"
                           value="{{ old('meal_time', $foodLog->meal_time ? \Carbon\Carbon::parse($foodLog->meal_time)->format('H:i') : '') }}">
                </div>
            </div>

            {{-- Food Image Upload --}}
            <div class="form-group">
                <label class="form-label">Food Photo</label>
                @if($foodLog->image_path)
                    <div style="margin-bottom:0.75rem;">
                        <img src="{{ Storage::disk('public')->url($foodLog->image_path) }}" alt="Current food photo"
                             style="max-height:100px;border-radius:8px;border:1px solid var(--border);">
                        <p style="font-size:0.72rem;color:var(--text-muted);margin-top:0.25rem;">Current photo</p>
                    </div>
                @endif
                <div style="display:flex;align-items:center;gap:1rem;">
                    <label for="image" class="btn btn-outline btn-sm" style="cursor:pointer;">
                        <i data-lucide="image" style="width:14px;height:14px;"></i> {{ $foodLog->image_path ? 'Replace Food Photo' : 'Upload Food Photo' }}
                    </label>
                    <input type="file" id="image" name="image" accept="image/*" style="display:none;" onchange="previewFoodImage(this)">
                    <span id="fileNameDisplay" style="font-size:0.75rem;color:var(--text-muted);">PNG, JPG, WEBP up to 3MB</span>
                </div>
                <div id="imagePreviewContainer" style="display:none;margin-top:0.75rem;">
                    <img id="foodImagePreview" src="" alt="Preview" style="max-height:140px;border-radius:10px;object-fit:cover;border:1px solid var(--border);">
                </div>
            </div>

            {{-- Notes --}}
            <div class="form-group">
                <label class="form-label" for="notes">Notes / Ingredients</label>
                <textarea id="notes" name="notes" class="form-control" rows="2">{{ old('notes', $foodLog->notes) }}</textarea>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:1.5rem;">
                <a href="{{ route('food.index', ['date' => $foodLog->meal_date->toDateString()]) }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary" style="padding-left:1.5rem;padding-right:1.5rem;">
                    <i data-lucide="save" style="width:14px;height:14px;"></i> Update Meal Record
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<style>
    .meal-type-badge.selected {
        background: var(--primary) !important;
        color: #fff !important;
        border-color: var(--primary) !important;
    }
</style>
<script>
    function updateMealSelection(radio) {
        document.querySelectorAll('.meal-type-badge').forEach(b => b.classList.remove('selected'));
        const badge = document.getElementById('badge_' + radio.value);
        if (badge) {
            badge.classList.add('selected');
        }
    }

    function previewFoodImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('foodImagePreview').src = e.target.result;
                document.getElementById('imagePreviewContainer').style.display = 'block';
                document.getElementById('fileNameDisplay').textContent = input.files[0].name;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
@endsection
