@extends('layouts.app')
@section('title', 'Profil Saya')
@push('breadcrumb_content', '<strong>Profil Saya</strong>')
@section('content')
<div class="page-header">
    <div><div class="page-title">Profil Saya</div><div class="page-subtitle">Kelola informasi akun Anda</div></div>
</div>

<div style="max-width:640px;">
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-user-circle" style="color:var(--primary)"></i> Informasi Profil</div></div>
        <div class="card-body">
            <div style="display:flex;align-items:center;gap:20px;margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid #f1f5f9;">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" id="avatarPreview"
                    style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid var(--primary-light);">
                <div>
                    <div style="font-weight:700;font-size:17px;">{{ $user->name }}</div>
                    <div style="font-size:13px;color:#64748b;margin-top:3px;">{{ $user->email }}</div>
                    <div style="margin-top:8px;">
                        @php $rc=['admin'=>'danger','manager'=>'warning','staff'=>'info']; @endphp
                        <span class="badge badge-{{ $rc[$user->role]??'secondary' }}">{{ $user->role_label }}</span>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="form-group">
                    <label class="form-label">Foto Profil</label>
                    <input type="file" name="avatar" id="avatarInput" accept="image/*" class="form-control"
                        onchange="previewAvatar(this)">
                    <div class="form-hint">JPG atau PNG, maks 2MB</div>
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control {{ $errors->has('name')?'is-invalid':'' }}"
                            value="{{ old('name', $user->name) }}">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Username <span class="required">*</span></label>
                        <div style="position:relative;">
                            <span style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px;">@</span>
                            <input type="text" name="username"
                                class="form-control {{ $errors->has('username')?'is-invalid':'' }}"
                                style="padding-left:26px;"
                                value="{{ old('username', $user->username) }}">
                        </div>
                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">No. HP</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                </div>

                <div style="border-top:1px solid #f1f5f9;padding-top:16px;margin-top:4px;">
                    <div style="font-size:13px;font-weight:600;color:#374151;margin-bottom:12px;">Ubah Password</div>
                    <div class="form-row cols-2">
                        <div class="form-group">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="password" class="form-control {{ $errors->has('password')?'is-invalid':'' }}"
                                placeholder="Kosongkan jika tidak diubah">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password baru">
                        </div>
                    </div>
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:8px;border-top:1px solid #f1f5f9;margin-top:8px;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
