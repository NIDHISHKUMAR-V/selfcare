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

    <style>
        :root {
            --primary: #8E8CD8;
            --primary-light: #EDE9FE;
            --sage: #7FA68E;
            --peach: #F4A261;
            --text: #2D2D3A;
            --text-muted: #6B6B80;
            --border: #E8E6F0;
            --bg: #F8F7FC;
            --card: #FFFFFF;
        }
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; min-height: 100vh; background: var(--bg); display: flex; align-items: center; justify-content: center; }

        .auth-wrapper {
            display: flex; width: 100%; min-height: 100vh;
        }
        .auth-left {
            flex: 1; background: linear-gradient(145deg, #EDE9FE 0%, #E8F5ED 50%, #FEF3E8 100%);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 3rem; position: relative; overflow: hidden;
        }
        .auth-left::before {
            content: ''; position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%238E8CD8' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='10'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .auth-left-content { position: relative; text-align: center; max-width: 380px; }
        .auth-left .logo-icon {
            width: 70px; height: 70px; background: linear-gradient(135deg, var(--primary), #A78BFA);
            border-radius: 20px; display: flex; align-items: center; justify-content: center;
            font-size: 32px; color: #fff; margin: 0 auto 1.5rem; box-shadow: 0 8px 30px rgba(142,140,216,0.3);
        }
        .auth-left h1 { font-size: 2rem; font-weight: 800; color: var(--text); margin-bottom: 0.5rem; }
        .auth-left p { font-size: 0.95rem; color: var(--text-muted); line-height: 1.6; }

        .features-list { margin-top: 2rem; display: flex; flex-direction: column; gap: 0.75rem; text-align: left; }
        .feature-item { display: flex; align-items: center; gap: 0.65rem; font-size: 0.85rem; color: var(--text); }
        .feature-dot { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

        .auth-right {
            width: 480px; background: var(--card);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 2.5rem 2.5rem;
            box-shadow: -4px 0 40px rgba(0,0,0,0.05);
        }
        .auth-form-container { width: 100%; max-width: 380px; }
        .auth-form-container h2 { font-size: 1.5rem; font-weight: 700; color: var(--text); margin-bottom: 0.25rem; }
        .auth-form-container .subtitle { font-size: 0.84rem; color: var(--text-muted); margin-bottom: 1.75rem; }

        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; font-size: 0.82rem; font-weight: 500; color: var(--text); margin-bottom: 0.4rem; }
        .form-control {
            width: 100%; padding: 0.65rem 0.9rem;
            border: 1.5px solid var(--border); border-radius: 10px;
            font-size: 0.88rem; color: var(--text); background: var(--bg);
            transition: border-color 0.15s, box-shadow 0.15s;
            outline: none; font-family: inherit;
        }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(142,140,216,0.15); background: #fff; }
        .form-control.is-invalid { border-color: #EF4444; }
        .invalid-feedback { font-size: 0.75rem; color: #EF4444; margin-top: 0.3rem; }

        .input-group { position: relative; }
        .input-group .form-control { padding-left: 2.5rem; }
        .input-group-icon { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); width: 16px; height: 16px; }

        .btn-auth {
            width: 100%; padding: 0.75rem;
            background: linear-gradient(135deg, var(--primary), #A78BFA);
            color: #fff; border: none; border-radius: 10px; font-size: 0.9rem; font-weight: 600;
            cursor: pointer; transition: opacity 0.15s, transform 0.1s; font-family: inherit;
        }
        .btn-auth:hover { opacity: 0.92; transform: translateY(-1px); }
        .btn-auth:active { transform: translateY(0); }

        .auth-link { font-size: 0.84rem; color: var(--text-muted); text-align: center; margin-top: 1.25rem; }
        .auth-link a { color: var(--primary); text-decoration: none; font-weight: 500; }
        .auth-link a:hover { text-decoration: underline; }

        .alert { padding: 0.75rem 1rem; border-radius: 10px; font-size: 0.83rem; margin-bottom: 1rem; display: flex; align-items: flex-start; gap: 0.5rem; }
        .alert-error { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; }
        .alert-success { background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; }

        .divider { display: flex; align-items: center; gap: 0.75rem; margin: 1rem 0; color: var(--text-muted); font-size: 0.78rem; }
        .divider::before, .divider::after { content:''; flex:1; height:1px; background: var(--border); }

        .remember-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
        .checkbox-group { display: flex; align-items: center; gap: 0.4rem; font-size: 0.82rem; color: var(--text-muted); cursor: pointer; }
        .checkbox-group input { accent-color: var(--primary); }

        @media (max-width: 768px) {
            .auth-left { display: none; }
            .auth-right { width: 100%; padding: 2rem 1.5rem; box-shadow: none; }
        }
    </style>
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

<script>lucide.createIcons();</script>
</body>
</html>
