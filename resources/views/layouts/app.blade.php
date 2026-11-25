<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'My Self-Care') }} – @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">

{{-- Sidebar Overlay (mobile) --}}
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

{{-- Sidebar --}}
<aside class="sidebar" id="sidebar">
    {{-- Brand --}}
    <div class="sidebar-brand">
        <div class="logo-icon">✦</div>
        <div class="brand-text">
            <h2>My Self-Care</h2>
            <p>Life Management App</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="sidebar-nav">
        <div class="nav-section-label">Overview</div>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i data-lucide="layout-dashboard"></i> Dashboard
        </a>

        <div class="nav-section-label" style="margin-top:0.75rem;">Nutrition</div>
        <a href="{{ route('food.index') }}" class="nav-link {{ request()->routeIs('food.index', 'food.create', 'food.edit', 'food.show') ? 'active' : '' }}">
            <i data-lucide="utensils"></i> Food Tracker
        </a>
        <a href="{{ route('water.index') }}" class="nav-link {{ request()->routeIs('water.*') ? 'active' : '' }}">
            <i data-lucide="droplets"></i> Water Tracker
        </a>
        <a href="{{ route('food.reports') }}" class="nav-link {{ request()->routeIs('food.reports') ? 'active' : '' }}">
            <i data-lucide="pie-chart"></i> Nutrition Reports
        </a>

        <div class="nav-section-label" style="margin-top:0.75rem;">Finance</div>
        <a href="{{ route('finance.index') }}" class="nav-link {{ request()->routeIs('finance.index', 'finance.create', 'finance.edit', 'finance.show') ? 'active' : '' }}">
            <i data-lucide="wallet"></i> Money Manager
        </a>
        <a href="{{ route('budgets.index') }}" class="nav-link {{ request()->routeIs('budgets.*') ? 'active' : '' }}">
            <i data-lucide="pie-chart"></i> Budget Planner
        </a>
        <a href="{{ route('savings.index') }}" class="nav-link {{ request()->routeIs('savings.*') ? 'active' : '' }}">
            <i data-lucide="piggy-bank"></i> Savings Goals
        </a>
        <a href="{{ route('finance.reports') }}" class="nav-link {{ request()->routeIs('finance.reports') ? 'active' : '' }}">
            <i data-lucide="trending-up"></i> Financial Reports
        </a>

        <div class="nav-section-label" style="margin-top:0.75rem;">Daily Life</div>
        <a href="{{ route('activities.index') }}" class="nav-link {{ request()->routeIs('activities.*') ? 'active' : '' }}">
            <i data-lucide="list-checks"></i> Daily Activities
        </a>
        <a href="{{ route('habits.index') }}" class="nav-link {{ request()->routeIs('habits.*') ? 'active' : '' }}">
            <i data-lucide="repeat"></i> Habit Tracker
        </a>
        <a href="{{ route('exercise.index') }}" class="nav-link {{ request()->routeIs('exercise.*') ? 'active' : '' }}">
            <i data-lucide="dumbbell"></i> Exercise Tracker
        </a>
        <a href="{{ route('sleep.index') }}" class="nav-link {{ request()->routeIs('sleep.*') ? 'active' : '' }}">
            <i data-lucide="moon"></i> Sleep Tracker
        </a>

        <div class="nav-section-label" style="margin-top:0.75rem;">Growth</div>
        <a href="{{ route('goals.index') }}" class="nav-link {{ request()->routeIs('goals.*') ? 'active' : '' }}">
            <i data-lucide="target"></i> Goals
        </a>
        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i data-lucide="bar-chart-2"></i> Reports
        </a>

        <div class="nav-section-label" style="margin-top:0.75rem;">Account</div>
        <a href="{{ route('profile.show') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i data-lucide="user"></i> Profile
        </a>
    </nav>

    {{-- User footer --}}
    <div class="sidebar-footer">
        <a href="{{ route('profile.edit') }}" class="sidebar-user">
            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}">
            <div class="user-info">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-email">{{ auth()->user()->email }}</div>
            </div>
            <i data-lucide="chevron-right" style="width:14px;height:14px;color:var(--text-muted);flex-shrink:0;"></i>
        </a>

        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit" class="btn btn-outline w-full" style="justify-content:center;font-size:0.8rem;">
                <i data-lucide="log-out" style="width:14px;height:14px;"></i> Sign Out
            </button>
        </form>
    </div>
</aside>

{{-- Main --}}
<div class="main-content">

    {{-- Topbar --}}
    <header class="topbar">
        <div style="display:flex;align-items:center;gap:0.75rem;">
            <button class="mobile-nav-toggle" onclick="openSidebar()" aria-label="Open navigation">
                <i data-lucide="menu" style="width:20px;height:20px;"></i>
            </button>
            <div>
                <h1 style="font-size:1rem;font-weight:700;color:var(--text);line-height:1.1;">@yield('page-title', 'Dashboard')</h1>
                <p style="font-size:0.72rem;color:var(--text-muted);">@yield('page-subtitle', 'Welcome back, ' . (auth()->user()?->name ?? 'User'))</p>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:0.75rem;">
            <span style="font-size:0.78rem;color:var(--text-muted);">{{ now()->format('l, d M Y') }}</span>
            <a href="{{ route('profile.edit') }}" style="text-decoration:none;">
                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                     style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid var(--primary-light);">
            </a>
        </div>
    </header>

    {{-- Flash Messages --}}
    <div style="padding:0 1.75rem;padding-top:1rem;" id="flashMessages">
        @if(session('success'))
            <div class="alert alert-success">
                <i data-lucide="check-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" style="margin-left:auto;background:none;border:none;cursor:pointer;color:inherit;"><i data-lucide="x" style="width:14px;height:14px;"></i></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">
                <i data-lucide="alert-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
                <span>{{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" style="margin-left:auto;background:none;border:none;cursor:pointer;color:inherit;"><i data-lucide="x" style="width:14px;height:14px;"></i></button>
            </div>
        @endif
    </div>

    {{-- Page Content --}}
    <main class="page-content">
        @yield('content')
    </main>
</div>

@stack('scripts')
</body>
</html>
