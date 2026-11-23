@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<h2>Welcome back</h2>
<p class="subtitle">Sign in to your self-care dashboard</p>

@if($errors->any())
    <div class="alert alert-error">
        <i data-lucide="alert-circle" style="width:15px;height:15px;flex-shrink:0;"></i>
        <div>{{ $errors->first() }}</div>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success">
        <i data-lucide="check-circle" style="width:15px;height:15px;flex-shrink:0;"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif

<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <div class="input-group">
            <i data-lucide="mail" class="input-group-icon"></i>
            <input type="email" id="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                   placeholder="you@example.com" value="{{ old('email') }}" required autofocus>
        </div>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div class="input-group">
            <i data-lucide="lock" class="input-group-icon"></i>
            <input type="password" id="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                   placeholder="••••••••" required>
        </div>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="remember-row">
        <label class="checkbox-group">
            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
            Remember me
        </label>
        <a href="{{ route('password.request') }}" style="font-size:0.82rem;color:var(--primary);text-decoration:none;font-weight:500;">Forgot password?</a>
    </div>

    <button type="submit" class="btn-auth">
        Sign In
    </button>
</form>

<p class="auth-link">
    Don't have an account? <a href="{{ route('register') }}">Create account</a>
</p>
@endsection
