@extends('layouts.app')
@section('title', 'Satuan')
@push('breadcrumb_content', 'Master Data / <strong>Satuan</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Satuan Barang</div>
        <div class="page-subtitle">Kelola satuan ukuran barang (pcs, kg, liter, dll)</div>
    </div>
    <button class="btn btn-primary" onclick="openModal('modalTambah')"><i class="fas fa-plus"></i> Tambah Satuan</button>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET"><div class="filter-bar">
            <div class="search-input"><i class="fas fa-search"></i>
                <input type="text" name="search" class="form-control" placeholder="Cari satuan..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
            <a href="{{ route('units.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
        </div></form>
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th width="50">No</th><th>Nama Satuan</th><th>Simbol</th>
                <th>Deskripsi</th><th style="text-align:center;">Produk</th>
                <th style="text-align:center;">Status</th><th style="text-align:center;">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse($units as $i => $unit)
                <tr>
                    <td style="color:#94a3b8;">{{ $units->firstItem() + $i }}</td>
                    <td style="font-weight:600;">{{ $unit->name }}</td>
                    <td><span class="badge badge-primary" style="font-size:13px;">{{ $unit->symbol }}</span></td>
                    <td style="color:#64748b;font-size:13px;">{{ $unit->description ?? '-' }}</td>
                    <td style="text-align:center;"><span class="badge badge-secondary">{{ $unit->products_count }}</span></td>
                    <td style="text-align:center;">
                        @if($unit->is_active)<span class="badge badge-success"><i class="fas fa-check-circle"></i> Aktif</span>
                        @else<span class="badge badge-secondary">Nonaktif</span>@endif
                    </td>
                    <td style="text-align:center;">
                        <div class="btn-group" style="justify-content:center;">
                            <button type="button" class="btn btn-sm btn-warning" title="Edit"
                                onclick="openEditModal({{ $unit->id }}, '{{ addslashes($unit->name) }}', '{{ addslashes($unit->symbol) }}', '{{ addslashes($unit->description ?? '') }}', {{ $unit->is_active ? 'true' : 'false' }})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="{{ route('units.destroy', $unit) }}" onsubmit="return confirmDelete(this)">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state"><i class="fas fa-ruler"></i><p>Belum ada satuan.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <span>{{ $units->firstItem() ?? 0 }}-{{ $units->lastItem() ?? 0 }} dari {{ $units->total() }}</span>
        {{ $units->links('vendor.pagination.simple') }}
    </div>
</div>

{{-- -- MODAL TAMBAH ------------------------------------ --}}
<div class="modal-backdrop" id="modalTambah">
    <div class="modal-box" style="max-width:460px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-ruler" style="color:var(--primary)"></i> Tambah Satuan</div>
            <button class="modal-close" onclick="closeModal('modalTambah')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('units.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Nama Satuan <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                            value="{{ old('name') }}" placeholder="Contoh: Kilogram">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Simbol <span class="required">*</span></label>
                        <input type="text" name="symbol" class="form-control {{ $errors->has('symbol') ? 'is-invalid' : '' }}"
                            value="{{ old('symbol') }}" placeholder="Contoh: kg">
                        @error('symbol')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Status</label>
                    <div class="toggle-wrap">
                        <label class="toggle">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                        <span style="font-size:13px;color:#64748b;">Aktif</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambah')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- -- MODAL EDIT -------------------------------------- --}}
<div class="modal-backdrop" id="modalEdit">
    <div class="modal-box" style="max-width:460px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-edit" style="color:#f59e0b"></i> Edit Satuan</div>
            <button class="modal-close" onclick="closeModal('modalEdit')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Nama Satuan <span class="required">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Simbol <span class="required">*</span></label>
                        <input type="text" name="symbol" id="editSymbol" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" id="editDescription" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Status</label>
                    <div class="toggle-wrap">
                        <label class="toggle">
                            <input type="checkbox" name="is_active" id="editIsActive" value="1">
                            <span class="toggle-slider"></span>
                        </label>
                        <span style="font-size:13px;color:#64748b;">Aktif</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEdit')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Perbarui</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openEditModal(id, name, symbol, description, isActive) {
    document.getElementById('editForm').action = '/units/' + id;
    document.getElementById('editName').value = name;
    document.getElementById('editSymbol').value = symbol;
    document.getElementById('editDescription').value = description;
    document.getElementById('editIsActive').checked = isActive;
    openModal('modalEdit');
}

@if($errors->any() && old('_method') === 'PUT')
    document.addEventListener('DOMContentLoaded', () => openModal('modalEdit'));
@elseif($errors->any())
    document.addEventListener('DOMContentLoaded', () => openModal('modalTambah'));
@endif
</script>
@endpush
