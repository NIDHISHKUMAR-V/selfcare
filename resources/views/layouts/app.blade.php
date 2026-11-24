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

    <style>
        :root {
            --bg: #F8F7FC;
            --card: #FFFFFF;
            --primary: #8E8CD8;
            --primary-light: #EDE9FE;
            --sage: #7FA68E;
            --sage-light: #E8F5ED;
            --peach: #F4A261;
            --peach-light: #FEF3E8;
            --text: #2D2D3A;
            --text-muted: #6B6B80;
            --border: #E8E6F0;
            --sidebar-w: 260px;
        }

        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); }

        /* ---- Sidebar ---- */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--card);
            border-right: 1px solid var(--border);
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 50;
            box-shadow: 2px 0 20px rgba(142,140,216,0.08);
        }
        .sidebar-brand {
            padding: 1.5rem 1.25rem 1rem;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 0.75rem;
        }
        .sidebar-brand .logo-icon {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--primary), #A78BFA);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 18px; font-weight: 700;
        }
        .sidebar-brand .brand-text h2 { font-size: 0.95rem; font-weight: 700; color: var(--text); line-height: 1.1; }
        .sidebar-brand .brand-text p { font-size: 0.7rem; color: var(--text-muted); }

        .sidebar-nav { flex: 1; padding: 1rem 0.75rem; }
        .nav-section-label {
            font-size: 0.65rem; font-weight: 600; letter-spacing: 0.08em;
            color: var(--text-muted); padding: 0.5rem 0.5rem 0.25rem;
            text-transform: uppercase;
        }
        .nav-link {
            display: flex; align-items: center; gap: 0.65rem;
            padding: 0.55rem 0.75rem;
            border-radius: 10px; color: var(--text-muted);
            font-size: 0.85rem; font-weight: 500;
            transition: all 0.15s ease;
            text-decoration: none; margin-bottom: 2px;
        }
        .nav-link:hover { background: var(--primary-light); color: var(--primary); }
        .nav-link.active { background: var(--primary); color: #fff; }
        .nav-link.active i { color: #fff; }
        .nav-link i { width: 16px; height: 16px; }

        .sidebar-footer {
            padding: 1rem; border-top: 1px solid var(--border);
        }
        .sidebar-user {
            display: flex; align-items: center; gap: 0.65rem;
            padding: 0.65rem 0.75rem; border-radius: 12px;
            background: var(--bg); cursor: pointer;
            text-decoration: none;
        }
        .sidebar-user img { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; }
        .sidebar-user .user-info { flex: 1; overflow: hidden; }
        .sidebar-user .user-name { font-size: 0.82rem; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-user .user-email { font-size: 0.7rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* ---- Main content ---- */
        .main-content {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex; flex-direction: column;
        }
        .topbar {
            background: var(--card);
            border-bottom: 1px solid var(--border);
            padding: 0.9rem 1.75rem;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 40;
        }
        .page-content { padding: 1.75rem; flex: 1; }

        /* ---- Cards ---- */
        .card {
            background: var(--card); border-radius: 16px;
            border: 1px solid var(--border); padding: 1.25rem;
            box-shadow: 0 1px 8px rgba(0,0,0,0.04);
        }
        .stat-card {
            border-radius: 16px; padding: 1.25rem;
            border: 1px solid transparent;
        }
        .stat-card.lavender { background: var(--primary-light); border-color: #DDD6FE; }
        .stat-card.sage { background: var(--sage-light); border-color: #BBF7D0; }
        .stat-card.peach { background: var(--peach-light); border-color: #FED7AA; }
        .stat-card.pink { background: #FDF2F8; border-color: #FBCFE8; }
        .stat-card.blue { background: #EFF6FF; border-color: #BFDBFE; }

        .stat-icon {
            width: 44px; height: 44px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 0.75rem;
        }
        .stat-icon.purple { background: var(--primary); color: #fff; }
        .stat-icon.green { background: var(--sage); color: #fff; }
        .stat-icon.orange { background: var(--peach); color: #fff; }
        .stat-icon.pink { background: #EC4899; color: #fff; }
        .stat-icon.blue { background: #3B82F6; color: #fff; }

        .stat-value { font-size: 1.6rem; font-weight: 700; color: var(--text); line-height: 1.1; }
        .stat-label { font-size: 0.75rem; color: var(--text-muted); font-weight: 500; margin-top: 2px; }
        .stat-change { font-size: 0.72rem; font-weight: 500; margin-top: 0.4rem; }

        /* Progress bar */
        .progress-bar { height: 8px; border-radius: 999px; background: #E8E6F0; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: 999px; transition: width 0.6s ease; }

        /* Mobile toggle */
        .mobile-nav-toggle {
            display: none;
            background: none; border: none; cursor: pointer; padding: 0.4rem;
            border-radius: 8px; color: var(--text);
        }
        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.3); z-index: 45;
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.open { display: block; }
            .main-content { margin-left: 0; }
            .mobile-nav-toggle { display: flex; align-items: center; }
            .topbar { padding: 0.75rem 1rem; }
            .page-content { padding: 1rem; }
        }

        /* Notifications / alerts */
        .alert {
            padding: 0.8rem 1rem; border-radius: 10px;
            display: flex; align-items: flex-start; gap: 0.65rem;
            font-size: 0.84rem; margin-bottom: 1rem;
        }
        .alert-success { background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; }
        .alert-error { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; }

        /* Badges */
        .badge { padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.7rem; font-weight: 600; }
        .badge-purple { background: var(--primary-light); color: var(--primary); }
        .badge-green { background: var(--sage-light); color: var(--sage); }
        .badge-orange { background: var(--peach-light); color: #C2500C; }
        .badge-red { background: #FEF2F2; color: #DC2626; }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.84rem; font-weight: 500; cursor: pointer; border: none; transition: all 0.15s ease; text-decoration: none; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: #7c6fd4; color: #fff; }
        .btn-sage { background: var(--sage); color: #fff; }
        .btn-sage:hover { background: #6b9079; }
        .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text); }
        .btn-outline:hover { background: var(--bg); }
        .btn-sm { padding: 0.35rem 0.75rem; font-size: 0.78rem; }

        /* Section header */
        .section-title { font-size: 1rem; font-weight: 700; color: var(--text); }
        .section-subtitle { font-size: 0.78rem; color: var(--text-muted); margin-top: 1px; }
    </style>
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
        <a href="#" class="nav-link {{ request()->routeIs('activities.*') ? 'active' : '' }}">
            <i data-lucide="list-checks"></i> Daily Activities
        </a>
        <a href="#" class="nav-link {{ request()->routeIs('habits.*') ? 'active' : '' }}">
            <i data-lucide="repeat"></i> Habit Tracker
        </a>
        <a href="#" class="nav-link {{ request()->routeIs('exercise.*') ? 'active' : '' }}">
            <i data-lucide="dumbbell"></i> Exercise Tracker
        </a>
        <a href="#" class="nav-link {{ request()->routeIs('sleep.*') ? 'active' : '' }}">
            <i data-lucide="moon"></i> Sleep Tracker
        </a>

        <div class="nav-section-label" style="margin-top:0.75rem;">Growth</div>
        <a href="#" class="nav-link {{ request()->routeIs('goals.*') ? 'active' : '' }}">
            <i data-lucide="target"></i> Goals
        </a>
        <a href="#" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i data-lucide="bar-chart-2"></i> Reports
        </a>

        <div class="nav-section-label" style="margin-top:0.75rem;">Account</div>
        <a href="{{ route('profile.show') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i data-lucide="user"></i> Profile
        </a>
        <a href="#" class="nav-link">
            <i data-lucide="settings"></i> Settings
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

<script>
    // Init Lucide icons
    lucide.createIcons();

    // Mobile sidebar
    function openSidebar() {
        document.getElementById('sidebar').classList.add('open');
        document.getElementById('sidebarOverlay').classList.add('open');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('open');
    }

    // Auto-dismiss flash messages after 5s
    setTimeout(() => {
        const flash = document.getElementById('flashMessages');
        if (flash) {
            flash.querySelectorAll('.alert').forEach(el => {
                el.style.opacity = '0';
                el.style.transition = 'opacity 0.5s';
                setTimeout(() => el.remove(), 500);
            });
        }
    }, 5000);
</script>

@stack('scripts')
</body>
</html>
