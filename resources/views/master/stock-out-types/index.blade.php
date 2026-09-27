@extends('layouts.app')
@section('title', 'Tipe Stok Keluar')
@push('breadcrumb_content', 'Master Data / <strong>Tipe Stok Keluar</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Tipe Stok Keluar</div>
        <div class="page-subtitle">Kelola jenis pengeluaran barang (rusak, retur, penyesuaian, dll)</div>
    </div>
    <button class="btn btn-primary" onclick="openModal('modalTambah')"><i class="fas fa-plus"></i> Tambah Tipe</button>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET"><div class="filter-bar">
            <div class="search-input"><i class="fas fa-search"></i>
                <input type="text" name="search" class="form-control" placeholder="Cari tipe keluar..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
            <a href="{{ route('stock-out-types.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
        </div></form>
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th width="50">No</th><th>Tipe</th><th>Kode</th>
                <th style="text-align:center;">Warna Badge</th>
                <th style="text-align:center;">Kurangi Stok</th>
                <th style="text-align:center;">Digunakan</th>
                <th style="text-align:center;">Status</th>
                <th style="text-align:center;">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse($types as $i => $t)
                <tr>
                    <td style="color:#94a3b8;">{{ $types->firstItem() + $i }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            @if($t->icon)<i class="{{ $t->icon }}" style="width:16px;text-align:center;color:#64748b;"></i>@endif
                            <span style="font-weight:600;">{{ $t->name }}</span>
                        </div>
                    </td>
                    <td><code style="font-size:12px;background:#f1f5f9;padding:2px 6px;border-radius:4px;">{{ $t->code }}</code></td>
                    <td style="text-align:center;">
                        <span class="badge badge-{{ $t->color }}">{{ $t->name }}</span>
                    </td>
                    <td style="text-align:center;">
                        @if($t->affects_stock)
                            <span class="badge badge-danger"><i class="fas fa-minus-circle"></i> Ya</span>
                        @else
                            <span class="badge badge-secondary">Tidak</span>
                        @endif
                    </td>
                    <td style="text-align:center;"><span class="badge badge-secondary">{{ number_format($t->stock_outs_count) }}x</span></td>
                    <td style="text-align:center;">
                        @if($t->is_active)<span class="badge badge-success"><i class="fas fa-check-circle"></i> Aktif</span>
                        @else<span class="badge badge-secondary">Nonaktif</span>@endif
                    </td>
                    <td style="text-align:center;">
                        <div class="btn-group" style="justify-content:center;">
                            <button type="button" class="btn btn-sm btn-warning" onclick="editType({{ $t->id }})"><i class="fas fa-edit"></i></button>
                            <form method="POST" action="{{ route('stock-out-types.destroy', $t) }}" onsubmit="return confirmDelete(this)">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8"><div class="empty-state"><i class="fas fa-tag"></i><p>Belum ada tipe keluar.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <span>{{ $types->firstItem() ?? 0 }}-{{ $types->lastItem() ?? 0 }} dari {{ $types->total() }}</span>
        {{ $types->links('vendor.pagination.simple') }}
    </div>
</div>

<script>
const typesData = {!! json_encode($types->keyBy('id')->map(function($t) {
    return ['id' => $t->id, 'code' => $t->code, 'name' => $t->name, 'color' => $t->color,
        'icon' => $t->icon, 'affects_stock' => $t->affects_stock,
        'is_active' => $t->is_active, 'sort_order' => $t->sort_order];
})->toArray()) !!};
</script>

@php $colorOptions = ['success'=>'Hijau (success)','danger'=>'Merah (danger)','warning'=>'Kuning (warning)','info'=>'Biru (info)','secondary'=>'Abu-abu (secondary)','primary'=>'Ungu (primary)']; @endphp

{{-- -- MODAL TAMBAH ------------------------------------ --}}
<div class="modal-backdrop" id="modalTambah">
    <div class="modal-box" style="max-width:480px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-tag" style="color:var(--primary)"></i> Tambah Tipe Keluar</div>
            <button class="modal-close" onclick="closeModal('modalTambah')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('stock-out-types.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Nama <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="Barang Rusak">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kode <span class="required">*</span></label>
                        <input type="text" name="code" class="form-control" required placeholder="DAMAGED" style="text-transform:uppercase;">
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Warna Badge <span class="required">*</span></label>
                        <select name="color" class="form-control" required>
                            @foreach($colorOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Urutan</label>
                        <input type="number" name="sort_order" class="form-control" value="0" min="0">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Icon (Font Awesome class)</label>
                    <input type="text" name="icon" class="form-control" placeholder="fas fa-times-circle">
                </div>
                <div style="display:flex;gap:20px;">
                    <div class="toggle-wrap">
                        <label class="toggle"><input type="checkbox" name="affects_stock" value="1" checked><span class="toggle-slider"></span></label>
                        <span style="font-size:13px;color:#64748b;">Kurangi stok</span>
                    </div>
                    <div class="toggle-wrap">
                        <label class="toggle"><input type="checkbox" name="is_active" value="1" checked><span class="toggle-slider"></span></label>
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
    <div class="modal-box" style="max-width:480px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-edit" style="color:#f59e0b"></i> Edit Tipe Keluar</div>
            <button class="modal-close" onclick="closeModal('modalEdit')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Nama <span class="required">*</span></label>
                        <input type="text" name="name" id="eName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kode <span class="required">*</span></label>
                        <input type="text" name="code" id="eCode" class="form-control" required style="text-transform:uppercase;">
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Warna Badge <span class="required">*</span></label>
                        <select name="color" id="eColor" class="form-control" required>
                            @foreach($colorOptions as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Urutan</label>
                        <input type="number" name="sort_order" id="eSortOrder" class="form-control" min="0">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Icon (Font Awesome class)</label>
                    <input type="text" name="icon" id="eIcon" class="form-control">
                </div>
                <div style="display:flex;gap:20px;">
                    <div class="toggle-wrap">
                        <label class="toggle"><input type="checkbox" name="affects_stock" id="eAffects" value="1"><span class="toggle-slider"></span></label>
                        <span style="font-size:13px;color:#64748b;">Kurangi stok</span>
                    </div>
                    <div class="toggle-wrap">
                        <label class="toggle"><input type="checkbox" name="is_active" id="eActive" value="1"><span class="toggle-slider"></span></label>
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
function editType(id) {
    const t = typesData[id]; if (!t) return;
    document.getElementById('editForm').action = '/stock-out-types/' + id;
    document.getElementById('eName').value      = t.name || '';
    document.getElementById('eCode').value      = t.code || '';
    document.getElementById('eColor').value     = t.color || 'secondary';
    document.getElementById('eSortOrder').value = t.sort_order || 0;
    document.getElementById('eIcon').value      = t.icon || '';
    document.getElementById('eAffects').checked = !!t.affects_stock;
    document.getElementById('eActive').checked  = !!t.is_active;
    openModal('modalEdit');
}
</script>
@endpush
