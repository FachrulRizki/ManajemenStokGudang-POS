@extends('layouts.app')
@section('title', 'Log Aktivitas')
@push('breadcrumb_content', 'Sistem / <strong>Log Aktivitas</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Log Aktivitas</div>
        <div class="page-subtitle">Rekam jejak seluruh kegiatan CRUD pengguna sistem</div>
    </div>
    @if(auth()->user()->isAdmin())
    <div class="dropdown">
        <button class="btn btn-secondary" onclick="toggleDropdown('clearDrop')">
            <i class="fas fa-trash-alt"></i> Hapus Log Lama <i class="fas fa-chevron-down" style="font-size:10px;"></i>
        </button>
        <div class="dropdown-menu" id="clearDrop" style="right:0;min-width:200px;">
            <form method="POST" action="{{ route('activity-logs.clear') }}">
                @csrf
                <div style="padding:10px 14px;">
                    <label style="font-size:12px;color:#64748b;display:block;margin-bottom:6px;">Hapus log lebih dari:</label>
                    <select name="days" class="form-control" style="margin-bottom:8px;">
                        <option value="30">30 hari</option>
                        <option value="60">60 hari</option>
                        <option value="90" selected>90 hari</option>
                        <option value="180">180 hari</option>
                    </select>
                    <button type="submit" class="btn btn-danger btn-sm" style="width:100%;justify-content:center;"
                        onclick="return confirm('Yakin hapus log lama?')">
                        <i class="fas fa-trash"></i> Hapus Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET"><div class="filter-bar" style="flex-wrap:wrap;">
            <div class="search-input"><i class="fas fa-search"></i>
                <input type="text" name="search" class="form-control" placeholder="Deskripsi aktivitas..." value="{{ request('search') }}">
            </div>
            <select name="user_id" class="form-control" style="width:180px;">
                <option value="">Semua User</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}" {{ request('user_id')==$u->id?'selected':'' }}>{{ $u->name }}</option>
                @endforeach
            </select>
            <select name="action" class="form-control" style="width:140px;">
                <option value="">Semua Aksi</option>
                @foreach($actions as $a)
                <option value="{{ $a }}" {{ request('action')==$a?'selected':'' }}>{{ ucfirst($a) }}</option>
                @endforeach
            </select>
            <select name="module" class="form-control" style="width:160px;">
                <option value="">Semua Modul</option>
                @foreach($modules as $m)
                <option value="{{ $m }}" {{ request('module')==$m?'selected':'' }}>{{ $m }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" class="form-control" style="width:150px;" value="{{ request('date_from') }}">
            <input type="date" name="date_to" class="form-control" style="width:150px;" value="{{ request('date_to') }}">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('activity-logs.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
        </div></form>
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th width="50">No</th>
                <th>Waktu</th>
                <th>User</th>
                <th style="text-align:center;">Aksi</th>
                <th>Modul</th>
                <th>Deskripsi</th>
                <th>IP Address</th>
                <th style="text-align:center;">Detail</th>
            </tr></thead>
            <tbody>
                @forelse($logs as $i => $log)
                <tr>
                    <td style="color:#94a3b8;">{{ $logs->firstItem()+$i }}</td>
                    <td style="font-size:11.5px;white-space:nowrap;">
                        <div style="font-weight:500;">{{ $log->created_at->format('d M Y') }}</div>
                        <div style="color:#94a3b8;">{{ $log->created_at->format('H:i:s') }}</div>
                    </td>
                    <td>
                        @if($log->user)
                        <div style="display:flex;align-items:center;gap:8px;">
                            <img src="{{ $log->user->avatar_url }}" alt="" style="width:26px;height:26px;border-radius:50%;object-fit:cover;">
                            <div>
                                <div style="font-size:12.5px;font-weight:600;">{{ $log->user->name }}</div>
                                <div style="font-size:10px;color:#94a3b8;">{{ $log->user->role_label }}</div>
                            </div>
                        </div>
                        @else
                        <span style="font-size:12px;color:#94a3b8;">Sistem</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <span class="badge badge-{{ $log->action_color }}">{{ $log->action_label }}</span>
                    </td>
                    <td>
                        <code style="font-size:11px;background:#f1f5f9;padding:2px 6px;border-radius:4px;">{{ $log->module }}</code>
                    </td>
                    <td style="font-size:12.5px;max-width:300px;">{{ Str::limit($log->description, 70) }}</td>
                    <td style="font-size:11px;color:#94a3b8;">{{ $log->ip_address ?? '-' }}</td>
                    <td style="text-align:center;">
                        @if($log->old_values || $log->new_values)
                        <a href="{{ route('activity-logs.show', $log) }}" class="btn btn-sm btn-secondary" title="Lihat detail perubahan">
                            <i class="fas fa-eye"></i>
                        </a>
                        @else
                        <span style="color:#d1d5db;font-size:12px;">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8"><div class="empty-state"><i class="fas fa-history"></i><p>Belum ada log aktivitas.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <span>{{ $logs->firstItem()??0 }}-{{ $logs->lastItem()??0 }} dari {{ $logs->total() }}</span>
        {{ $logs->links('vendor.pagination.simple') }}
    </div>
</div>
@endsection
