@extends('layouts.app')
@section('title', 'Laporan per Kasir')
@push('breadcrumb_content', 'Laporan / <strong>Per Kasir</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Laporan Transaksi per Kasir</div>
        <div class="page-subtitle">Ringkasan performa masing-masing kasir</div>
    </div>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET"><div class="filter-bar">
            <select name="user_id" class="form-control" style="width:180px;">
                <option value="">Semua Kasir</option>
                @foreach($kasirs as $k)
                <option value="{{ $k->id }}" {{ request('user_id')==$k->id?'selected':'' }}>{{ $k->name }}</option>
                @endforeach
            </select>
            <select name="payment_method_id" class="form-control" style="width:160px;">
                <option value="">Semua Metode</option>
                @foreach($paymentMethods as $m)
                <option value="{{ $m->id }}" {{ request('payment_method_id')==$m->id?'selected':'' }}>{{ $m->name }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" class="form-control" style="width:155px;" value="{{ request('date_from') }}">
            <input type="date" name="date_to" class="form-control" style="width:155px;" value="{{ request('date_to') }}">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('reports.kasir') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
        </div></form>
    </div>
</div>

{{-- Ringkasan per kasir --}}
<div class="grid grid-4" style="margin-bottom:20px;">
    @foreach($kasirSummary as $s)
    <div class="stat-card">
        <div style="width:40px;height:40px;border-radius:50%;overflow:hidden;flex-shrink:0;">
            <img src="{{ $s->user?->avatar_url }}" alt="" style="width:100%;height:100%;object-fit:cover;">
        </div>
        <div class="stat-content">
            <div style="font-weight:700;font-size:13px;color:#0f172a;">{{ $s->user?->name ?? 'Unknown' }}</div>
            <div style="font-size:11px;color:#64748b;margin-bottom:4px;">{{ $s->user?->role_label ?? '' }}</div>
            <div style="font-size:18px;font-weight:700;color:#10b981;">Rp {{ number_format($s->total_revenue, 0, ',', '.') }}</div>
            <div style="font-size:11px;color:#94a3b8;">{{ $s->total_transactions }} transaksi</div>
        </div>
    </div>
    @endforeach
</div>

{{-- Daftar transaksi --}}
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-list" style="color:var(--primary)"></i> Detail Transaksi</div>
        <span style="font-size:12px;color:#94a3b8;">{{ $transactions->total() }} transaksi</span>
    </div>
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th>Invoice</th><th>Kasir</th><th>Shift</th>
                <th>Pelanggan</th>
                <th style="text-align:center;">Item</th>
                <th>Metode</th>
                <th style="text-align:right;">Total</th>
                <th style="text-align:right;">Diskon</th>
                <th>Waktu</th>
            </tr></thead>
            <tbody>
                @forelse($transactions as $t)
                <tr>
                    <td>
                        <code style="font-size:11px;background:#f1f5f9;padding:2px 5px;border-radius:4px;">{{ $t->invoice_number }}</code>
                    </td>
                    <td style="font-size:12px;font-weight:600;">{{ $t->user->name ?? '-' }}</td>
                    <td style="font-size:11px;color:#64748b;">{{ $t->shift->shift_number ?? '-' }}</td>
                    <td style="font-size:12px;color:#64748b;">{{ $t->customer_name ?? '-' }}</td>
                    <td style="text-align:center;">
                        <button type="button" class="badge badge-secondary" style="cursor:pointer;border:none;font-size:11px;padding:4px 8px;"
                            onclick="showTxDetail({{ $t->id }})">
                            {{ $t->items_count }} item
                        </button>
                    </td>
                    <td style="font-size:12px;">{{ $t->paymentMethod->name ?? '-' }}</td>
                    <td style="text-align:right;font-weight:700;color:#10b981;font-size:13px;">Rp {{ number_format($t->grand_total, 0, ',', '.') }}</td>
                    <td style="text-align:right;font-size:12px;color:#94a3b8;">
                        {{ $t->discount_amount > 0 ? 'Rp ' . number_format($t->discount_amount, 0, ',', '.') : '-' }}
                    </td>
                    <td style="font-size:11.5px;color:#64748b;white-space:nowrap;">{{ $t->transaction_at->format('d M Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="9"><div class="empty-state"><i class="fas fa-inbox"></i><p>Belum ada data transaksi.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <span>{{ $transactions->firstItem() ?? 0 }}-{{ $transactions->lastItem() ?? 0 }} dari {{ $transactions->total() }}</span>
        {{ $transactions->links('vendor.pagination.simple') }}
    </div>
</div>

{{-- Modal Detail Item Transaksi --}}
<div class="modal-backdrop" id="modalTxDetail">
    <div class="modal-box" style="max-width:600px;">
        <div class="modal-header">
            <div class="modal-title" id="txDetailTitle"><i class="fas fa-receipt" style="color:var(--primary)"></i> Detail Transaksi</div>
            <button class="modal-close" onclick="closeModal('modalTxDetail')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="txDetailBody"></div>
        <div class="modal-footer" id="txDetailFooter">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modalTxDetail')">Tutup</button>
        </div>
    </div>
</div>

@php
$txData = $transactions->keyBy('id')->map(function($t) {
    return [
        'invoice'       => $t->invoice_number,
        'kasir'         => $t->user->name ?? '-',
        'shift'         => $t->shift->shift_number ?? '-',
        'pelanggan'     => $t->customer_name ?? '-',
        'metode'        => $t->paymentMethod->name ?? '-',
        'subtotal'      => 'Rp ' . number_format($t->subtotal, 0, ',', '.'),
        'diskon'        => $t->discount_amount > 0 ? 'Rp ' . number_format($t->discount_amount, 0, ',', '.') : null,
        'grand_total'   => 'Rp ' . number_format($t->grand_total, 0, ',', '.'),
        'bayar'         => 'Rp ' . number_format($t->amount_paid, 0, ',', '.'),
        'kembalian'     => 'Rp ' . number_format($t->change_amount, 0, ',', '.'),
        'status'        => $t->status_label,
        'status_color'  => $t->status_color,
        'waktu'         => $t->transaction_at->format('d F Y, H:i:s'),
        'receipt_url'   => route('pos.receipt', $t->id),
        'items'         => $t->items->map(function($item) {
            return [
                'name'     => $item->product_name,
                'code'     => $item->product_code,
                'qty'      => $item->quantity,
                'unit'     => $item->product->unit->symbol ?? 'pcs',
                'price'    => 'Rp ' . number_format($item->unit_price, 0, ',', '.'),
                'discount' => $item->discount_amount > 0 ? 'Rp ' . number_format($item->discount_amount, 0, ',', '.') : null,
                'subtotal' => 'Rp ' . number_format($item->subtotal, 0, ',', '.'),
            ];
        })->toArray(),
    ];
})->toArray();
@endphp

@push('scripts')
<script>
const txData = {!! json_encode($txData) !!};

function showTxDetail(id) {
    const t = txData[id]; if (!t) return;
    document.getElementById('txDetailTitle').innerHTML =
        '<i class="fas fa-receipt" style="color:var(--primary)"></i> ' + t.invoice +
        ' &nbsp;<span class="badge badge-' + t.status_color + '">' + t.status + '</span>';

    let itemsHtml = `<div style="margin-bottom:16px;">
        <table style="width:100%;border-collapse:collapse;">
            <thead><tr style="background:#f8fafc;">
                <th style="padding:8px 12px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;text-align:left;">Produk</th>
                <th style="padding:8px 12px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;text-align:center;">Qty</th>
                <th style="padding:8px 12px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;text-align:right;">Harga</th>
                <th style="padding:8px 12px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;text-align:right;">Subtotal</th>
            </tr></thead><tbody>`;

    t.items.forEach(item => {
        itemsHtml += `<tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:10px 12px;">
                <div style="font-weight:600;font-size:13px;">${item.name}</div>
                <div style="font-size:11px;color:#94a3b8;">${item.code}</div>
                ${item.discount ? '<div style="font-size:11px;color:#10b981;">Diskon: -' + item.discount + '</div>' : ''}
            </td>
            <td style="padding:10px 12px;text-align:center;font-weight:600;">${item.qty} ${item.unit}</td>
            <td style="padding:10px 12px;text-align:right;font-size:13px;">${item.price}</td>
            <td style="padding:10px 12px;text-align:right;font-weight:700;">${item.subtotal}</td>
        </tr>`;
    });

    itemsHtml += '</tbody></table></div>';

    const infoRows = [['Kasir', t.kasir], ['Shift', t.shift], ['Pelanggan', t.pelanggan], ['Waktu', t.waktu]];
    let infoHtml = '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;">';
    infoRows.forEach(([l, v]) => {
        infoHtml += `<div><span style="font-size:11px;color:#94a3b8;">${l}</span><div style="font-size:13px;font-weight:500;">${v}</div></div>`;
    });
    infoHtml += '</div>';

    const totalsHtml = `
        <div style="background:#f8fafc;border-radius:8px;padding:12px 16px;">
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;"><span style="color:#64748b;">Subtotal</span><span>${t.subtotal}</span></div>
            ${t.diskon ? '<div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;"><span style="color:#10b981;">Diskon</span><span style="color:#10b981;">-' + t.diskon + '</span></div>' : ''}
            <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:700;padding-top:8px;border-top:1px solid #e2e8f0;"><span>Total</span><span>${t.grand_total}</span></div>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-top:4px;"><span style="color:#64748b;">Bayar (${t.metode})</span><span>${t.bayar}</span></div>
            <div style="display:flex;justify-content:space-between;font-size:14px;font-weight:700;color:#10b981;margin-top:4px;"><span>Kembalian</span><span>${t.kembalian}</span></div>
        </div>`;

    document.getElementById('txDetailBody').innerHTML = infoHtml + itemsHtml + totalsHtml;
    document.getElementById('txDetailFooter').innerHTML = `
        <button type="button" class="btn btn-secondary" onclick="closeModal('modalTxDetail')">Tutup</button>
        <a href="${t.receipt_url}" target="_blank" class="btn btn-outline"><i class="fas fa-file-pdf"></i> Struk PDF</a>`;

    openModal('modalTxDetail');
}
</script>
@endpush
@endsection
