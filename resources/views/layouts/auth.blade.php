<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'My Self-Care') }} – @yield('title', 'Auth')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Lucide -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body>
<div class="auth-wrapper">
    {{-- Left Panel --}}
    <div class="auth-left">
        <div class="auth-left-content">
            <div class="logo-icon">✦</div>
            <h1>My Self-Care</h1>
            <p>Your personal life management companion. Track food, finances, habits, and more — all in one beautiful place.</p>

            <div class="features-list">
                <div class="feature-item">
                    <div class="feature-dot" style="background:#EDE9FE;"><i data-lucide="utensils" style="width:14px;height:14px;color:#8E8CD8;"></i></div>
                    <span>Nutrition & Food Tracking</span>
                </div>
                <div class="feature-item">
                    <div class="feature-dot" style="background:#E8F5ED;"><i data-lucide="wallet" style="width:14px;height:14px;color:#7FA68E;"></i></div>
                    <span>Personal Finance Manager</span>
                </div>
                <div class="feature-item">
                    <div class="feature-dot" style="background:#FEF3E8;"><i data-lucide="repeat" style="width:14px;height:14px;color:#F4A261;"></i></div>
                    <span>Habit & Activity Tracking</span>
                </div>
                <div class="feature-item">
                    <div class="feature-dot" style="background:#FDF2F8;"><i data-lucide="target" style="width:14px;height:14px;color:#EC4899;"></i></div>
                    <span>Goals & Personal Development</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Panel --}}
    <div class="auth-right">
        <div class="auth-form-container">
            @yield('content')
        </div>
    </div>
</div>

</body>
</html>
