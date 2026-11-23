@extends('layouts.auth')

@section('title', 'Create Account')

@section('content')
<h2>Create account</h2>
<p class="subtitle">Start your self-care journey today — it's free</p>

@if($errors->any())
    <div class="alert alert-error">
        <i data-lucide="alert-circle" style="width:15px;height:15px;flex-shrink:0;"></i>
        <div>
            <ul style="margin:0;padding-left:1rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="form-group">
        <label class="form-label" for="name">Full Name</label>
        <div class="input-group">
            <i data-lucide="user" class="input-group-icon"></i>
            <input type="text" id="name" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                   placeholder="Your Name" value="{{ old('name') }}" required autofocus>
        </div>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <div class="input-group">
            <i data-lucide="mail" class="input-group-icon"></i>
            <input type="email" id="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                   placeholder="you@example.com" value="{{ old('email') }}" required>
        </div>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div class="input-group">
            <i data-lucide="lock" class="input-group-icon"></i>
            <input type="password" id="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                   placeholder="Min. 8 characters" required>
        </div>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="password_confirmation">Confirm Password</label>
        <div class="input-group">
            <i data-lucide="lock" class="input-group-icon"></i>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   class="form-control" placeholder="Repeat password" required>
        </div>
    </div>

    <button type="submit" class="btn-auth" style="margin-top:0.5rem;">
        Create Account
    </button>
</form>

<p class="auth-link">
    Already have an account? <a href="{{ route('login') }}">Sign in</a>
</p>
@endsection
