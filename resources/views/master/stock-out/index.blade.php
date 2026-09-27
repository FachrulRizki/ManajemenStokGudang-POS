@extends('layouts.app')
@section('title', 'Stok Keluar')
@push('breadcrumb_content', 'Transaksi / <strong>Stok Keluar</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Stok Keluar</div>
        <div class="page-subtitle">Catat pengeluaran barang dari gudang</div>
    </div>
    <a href="{{ route('stock-out.create') }}" class="btn btn-danger"><i class="fas fa-plus"></i> Catat Stok Keluar</a>
</div>

<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-boxes"></i></div>
        <div class="stat-content"><div class="stat-value">{{ number_format($totalQty) }}</div><div class="stat-label">Total Unit Keluar</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-content">
            <div class="stat-value" style="font-size:18px;">Rp {{ number_format($totalValue, 0, ',', '.') }}</div>
            <div class="stat-label">Total Nilai Penjualan</div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET"><div class="filter-bar">
            <div class="search-input"><i class="fas fa-search"></i>
                <input type="text" name="search" class="form-control" placeholder="No. referensi, produk, pelanggan..." value="{{ request('search') }}">
            </div>
            <select name="type" class="form-control" style="width:160px;">
                <option value="">Semua Tipe</option>
                <option value="sale" {{ request('type')=='sale'?'selected':'' }}>Penjualan</option>
                <option value="return" {{ request('type')=='return'?'selected':'' }}>Retur</option>
                <option value="damaged" {{ request('type')=='damaged'?'selected':'' }}>Rusak/Hilang</option>
                <option value="other" {{ request('type')=='other'?'selected':'' }}>Lainnya</option>
            </select>
            <input type="date" name="date_from" class="form-control" style="width:155px;" value="{{ request('date_from') }}">
            <input type="date" name="date_to" class="form-control" style="width:155px;" value="{{ request('date_to') }}">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('stock-out.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
        </div></form>
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th width="50">No</th><th>No. Referensi</th><th>Produk</th>
                <th style="text-align:center;">Tipe</th>
                <th style="text-align:center;">Qty</th>
                <th style="text-align:right;">Harga Jual</th>
                <th style="text-align:right;">Total</th>
                <th>Pelanggan</th><th>Tanggal</th><th>Oleh</th>
                <th style="text-align:center;">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse($stockOuts as $i => $so)
                @php $typeColors = ['sale'=>'info','return'=>'warning','damaged'=>'danger','other'=>'secondary']; @endphp
                <tr>
                    <td style="color:#94a3b8;">{{ $stockOuts->firstItem() + $i }}</td>
                    <td>
                        <button type="button" style="background:#f1f5f9;border:none;padding:2px 6px;border-radius:4px;font-family:monospace;font-size:11.5px;color:#374151;cursor:pointer;"
                            onclick="showDetail({{ $so->id }})">
                            {{ $so->reference_number }}
                        </button>
                    </td>
                    <td>
                        <div style="font-weight:600;font-size:13px;">{{ $so->product->name ?? '-' }}</div>
                        <div style="font-size:11px;color:#94a3b8;">{{ $so->product->code ?? '' }}</div>
                    </td>
                    <td style="text-align:center;"><span class="badge badge-{{ $typeColors[$so->type] ?? 'secondary' }}">{{ $so->type_label }}</span></td>
                    <td style="text-align:center;"><span style="color:#ef4444;font-weight:700;">-{{ number_format($so->quantity) }}</span></td>
                    <td style="text-align:right;font-size:13px;">Rp {{ number_format($so->selling_price, 0, ',', '.') }}</td>
                    <td style="text-align:right;font-weight:600;font-size:13px;">Rp {{ number_format($so->total_price, 0, ',', '.') }}</td>
                    <td style="font-size:12px;color:#64748b;">{{ $so->customer_name ?? '-' }}</td>
                    <td style="font-size:12px;color:#64748b;white-space:nowrap;">{{ $so->transaction_date->format('d M Y') }}</td>
                    <td style="font-size:12px;color:#64748b;">{{ $so->user->name ?? '-' }}</td>
                    <td style="text-align:center;">
                        <div class="btn-group" style="justify-content:center;">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="showDetail({{ $so->id }})"><i class="fas fa-eye"></i></button>
                            <form method="POST" action="{{ route('stock-out.destroy', $so) }}" onsubmit="return confirmDelete(this)">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="11"><div class="empty-state"><i class="fas fa-inbox"></i><p>Belum ada data stok keluar.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <span>{{ $stockOuts->firstItem() ?? 0 }}-{{ $stockOuts->lastItem() ?? 0 }} dari {{ $stockOuts->total() }}</span>
        {{ $stockOuts->links('vendor.pagination.simple') }}
    </div>
</div>

{{-- Data untuk modal --}}
{{-- Data untuk modal --}}
@php
$stockOutJson = $stockOuts->keyBy('id')->map(function($so) {
    return [
        'id'               => $so->id,
        'reference_number' => $so->reference_number,
        'transaction_date' => $so->transaction_date->format('d F Y'),
        'product_name'     => $so->product->name ?? '-',
        'product_code'     => $so->product->code ?? '-',
        'unit_symbol'      => $so->product->unit->symbol ?? '',
        'type_label'       => $so->type_label,
        'type'             => $so->type,
        'customer_name'    => $so->customer_name ?? '-',
        'quantity'         => number_format($so->quantity),
        'selling_price'    => 'Rp ' . number_format($so->selling_price, 0, ',', '.'),
        'total_price'      => 'Rp ' . number_format($so->total_price, 0, ',', '.'),
        'user_name'        => $so->user->name ?? '-',
        'notes'            => $so->notes ?? '-',
        'created_at'       => $so->created_at->format('d M Y H:i'),
        'destroy_url'      => route('stock-out.destroy', $so->id),
    ];
})->toArray();
@endphp
<script>
const stockOutData = {!! json_encode($stockOutJson) !!};
</script>

{{-- -- MODAL DETAIL ------------------------------------ --}}
<div class="modal-backdrop" id="modalDetail">
    <div class="modal-box" style="max-width:520px;">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fas fa-arrow-circle-up" style="color:#ef4444"></i>
                <span id="modalDetailTitle">Detail Stok Keluar</span>
                <span id="modalDetailBadge"></span>
            </div>
            <button class="modal-close" onclick="closeModal('modalDetail')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="modalDetailBody"></div>
        <div class="modal-footer" id="modalDetailFooter"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const typeColors = { sale:'info', return:'warning', damaged:'danger', other:'secondary' };
const typeLabels = { sale:'Penjualan', return:'Retur', damaged:'Rusak/Hilang', other:'Lainnya' };

function showDetail(id) {
    const d = stockOutData[id];
    if (!d) return;

    document.getElementById('modalDetailTitle').textContent = d.reference_number;
    document.getElementById('modalDetailBadge').innerHTML =
        `<span class="badge badge-${typeColors[d.type] || 'secondary'}" style="margin-left:8px;">${d.type_label}</span>`;

    const rows = [
        ['No. Referensi', `<strong>${d.reference_number}</strong>`],
        ['Tanggal',       d.transaction_date],
        ['Produk',        d.product_name],
        ['Kode Produk',   d.product_code],
        ['Tipe Keluar',   `<span class="badge badge-${typeColors[d.type] || 'secondary'}">${d.type_label}</span>`],
        ['Pelanggan',     d.customer_name],
        ['Jumlah',        `<span style="color:#ef4444;font-weight:700;">-${d.quantity} ${d.unit_symbol}</span>`],
        ['Harga Jual/Unit', d.selling_price],
        ['Total Nilai',   `<strong>${d.total_price}</strong>`],
        ['Dicatat Oleh',  d.user_name],
        ['Catatan',       d.notes],
    ];

    document.getElementById('modalDetailBody').innerHTML = rows.map(([l, v]) =>
        `<div class="detail-row"><span class="detail-label">${l}</span><span class="detail-value">${v}</span></div>`
    ).join('') + `<div style="margin-top:12px;font-size:11px;color:#94a3b8;">Dibuat: ${d.created_at}</div>`;

    document.getElementById('modalDetailFooter').innerHTML = `
        <button type="button" class="btn btn-secondary" onclick="closeModal('modalDetail')">Tutup</button>
        <form method="POST" action="${d.destroy_url}" onsubmit="return confirmDelete(this)" style="display:inline;">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="_method" value="DELETE">
            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Hapus</button>
        </form>`;

    openModal('modalDetail');
}
</script>
@endpush
