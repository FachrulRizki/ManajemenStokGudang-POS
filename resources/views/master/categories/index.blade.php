@extends('layouts.app')
@section('title', 'Kategori')
@push('breadcrumb_content', 'Master Data / <strong>Kategori</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Kategori Barang</div>
        <div class="page-subtitle">Kelola kategori / jenis barang di gudang</div>
    </div>
    <button class="btn btn-primary" onclick="openModal('modalTambah')">
        <i class="fas fa-plus"></i> Tambah Kategori
    </button>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" action="{{ route('categories.index') }}">
            <div class="filter-bar">
                <div class="search-input">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama atau kode..." value="{{ request('search') }}">
                </div>
                <select name="status" class="form-control" style="width:160px;">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status')=='active'?'selected':'' }}>Aktif</option>
                    <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Nonaktif</option>
                </select>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
                <a href="{{ route('categories.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th>Nama Kategori</th>
                    <th>Kode</th>
                    <th>Deskripsi</th>
                    <th style="text-align:center;">Jumlah Produk</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:center;" width="130">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $i => $cat)
                <tr>
                    <td style="color:#94a3b8;">{{ $categories->firstItem() + $i }}</td>
                    <td><div style="font-weight:600;">{{ $cat->name }}</div></td>
                    <td><code style="font-size:12px;background:#f1f5f9;padding:2px 6px;border-radius:4px;">{{ $cat->code ?? '-' }}</code></td>
                    <td style="color:#64748b;font-size:13px;">{{ Str::limit($cat->description, 60) ?? '-' }}</td>
                    <td style="text-align:center;">
                        <a href="{{ route('products.index', ['category_id'=>$cat->id]) }}" class="badge badge-primary" style="text-decoration:none;">
                            {{ number_format($cat->products_count) }} produk
                        </a>
                    </td>
                    <td style="text-align:center;">
                        @if($cat->is_active)
                            <span class="badge badge-success"><i class="fas fa-check-circle"></i> Aktif</span>
                        @else
                            <span class="badge badge-secondary"><i class="fas fa-times-circle"></i> Nonaktif</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <div class="btn-group" style="justify-content:center;">
                            <button type="button" class="btn btn-sm btn-warning" title="Edit"
                                onclick="openEditModal({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ addslashes($cat->code ?? '') }}', '{{ addslashes($cat->description ?? '') }}', {{ $cat->is_active ? 'true' : 'false' }})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="{{ route('categories.destroy', $cat) }}" onsubmit="return confirmDelete(this)">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="fas fa-tags"></i>
                            <p>Belum ada data kategori. <button class="btn btn-sm btn-primary" onclick="openModal('modalTambah')">Tambah sekarang</button></p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <span>Menampilkan {{ $categories->firstItem() ?? 0 }}-{{ $categories->lastItem() ?? 0 }} dari {{ $categories->total() }} data</span>
        <div class="pagination">
            @if($categories->onFirstPage())
                <span class="disabled"><i class="fas fa-chevron-left"></i></span>
            @else
                <a href="{{ $categories->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>
            @endif
            @foreach($categories->getUrlRange(1, $categories->lastPage()) as $page => $url)
                @if($page == $categories->currentPage())
                    <span class="active">{{ $page }}</span>
                @elseif(abs($page - $categories->currentPage()) <= 2)
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach
            @if($categories->hasMorePages())
                <a href="{{ $categories->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a>
            @else
                <span class="disabled"><i class="fas fa-chevron-right"></i></span>
            @endif
        </div>
    </div>
</div>

{{-- -- MODAL TAMBAH ----------------------------------- --}}
<div class="modal-backdrop" id="modalTambah">
    <div class="modal-box" style="max-width:520px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-tag" style="color:var(--primary)"></i> Tambah Kategori</div>
            <button class="modal-close" onclick="closeModal('modalTambah')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('categories.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Kategori <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                        value="{{ old('name') }}" placeholder="Contoh: Elektronik" autofocus>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Kode Kategori</label>
                    <input type="text" name="code" class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}"
                        value="{{ old('code') }}" placeholder="Contoh: ELKT" style="text-transform:uppercase;">
                    <div class="form-hint">Kode unik untuk identifikasi cepat (opsional)</div>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3"
                        placeholder="Deskripsi singkat kategori...">{{ old('description') }}</textarea>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Status</label>
                    <div class="toggle-wrap">
                        <label class="toggle">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                        <span style="font-size:13px;color:#64748b;">Kategori aktif</span>
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

{{-- -- MODAL EDIT ------------------------------------ --}}
<div class="modal-backdrop" id="modalEdit">
    <div class="modal-box" style="max-width:520px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-edit" style="color:#f59e0b"></i> Edit Kategori</div>
            <button class="modal-close" onclick="closeModal('modalEdit')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Kategori <span class="required">*</span></label>
                    <input type="text" name="name" id="editName" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Kode Kategori</label>
                    <input type="text" name="code" id="editCode" class="form-control" style="text-transform:uppercase;">
                    <div class="form-hint">Kode unik untuk identifikasi cepat (opsional)</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Status</label>
                    <div class="toggle-wrap">
                        <label class="toggle">
                            <input type="checkbox" name="is_active" id="editIsActive" value="1">
                            <span class="toggle-slider"></span>
                        </label>
                        <span style="font-size:13px;color:#64748b;">Kategori aktif</span>
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
function openEditModal(id, name, code, description, isActive) {
    document.getElementById('editForm').action = '/categories/' + id;
    document.getElementById('editName').value = name;
    document.getElementById('editCode').value = code;
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
