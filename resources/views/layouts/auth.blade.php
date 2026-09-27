<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login') - {{ \App\Models\AppSetting::get('app_name', config('app.name')) }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        /* Background decoration */
        body::before {
            content: '';
            position: absolute;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(99,102,241,.25) 0%, transparent 70%);
            top: -200px; right: -100px;
            pointer-events: none;
        }
        body::after {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(16,185,129,.15) 0%, transparent 70%);
            bottom: -100px; left: -100px;
            pointer-events: none;
        }
        .auth-container {
            width: 100%;
            max-width: 420px;
            padding: 24px 16px;
            position: relative;
            z-index: 1;
        }
        .auth-card {
            background: #1e293b;
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0,0,0,.5);
        }
        .auth-header {
            padding: 32px 32px 24px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }
        .auth-logo {
            width: 60px; height: 60px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            font-size: 26px;
            color: #fff;
            box-shadow: 0 8px 24px rgba(99,102,241,.4);
        }
        .auth-logo img { width: 36px; height: 36px; object-fit: contain; }
        .auth-app-name {
            font-size: 20px;
            font-weight: 700;
            color: #f8fafc;
            margin-bottom: 4px;
        }
        .auth-tagline {
            font-size: 13px;
            color: #64748b;
        }
        .auth-body { padding: 28px 32px 32px; }
        .auth-title {
            font-size: 18px;
            font-weight: 700;
            color: #f8fafc;
            margin-bottom: 4px;
        }
        .auth-subtitle {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 24px;
        }

        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #94a3b8;
            margin-bottom: 7px;
        }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute;
            left: 12px; top: 50%;
            transform: translateY(-50%);
            color: #475569;
            font-size: 14px;
        }
        .form-control {
            width: 100%;
            padding: 11px 12px 11px 38px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 8px;
            font-size: 14px;
            color: #f1f5f9;
            font-family: inherit;
            transition: border .15s, box-shadow .15s;
        }
        .form-control::placeholder { color: #475569; }
        .form-control:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,.2);
            background: rgba(255,255,255,.07);
        }
        .form-control.is-invalid { border-color: #ef4444; }
        .invalid-feedback { font-size: 12px; color: #f87171; margin-top: 5px; }

        .input-toggle-pw {
            position: absolute;
            right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: #475569; cursor: pointer;
            padding: 2px; font-size: 14px;
        }
        .input-toggle-pw:hover { color: #94a3b8; }

        .form-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
        }
        .remember-wrap {
            display: flex; align-items: center; gap: 7px;
            font-size: 13px; color: #64748b;
        }
        .remember-wrap input[type=checkbox] {
            width: 15px; height: 15px;
            accent-color: #6366f1;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: opacity .2s, transform .15s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-login:hover { opacity: .9; transform: translateY(-1px); }
        .btn-login:active { transform: translateY(0); }

        .alert {
            display: flex; align-items: center; gap: 8px;
            padding: 11px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
        }
        .alert-danger { background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.25); color: #f87171; }
        .alert-success { background: rgba(16,185,129,.12); border: 1px solid rgba(16,185,129,.25); color: #34d399; }

        .auth-footer {
            text-align: center;
            padding: 16px;
            font-size: 12px;
            color: #475569;
            border-top: 1px solid rgba(255,255,255,.04);
        }
    </style>
</head>
<body>
<div class="auth-container">
    @yield('content')
</div>
<script>
    function togglePassword(btn) {
        const input = btn.previousElementSibling;
        const icon  = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }
</script>
</body>
</html>
