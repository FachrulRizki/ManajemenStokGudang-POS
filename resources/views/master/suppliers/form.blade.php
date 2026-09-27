@extends('layouts.app')
@section('title', isset($supplier) ? 'Edit Supplier' : 'Tambah Supplier')
@push('breadcrumb_content', 'Master Data / <a href="' . route('suppliers.index') . '" style="color:inherit;">Supplier</a> / <strong>' . (isset($supplier) ? 'Edit' : 'Tambah') . '</strong>')
@section('content')
<div class="page-header">
    <div><div class="page-title">{{ isset($supplier) ? 'Edit Supplier' : 'Tambah Supplier' }}</div></div>
    <a href="{{ route('suppliers.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div style="max-width:700px;">
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-truck" style="color:var(--primary)"></i> Data Supplier</div></div>
        <div class="card-body">
            <form method="POST" action="{{ isset($supplier) ? route('suppliers.update', $supplier) : route('suppliers.store') }}">
                @csrf @if(isset($supplier)) @method('PUT') @endif

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Nama Supplier <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                            value="{{ old('name', $supplier->name ?? '') }}" placeholder="Nama perusahaan supplier" autofocus>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kode Supplier</label>
                        <input type="text" name="code" class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}"
                            value="{{ old('code', $supplier->code ?? '') }}" placeholder="Contoh: SUP001">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Nama Kontak</label>
                        <input type="text" name="contact_person" class="form-control"
                            value="{{ old('contact_person', $supplier->contact_person ?? '') }}" placeholder="Nama PIC supplier">
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. Telepon</label>
                        <input type="text" name="phone" class="form-control"
                            value="{{ old('phone', $supplier->phone ?? '') }}" placeholder="08xx-xxxx-xxxx">
                    </div>
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                            value="{{ old('email', $supplier->email ?? '') }}" placeholder="email@supplier.com">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kota</label>
                        <input type="text" name="city" class="form-control"
                            value="{{ old('city', $supplier->city ?? '') }}" placeholder="Jakarta">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Alamat Lengkap</label>
                    <textarea name="address" class="form-control" rows="2"
                        placeholder="Alamat lengkap supplier...">{{ old('address', $supplier->address ?? '') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="2"
                        placeholder="Catatan tambahan...">{{ old('notes', $supplier->notes ?? '') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <div class="toggle-wrap">
                        <label class="toggle">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $supplier->is_active ?? true) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                        <span style="font-size:13px;color:#64748b;">Supplier aktif</span>
                    </div>
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:8px;border-top:1px solid #f1f5f9;margin-top:8px;">
                    <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
