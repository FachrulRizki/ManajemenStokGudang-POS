@extends('layouts.app')
@section('title', 'Stok per Rak')
@push('breadcrumb_content', 'Laporan / <strong>Stok per Rak</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Stok per Rak / Lokasi</div>
        <div class="page-subtitle">Distribusi stok berdasarkan lokasi rak di gudang</div>
    </div>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET"><div class="filter-bar">
            <select name="warehouse_id" id="warehouseFilter" class="form-control" style="width:180px;" onchange="this.form.submit()">
                <option value="">Semua Gudang</option>
                @foreach($warehouses as $wh)
                <option value="{{ $wh->id }}" {{ request('warehouse_id')==$wh->id?'selected':'' }}>{{ $wh->name }}</option>
                @endforeach
            </select>
            <select name="rack_id" class="form-control" style="width:160px;">
                <option value="">Semua Rak</option>
                @foreach($warehouses as $wh)
                @foreach($wh->racks as $rack)
                <option value="{{ $rack->id }}" {{ request('rack_id')==$rack->id?'selected':'' }}>
                    {{ $wh->name }} › {{ $rack->name }}
                </option>
                @endforeach
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('reports.stock-by-rack') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
        </div></form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-th-large" style="color:var(--primary)"></i> Produk per Rak</div>
        <span style="font-size:12px;color:#94a3b8;">{{ $products->total() }} produk</span>
    </div>
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th>Produk</th>
                <th>Kategori</th>
                <th>Gudang</th>
                <th>Rak</th>
                <th>Lokasi Teks</th>
                <th style="text-align:center;">Stok</th>
                <th style="text-align:center;">Min. Stok</th>
                <th style="text-align:center;">Status</th>
                <th style="text-align:right;">Nilai Stok</th>
            </tr></thead>
            <tbody>
                @forelse($products as $p)
                <tr>
                    <td>
                        <div style="font-weight:600;font-size:13px;">{{ $p->name }}</div>
                        <div style="font-size:11px;color:#94a3b8;">{{ $p->code }}</div>
                    </td>
                    <td><span class="badge badge-secondary">{{ $p->category->name ?? '-' }}</span></td>
                    <td style="font-size:12px;color:#64748b;">{{ $p->rack->warehouse->name ?? '-' }}</td>
                    <td>
                        @if($p->rack)
                            <span class="badge badge-info">{{ $p->rack->name }}</span>
                        @else
                            <span style="color:#94a3b8;font-size:12px;">Belum ditentukan</span>
                        @endif
                    </td>
                    <td style="font-size:12px;color:#64748b;">{{ $p->rack_location ?: '-' }}</td>
                    <td style="text-align:center;">
                        <span style="font-size:15px;font-weight:700;color:{{ $p->stock <= 0 ? '#dc2626' : ($p->isLowStock() ? '#d97706' : '#10b981') }};">
                            {{ number_format($p->stock) }}
                        </span>
                        <span style="font-size:10px;color:#94a3b8;"> {{ $p->unit->symbol ?? '' }}</span>
                    </td>
                    <td style="text-align:center;color:#64748b;">{{ $p->min_stock }}</td>
                    <td style="text-align:center;">
                        @if($p->stock <= 0)
                            <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Habis</span>
                        @elseif($p->isLowStock())
                            <span class="badge badge-warning"><i class="fas fa-exclamation"></i> Menipis</span>
                        @else
                            <span class="badge badge-success"><i class="fas fa-check"></i> Normal</span>
                        @endif
                    </td>
                    <td style="text-align:right;font-size:13px;">Rp {{ number_format($p->stock * $p->purchase_price, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="9"><div class="empty-state"><i class="fas fa-boxes"></i><p>Tidak ada produk untuk filter ini.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <span>{{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} dari {{ $products->total() }}</span>
        {{ $products->links('vendor.pagination.simple') }}
    </div>
</div>
@endsection
