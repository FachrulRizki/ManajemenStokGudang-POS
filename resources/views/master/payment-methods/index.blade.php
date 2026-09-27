@extends('layouts.app')
@section('title', 'Metode Pembayaran')
@push('breadcrumb_content', 'Master Data / <strong>Metode Pembayaran</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Metode Pembayaran</div>
        <div class="page-subtitle">Kelola opsi pembayaran yang tersedia di kasir</div>
    </div>
    <button class="btn btn-primary" onclick="openModal('modalTambah')"><i class="fas fa-plus"></i> Tambah Metode</button>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET"><div class="filter-bar">
            <div class="search-input"><i class="fas fa-search"></i>
                <input type="text" name="search" class="form-control" placeholder="Cari metode pembayaran..." value="{{ request('search') }}">
            </div>
            <select name="type" class="form-control" style="width:160px;">
                <option value="">Semua Tipe</option>
                <option value="cash" {{ request('type')=='cash'?'selected':'' }}>Tunai</option>
                <option value="digital" {{ request('type')=='digital'?'selected':'' }}>Digital</option>
                <option value="card" {{ request('type')=='card'?'selected':'' }}>Kartu</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
            <a href="{{ route('payment-methods.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
        </div></form>
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th width="50">No</th><th>Metode</th><th>Kode</th><th>Tipe</th>
                <th style="text-align:center;">Perlu Referensi</th>
                <th style="text-align:center;">Transaksi</th>
                <th style="text-align:center;">Status</th>
                <th style="text-align:center;">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse($methods as $i => $m)
                @php $typeLabel = ['cash'=>'Tunai','digital'=>'Digital','card'=>'Kartu'][$m->type] ?? $m->type; @endphp
                <tr>
                    <td style="color:#94a3b8;">{{ $methods->firstItem() + $i }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:36px;height:36px;background:{{ ['cash'=>'#dcfce7','digital'=>'#ede9fe','card'=>'#dbeafe'][$m->type] ?? '#f1f5f9' }};border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                <i class="{{ $m->icon_class }}" style="color:{{ $m->type_color }};font-size:16px;"></i>
                            </div>
                            <div style="font-weight:600;">{{ $m->name }}</div>
                        </div>
                    </td>
                    <td><code style="font-size:12px;background:#f1f5f9;padding:2px 6px;border-radius:4px;">{{ $m->code }}</code></td>
                    <td>
                        <span class="badge {{ ['cash'=>'badge-success','digital'=>'badge-primary','card'=>'badge-info'][$m->type] ?? 'badge-secondary' }}">
                            {{ $typeLabel }}
                        </span>
                    </td>
                    <td style="text-align:center;">
                        @if($m->requires_reference)
                            <span class="badge badge-warning"><i class="fas fa-check"></i> Ya</span>
                        @else
                            <span class="badge badge-secondary">Tidak</span>
                        @endif
                    </td>
                    <td style="text-align:center;"><span class="badge badge-secondary">{{ number_format($m->transactions_count) }}</span></td>
                    <td style="text-align:center;">
                        @if($m->is_active)<span class="badge badge-success"><i class="fas fa-check-circle"></i> Aktif</span>
                        @else<span class="badge badge-secondary">Nonaktif</span>@endif
                    </td>
                    <td style="text-align:center;">
                        <div class="btn-group" style="justify-content:center;">
                            <button type="button" class="btn btn-sm btn-warning" onclick="editMethod({{ $m->id }})"><i class="fas fa-edit"></i></button>
                            <form method="POST" action="{{ route('payment-methods.destroy', $m) }}" onsubmit="return confirmDelete(this)">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8"><div class="empty-state"><i class="fas fa-wallet"></i><p>Belum ada metode pembayaran.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <span>{{ $methods->firstItem() ?? 0 }}-{{ $methods->lastItem() ?? 0 }} dari {{ $methods->total() }}</span>
        {{ $methods->links('vendor.pagination.simple') }}
    </div>
</div>

<script>
const methodsData = {!! json_encode($methods->keyBy('id')->map(function($m) {
    return ['id' => $m->id, 'code' => $m->code, 'name' => $m->name, 'type' => $m->type,
        'description' => $m->description, 'icon' => $m->icon,
        'requires_reference' => $m->requires_reference, 'is_active' => $m->is_active,
        'sort_order' => $m->sort_order];
})->toArray()) !!};
</script>

{{-- -- MODAL TAMBAH ------------------------------------ --}}
<div class="modal-backdrop" id="modalTambah">
    <div class="modal-box" style="max-width:500px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-wallet" style="color:var(--primary)"></i> Tambah Metode Pembayaran</div>
            <button class="modal-close" onclick="closeModal('modalTambah')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('payment-methods.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Nama <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="Tunai">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kode <span class="required">*</span></label>
                        <input type="text" name="code" class="form-control" required placeholder="CASH" style="text-transform:uppercase;">
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Tipe <span class="required">*</span></label>
                        <select name="type" class="form-control" required>
                            <option value="cash">Tunai (Cash)</option>
                            <option value="digital">Digital (QRIS/Transfer)</option>
                            <option value="card">Kartu (Debit/Kredit)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Urutan</label>
                        <input type="number" name="sort_order" class="form-control" value="0" min="0">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Icon (Font Awesome class)</label>
                    <input type="text" name="icon" class="form-control" placeholder="fas fa-money-bill-wave">
                    <div class="form-hint">Kosongkan untuk menggunakan icon default berdasarkan tipe</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
                <div style="display:flex;gap:20px;">
                    <div class="toggle-wrap">
                        <label class="toggle"><input type="checkbox" name="requires_reference" value="1"><span class="toggle-slider"></span></label>
                        <span style="font-size:13px;color:#64748b;">Perlu no. referensi</span>
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
    <div class="modal-box" style="max-width:500px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-edit" style="color:#f59e0b"></i> Edit Metode Pembayaran</div>
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
                        <label class="form-label">Tipe <span class="required">*</span></label>
                        <select name="type" id="eType" class="form-control" required>
                            <option value="cash">Tunai (Cash)</option>
                            <option value="digital">Digital (QRIS/Transfer)</option>
                            <option value="card">Kartu (Debit/Kredit)</option>
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
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" id="eDesc" class="form-control" rows="2"></textarea>
                </div>
                <div style="display:flex;gap:20px;">
                    <div class="toggle-wrap">
                        <label class="toggle"><input type="checkbox" name="requires_reference" id="eRef" value="1"><span class="toggle-slider"></span></label>
                        <span style="font-size:13px;color:#64748b;">Perlu no. referensi</span>
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
function editMethod(id) {
    const m = methodsData[id]; if (!m) return;
    document.getElementById('editForm').action = '/payment-methods/' + id;
    document.getElementById('eName').value       = m.name || '';
    document.getElementById('eCode').value       = m.code || '';
    document.getElementById('eType').value       = m.type || 'cash';
    document.getElementById('eSortOrder').value  = m.sort_order || 0;
    document.getElementById('eIcon').value       = m.icon || '';
    document.getElementById('eDesc').value       = m.description || '';
    document.getElementById('eRef').checked      = !!m.requires_reference;
    document.getElementById('eActive').checked   = !!m.is_active;
    openModal('modalEdit');
}
</script>
@endpush
