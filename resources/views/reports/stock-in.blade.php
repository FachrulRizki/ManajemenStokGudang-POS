@extends('layouts.app')
@section('title', 'Laporan Stok Masuk')
@push('breadcrumb_content', 'Laporan / <strong>Stok Masuk</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Laporan Stok Masuk</div>
        <div class="page-subtitle">Riwayat seluruh penerimaan barang</div>
    </div>
    <div class="btn-group">
        <a href="{{ route('reports.stock-in.pdf', request()->query()) }}" class="btn btn-danger" target="_blank">
            <i class="fas fa-file-pdf"></i> PDF
        </a>
        <a href="{{ route('reports.stock-in.excel', request()->query()) }}" class="btn btn-success">
            <i class="fas fa-file-excel"></i> Excel
        </a>
    </div>
</div>

<div class="grid grid-2" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-arrow-circle-down"></i></div>
        <div class="stat-content"><div class="stat-value">{{ number_format($totalQty) }}</div><div class="stat-label">Total Unit Masuk</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-content">
            <div class="stat-value" style="font-size:18px;">Rp {{ number_format($totalValue,0,',','.') }}</div>
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
            <div style="display:flex;align-items:center;gap:6px;font-size:13px;color:#64748b;">
                <span>Dari</span>
                <input type="date" name="date_from" class="form-control" style="width:155px;" value="{{ request('date_from') }}">
                <span>s/d</span>
                <input type="date" name="date_to" class="form-control" style="width:155px;" value="{{ request('date_to') }}">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('reports.stock-in') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
        </div></form>
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th width="50">No</th><th>No. Referensi</th><th>Tanggal</th>
                <th>Produk</th><th>Supplier</th>
                <th style="text-align:center;">Qty</th>
                <th style="text-align:right;">Harga Beli</th>
                <th style="text-align:right;">Total</th>
                <th>Dicatat Oleh</th>
            </tr></thead>
            <tbody>
                @forelse($stockIns as $i => $si)
                <tr>
                    <td style="color:#94a3b8;">{{ $stockIns->firstItem()+$i }}</td>
                    <td><code style="font-size:11px;background:#f1f5f9;padding:2px 6px;border-radius:4px;">{{ $si->reference_number }}</code></td>
                    <td style="font-size:12px;white-space:nowrap;">{{ $si->transaction_date->format('d M Y') }}</td>
                    <td>
                        <div style="font-weight:600;font-size:13px;">{{ $si->product->name ?? '-' }}</div>
                        <div style="font-size:11px;color:#94a3b8;">{{ $si->product->category->name ?? '' }}</div>
                    </td>
                    <td style="font-size:13px;color:#64748b;">{{ $si->supplier->name ?? '-' }}</td>
                    <td style="text-align:center;color:#10b981;font-weight:700;">+{{ number_format($si->quantity) }}</td>
                    <td style="text-align:right;font-size:13px;">Rp {{ number_format($si->purchase_price,0,',','.') }}</td>
                    <td style="text-align:right;font-weight:600;">Rp {{ number_format($si->total_price,0,',','.') }}</td>
                    <td style="font-size:12px;color:#64748b;">{{ $si->user->name ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="9"><div class="empty-state"><i class="fas fa-inbox"></i><p>Tidak ada data.</p></div></td></tr>
                @endforelse
            </tbody>
            @if($stockIns->count())
            <tfoot><tr style="background:#f8fafc;">
                <td colspan="5" style="text-align:right;font-weight:700;font-size:13px;padding:12px 16px;">Total:</td>
                <td style="text-align:center;font-weight:700;color:#10b981;padding:12px 16px;">{{ number_format($totalQty) }}</td>
                <td></td>
                <td style="text-align:right;font-weight:700;font-size:14px;color:var(--primary);padding:12px 16px;">
                    Rp {{ number_format($totalValue,0,',','.') }}
                </td>
                <td></td>
            </tr></tfoot>
            @endif
        </table>
    </div>
    <div class="pagination-wrap">
        <span>{{ $stockIns->firstItem()??0 }}-{{ $stockIns->lastItem()??0 }} dari {{ $stockIns->total() }}</span>
        {{ $stockIns->links('vendor.pagination.simple') }}
    </div>
</div>
@endsection
