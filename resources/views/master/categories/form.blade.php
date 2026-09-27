@extends('layouts.app')
@section('title', isset($category) ? 'Edit Kategori' : 'Tambah Kategori')
@push('breadcrumb_content', 'Master Data / <a href="' . route('categories.index') . '" style="color:inherit;">Kategori</a> / <strong>' . (isset($category) ? 'Edit' : 'Tambah') . '</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ isset($category) ? 'Edit Kategori' : 'Tambah Kategori' }}</div>
        <div class="page-subtitle">{{ isset($category) ? 'Perbarui data kategori barang' : 'Tambah kategori barang baru ke sistem' }}</div>
    </div>
    <a href="{{ route('categories.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div style="max-width:600px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-tag" style="color:var(--primary)"></i> Data Kategori</div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ isset($category) ? route('categories.update', $category) : route('categories.store') }}">
                @csrf
                @if(isset($category)) @method('PUT') @endif

                <div class="form-group">
                    <label class="form-label">Nama Kategori <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                        value="{{ old('name', $category->name ?? '') }}" placeholder="Contoh: Elektronik" autofocus>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Kode Kategori</label>
                    <input type="text" name="code" class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}"
                        value="{{ old('code', $category->code ?? '') }}" placeholder="Contoh: ELKT" style="text-transform:uppercase;">
                    <div class="form-hint">Kode unik untuk identifikasi cepat (opsional)</div>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3"
                        placeholder="Deskripsi singkat kategori...">{{ old('description', $category->description ?? '') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <div class="toggle-wrap">
                        <label class="toggle">
                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                        <span style="font-size:13px;color:#64748b;">Kategori aktif</span>
                    </div>
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:8px;border-top:1px solid #f1f5f9;margin-top:8px;">
                    <a href="{{ route('categories.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ isset($category) ? 'Perbarui' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
