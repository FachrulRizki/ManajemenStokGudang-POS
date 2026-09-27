@extends('layouts.app')
@section('title', 'Detail User: ' . $user->name)
@push('breadcrumb_content', 'Sistem / <a href="' . route('users.index') . '" style="color:inherit;">User</a> / <strong>' . $user->name . '</strong>')

@push('styles')
<style>
.perm-tab-nav { display:flex; gap:0; border-bottom:2px solid #e2e8f0; margin-bottom:20px; }
.perm-tab-btn {
    padding:9px 18px; font-size:13px; font-weight:500; color:#64748b;
    background:none; border:none; border-bottom:2px solid transparent;
    margin-bottom:-2px; cursor:pointer; white-space:nowrap; transition:all .15s;
    text-decoration:none; display:inline-block;
}
.perm-tab-btn.active { color:var(--primary); border-bottom-color:var(--primary); }
.perm-tab-btn:hover:not(.active) { color:#374151; background:#f8fafc; }
.perm-group { margin-bottom:20px; }
.perm-group-title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; margin-bottom:10px; }
.perm-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:8px; }
.perm-item {
    display:flex; align-items:center; gap:10px; padding:10px 14px;
    border-radius:8px; border:1.5px solid #e2e8f0; background:#fff; cursor:pointer;
    transition:all .15s;
}
.perm-item:hover { border-color:var(--primary); background:var(--primary-light); }
.perm-item.is-default { border-color:#e2e8f0; background:#f8fafc; }
.perm-item.is-granted-extra { border-color:#10b981; background:#f0fdf4; }
.perm-item.is-revoked { border-color:#fecaca; background:#fef2f2; opacity:.7; }
.perm-item input[type=checkbox] { width:16px; height:16px; cursor:pointer; }
.perm-label { font-size:13px; color:#374151; flex:1; }
.perm-badge { font-size:10px; font-weight:600; padding:2px 6px; border-radius:4px; flex-shrink:0; }
.perm-badge.default { background:#e0e7ff; color:#4338ca; }
.perm-badge.extra   { background:#dcfce7; color:#16a34a; }
.perm-badge.revoked { background:#fee2e2; color:#dc2626; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ $user->name }}</div>
        <div class="page-subtitle">{{ $user->role_label }} &bull; {{ $user->email ?? $user->username }}</div>
    </div>
    <div class="btn-group">
        <a href="{{ route('users.edit', $user) }}" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
        <a href="{{ route('users.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
</div>

@php $activeTab = request('tab', 'info'); @endphp

<div class="perm-tab-nav">
    <a href="{{ route('users.show', [$user, 'tab'=>'info']) }}" class="perm-tab-btn {{ $activeTab==='info'?'active':'' }}">
        <i class="fas fa-user"></i> Informasi
    </a>
    <a href="{{ route('users.show', [$user, 'tab'=>'permissions']) }}" class="perm-tab-btn {{ $activeTab==='permissions'?'active':'' }}">
        <i class="fas fa-shield-alt"></i> Permission
        @php $overrideCount = $user->permissionOverrides()->count(); @endphp
        @if($overrideCount > 0)
            <span class="badge badge-warning" style="margin-left:6px;">{{ $overrideCount }} override</span>
        @endif
    </a>
    <a href="{{ route('users.show', [$user, 'tab'=>'activity']) }}" class="perm-tab-btn {{ $activeTab==='activity'?'active':'' }}">
        <i class="fas fa-history"></i> Aktivitas
    </a>
</div>

{{-- ===== TAB INFO ===== --}}
@if($activeTab === 'info')
<div class="grid grid-2" style="margin-bottom:20px;">
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-user-circle" style="color:var(--primary)"></i> Profil</div></div>
        <div class="card-body">
            <div style="text-align:center;margin-bottom:20px;">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin-bottom:12px;">
                <div style="font-size:16px;font-weight:700;">{{ $user->name }}</div>
                <div>
                    @php $roleColors=['admin'=>'danger','manager'=>'warning','staff'=>'info']; @endphp
                    <span class="badge badge-{{ $roleColors[$user->role]??'secondary' }}" style="font-size:12px;padding:5px 12px;">
                        <i class="fas fa-{{ $user->role=='admin'?'shield-alt':($user->role=='manager'?'user-tie':'user') }}"></i>
                        {{ $user->role_label }}
                    </span>
                </div>
            </div>
            @php $rows = [
                ['Username', '@' . $user->username],
                ['Email', $user->email ?? '-'],
                ['No. HP', $user->phone ?? '-'],
                ['Status', $user->is_active ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Nonaktif</span>'],
                ['Login Terakhir', $user->last_login_at ? $user->last_login_at->format('d M Y H:i') : 'Belum pernah'],
                ['Bergabung', $user->created_at->format('d M Y')],
            ]; @endphp
            @foreach($rows as [$label, $value])
            <div class="detail-row">
                <span class="detail-label">{{ $label }}</span>
                <span class="detail-value">{!! $value !!}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-chart-bar" style="color:#10b981"></i> Statistik</div></div>
        <div class="card-body">
            <div class="grid grid-2" style="margin-bottom:16px;">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-arrow-down"></i></div>
                    <div class="stat-content"><div class="stat-value">{{ number_format($totalStockIn) }}</div><div class="stat-label">Stok Masuk</div></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-arrow-up"></i></div>
                    <div class="stat-content"><div class="stat-value">{{ number_format($totalStockOut) }}</div><div class="stat-label">Stok Keluar</div></div>
                </div>
            </div>
            <div class="grid grid-2">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-cash-register"></i></div>
                    <div class="stat-content">
                        <div class="stat-value">{{ number_format($user->transactions()->count()) }}</div>
                        <div class="stat-label">Transaksi POS</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-user-clock"></i></div>
                    <div class="stat-content">
                        <div class="stat-value">{{ number_format($user->shifts()->count()) }}</div>
                        <div class="stat-label">Total Shift</div>
                    </div>
                </div>
            </div>
        </div>
        @if($user->id !== auth()->id())
        <div class="card-footer" style="display:flex;gap:8px;justify-content:flex-end;">
            <form method="POST" action="{{ route('users.toggle-status', $user) }}">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-secondary' : 'btn-success' }}">
                    <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
            </form>
            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirmDelete(this)">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Hapus User</button>
            </form>
        </div>
        @endif
    </div>
</div>

{{-- ===== TAB PERMISSIONS ===== --}}
@elseif($activeTab === 'permissions')

<div class="alert alert-info" style="margin-bottom:16px;">
    <i class="fas fa-info-circle"></i>
    <div>
        <strong>Cara kerja permission:</strong> Setiap role sudah punya permission default.
        Di sini Anda bisa menambah atau mencabut permission tertentu untuk user ini secara spesifik.
        <span style="display:inline-flex;gap:12px;margin-top:6px;">
            <span style="font-size:12px;"><span class="perm-badge default">Default</span> = dari role</span>
            <span style="font-size:12px;"><span class="perm-badge extra">+Extra</span> = ditambah khusus</span>
            <span style="font-size:12px;"><span class="perm-badge revoked">-Dicabut</span> = dicabut dari role</span>
        </span>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-shield-alt" style="color:var(--primary)"></i> Permission untuk {{ $user->name }}</div>
        <span style="font-size:12px;color:#64748b;">Role: <strong>{{ $user->role_label }}</strong> &bull; {{ count($effectivePerms) }} permission aktif</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('users.permissions', $user) }}">
            @csrf @method('PUT')

            @php
            $groupLabels = [
                'dashboard'   => 'Dashboard',
                'master_data' => 'Master Data',
                'stok'        => 'Manajemen Stok',
                'pos'         => 'Kasir POS',
                'laporan'     => 'Laporan',
                'sistem'      => 'Sistem & User',
            ];
            $overridesMap = $overrides->mapWithKeys(fn($p) => [$p->name => $p->pivot->granted]);
            @endphp

            @foreach($groupLabels as $groupKey => $groupLabel)
            @php $groupPerms = $allPermissions->get($groupKey, collect()); @endphp
            @if($groupPerms->count())
            <div class="perm-group">
                <div class="perm-group-title"><i class="fas fa-layer-group"></i> {{ $groupLabel }}</div>
                <div class="perm-grid">
                    @foreach($groupPerms as $perm)
                    @php
                    $isDefault = in_array($perm->name, $defaultPerms);
                    $isEffective = in_array($perm->name, $effectivePerms);
                    $overrideValue = $overridesMap->get($perm->name);
                    $isExtra   = $overrideValue === true && !$isDefault;
                    $isRevoked = $overrideValue === false;
                    $itemClass = $isRevoked ? 'is-revoked' : ($isExtra ? 'is-granted-extra' : ($isDefault ? 'is-default' : ''));
                    @endphp
                    <label class="perm-item {{ $itemClass }}" for="perm_{{ $perm->name }}">
                        <input type="checkbox" id="perm_{{ $perm->name }}"
                            name="permissions[{{ $perm->name }}]" value="1"
                            {{ $isEffective ? 'checked' : '' }}
                            onchange="markChanged(this)">
                        <span class="perm-label">{{ $perm->label }}</span>
                        @if($isExtra)<span class="perm-badge extra">+Extra</span>
                        @elseif($isRevoked)<span class="perm-badge revoked">-Dicabut</span>
                        @elseif($isDefault)<span class="perm-badge default">Default</span>
                        @endif
                    </label>
                    @endforeach
                </div>
            </div>
            @endif
            @endforeach

            <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:16px;border-top:1px solid #f1f5f9;margin-top:8px;">
                <a href="{{ route('users.show', [$user, 'tab'=>'permissions']) }}" class="btn btn-secondary">Reset Tampilan</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Permission</button>
            </div>
        </form>
    </div>
</div>

{{-- ===== TAB AKTIVITAS ===== --}}
@elseif($activeTab === 'activity')
<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-history" style="color:var(--primary)"></i> Log Aktivitas Terbaru</div></div>
    <div class="table-wrapper">
        <table>
            <thead><tr><th>Aksi</th><th>Modul</th><th>Keterangan</th><th>IP</th><th>Waktu</th></tr></thead>
            <tbody>
                @forelse($recentActivity as $log)
                <tr>
                    <td>
                        @php $actionColors=['create'=>'success','update'=>'warning','delete'=>'danger','login'=>'info','logout'=>'secondary','export'=>'primary']; @endphp
                        <span class="badge badge-{{ $actionColors[$log->action]??'secondary' }}">{{ ucfirst($log->action) }}</span>
                    </td>
                    <td style="font-size:12px;color:#64748b;">{{ $log->module }}</td>
                    <td style="font-size:13px;">{{ $log->description }}</td>
                    <td style="font-size:11px;color:#94a3b8;">{{ $log->ip_address }}</td>
                    <td style="font-size:12px;color:#64748b;white-space:nowrap;">{{ $log->created_at->format('d M Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="5"><div class="empty-state" style="padding:24px;"><i class="fas fa-history"></i><p>Belum ada aktivitas.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function markChanged(checkbox) {
    const label = checkbox.closest('.perm-item');
    label.classList.remove('is-default', 'is-granted-extra', 'is-revoked');
    if (checkbox.checked) {
        label.style.borderColor = '#6366f1';
        label.style.background  = '#e0e7ff';
    } else {
        label.style.borderColor = '#fecaca';
        label.style.background  = '#fef2f2';
    }
}
</script>
@endpush
