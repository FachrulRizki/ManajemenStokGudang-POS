@extends('layouts.app')
@section('title', isset($unit) ? 'Edit Satuan' : 'Tambah Satuan')
@push('breadcrumb_content', 'Master Data / <a href="' . route('units.index') . '" style="color:inherit;">Satuan</a> / <strong>' . (isset($unit) ? 'Edit' : 'Tambah') . '</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ isset($unit) ? 'Edit Satuan' : 'Tambah Satuan' }}</div>
    </div>
    <a href="{{ route('units.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div style="max-width:500px;">
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-ruler" style="color:var(--primary)"></i> Data Satuan</div></div>
        <div class="card-body">
            <form method="POST" action="{{ isset($unit) ? route('units.update', $unit) : route('units.store') }}">
                @csrf @if(isset($unit)) @method('PUT') @endif

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Nama Satuan <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                            value="{{ old('name', $unit->name ?? '') }}" placeholder="Contoh: Kilogram" autofocus>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Simbol <span class="required">*</span></label>
                        <input type="text" name="symbol" class="form-control {{ $errors->has('symbol') ? 'is-invalid' : '' }}"
                            value="{{ old('symbol', $unit->symbol ?? '') }}" placeholder="Contoh: kg">
                        @error('symbol')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $unit->description ?? '') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <div class="toggle-wrap">
                        <label class="toggle">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $unit->is_active ?? true) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                        <span style="font-size:13px;color:#64748b;">Aktif</span>
                    </div>
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:8px;border-top:1px solid #f1f5f9;margin-top:8px;">
                    <a href="{{ route('units.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
