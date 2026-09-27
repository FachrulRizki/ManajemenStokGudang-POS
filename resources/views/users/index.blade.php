@extends('layouts.app')
@section('title', 'Manajemen User')
@push('breadcrumb_content', 'Sistem / <strong>Manajemen User</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Manajemen User</div>
        <div class="page-subtitle">Kelola akun pengguna sistem gudang</div>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary"><i class="fas fa-user-plus"></i> Tambah User</a>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET"><div class="filter-bar">
            <div class="search-input"><i class="fas fa-search"></i>
                <input type="text" name="search" class="form-control" placeholder="Nama, email, username..." value="{{ request('search') }}">
            </div>
            <select name="role" class="form-control" style="width:160px;">
                <option value="">Semua Role</option>
                <option value="admin" {{ request('role')=='admin'?'selected':'' }}>Administrator</option>
                <option value="manager" {{ request('role')=='manager'?'selected':'' }}>Manager</option>
                <option value="staff" {{ request('role')=='staff'?'selected':'' }}>Staff</option>
            </select>
            <select name="status" class="form-control" style="width:140px;">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status')=='active'?'selected':'' }}>Aktif</option>
                <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Nonaktif</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
        </div></form>
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th width="50">No</th><th>User</th><th>Role</th>
                <th>Kontak</th><th>Login Terakhir</th>
                <th style="text-align:center;">Status</th>
                <th style="text-align:center;">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse($users as $i => $user)
                <tr>
                    <td style="color:#94a3b8;">{{ $users->firstItem()+$i }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <img src="{{ $user->avatar_url }}" alt="" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                            <div>
                                <div style="font-weight:600;font-size:13px;">{{ $user->name }}</div>
                                <div style="font-size:11px;color:#94a3b8;">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @php $roleColors=['admin'=>'danger','manager'=>'warning','staff'=>'info']; @endphp
                        <span class="badge badge-{{ $roleColors[$user->role]??'secondary' }}">
                            <i class="fas fa-{{ $user->role=='admin'?'shield-alt':($user->role=='manager'?'user-tie':'user') }}"></i>
                            {{ $user->role_label }}
                        </span>
                    </td>
                    <td>
                        <div style="font-size:12px;">{{ $user->phone ?? '-' }}</div>
                        @if($user->username)<div style="font-size:11px;color:#94a3b8;">@{{ $user->username }}</div>@endif
                    </td>
                    <td style="font-size:12px;color:#64748b;">
                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Belum pernah' }}
                    </td>
                    <td style="text-align:center;">
                        @if($user->is_active)<span class="badge badge-success"><i class="fas fa-check-circle"></i> Aktif</span>
                        @else<span class="badge badge-danger"><i class="fas fa-times-circle"></i> Nonaktif</span>@endif
                    </td>
                    <td style="text-align:center;">
                        <div class="btn-group" style="justify-content:center;">
                            <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-secondary" title="Detail"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('users.show', [$user, 'tab'=>'permissions']) }}" class="btn btn-sm btn-info" title="Permission"><i class="fas fa-shield-alt"></i></a>
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('users.toggle-status', $user) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-secondary' : 'btn-success' }}"
                                    title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirmDelete(this)">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state"><i class="fas fa-users"></i><p>Belum ada user.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <span>{{ $users->firstItem()??0 }}-{{ $users->lastItem()??0 }} dari {{ $users->total() }}</span>
        {{ $users->links('vendor.pagination.simple') }}
    </div>
</div>
@endsection
