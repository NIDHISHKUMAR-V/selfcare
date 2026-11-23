@extends('layouts.app')

@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')
@section('page-subtitle', 'Update your personal information and settings')

@section('content')
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

    {{-- Personal Information --}}
    <div class="card">
        <div class="section-title" style="margin-bottom:1.25rem;">Personal Information</div>

        @if(session('success'))
            <div class="alert alert-success">
                <i data-lucide="check-circle" style="width:15px;height:15px;flex-shrink:0;"></i>
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Avatar preview --}}
            <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.25rem;">
                <img src="{{ $user->avatar_url }}" alt="Avatar" id="avatarPreview"
                     style="width:70px;height:70px;border-radius:50%;object-fit:cover;border:2px solid var(--primary-light);">
                <div>
                    <label for="avatar" class="btn btn-outline btn-sm" style="cursor:pointer;">
                        <i data-lucide="upload" style="width:13px;height:13px;"></i> Change Photo
                    </label>
                    <input type="file" id="avatar" name="avatar" accept="image/*" style="display:none;"
                           onchange="previewAvatar(this)">
                    <p style="font-size:0.72rem;color:var(--text-muted);margin-top:0.25rem;">JPG, PNG, GIF, WEBP · Max 2MB</p>
                </div>
            </div>
            @error('avatar')<div class="invalid-feedback" style="display:block;margin-bottom:0.5rem;">{{ $message }}</div>@enderror

            <div class="form-group">
                <label class="form-label" for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                       value="{{ old('name', $user->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                       value="{{ old('email', $user->email) }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                       value="{{ old('phone', $user->phone) }}" placeholder="Optional">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="bio">Bio</label>
                <textarea id="bio" name="bio" class="form-control {{ $errors->has('bio') ? 'is-invalid' : '' }}"
                          rows="3" placeholder="Tell us a little about yourself...">{{ old('bio', $user->bio) }}</textarea>
                @error('bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                <i data-lucide="save" style="width:14px;height:14px;"></i> Save Changes
            </button>
        </form>
    </div>

    <div style="display:flex;flex-direction:column;gap:1.5rem;">

        {{-- Health & Finance Goals --}}
        <div class="card">
            <div class="section-title" style="margin-bottom:1.25rem;">Health & Finance Goals</div>
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                {{-- hidden fields to pass name/email unchanged --}}
                <input type="hidden" name="name" value="{{ $user->name }}">
                <input type="hidden" name="email" value="{{ $user->email }}">

                <div class="form-group">
                    <label class="form-label" for="daily_calorie_goal">Daily Calorie Goal (kcal)</label>
                    <input type="number" id="daily_calorie_goal" name="daily_calorie_goal" class="form-control"
                           value="{{ old('daily_calorie_goal', $user->daily_calorie_goal) }}" min="500" max="10000" placeholder="2000">
                </div>

                <div class="form-group">
                    <label class="form-label" for="daily_water_goal_ml">Daily Water Goal (ml)</label>
                    <input type="number" id="daily_water_goal_ml" name="daily_water_goal_ml" class="form-control"
                           value="{{ old('daily_water_goal_ml', $user->daily_water_goal_ml) }}" min="500" max="10000" placeholder="2500">
                </div>

                <div class="form-group">
                    <label class="form-label" for="monthly_budget_limit">Monthly Budget Limit (₹)</label>
                    <input type="number" id="monthly_budget_limit" name="monthly_budget_limit" class="form-control"
                           value="{{ old('monthly_budget_limit', $user->monthly_budget_limit) }}" min="0" step="0.01" placeholder="1000.00">
                </div>

                <button type="submit" class="btn btn-sage" style="width:100%;justify-content:center;">
                    <i data-lucide="target" style="width:14px;height:14px;"></i> Update Goals
                </button>
            </form>
        </div>

        {{-- Change Password --}}
        <div class="card">
            <div class="section-title" style="margin-bottom:1.25rem;">Change Password</div>

            <form method="POST" action="{{ route('profile.change-password') }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label" for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password"
                           class="form-control {{ $errors->has('current_password') ? 'is-invalid' : '' }}"
                           placeholder="••••••••">
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="new_password">New Password</label>
                    <input type="password" id="new_password" name="password"
                           class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                           placeholder="Min. 8 characters">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="new_password_confirmation">Confirm New Password</label>
                    <input type="password" id="new_password_confirmation" name="password_confirmation"
                           class="form-control" placeholder="Repeat new password">
                </div>

                <button type="submit" class="btn btn-outline" style="width:100%;justify-content:center;">
                    <i data-lucide="key" style="width:14px;height:14px;"></i> Change Password
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('avatarPreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
