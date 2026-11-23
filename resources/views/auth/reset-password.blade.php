@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<h2>Reset password</h2>
<p class="subtitle">Choose a strong new password for your account</p>

@if($errors->any())
    <div class="alert alert-error">
        <i data-lucide="alert-circle" style="width:15px;height:15px;flex-shrink:0;"></i>
        <div>{{ $errors->first() }}</div>
    </div>
@endif

<form method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <div class="input-group">
            <i data-lucide="mail" class="input-group-icon"></i>
            <input type="email" id="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                   value="{{ old('email', $email ?? '') }}" required>
        </div>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="password">New Password</label>
        <div class="input-group">
            <i data-lucide="lock" class="input-group-icon"></i>
            <input type="password" id="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                   placeholder="Min. 8 characters" required>
        </div>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="password_confirmation">Confirm New Password</label>
        <div class="input-group">
            <i data-lucide="lock" class="input-group-icon"></i>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   class="form-control" placeholder="Repeat password" required>
        </div>
    </div>

    <button type="submit" class="btn-auth">Reset Password</button>
</form>

<p class="auth-link"><a href="{{ route('login') }}">← Back to login</a></p>
@endsection
