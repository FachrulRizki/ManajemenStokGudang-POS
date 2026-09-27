@extends('layouts.app')
@section('title', 'Gudang & Rak')
@push('breadcrumb_content', 'Master Data / <strong>Gudang & Rak</strong>')
@push('styles')
<style>
.tab-nav { display:flex; gap:0; border-bottom:2px solid #e2e8f0; margin-bottom:20px; }
.tab-btn {
    padding:10px 20px; font-size:13.5px; font-weight:500; color:#64748b;
    background:none; border:none; border-bottom:2px solid transparent;
    margin-bottom:-2px; cursor:pointer; transition:all .15s; display:flex; align-items:center; gap:7px;
}
.tab-btn.active { color:var(--primary); border-bottom-color:var(--primary); }
.tab-btn:hover:not(.active) { color:#374151; background:#f8fafc; }
.tab-pane { display:none; }
.tab-pane.active { display:block; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Gudang & Rak</div>
        <div class="page-subtitle">Kelola lokasi penyimpanan barang di gudang</div>
    </div>
    <div class="btn-group" id="headerActions">
        <button class="btn btn-primary" id="btnTambahGudang" onclick="openModal('modalTambahGudang')">
            <i class="fas fa-warehouse"></i> Tambah Gudang
        </button>
        <button class="btn btn-success" id="btnTambahRak" style="display:none" onclick="openModal('modalTambahRak')">
            <i class="fas fa-th-large"></i> Tambah Rak
        </button>
    </div>
</div>

<div class="tab-nav">
    <button class="tab-btn active" onclick="switchTab('gudang', this)" id="tabGudang">
        <i class="fas fa-warehouse"></i> Gudang ({{ $warehouses->total() }})
    </button>
    <button class="tab-btn" onclick="switchTab('rak', this)" id="tabRak">
        <i class="fas fa-th-large"></i> Rak ({{ $racks->count() }})
    </button>
</div>

{{-- -- TAB GUDANG ------------------------------------ --}}
<div class="tab-pane active" id="paneGudang">
    <div class="card" style="margin-bottom:16px;">
        <div class="card-body" style="padding:14px 20px;">
            <form method="GET"><input type="hidden" name="tab" value="gudang">
                <div class="filter-bar">
                    <div class="search-input"><i class="fas fa-search"></i>
                        <input type="text" name="search" class="form-control" placeholder="Cari gudang..." value="{{ request('search') }}">
                    </div>
                    <select name="status" class="form-control" style="width:160px;">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status')=='active'?'selected':'' }}>Aktif</option>
                        <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Nonaktif</option>
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
                    <a href="{{ route('warehouses.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead><tr>
                    <th width="50">No</th><th>Gudang</th><th>Kode</th><th>Lokasi</th>
                    <th style="text-align:center;">Rak</th><th style="text-align:center;">Status</th>
                    <th style="text-align:center;">Aksi</th>
                </tr></thead>
                <tbody>
                    @forelse($warehouses as $i => $wh)
                    <tr>
                        <td style="color:#94a3b8;">{{ $warehouses->firstItem() + $i }}</td>
                        <td>
                            <div style="font-weight:600;">{{ $wh->name }}</div>
                            <div style="font-size:11px;color:#94a3b8;">{{ $wh->description }}</div>
                        </td>
                        <td><code style="font-size:12px;background:#f1f5f9;padding:2px 6px;border-radius:4px;">{{ $wh->code ?? '-' }}</code></td>
                        <td style="font-size:13px;color:#64748b;">{{ $wh->location ?? '-' }}</td>
                        <td style="text-align:center;"><span class="badge badge-primary">{{ $wh->racks_count }} rak</span></td>
                        <td style="text-align:center;">
                            @if($wh->is_active)<span class="badge badge-success"><i class="fas fa-check-circle"></i> Aktif</span>
                            @else<span class="badge badge-secondary">Nonaktif</span>@endif
                        </td>
                        <td style="text-align:center;">
                            <div class="btn-group" style="justify-content:center;">
                                <button type="button" class="btn btn-sm btn-warning" onclick="editGudang({{ $wh->id }})"><i class="fas fa-edit"></i></button>
                                <form method="POST" action="{{ route('warehouses.destroy', $wh) }}" onsubmit="return confirmDelete(this)">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7"><div class="empty-state"><i class="fas fa-warehouse"></i><p>Belum ada gudang.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">
            <span>{{ $warehouses->firstItem() ?? 0 }}-{{ $warehouses->lastItem() ?? 0 }} dari {{ $warehouses->total() }}</span>
            {{ $warehouses->links('vendor.pagination.simple') }}
        </div>
    </div>
</div>

{{-- -- TAB RAK --------------------------------------- --}}
<div class="tab-pane" id="paneRak">
    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead><tr>
                    <th width="50">No</th><th>Rak</th><th>Gudang</th><th>Kode</th>
                    <th>Baris / Kolom</th>
                    <th style="text-align:center;">Produk</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:center;">Aksi</th>
                </tr></thead>
                <tbody>
                    @forelse($racks as $i => $rack)
                    <tr>
                        <td style="color:#94a3b8;">{{ $i + 1 }}</td>
                        <td style="font-weight:600;">{{ $rack->name }}</td>
                        <td><span class="badge badge-info">{{ $rack->warehouse->name ?? '-' }}</span></td>
                        <td><code style="font-size:12px;background:#f1f5f9;padding:2px 6px;border-radius:4px;">{{ $rack->code ?? '-' }}</code></td>
                        <td style="font-size:13px;color:#64748b;">
                            {{ $rack->row ? 'Baris ' . $rack->row : '-' }}
                            {{ $rack->column ? ' / Kol ' . $rack->column : '' }}
                        </td>
                        <td style="text-align:center;"><span class="badge badge-secondary">{{ $rack->products_count }} produk</span></td>
                        <td style="text-align:center;">
                            @if($rack->is_active)<span class="badge badge-success"><i class="fas fa-check-circle"></i> Aktif</span>
                            @else<span class="badge badge-secondary">Nonaktif</span>@endif
                        </td>
                        <td style="text-align:center;">
                            <div class="btn-group" style="justify-content:center;">
                                <button type="button" class="btn btn-sm btn-warning" onclick="editRak({{ $rack->id }})"><i class="fas fa-edit"></i></button>
                                <form method="POST" action="{{ route('racks.destroy', $rack) }}" onsubmit="return confirmDelete(this)">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8"><div class="empty-state"><i class="fas fa-th-large"></i><p>Belum ada rak.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Data JS --}}
<script>
const warehousesData = {!! json_encode($warehouses->keyBy('id')->map(function($w) {
    return ['id' => $w->id, 'code' => $w->code, 'name' => $w->name,
        'location' => $w->location, 'description' => $w->description, 'is_active' => $w->is_active];
})->toArray()) !!};
const racksData = {!! json_encode($racks->keyBy('id')->map(function($r) {
    return ['id' => $r->id, 'warehouse_id' => $r->warehouse_id, 'code' => $r->code,
        'name' => $r->name, 'row' => $r->row, 'column' => $r->column,
        'description' => $r->description, 'is_active' => $r->is_active];
})->toArray()) !!};
const allWarehouses = {!! json_encode($warehouses->map(function($w) {
    return ['id' => $w->id, 'name' => $w->name];
})->values()->toArray()) !!};
</script>

{{-- -- MODAL TAMBAH GUDANG -------------------------- --}}
<div class="modal-backdrop" id="modalTambahGudang">
    <div class="modal-box" style="max-width:500px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-warehouse" style="color:var(--primary)"></i> Tambah Gudang</div>
            <button class="modal-close" onclick="closeModal('modalTambahGudang')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('warehouses.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Nama Gudang <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="Gudang Utama">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kode</label>
                        <input type="text" name="code" class="form-control" placeholder="GDG-01">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Lokasi / Alamat</label>
                    <input type="text" name="location" class="form-control" placeholder="Lantai 1, Gedung A">
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <div class="toggle-wrap">
                        <label class="toggle"><input type="checkbox" name="is_active" value="1" checked><span class="toggle-slider"></span></label>
                        <span style="font-size:13px;color:#64748b;">Gudang aktif</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahGudang')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- -- MODAL EDIT GUDANG ---------------------------- --}}
<div class="modal-backdrop" id="modalEditGudang">
    <div class="modal-box" style="max-width:500px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-edit" style="color:#f59e0b"></i> Edit Gudang</div>
            <button class="modal-close" onclick="closeModal('modalEditGudang')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="formEditGudang">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Nama Gudang <span class="required">*</span></label>
                        <input type="text" name="name" id="eGName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kode</label>
                        <input type="text" name="code" id="eGCode" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Lokasi / Alamat</label>
                    <input type="text" name="location" id="eGLocation" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" id="eGDesc" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <div class="toggle-wrap">
                        <label class="toggle"><input type="checkbox" name="is_active" id="eGActive" value="1"><span class="toggle-slider"></span></label>
                        <span style="font-size:13px;color:#64748b;">Gudang aktif</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditGudang')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Perbarui</button>
            </div>
        </form>
    </div>
</div>

{{-- -- MODAL TAMBAH RAK ------------------------------ --}}
<div class="modal-backdrop" id="modalTambahRak">
    <div class="modal-box" style="max-width:520px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-th-large" style="color:#10b981"></i> Tambah Rak</div>
            <button class="modal-close" onclick="closeModal('modalTambahRak')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('racks.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Gudang <span class="required">*</span></label>
                    <select name="warehouse_id" class="form-control" required>
                        <option value="">-- Pilih Gudang --</option>
                        @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Nama Rak <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="Rak A">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kode</label>
                        <input type="text" name="code" class="form-control" placeholder="A">
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Baris</label>
                        <input type="text" name="row" class="form-control" placeholder="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kolom</label>
                        <input type="text" name="column" class="form-control" placeholder="1">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <div class="toggle-wrap">
                        <label class="toggle"><input type="checkbox" name="is_active" value="1" checked><span class="toggle-slider"></span></label>
                        <span style="font-size:13px;color:#64748b;">Rak aktif</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahRak')">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- -- MODAL EDIT RAK -------------------------------- --}}
<div class="modal-backdrop" id="modalEditRak">
    <div class="modal-box" style="max-width:520px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-edit" style="color:#f59e0b"></i> Edit Rak</div>
            <button class="modal-close" onclick="closeModal('modalEditRak')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="formEditRak">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Gudang <span class="required">*</span></label>
                    <select name="warehouse_id" id="eRWarehouse" class="form-control" required>
                        <option value="">-- Pilih Gudang --</option>
                        @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Nama Rak <span class="required">*</span></label>
                        <input type="text" name="name" id="eRName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kode</label>
                        <input type="text" name="code" id="eRCode" class="form-control">
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Baris</label>
                        <input type="text" name="row" id="eRRow" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kolom</label>
                        <input type="text" name="column" id="eRCol" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" id="eRDesc" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <div class="toggle-wrap">
                        <label class="toggle"><input type="checkbox" name="is_active" id="eRActive" value="1"><span class="toggle-slider"></span></label>
                        <span style="font-size:13px;color:#64748b;">Rak aktif</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditRak')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Perbarui</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('pane' + tab.charAt(0).toUpperCase() + tab.slice(1)).classList.add('active');
    document.getElementById('btnTambahGudang').style.display = tab === 'gudang' ? '' : 'none';
    document.getElementById('btnTambahRak').style.display = tab === 'rak' ? '' : 'none';
}

function editGudang(id) {
    const w = warehousesData[id]; if (!w) return;
    document.getElementById('formEditGudang').action = '/warehouses/' + id;
    document.getElementById('eGName').value     = w.name || '';
    document.getElementById('eGCode').value     = w.code || '';
    document.getElementById('eGLocation').value = w.location || '';
    document.getElementById('eGDesc').value     = w.description || '';
    document.getElementById('eGActive').checked = !!w.is_active;
    openModal('modalEditGudang');
}

function editRak(id) {
    const r = racksData[id]; if (!r) return;
    document.getElementById('formEditRak').action = '/racks/' + id;
    document.getElementById('eRWarehouse').value = r.warehouse_id;
    document.getElementById('eRName').value      = r.name || '';
    document.getElementById('eRCode').value      = r.code || '';
    document.getElementById('eRRow').value       = r.row || '';
    document.getElementById('eRCol').value       = r.column || '';
    document.getElementById('eRDesc').value      = r.description || '';
    document.getElementById('eRActive').checked  = !!r.is_active;
    openModal('modalEditRak');
}

// Restore active tab from URL
const urlTab = new URLSearchParams(window.location.search).get('tab');
if (urlTab === 'rak') switchTab('rak', document.getElementById('tabRak'));
</script>
@endpush
