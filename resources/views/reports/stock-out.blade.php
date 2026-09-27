@extends('layouts.app')
@section('title', 'Laporan Stok Keluar')
@push('breadcrumb_content', 'Laporan / <strong>Stok Keluar</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Laporan Stok Keluar</div>
        <div class="page-subtitle">Riwayat seluruh pengeluaran barang dari gudang</div>
    </div>
    <div class="btn-group">
        <a href="{{ route('reports.stock-out.pdf', request()->query()) }}" class="btn btn-danger" target="_blank">
            <i class="fas fa-file-pdf"></i> PDF
        </a>
        <a href="{{ route('reports.stock-out.excel', request()->query()) }}" class="btn btn-success">
            <i class="fas fa-file-excel"></i> Excel
        </a>
    </div>
</div>

<div class="grid grid-2" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-arrow-circle-up"></i></div>
        <div class="stat-content"><div class="stat-value">{{ number_format($totalQty) }}</div><div class="stat-label">Total Unit Keluar</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-content">
            <div class="stat-value" style="font-size:18px;">Rp {{ number_format($totalValue,0,',','.') }}</div>
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
            <div style="display:flex;align-items:center;gap:6px;font-size:13px;color:#64748b;">
                <span>Dari</span>
                <input type="date" name="date_from" class="form-control" style="width:155px;" value="{{ request('date_from') }}">
                <span>s/d</span>
                <input type="date" name="date_to" class="form-control" style="width:155px;" value="{{ request('date_to') }}">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('reports.stock-out') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
        </div></form>
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th width="50">No</th><th>No. Referensi</th><th>Tanggal</th>
                <th>Produk</th><th style="text-align:center;">Tipe</th>
                <th>Pelanggan</th>
                <th style="text-align:center;">Qty</th>
                <th style="text-align:right;">Harga Jual</th>
                <th style="text-align:right;">Total</th>
                <th>Oleh</th>
            </tr></thead>
            <tbody>
                @forelse($stockOuts as $i => $so)
                @php $tc=['sale'=>'info','return'=>'warning','damaged'=>'danger','other'=>'secondary']; @endphp
                <tr>
                    <td style="color:#94a3b8;">{{ $stockOuts->firstItem()+$i }}</td>
                    <td><code style="font-size:11px;background:#f1f5f9;padding:2px 6px;border-radius:4px;">{{ $so->reference_number }}</code></td>
                    <td style="font-size:12px;white-space:nowrap;">{{ $so->transaction_date->format('d M Y') }}</td>
                    <td>
                        <div style="font-weight:600;font-size:13px;">{{ $so->product->name??'-' }}</div>
                        <div style="font-size:11px;color:#94a3b8;">{{ $so->product->category->name??'' }}</div>
                    </td>
                    <td style="text-align:center;"><span class="badge badge-{{ $tc[$so->type]??'secondary' }}">{{ $so->type_label }}</span></td>
                    <td style="font-size:12px;color:#64748b;">{{ $so->customer_name??'-' }}</td>
                    <td style="text-align:center;color:#ef4444;font-weight:700;">-{{ number_format($so->quantity) }}</td>
                    <td style="text-align:right;font-size:13px;">Rp {{ number_format($so->selling_price,0,',','.') }}</td>
                    <td style="text-align:right;font-weight:600;">Rp {{ number_format($so->total_price,0,',','.') }}</td>
                    <td style="font-size:12px;color:#64748b;">{{ $so->user->name??'-' }}</td>
                </tr>
                @empty
                <tr><td colspan="10"><div class="empty-state"><i class="fas fa-inbox"></i><p>Tidak ada data.</p></div></td></tr>
                @endforelse
            </tbody>
            @if($stockOuts->count())
            <tfoot><tr style="background:#f8fafc;">
                <td colspan="6" style="text-align:right;font-weight:700;font-size:13px;padding:12px 16px;">Total:</td>
                <td style="text-align:center;font-weight:700;color:#ef4444;padding:12px 16px;">{{ number_format($totalQty) }}</td>
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
        <span>{{ $stockOuts->firstItem()??0 }}-{{ $stockOuts->lastItem()??0 }} dari {{ $stockOuts->total() }}</span>
        {{ $stockOuts->links('vendor.pagination.simple') }}
    </div>
</div>
@endsection
