<!DOCTYPE html>
<html lang="id" data-theme="{{ \App\Models\AppSetting::get('app_theme', 'indigo') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ \App\Models\AppSetting::get('app_name', config('app.name')) }}</title>

    {{-- Favicon --}}
    @if(\App\Models\AppSetting::get('app_favicon'))
        <link rel="icon" href="{{ asset('storage/' . \App\Models\AppSetting::get('app_favicon')) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        /* --- CSS Variables per tema ------------------------- */
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #e0e7ff;
            --sidebar-w: 260px;
            --header-h: 64px;
            --radius: 12px;
            --shadow: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.06);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -1px rgba(0,0,0,.06);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,.1), 0 4px 6px -2px rgba(0,0,0,.05);
        }
        [data-theme="blue"]   { --primary:#3b82f6; --primary-dark:#2563eb; --primary-light:#dbeafe; }
        [data-theme="indigo"] { --primary:#6366f1; --primary-dark:#4f46e5; --primary-light:#e0e7ff; }
        [data-theme="green"]  { --primary:#10b981; --primary-dark:#059669; --primary-light:#d1fae5; }
        [data-theme="red"]    { --primary:#ef4444; --primary-dark:#dc2626; --primary-light:#fee2e2; }
        [data-theme="orange"] { --primary:#f97316; --primary-dark:#ea580c; --primary-light:#ffedd5; }
        [data-theme="purple"] { --primary:#8b5cf6; --primary-dark:#7c3aed; --primary-light:#ede9fe; }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* --- Sidebar ---------------------------------------- */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-w);
            height: 100vh;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: transform .25s ease;
        }
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 20px 20px 16px;
            border-bottom: 1px solid #f1f5f9;
            text-decoration: none;
        }
        .sidebar-brand-icon {
            width: 36px; height: 36px;
            background: var(--primary);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .sidebar-brand-icon img { width: 24px; height: 24px; object-fit: contain; }
        .sidebar-brand-text { overflow: hidden; }
        .sidebar-brand-name {
            color: #0f172a;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-brand-tagline {
            color: #94a3b8;
            font-size: 10px;
            margin-top: 1px;
        }

        .sidebar-scroll { flex: 1; overflow-y: auto; padding: 12px 0; }
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 2px; }

        .nav-section-label {
            color: #94a3b8;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .08em;
            padding: 8px 20px 4px;
        }
        .nav-item { display: block; text-decoration: none; }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 20px;
            color: #64748b;
            font-size: 13.5px;
            font-weight: 500;
            border-radius: 0;
            transition: all .15s;
            position: relative;
        }
        .nav-link:hover { color: #0f172a; background: #f8fafc; }
        .nav-link.active {
            color: var(--primary-dark);
            background: var(--primary-light);
        }
        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0; top: 6px; bottom: 6px;
            width: 3px;
            background: var(--primary);
            border-radius: 0 4px 4px 0;
        }
        .nav-link .nav-icon {
            width: 18px;
            text-align: center;
            font-size: 14px;
            flex-shrink: 0;
        }
        .nav-badge {
            margin-left: auto;
            background: var(--primary);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 99px;
        }

        /* submenu */
        .nav-submenu { display: none; }
        .nav-submenu.show { display: block; }
        .nav-submenu .nav-link {
            padding-left: 48px;
            font-size: 13px;
            color: #94a3b8;
        }
        .nav-submenu .nav-link:hover { color: #0f172a; background: #f8fafc; }
        .nav-submenu .nav-link.active { color: var(--primary-dark); background: var(--primary-light); }
        .nav-parent { cursor: pointer; }
        .nav-parent .nav-arrow {
            margin-left: auto;
            transition: transform .2s;
            font-size: 11px;
            color: #cbd5e1;
        }
        .nav-parent.open .nav-arrow { transform: rotate(90deg); }

        .sidebar-footer {
            padding: 12px 16px;
            border-top: 1px solid #f1f5f9;
        }
        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px;
            border-radius: 8px;
            text-decoration: none;
            transition: background .15s;
        }
        .sidebar-user:hover { background: #f8fafc; }
        .sidebar-user-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }
        .sidebar-user-info { overflow: hidden; flex: 1; }
        .sidebar-user-name {
            color: #0f172a;
            font-size: 12.5px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-user-role {
            color: #94a3b8;
            font-size: 10.5px;
        }

        /* --- Top Header ------------------------------------- */
        .header {
            position: fixed;
            top: 0;
            left: var(--sidebar-w);
            right: 0;
            height: var(--header-h);
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 12px;
            z-index: 900;
            transition: left .25s ease;
        }
        .header-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            color: #475569;
            font-size: 18px;
        }
        .header-breadcrumb {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #64748b;
        }
        .header-breadcrumb strong { color: #1e293b; font-weight: 600; }
        .header-breadcrumb .separator { color: #cbd5e1; }

        .header-actions { display: flex; align-items: center; gap: 8px; }
        .header-btn {
            position: relative;
            background: none;
            border: none;
            cursor: pointer;
            width: 36px; height: 36px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: #64748b;
            font-size: 16px;
            transition: background .15s;
        }
        .header-btn:hover { background: #f1f5f9; color: #1e293b; }
        .header-btn .badge {
            position: absolute;
            top: 4px; right: 4px;
            width: 8px; height: 8px;
            background: #ef4444;
            border-radius: 50%;
        }
        .header-divider { width: 1px; height: 24px; background: #e2e8f0; }

        .header-user-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 8px;
            transition: background .15s;
            text-decoration: none;
        }
        .header-user-btn:hover { background: #f1f5f9; }
        .header-user-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }
        .header-user-name {
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
            max-width: 120px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Dropdown */
        .dropdown { position: relative; }
        .dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            min-width: 180px;
            z-index: 9999;
            display: none;
            overflow: hidden;
        }
        .dropdown-menu.show { display: block; }
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 14px;
            font-size: 13px;
            color: #374151;
            text-decoration: none;
            transition: background .12s;
        }
        .dropdown-item:hover { background: #f8fafc; }
        .dropdown-item.danger { color: #ef4444; }
        .dropdown-item.danger:hover { background: #fef2f2; }
        .dropdown-divider { height: 1px; background: #f1f5f9; margin: 4px 0; }

        /* --- Main Content ----------------------------------- */
        .main-wrapper {
            margin-left: var(--sidebar-w);
            margin-top: var(--header-h);
            min-height: calc(100vh - var(--header-h));
            padding: 24px;
            transition: margin-left .25s ease;
        }

        /* --- Page Header ------------------------------------ */
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 24px;
            gap: 16px;
            flex-wrap: wrap;
        }
        .page-title {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }
        .page-subtitle {
            font-size: 13px;
            color: #64748b;
            margin-top: 3px;
        }

        /* --- Cards ------------------------------------------ */
        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius);
            overflow: hidden;
        }
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
        }
        .card-title {
            font-size: 14px;
            font-weight: 600;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-body { padding: 20px; }
        .card-footer {
            padding: 12px 20px;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
        }

        /* --- Stat Cards ------------------------------------- */
        .stat-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius);
            padding: 20px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            transition: box-shadow .2s, transform .2s;
        }
        .stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-1px); }
        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .stat-content { flex: 1; min-width: 0; }
        .stat-value {
            font-size: 26px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1;
        }
        .stat-label {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
            font-weight: 500;
        }
        .stat-change {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            font-size: 11px;
            font-weight: 600;
            margin-top: 6px;
            padding: 2px 6px;
            border-radius: 99px;
        }
        .stat-change.up { background: #dcfce7; color: #16a34a; }
        .stat-change.down { background: #fee2e2; color: #dc2626; }

        /* --- Tables ----------------------------------------- */
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: #f8fafc;
            color: #475569;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            padding: 10px 16px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        tbody tr { border-bottom: 1px solid #f1f5f9; transition: background .1s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #f8fafc; }
        tbody td { padding: 12px 16px; font-size: 13.5px; color: #374151; }

        /* --- Badges ----------------------------------------- */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-success { background: #dcfce7; color: #16a34a; }
        .badge-danger  { background: #fee2e2; color: #dc2626; }
        .badge-warning { background: #fef3c7; color: #d97706; }
        .badge-info    { background: #dbeafe; color: #2563eb; }
        .badge-secondary { background: #f1f5f9; color: #64748b; }
        .badge-primary { background: var(--primary-light); color: var(--primary-dark); }

        /* --- Buttons ---------------------------------------- */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 500;
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
            transition: all .15s;
            white-space: nowrap;
        }
        .btn:disabled { opacity: .55; cursor: not-allowed; }
        .btn-primary { background: var(--primary); color: #fff; border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); }
        .btn-secondary { background: #f1f5f9; color: #374151; border-color: #e2e8f0; }
        .btn-secondary:hover { background: #e2e8f0; }
        .btn-success { background: #10b981; color: #fff; border-color: #10b981; }
        .btn-success:hover { background: #059669; }
        .btn-danger { background: #ef4444; color: #fff; border-color: #ef4444; }
        .btn-danger:hover { background: #dc2626; }
        .btn-warning { background: #f59e0b; color: #fff; border-color: #f59e0b; }
        .btn-warning:hover { background: #d97706; }
        .btn-outline { background: transparent; color: var(--primary); border-color: var(--primary); }
        .btn-outline:hover { background: var(--primary-light); }
        .btn-sm { padding: 5px 10px; font-size: 12px; gap: 5px; }
        .btn-icon { padding: 7px; }
        .btn-group { display: flex; gap: 6px; flex-wrap: wrap; }

        /* --- Forms ------------------------------------------ */
        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }
        .form-label .required { color: #ef4444; margin-left: 2px; }
        .form-control {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 13.5px;
            color: #1e293b;
            background: #fff;
            transition: border .15s, box-shadow .15s;
            font-family: inherit;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99,102,241,.12);
        }
        .form-control.is-invalid { border-color: #ef4444; }
        .form-control.is-invalid:focus { box-shadow: 0 0 0 3px rgba(239,68,68,.12); }
        .invalid-feedback { font-size: 12px; color: #ef4444; margin-top: 4px; }
        .form-hint { font-size: 11.5px; color: #94a3b8; margin-top: 4px; }
        select.form-control { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%236b7280'%3E%3Cpath fill-rule='evenodd' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; background-size: 16px; padding-right: 36px; }
        textarea.form-control { resize: vertical; min-height: 80px; }
        .form-row { display: grid; gap: 16px; }
        .form-row.cols-2 { grid-template-columns: repeat(2, 1fr); }
        .form-row.cols-3 { grid-template-columns: repeat(3, 1fr); }

        /* Toggle switch */
        .toggle-wrap { display: flex; align-items: center; gap: 10px; }
        .toggle { position: relative; width: 40px; height: 22px; flex-shrink: 0; }
        .toggle input { opacity: 0; width: 0; height: 0; }
        .toggle-slider {
            position: absolute; inset: 0;
            background: #d1d5db;
            border-radius: 99px;
            cursor: pointer;
            transition: .2s;
        }
        .toggle-slider::before {
            content: '';
            position: absolute;
            width: 16px; height: 16px;
            left: 3px; top: 3px;
            background: #fff;
            border-radius: 50%;
            transition: .2s;
        }
        .toggle input:checked + .toggle-slider { background: var(--primary); }
        .toggle input:checked + .toggle-slider::before { transform: translateX(18px); }

        /* --- Alerts ----------------------------------------- */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13.5px;
            margin-bottom: 16px;
        }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
        .alert-danger  { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }
        .alert-warning { background: #fffbeb; border: 1px solid #fde68a; color: #b45309; }
        .alert-info    { background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; }
        .alert-close {
            margin-left: auto;
            background: none;
            border: none;
            cursor: pointer;
            opacity: .6;
            font-size: 14px;
            padding: 0 2px;
        }
        .alert-close:hover { opacity: 1; }

        /* --- Search / Filter bar ---------------------------- */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .filter-bar .form-control { width: auto; min-width: 180px; }
        .search-input {
            position: relative;
            flex: 1;
            min-width: 200px;
            max-width: 320px;
        }
        .search-input i {
            position: absolute;
            left: 11px; top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 13px;
        }
        .search-input input { padding-left: 34px; }

        /* --- Pagination ------------------------------------- */
        .pagination-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-top: 1px solid #f1f5f9;
            font-size: 13px;
            color: #64748b;
            flex-wrap: wrap;
            gap: 8px;
        }
        .pagination { display: flex; gap: 4px; }
        .pagination a, .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px; height: 32px;
            padding: 0 8px;
            border-radius: 6px;
            font-size: 13px;
            border: 1px solid #e2e8f0;
            text-decoration: none;
            color: #374151;
            transition: all .12s;
        }
        .pagination a:hover { background: var(--primary-light); border-color: var(--primary); color: var(--primary-dark); }
        .pagination span.active { background: var(--primary); border-color: var(--primary); color: #fff; font-weight: 600; }
        .pagination span.disabled { color: #d1d5db; cursor: not-allowed; }

        /* --- Empty state ------------------------------------ */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }
        .empty-state i { font-size: 48px; margin-bottom: 12px; opacity: .4; }
        .empty-state p { font-size: 14px; }

        /* --- Overlay (mobile) ------------------------------- */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 999;
        }
        .sidebar-overlay.show { display: block; }

        /* --- Scrollbar -------------------------------------- */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }

        /* --- Modal ------------------------------------------ */
        .modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15,23,42,.45);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-backdrop.show { display: flex; }
        .modal-box {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            animation: modalIn .18s ease;
        }
        @keyframes modalIn {
            from { opacity: 0; transform: translateY(-12px) scale(.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            flex-shrink: 0;
        }
        .modal-title {
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .modal-close {
            background: none;
            border: none;
            cursor: pointer;
            width: 30px; height: 30px;
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            color: #94a3b8;
            font-size: 14px;
            transition: all .12s;
        }
        .modal-close:hover { background: #f1f5f9; color: #374151; }
        .modal-body { padding: 20px; overflow-y: auto; flex: 1; }
        .modal-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            padding: 14px 20px;
            border-top: 1px solid #f1f5f9;
            flex-shrink: 0;
        }
        /* Detail rows inside modal */
        .detail-row {
            display: flex;
            gap: 16px;
            padding: 10px 0;
            border-bottom: 1px solid #f8fafc;
            font-size: 13px;
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { width: 160px; color: #64748b; flex-shrink: 0; }
        .detail-value { color: #1e293b; font-weight: 500; flex: 1; }

        /* --- Toast Notification -------------------------- */
        #toast-container {
            position: fixed; bottom: 24px; right: 24px;
            z-index: 9999; display: flex; flex-direction: column; gap: 10px;
            max-width: 360px;
        }
        .toast {
            display: flex; align-items: flex-start; gap: 12px;
            background: #fff; border-radius: 12px;
            box-shadow: 0 8px 32px rgba(15,23,42,.18), 0 2px 8px rgba(15,23,42,.08);
            padding: 14px 16px; border-left: 4px solid #6366f1;
            animation: toastIn .25s ease; min-width: 280px;
        }
        .toast.success { border-color: #10b981; }
        .toast.error   { border-color: #ef4444; }
        .toast.warning { border-color: #f59e0b; }
        .toast.info    { border-color: #3b82f6; }
        .toast-icon { font-size: 18px; flex-shrink: 0; margin-top: 1px; }
        .toast.success .toast-icon { color: #10b981; }
        .toast.error   .toast-icon { color: #ef4444; }
        .toast.warning .toast-icon { color: #f59e0b; }
        .toast.info    .toast-icon { color: #3b82f6; }
        .toast-body { flex: 1; }
        .toast-title { font-size: 13px; font-weight: 700; color: #0f172a; }
        .toast-msg   { font-size: 12.5px; color: #64748b; margin-top: 2px; line-height: 1.4; }
        .toast-close { background: none; border: none; cursor: pointer; color: #94a3b8; font-size: 14px; padding: 0; flex-shrink: 0; }
        .toast-close:hover { color: #374151; }
        @keyframes toastIn  { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:translateX(0); } }
        @keyframes toastOut { from { opacity:1; transform:translateX(0);    } to { opacity:0; transform:translateX(20px); } }

        /* --- Confirm Dialog ------------------------------- */
        #confirmDialog {
            position: fixed; inset: 0; background: rgba(15,23,42,.5);
            z-index: 9998; display: none; align-items: center; justify-content: center;
        }
        #confirmDialog.show { display: flex; }
        #confirmDialog .confirm-box {
            background: #fff; border-radius: 16px;
            box-shadow: 0 20px 60px rgba(15,23,42,.2);
            padding: 28px 28px 20px; max-width: 400px; width: 100%;
            text-align: center; animation: modalIn .2s ease;
        }
        .confirm-icon-wrap {
            width: 60px; height: 60px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px; font-size: 26px;
        }
        .confirm-icon-wrap.danger  { background: #fee2e2; color: #dc2626; }
        .confirm-icon-wrap.warning { background: #fef3c7; color: #d97706; }
        .confirm-title { font-size: 17px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
        .confirm-msg   { font-size: 13.5px; color: #64748b; margin-bottom: 24px; line-height: 1.5; }
        .confirm-btns  { display: flex; gap: 10px; justify-content: center; }

        /* --- Utility ---------------------------------------- */
        .text-muted { color: #94a3b8; }
        .text-sm { font-size: 12px; }
        .fw-600 { font-weight: 600; }
        .d-flex { display: flex; }
        .align-center { align-items: center; }
        .gap-8 { gap: 8px; }
        .gap-4 { gap: 4px; }
        .ms-auto { margin-left: auto; }
        .mt-4 { margin-top: 4px; }
        .mb-16 { margin-bottom: 16px; }
        .grid { display: grid; }
        .grid-2 { grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .grid-3 { grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .grid-4 { grid-template-columns: repeat(4, 1fr); gap: 20px; }

        /* --- Responsive ------------------------------------- */
        @media (max-width: 1024px) {
            .grid-4 { grid-template-columns: repeat(2, 1fr); }
            .grid-3 { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            /* Sidebar */
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .header { left: 0; }
            .header-toggle { display: flex; }
            .main-wrapper { margin-left: 0; padding: 12px; }

            /* Grid */
            .grid-4, .grid-3, .grid-2 { grid-template-columns: 1fr; }
            .form-row.cols-2, .form-row.cols-3 { grid-template-columns: 1fr; }

            /* Page header */
            .page-header { flex-direction: column; align-items: flex-start; gap: 10px; }
            .page-header .btn-group, .page-header .btn { width: 100%; justify-content: center; }

            /* Modal — kritis di mobile */
            .modal-backdrop { padding: 8px; align-items: flex-end; }
            .modal-box {
                max-height: 95vh;
                max-width: 100% !important;
                border-radius: 16px 16px 0 0;
                margin-bottom: 0;
            }
            .modal-body { padding: 14px; }
            .modal-header { padding: 12px 14px; }
            .modal-footer { padding: 10px 14px; }

            /* Filter bar — stack ke bawah */
            .filter-bar { flex-direction: column; align-items: stretch; gap: 8px; }
            .filter-bar .form-control,
            .filter-bar .search-input { width: 100% !important; min-width: 0 !important; max-width: 100% !important; }
            .filter-bar .btn { width: 100%; justify-content: center; }
            .search-input { max-width: 100%; }

            /* Stat cards stack */
            .stat-card { flex-direction: row; }

            /* Table scroll hint */
            .table-wrapper { -webkit-overflow-scrolling: touch; }
            table { min-width: 500px; }

            /* Tabs */
            .master-tab-nav, .stok-tab-nav, .report-tab-nav, .perm-tab-nav { overflow-x: auto; -webkit-overflow-scrolling: touch; }

            /* POS kasir layout */
            .pos-wrap { grid-template-columns: 1fr; height: auto; }
            .pos-right { min-height: 400px; }
            .product-grid { grid-template-columns: repeat(2, 1fr); }

            /* Breadcrumb */
            .header-breadcrumb { font-size: 11px; display: none; }

            /* Btn group */
            .btn-group { flex-wrap: wrap; }

            /* Header user name */
            .header-user-name { display: none; }

            /* Pagination */
            .pagination-wrap { flex-direction: column; gap: 8px; text-align: center; }
        }

        @media (max-width: 480px) {
            /* Extra small */
            .modal-backdrop { padding: 0; }
            .modal-box { border-radius: 12px 12px 0 0; max-height: 98vh; }
            .product-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
            .product-card { padding: 8px; }
            .product-card-name { font-size: 11.5px; }
            .stat-value { font-size: 20px !important; }
            .page-title { font-size: 18px; }
            tbody td { padding: 8px 10px; font-size: 12px; }
            thead th { padding: 7px 10px; }
        }

        /* Selalu scrollable di dalam modal */
        .modal-body { overflow-y: auto; -webkit-overflow-scrolling: touch; }
    </style>
    @stack('styles')
</head>
<body>

{{-- Sidebar Overlay (mobile) --}}
<div class="sidebar-overlay" id="sidebarOverlay"></div>

{{-- -- SIDEBAR -------------------------------------------- --}}
<aside class="sidebar" id="sidebar">
    {{-- Brand --}}
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <div class="sidebar-brand-icon">
            @if(\App\Models\AppSetting::get('app_logo'))
                <img src="{{ asset('storage/' . \App\Models\AppSetting::get('app_logo')) }}" alt="Logo">
            @else
                <i class="fas fa-warehouse" style="color:#fff;font-size:16px;"></i>
            @endif
        </div>
        <div class="sidebar-brand-text">
            <div class="sidebar-brand-name">{{ \App\Models\AppSetting::get('app_name', config('app.name')) }}</div>
            <div class="sidebar-brand-tagline">{{ \App\Models\AppSetting::get('app_tagline', 'Sistem Manajemen Gudang') }}</div>
        </div>
    </a>

    <div class="sidebar-scroll">
        {{-- Main --}}
        <div class="nav-section-label">Utama</div>
        <a href="{{ route('dashboard') }}" class="nav-item nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-th-large"></i></span> Dashboard
        </a>

        {{-- Master Data --}}
        <div class="nav-section-label">Master Data</div>

        <a href="{{ route('products.index') }}" class="nav-item nav-link {{ request()->routeIs('products.*') || request()->routeIs('categories.*') || request()->routeIs('units.*') || request()->routeIs('suppliers.*') || request()->routeIs('warehouses.*') || request()->routeIs('racks.*') || request()->routeIs('stock-out-types.*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-boxes"></i></span> Produk
        </a>

        <a href="{{ route('stok.index') }}" class="nav-item nav-link {{ request()->routeIs('stok.*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-layer-group"></i></span> Stok
        </a>

        <a href="{{ route('payment-methods.index') }}" class="nav-item nav-link {{ request()->routeIs('payment-methods.*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-wallet"></i></span> Metode Pembayaran
        </a>

        {{-- Transaksi --}}
        <div class="nav-section-label">Transaksi</div>
        <a href="{{ route('pos.kasir') }}" class="nav-item nav-link {{ request()->routeIs('pos.kasir') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-cash-register" style="color:#6366f1;"></i></span> Kasir POS
        </a>
        <a href="{{ route('pos.history') }}" class="nav-item nav-link {{ request()->routeIs('pos.history') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-receipt"></i></span> Riwayat Transaksi
        </a>
        <a href="{{ route('shifts.index') }}" class="nav-item nav-link {{ request()->routeIs('shifts.*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-user-clock"></i></span> Shift Kasir
        </a>

        {{-- Laporan --}}
        <div class="nav-section-label">Laporan</div>
        <a href="{{ route('reports.stock') }}" class="nav-item nav-link {{ request()->routeIs('reports.stock') || request()->routeIs('reports.stock-in') || request()->routeIs('reports.stock-out') || request()->routeIs('reports.top-products') || request()->routeIs('reports.stock-by-rack') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-chart-bar"></i></span> Laporan Stok
        </a>
        <a href="{{ route('reports.kasir') }}" class="nav-item nav-link {{ request()->routeIs('reports.kasir') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-users"></i></span> Laporan Kasir
        </a>

        {{-- Sistem --}}
        <div class="nav-section-label">Sistem</div>
        @if(auth()->user()->isAdmin() || auth()->user()->isManager())
        <a href="{{ route('users.index') }}" class="nav-item nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-users-cog"></i></span> Manajemen User
        </a>
        @endif
        <a href="{{ route('activity-logs.index') }}" class="nav-item nav-link {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-history"></i></span> Log Aktivitas
        </a>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('settings.index') }}" class="nav-item nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-cog"></i></span> Pengaturan
        </a>
        @endif
    </div>

    {{-- User footer --}}
    <div class="sidebar-footer">
        <div class="sidebar-user" style="cursor:default;">
            <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="sidebar-user-avatar">
            <div class="sidebar-user-info">
                <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                <div class="sidebar-user-role">{{ auth()->user()->role_label }}</div>
            </div>
        </div>
        <div style="display:flex;gap:6px;padding:0 8px 4px;">
            <a href="{{ route('profile') }}" style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:7px 0;border-radius:7px;font-size:12px;font-weight:500;color:#64748b;text-decoration:none;background:#f8fafc;border:1px solid #e2e8f0;transition:all .15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                <i class="fas fa-user-circle"></i> Profil
            </a>
            <form method="POST" action="{{ route('logout') }}" style="flex:1;">
                @csrf
                <button type="submit" style="width:100%;display:flex;align-items:center;justify-content:center;gap:6px;padding:7px 0;border-radius:7px;font-size:12px;font-weight:500;color:#ef4444;background:#fef2f2;border:1px solid #fecaca;cursor:pointer;transition:all .15s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- -- HEADER --------------------------------------------- --}}
<header class="header">
    <button class="header-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <div class="header-breadcrumb">
    <div class="header-breadcrumb">
        <i class="fas fa-home" style="font-size:12px;"></i>
        <span class="separator">/</span>
        @stack('breadcrumb_content')
    </div>

    <div class="header-actions">
        {{-- Low stock alert --}}
        @php $lowCount = \App\Models\Product::where('is_active',true)->whereColumn('stock','<=','min_stock')->count(); @endphp
        @if($lowCount > 0)
        <a href="{{ route('products.index', ['status'=>'low']) }}" class="header-btn" title="{{ $lowCount }} produk stok menipis">
            <i class="fas fa-bell"></i>
            <span class="badge"></span>
        </a>
        @endif

        <div class="header-divider"></div>

        {{-- User dropdown --}}
        <div class="dropdown">
            <button class="header-user-btn" onclick="toggleDropdown('userDrop')" type="button">
                <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="header-user-avatar">
                <span class="header-user-name">{{ auth()->user()->name }}</span>
                <i class="fas fa-chevron-down" style="font-size:10px;color:#94a3b8;"></i>
            </button>
            <div class="dropdown-menu" id="userDrop">
                <a href="{{ route('profile') }}" class="dropdown-item">
                    <i class="fas fa-user-circle"></i> Profil Saya
                </a>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('settings.index') }}" class="dropdown-item">
                    <i class="fas fa-cog"></i> Pengaturan
                </a>
                @endif
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item danger" style="width:100%;text-align:left;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

{{-- -- MAIN ----------------------------------------------- --}}
<main class="main-wrapper">
    {{-- Flash messages via toast (handled by JS above) --}}
    @yield('content')
</main>

{{-- Toast Container --}}
<div id="toast-container"></div>

{{-- Confirm Dialog --}}
<div id="confirmDialog">
    <div class="confirm-box">
        <div class="confirm-icon-wrap danger" id="confirmIconWrap"><i class="fas fa-trash-alt" id="confirmIcon"></i></div>
        <div class="confirm-title" id="confirmTitle">Hapus Data?</div>
        <div class="confirm-msg" id="confirmMsg">Tindakan ini tidak dapat dibatalkan.</div>
        <div class="confirm-btns">
            <button type="button" class="btn btn-secondary" id="confirmCancel">Batal</button>
            <button type="button" class="btn btn-danger" id="confirmOk"><i class="fas fa-trash"></i> Ya, Hapus</button>
        </div>
    </div>
</div>

<script>
    // -- Toast Notification -----------------------------
    const toastIcons = { success:'fas fa-check-circle', error:'fas fa-times-circle', warning:'fas fa-exclamation-triangle', info:'fas fa-info-circle' };
    const toastTitles = { success:'Berhasil', error:'Terjadi Kesalahan', warning:'Perhatian', info:'Informasi' };

    function showToast(type, message, title = null, duration = 4000) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <i class="${toastIcons[type] || toastIcons.info} toast-icon"></i>
            <div class="toast-body">
                <div class="toast-title">${title || toastTitles[type] || 'Notifikasi'}</div>
                <div class="toast-msg">${message}</div>
            </div>
            <button class="toast-close" onclick="removeToast(this.parentElement)"><i class="fas fa-times"></i></button>`;
        container.appendChild(toast);
        setTimeout(() => removeToast(toast), duration);
    }

    function removeToast(toast) {
        if (!toast || !toast.parentElement) return;
        toast.style.animation = 'toastOut .25s ease forwards';
        setTimeout(() => toast.remove(), 250);
    }

    // Auto show toast dari session flash
    @if(session('success'))
        document.addEventListener('DOMContentLoaded', () => showToast('success', {!! json_encode(session('success')) !!}));
    @endif
    @if(session('error'))
        document.addEventListener('DOMContentLoaded', () => showToast('error', {!! json_encode(session('error')) !!}));
    @endif
    @if(session('warning'))
        document.addEventListener('DOMContentLoaded', () => showToast('warning', {!! json_encode(session('warning')) !!}));
    @endif

    // -- Confirm Dialog ---------------------------------
    let _confirmCallback = null;

    function confirmDelete(formOrCallback, options = {}) {
        const title   = options.title   || 'Hapus Data?';
        const message = options.message || 'Data yang dihapus tidak dapat dikembalikan.';
        const btnText = options.btnText || 'Ya, Hapus';
        const btnClass= options.btnClass|| 'btn-danger';
        const iconClass=options.iconClass||'fas fa-trash-alt';
        const iconType= options.iconType || 'danger';

        document.getElementById('confirmTitle').textContent   = title;
        document.getElementById('confirmMsg').textContent     = message;
        document.getElementById('confirmOk').className        = `btn ${btnClass}`;
        document.getElementById('confirmOk').innerHTML        = `<i class="${iconClass}"></i> ${btnText}`;
        document.getElementById('confirmIconWrap').className  = `confirm-icon-wrap ${iconType}`;
        document.getElementById('confirmIcon').className      = iconClass;

        document.getElementById('confirmDialog').classList.add('show');

        _confirmCallback = () => {
            document.getElementById('confirmDialog').classList.remove('show');
            if (typeof formOrCallback === 'function') {
                formOrCallback();
            } else if (formOrCallback && formOrCallback.submit) {
                formOrCallback.submit();
            }
        };
        return false; // prevent default form submit
    }

    document.getElementById('confirmOk').addEventListener('click', () => { if (_confirmCallback) _confirmCallback(); });
    document.getElementById('confirmCancel').addEventListener('click', () => { document.getElementById('confirmDialog').classList.remove('show'); });
    document.getElementById('confirmDialog').addEventListener('click', e => { if (e.target === e.currentTarget) e.currentTarget.classList.remove('show'); });

    // -- Modal -----------------------------------------
    function openModal(id) {
        const m = document.getElementById(id);
        if (m) {
            m.classList.add('show');
            document.body.style.overflow = 'hidden';
            // focus first input
            setTimeout(() => {
                const inp = m.querySelector('input:not([type=hidden]):not([type=checkbox])');
                if (inp) inp.focus();
            }, 50);
        }
    }
    function closeModal(id) {
        const m = document.getElementById(id);
        if (m) {
            m.classList.remove('show');
            document.body.style.overflow = '';
        }
    }
    // close modal on backdrop click
    document.addEventListener('click', e => {
        if (e.target.classList.contains('modal-backdrop')) {
            e.target.classList.remove('show');
            document.body.style.overflow = '';
        }
    });
    // close modal on ESC
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.show').forEach(m => {
                m.classList.remove('show');
                document.body.style.overflow = '';
            });
        }
    });

    // -- Sidebar toggle ---------------------------------
    function toggleSidebar() {
        const sidebar  = document.getElementById('sidebar');
        const overlay  = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('open');
        overlay.classList.toggle('show');
    }
    document.getElementById('sidebarOverlay').addEventListener('click', toggleSidebar);

    // -- Nav submenu -----------------------------------
    function toggleNav(el) {
        const submenu = el.nextElementSibling;
        if (!submenu || !submenu.classList.contains('nav-submenu')) return;
        el.classList.toggle('open');
        submenu.classList.toggle('show');
    }

    // -- Dropdown --------------------------------------
    function toggleDropdown(id) {
        document.querySelectorAll('.dropdown-menu').forEach(m => {
            if (m.id !== id) m.classList.remove('show');
        });
        document.getElementById(id).classList.toggle('show');
    }
    document.addEventListener('click', e => {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('show'));
        }
    });

    // -- Auto-close flash (legacy support) -------------
    const flash = document.getElementById('flashAlert');
    if (flash) setTimeout(() => flash.remove(), 4000);
</script>

@stack('scripts')
</body>
</html>
