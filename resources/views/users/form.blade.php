@extends('layouts.app')
@section('title', isset($user) ? 'Edit User' : 'Tambah User')
@push('breadcrumb_content', 'Sistem / <a href="' . route('users.index') . '" style="color:inherit;">User</a> / <strong>' . (isset($user) ? 'Edit' : 'Tambah') . '</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ isset($user) ? 'Edit User: '.$user->name : 'Tambah User Baru' }}</div>
        <div class="page-subtitle">{{ isset($user) ? 'Perbarui data akun pengguna' : 'Buat akun pengguna baru' }}</div>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div style="max-width:640px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-user-cog" style="color:var(--primary)"></i> Data User</div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ isset($user) ? route('users.update', $user) : route('users.store') }}">
                @csrf
                @if(isset($user)) @method('PUT') @endif

                {{-- Nama --}}
                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                    <input type="text" name="name"
                        class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                        value="{{ old('name', $user->name ?? '') }}"
                        placeholder="Nama lengkap" autofocus>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Username + Email --}}
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Username <span class="required">*</span></label>
                        <div style="position:relative;">
                            <span style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px;">@</span>
                            <input type="text" name="username"
                                class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}"
                                style="padding-left:26px;"
                                value="{{ old('username', $user->username ?? '') }}"
                                placeholder="username_kamu"
                                autocomplete="off">
                        </div>
                        <div class="form-hint">Huruf, angka, strip, underscore. Digunakan untuk login.</div>
                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <span style="font-size:11px;color:#94a3b8;">(opsional)</span></label>
                        <input type="email" name="email"
                            class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                            value="{{ old('email', $user->email ?? '') }}"
                            placeholder="email@domain.com">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Phone + Role --}}
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">No. HP</label>
                        <input type="text" name="phone" class="form-control"
                            value="{{ old('phone', $user->phone ?? '') }}" placeholder="08xx-xxxx-xxxx">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role <span class="required">*</span></label>
                        <select name="role" class="form-control {{ $errors->has('role') ? 'is-invalid' : '' }}">
                            <option value="staff"   {{ old('role', $user->role ?? 'staff') == 'staff'   ? 'selected' : '' }}>Staff</option>
                            <option value="manager" {{ old('role', $user->role ?? '')      == 'manager' ? 'selected' : '' }}>Manager</option>
                            <option value="admin"   {{ old('role', $user->role ?? '')      == 'admin'   ? 'selected' : '' }}>Administrator</option>
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Password --}}
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">
                            Password
                            @if(isset($user))<span style="font-size:11px;color:#94a3b8;">(kosongkan jika tidak diubah)</span>@else<span class="required">*</span>@endif
                        </label>
                        <div style="position:relative;">
                            <input type="password" name="password" id="pw"
                                class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                placeholder="{{ isset($user) ? '••••••••' : 'Min. 8 karakter' }}">
                            <button type="button" onclick="togglePw('pw','eyePw')"
                                style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;">
                                <i id="eyePw" class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="form-control"
                            placeholder="Ulangi password">
                    </div>
                </div>

                {{-- Status --}}
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Status Akun</label>
                    <div class="toggle-wrap">
                        <label class="toggle">
                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                        <span style="font-size:13px;color:#64748b;">Akun aktif (bisa login)</span>
                    </div>
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:16px;border-top:1px solid #f1f5f9;margin-top:16px;">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ isset($user) ? 'Perbarui' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePw(id, iconId) {
    const input = document.getElementById(id);
    const icon  = document.getElementById(iconId);
    input.type  = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}
</script>
@endpush
