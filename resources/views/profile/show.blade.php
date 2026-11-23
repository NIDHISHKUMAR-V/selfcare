@extends('layouts.app')

@section('title', 'Profile')
@section('page-title', 'My Profile')
@section('page-subtitle', 'View and manage your personal information')

@section('content')
<div style="display:grid;grid-template-columns:280px 1fr;gap:1.5rem;align-items:start;">

    {{-- Profile Card --}}
    <div class="card" style="text-align:center;">
        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
             style="width:90px;height:90px;border-radius:50%;object-fit:cover;margin:0 auto 1rem;border:3px solid var(--primary-light);display:block;">
        <h2 style="font-size:1.1rem;font-weight:700;color:var(--text);margin-bottom:0.2rem;">{{ $user->name }}</h2>
        <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:0.75rem;">{{ $user->email }}</p>
        @if($user->bio)
            <p style="font-size:0.82rem;color:var(--text-muted);line-height:1.5;margin-bottom:1rem;">{{ $user->bio }}</p>
        @endif
        <a href="{{ route('profile.edit') }}" class="btn btn-primary" style="width:100%;justify-content:center;">
            <i data-lucide="edit" style="width:14px;height:14px;"></i> Edit Profile
        </a>

        <div style="margin-top:1.25rem;border-top:1px solid var(--border);padding-top:1.25rem;display:flex;flex-direction:column;gap:0.5rem;text-align:left;">
            @if($user->phone)
                <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.8rem;color:var(--text-muted);">
                    <i data-lucide="phone" style="width:14px;height:14px;"></i> {{ $user->phone }}
                </div>
            @endif
            <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.8rem;color:var(--text-muted);">
                <i data-lucide="calendar" style="width:14px;height:14px;"></i> Joined {{ $user->created_at->format('M Y') }}
            </div>
        </div>
    </div>

    {{-- Settings Grid --}}
    <div style="display:flex;flex-direction:column;gap:1rem;">
        <div class="card">
            <div class="section-title" style="margin-bottom:1rem;">Health Goals</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="stat-card lavender" style="text-align:center;">
                    <div style="font-size:1.5rem;font-weight:700;color:var(--primary);">{{ number_format($user->daily_calorie_goal) }}</div>
                    <div style="font-size:0.75rem;color:var(--text-muted);">Daily Calorie Goal</div>
                </div>
                <div class="stat-card sage" style="text-align:center;">
                    <div style="font-size:1.5rem;font-weight:700;color:var(--sage);">{{ number_format($user->daily_water_goal_ml) }}</div>
                    <div style="font-size:0.75rem;color:var(--text-muted);">Daily Water Goal (ml)</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="section-title" style="margin-bottom:1rem;">Financial Settings</div>
            <div class="stat-card peach" style="text-align:center;">
                <div style="font-size:1.5rem;font-weight:700;color:var(--peach);">₹{{ number_format($user->monthly_budget_limit, 2) }}</div>
                <div style="font-size:0.75rem;color:var(--text-muted);">Monthly Budget Limit</div>
            </div>
        </div>
    </div>
</div>
@endsection
