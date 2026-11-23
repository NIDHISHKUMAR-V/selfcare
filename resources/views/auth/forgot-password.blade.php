@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<h2>Forgot password?</h2>
<p class="subtitle">Enter your email and we'll send you a reset link</p>

@if(session('success'))
    <div class="alert alert-success">
        <i data-lucide="check-circle" style="width:15px;height:15px;flex-shrink:0;"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-error">
        <i data-lucide="alert-circle" style="width:15px;height:15px;flex-shrink:0;"></i>
        <div>{{ $errors->first() }}</div>
    </div>
@endif

<form method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <div class="input-group">
            <i data-lucide="mail" class="input-group-icon"></i>
            <input type="email" id="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                   placeholder="you@example.com" value="{{ old('email') }}" required autofocus>
        </div>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <button type="submit" class="btn-auth">Send Reset Link</button>
</form>

<p class="auth-link"><a href="{{ route('login') }}">← Back to login</a></p>
@endsection
