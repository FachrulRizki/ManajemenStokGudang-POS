@extends('layouts.app')
@section('title', 'Stok Masuk')
@push('breadcrumb_content', 'Transaksi / <strong>Stok Masuk</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Stok Masuk</div>
        <div class="page-subtitle">Catat penerimaan barang dari supplier</div>
    </div>
    <div class="btn-group">
        <a href="{{ route('stock-in.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Catat Stok Masuk</a>
        <a href="{{ route('stock-in.scan') }}" class="btn btn-primary"><i class="fas fa-barcode"></i> Scan Barcode</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-boxes"></i></div>
        <div class="stat-content"><div class="stat-value">{{ number_format($totalQty) }}</div><div class="stat-label">Total Unit Diterima</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-content">
            <div class="stat-value" style="font-size:18px;">Rp {{ number_format($totalValue, 0, ',', '.') }}</div>
            <div class="stat-label">Total Nilai Pembelian</div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET"><div class="filter-bar">
            <div class="search-input"><i class="fas fa-search"></i>
                <input type="text" name="search" class="form-control" placeholder="No. referensi, nama produk..." value="{{ request('search') }}">
            </div>
            <input type="date" name="date_from" class="form-control" style="width:155px;" value="{{ request('date_from') }}">
            <input type="date" name="date_to" class="form-control" style="width:155px;" value="{{ request('date_to') }}">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('stock-in.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
        </div></form>
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th width="50">No</th><th>No. Referensi</th><th>Produk</th><th>Supplier</th>
                <th style="text-align:center;">Qty</th><th style="text-align:right;">Harga Beli</th>
                <th style="text-align:right;">Total</th><th>Tanggal</th><th>Dicatat Oleh</th>
                <th style="text-align:center;">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse($stockIns as $i => $si)
                <tr>
                    <td style="color:#94a3b8;">{{ $stockIns->firstItem() + $i }}</td>
                    <td>
                        <button type="button" class="btn btn-sm" style="background:none;border:none;padding:0;cursor:pointer;font-family:monospace;font-size:11.5px;background:#f1f5f9;padding:2px 6px;border-radius:4px;color:#374151;"
                            onclick="showDetail('si', {{ $si->id }})">
                            {{ $si->reference_number }}
                        </button>
                    </td>
                    <td>
                        <div style="font-weight:600;font-size:13px;">{{ $si->product->name ?? '-' }}</div>
                        <div style="font-size:11px;color:#94a3b8;">{{ $si->product->code ?? '' }}</div>
                    </td>
                    <td style="font-size:13px;color:#64748b;">{{ $si->supplier->name ?? '-' }}</td>
                    <td style="text-align:center;"><span style="color:#10b981;font-weight:700;font-size:14px;">+{{ number_format($si->quantity) }}</span></td>
                    <td style="text-align:right;font-size:13px;">Rp {{ number_format($si->purchase_price, 0, ',', '.') }}</td>
                    <td style="text-align:right;font-weight:600;font-size:13px;">Rp {{ number_format($si->total_price, 0, ',', '.') }}</td>
                    <td style="font-size:12px;color:#64748b;white-space:nowrap;">{{ $si->transaction_date->format('d M Y') }}</td>
                    <td style="font-size:12px;color:#64748b;">{{ $si->user->name ?? '-' }}</td>
                    <td style="text-align:center;">
                        <div class="btn-group" style="justify-content:center;">
                            <button type="button" class="btn btn-sm btn-secondary" title="Detail" onclick="showDetail('si', {{ $si->id }})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <form method="POST" action="{{ route('stock-in.destroy', $si) }}" onsubmit="return confirmDelete(this)">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10"><div class="empty-state"><i class="fas fa-inbox"></i><p>Belum ada data stok masuk.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <span>{{ $stockIns->firstItem() ?? 0 }}-{{ $stockIns->lastItem() ?? 0 }} dari {{ $stockIns->total() }}</span>
        {{ $stockIns->links('vendor.pagination.simple') }}
    </div>
</div>

{{-- Data untuk modal --}}
@php
$stockInJson = $stockIns->keyBy('id')->map(function($si) {
    return [
        'id'               => $si->id,
        'reference_number' => $si->reference_number,
        'transaction_date' => $si->transaction_date->format('d F Y'),
        'product_name'     => $si->product->name ?? '-',
        'product_code'     => $si->product->code ?? '-',
        'unit_symbol'      => $si->product->unit->symbol ?? '',
        'supplier_name'    => $si->supplier->name ?? '-',
        'quantity'         => number_format($si->quantity),
        'purchase_price'   => 'Rp ' . number_format($si->purchase_price, 0, ',', '.'),
        'total_price'      => 'Rp ' . number_format($si->total_price, 0, ',', '.'),
        'invoice_number'   => $si->invoice_number ?? '-',
        'user_name'        => $si->user->name ?? '-',
        'notes'            => $si->notes ?? '-',
        'created_at'       => $si->created_at->format('d M Y H:i'),
        'destroy_url'      => route('stock-in.destroy', $si->id),
    ];
})->toArray();
@endphp
<script>
const stockInData = {!! json_encode($stockInJson) !!};
</script>

{{-- -- MODAL DETAIL ------------------------------------ --}}
<div class="modal-backdrop" id="modalDetail">
    <div class="modal-box" style="max-width:520px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-arrow-circle-down" style="color:#10b981"></i> <span id="modalDetailTitle">Detail Stok Masuk</span></div>
            <button class="modal-close" onclick="closeModal('modalDetail')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="modalDetailBody"></div>
        <div class="modal-footer" id="modalDetailFooter">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modalDetail')">Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showDetail(type, id) {
    const d = stockInData[id];
    if (!d) return;

    document.getElementById('modalDetailTitle').textContent = d.reference_number;

    const rows = [
        ['No. Referensi', `<strong>${d.reference_number}</strong>`],
        ['Tanggal',       d.transaction_date],
        ['Produk',        d.product_name],
        ['Kode Produk',   d.product_code],
        ['Supplier',      d.supplier_name],
        ['Jumlah',        `<span style="color:#10b981;font-weight:700;">+${d.quantity} ${d.unit_symbol}</span>`],
        ['Harga Beli/Unit', d.purchase_price],
        ['Total Nilai',   `<strong>${d.total_price}</strong>`],
        ['No. Invoice',   d.invoice_number],
        ['Dicatat Oleh',  d.user_name],
        ['Catatan',       d.notes],
    ];

    document.getElementById('modalDetailBody').innerHTML = rows.map(([l, v]) =>
        `<div class="detail-row"><span class="detail-label">${l}</span><span class="detail-value">${v}</span></div>`
    ).join('') + `<div style="margin-top:12px;font-size:11px;color:#94a3b8;">Dibuat: ${d.created_at}</div>`;

    document.getElementById('modalDetailFooter').innerHTML = `
        <span style="font-size:12px;color:#94a3b8;flex:1;"></span>
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
