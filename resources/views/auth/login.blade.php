@extends('layouts.auth')
@section('title', 'Login')

@section('content')
<div class="auth-card">
    {{-- Header --}}
    <div class="auth-header">
        <div class="auth-logo">
            @if(\App\Models\AppSetting::get('app_logo'))
                <img src="{{ asset('storage/' . \App\Models\AppSetting::get('app_logo')) }}" alt="Logo">
            @else
                <i class="fas fa-warehouse"></i>
            @endif
        </div>
        <div class="auth-app-name">{{ \App\Models\AppSetting::get('app_name', config('app.name')) }}</div>
        <div class="auth-tagline">{{ \App\Models\AppSetting::get('app_tagline', 'Sistem Manajemen Stok Gudang') }}</div>
    </div>

    {{-- Body --}}
    <div class="auth-body">
        <div class="auth-title">Selamat Datang 👋</div>
        <div class="auth-subtitle">Masuk dengan username Anda untuk melanjutkan</div>

        {{-- Flash Messages --}}
        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            {{-- Username --}}
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <div class="input-wrap">
                    <i class="fas fa-user input-icon"></i>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}"
                        value="{{ old('username') }}"
                        placeholder="username Anda"
                        autofocus
                        autocomplete="username"
                    >
                </div>
                @error('username')
                    <div class="invalid-feedback"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>
                @enderror
            </div>

            {{-- Password --}}
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-wrap">
                    <i class="fas fa-lock input-icon"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                        placeholder="••••••••"
                        autocomplete="current-password"
                    >
                    <button type="button" class="input-toggle-pw" onclick="togglePassword(this)" tabindex="-1">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>
                @enderror
            </div>

            {{-- Remember --}}
            <div class="form-footer">
                <label class="remember-wrap">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Ingat saya</span>
                </label>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i>
                Masuk ke Sistem
            </button>
        </form>
    </div>
</div>

<div style="color:#334155;font-size:12px;text-align:center;margin-top:16px;">
    &copy; {{ date('Y') }} {{ \App\Models\AppSetting::get('app_name', config('app.name')) }}
</div>
@endsection
