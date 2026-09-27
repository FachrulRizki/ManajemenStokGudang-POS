@extends('layouts.app')
@section('title', 'Supplier')
@push('breadcrumb_content', 'Master Data / <strong>Supplier</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Manajemen Supplier</div>
        <div class="page-subtitle">Kelola data pemasok / supplier barang</div>
    </div>
    <button class="btn btn-primary" onclick="openModal('modalTambah')"><i class="fas fa-plus"></i> Tambah Supplier</button>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET"><div class="filter-bar">
            <div class="search-input"><i class="fas fa-search"></i>
                <input type="text" name="search" class="form-control" placeholder="Cari nama, kode, kontak..." value="{{ request('search') }}">
            </div>
            <select name="status" class="form-control" style="width:160px;">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status')=='active'?'selected':'' }}>Aktif</option>
                <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Nonaktif</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
        </div></form>
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th width="50">No</th><th>Supplier</th><th>Kontak</th>
                <th>Kota</th><th style="text-align:center;">Produk</th>
                <th style="text-align:center;">Status</th><th style="text-align:center;">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse($suppliers as $i => $sup)
                <tr>
                    <td style="color:#94a3b8;">{{ $suppliers->firstItem() + $i }}</td>
                    <td>
                        <div style="font-weight:600;">{{ $sup->name }}</div>
                        <div style="font-size:11px;color:#94a3b8;">{{ $sup->code ?? '' }}</div>
                    </td>
                    <td>
                        <div style="font-size:13px;">{{ $sup->contact_person ?? '-' }}</div>
                        <div style="font-size:11px;color:#64748b;">{{ $sup->phone ?? '' }}</div>
                    </td>
                    <td style="font-size:13px;color:#64748b;">{{ $sup->city ?? '-' }}</td>
                    <td style="text-align:center;"><span class="badge badge-primary">{{ $sup->products_count }} produk</span></td>
                    <td style="text-align:center;">
                        @if($sup->is_active)<span class="badge badge-success"><i class="fas fa-check-circle"></i> Aktif</span>
                        @else<span class="badge badge-secondary">Nonaktif</span>@endif
                    </td>
                    <td style="text-align:center;">
                        <div class="btn-group" style="justify-content:center;">
                            <button type="button" class="btn btn-sm btn-secondary" title="Detail"
                                onclick="showDetail({{ $sup->id }})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-warning" title="Edit"
                                onclick="openEditModal({{ $sup->id }})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="{{ route('suppliers.destroy', $sup) }}" onsubmit="return confirmDelete(this)">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state"><i class="fas fa-truck"></i><p>Belum ada supplier.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <span>{{ $suppliers->firstItem() ?? 0 }}-{{ $suppliers->lastItem() ?? 0 }} dari {{ $suppliers->total() }}</span>
        {{ $suppliers->links('vendor.pagination.simple') }}
    </div>
</div>

{{-- Data supplier untuk JS --}}
@php
$suppliersJson = $suppliers->keyBy('id')->map(function($s) {
    return [
        'id'             => $s->id,
        'name'           => $s->name,
        'code'           => $s->code,
        'contact_person' => $s->contact_person,
        'phone'          => $s->phone,
        'email'          => $s->email,
        'city'           => $s->city,
        'address'        => $s->address,
        'notes'          => $s->notes,
        'is_active'      => $s->is_active,
        'products_count' => $s->products_count,
    ];
})->toArray();
@endphp
<script>
const suppliersData = {!! json_encode($suppliersJson) !!};
</script>

{{-- -- MODAL TAMBAH ------------------------------------ --}}
<div class="modal-backdrop" id="modalTambah">
    <div class="modal-box" style="max-width:640px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-truck" style="color:var(--primary)"></i> Tambah Supplier</div>
            <button class="modal-close" onclick="closeModal('modalTambah')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('suppliers.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Nama Supplier <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                            value="{{ old('name') }}" placeholder="Nama perusahaan supplier">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kode Supplier</label>
                        <input type="text" name="code" class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}"
                            value="{{ old('code') }}" placeholder="Contoh: SUP001">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Nama Kontak</label>
                        <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person') }}" placeholder="Nama PIC supplier">
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. Telepon</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="08xx-xxxx-xxxx">
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                            value="{{ old('email') }}" placeholder="email@supplier.com">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kota</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city') }}" placeholder="Jakarta">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Alamat Lengkap</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Alamat lengkap supplier...">{{ old('address') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <div class="toggle-wrap">
                        <label class="toggle">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                        <span style="font-size:13px;color:#64748b;">Supplier aktif</span>
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
    <div class="modal-box" style="max-width:640px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-edit" style="color:#f59e0b"></i> Edit Supplier</div>
            <button class="modal-close" onclick="closeModal('modalEdit')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Nama Supplier <span class="required">*</span></label>
                        <input type="text" name="name" id="eName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kode Supplier</label>
                        <input type="text" name="code" id="eCode" class="form-control">
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Nama Kontak</label>
                        <input type="text" name="contact_person" id="eContact" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. Telepon</label>
                        <input type="text" name="phone" id="ePhone" class="form-control">
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="eEmail" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kota</label>
                        <input type="text" name="city" id="eCity" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Alamat Lengkap</label>
                    <textarea name="address" id="eAddress" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" id="eNotes" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <div class="toggle-wrap">
                        <label class="toggle">
                            <input type="checkbox" name="is_active" id="eIsActive" value="1">
                            <span class="toggle-slider"></span>
                        </label>
                        <span style="font-size:13px;color:#64748b;">Supplier aktif</span>
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

{{-- -- MODAL DETAIL ------------------------------------ --}}
<div class="modal-backdrop" id="modalDetail">
    <div class="modal-box" style="max-width:480px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-truck" style="color:var(--primary)"></i> Detail Supplier</div>
            <button class="modal-close" onclick="closeModal('modalDetail')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="detailContent"></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modalDetail')">Tutup</button>
            <button type="button" class="btn btn-warning" id="detailEditBtn"><i class="fas fa-edit"></i> Edit</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showDetail(id) {
    const s = suppliersData[id];
    if (!s) return;
    const rows = [
        ['Nama',    s.name],
        ['Kode',    s.code || '-'],
        ['Kontak',  s.contact_person || '-'],
        ['Telepon', s.phone || '-'],
        ['Email',   s.email || '-'],
        ['Kota',    s.city || '-'],
        ['Alamat',  s.address || '-'],
        ['Catatan', s.notes || '-'],
        ['Produk',  s.products_count + ' produk'],
        ['Status',  s.is_active ? '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Aktif</span>' : '<span class="badge badge-secondary">Nonaktif</span>'],
    ];
    document.getElementById('detailContent').innerHTML = rows.map(([l, v]) =>
        `<div class="detail-row"><span class="detail-label">${l}</span><span class="detail-value">${v}</span></div>`
    ).join('');
    document.getElementById('detailEditBtn').onclick = () => { closeModal('modalDetail'); openEditModal(id); };
    openModal('modalDetail');
}

function openEditModal(id) {
    const s = suppliersData[id];
    if (!s) return;
    document.getElementById('editForm').action = '/suppliers/' + id;
    document.getElementById('eName').value    = s.name || '';
    document.getElementById('eCode').value    = s.code || '';
    document.getElementById('eContact').value = s.contact_person || '';
    document.getElementById('ePhone').value   = s.phone || '';
    document.getElementById('eEmail').value   = s.email || '';
    document.getElementById('eCity').value    = s.city || '';
    document.getElementById('eAddress').value = s.address || '';
    document.getElementById('eNotes').value   = s.notes || '';
    document.getElementById('eIsActive').checked = !!s.is_active;
    openModal('modalEdit');
}

@if($errors->any() && old('_method') === 'PUT')
    document.addEventListener('DOMContentLoaded', () => openModal('modalEdit'));
@elseif($errors->any())
    document.addEventListener('DOMContentLoaded', () => openModal('modalTambah'));
@endif
</script>
@endpush
