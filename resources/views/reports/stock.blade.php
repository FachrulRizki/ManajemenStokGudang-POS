@extends('layouts.app')
@section('title', 'Laporan Stok')
@push('breadcrumb_content', 'Laporan / <strong>Laporan Stok</strong>')
@push('styles')
<style>
.report-tab-nav {
    display: flex; gap: 0; border-bottom: 2px solid #e2e8f0; margin-bottom: 20px; overflow-x: auto;
}
.report-tab-btn {
    padding: 10px 18px; font-size: 13px; font-weight: 500; color: #64748b;
    background: none; border: none; border-bottom: 2px solid transparent;
    margin-bottom: -2px; cursor: pointer; white-space: nowrap; transition: all .15s;
    display: flex; align-items: center; gap: 7px; text-decoration: none;
}
.report-tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }
.report-tab-btn:hover:not(.active) { color: #374151; background: #f8fafc; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Laporan Stok</div>
        <div class="page-subtitle">Stok barang, transaksi masuk/keluar, terlaris, dan per rak</div>
    </div>
    <div class="btn-group" id="exportBtns">
        @if($tab === 'stok')
        <a href="{{ route('reports.stock.pdf', request()->query()) }}" class="btn btn-danger" target="_blank"><i class="fas fa-file-pdf"></i> PDF</a>
        <a href="{{ route('reports.stock.excel', request()->query()) }}" class="btn btn-success"><i class="fas fa-file-excel"></i> Excel</a>
        @elseif($tab === 'masuk')
        <a href="{{ route('reports.stock-in.pdf', request()->query()) }}" class="btn btn-danger" target="_blank"><i class="fas fa-file-pdf"></i> PDF</a>
        <a href="{{ route('reports.stock-in.excel', request()->query()) }}" class="btn btn-success"><i class="fas fa-file-excel"></i> Excel</a>
        @elseif($tab === 'keluar')
        <a href="{{ route('reports.stock-out.pdf', request()->query()) }}" class="btn btn-danger" target="_blank"><i class="fas fa-file-pdf"></i> PDF</a>
        <a href="{{ route('reports.stock-out.excel', request()->query()) }}" class="btn btn-success"><i class="fas fa-file-excel"></i> Excel</a>
        @endif
    </div>
</div>

{{-- -- Tab Navigation -------------------------------- --}}
<div class="report-tab-nav">
    <a href="{{ route('reports.stock', array_merge(request()->except(['tab','page','si_page','so_page','rack_page']), ['tab'=>'stok'])) }}"
        class="report-tab-btn {{ $tab==='stok' ? 'active' : '' }}">
        <i class="fas fa-boxes"></i> Stok Barang
    </a>
    <a href="{{ route('reports.stock', array_merge(request()->except(['tab','page','si_page','so_page','rack_page']), ['tab'=>'masuk'])) }}"
        class="report-tab-btn {{ $tab==='masuk' ? 'active' : '' }}">
        <i class="fas fa-arrow-circle-down" style="color:#10b981;"></i> Stok Masuk
    </a>
    <a href="{{ route('reports.stock', array_merge(request()->except(['tab','page','si_page','so_page','rack_page']), ['tab'=>'keluar'])) }}"
        class="report-tab-btn {{ $tab==='keluar' ? 'active' : '' }}">
        <i class="fas fa-arrow-circle-up" style="color:#ef4444;"></i> Stok Keluar
    </a>
    <a href="{{ route('reports.stock', array_merge(request()->except(['tab','page','si_page','so_page','rack_page']), ['tab'=>'terlaris'])) }}"
        class="report-tab-btn {{ $tab==='terlaris' ? 'active' : '' }}">
        <i class="fas fa-fire" style="color:#f97316;"></i> Produk Terlaris
    </a>
    <a href="{{ route('reports.stock', array_merge(request()->except(['tab','page','si_page','so_page','rack_page']), ['tab'=>'rak'])) }}"
        class="report-tab-btn {{ $tab==='rak' ? 'active' : '' }}">
        <i class="fas fa-th-large"></i> Per Rak
    </a>
</div>

{{-- ================ TAB: STOK BARANG ================ --}}
@if($tab === 'stok')

<div class="grid grid-4" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-boxes"></i></div>
        <div class="stat-content"><div class="stat-value">{{ number_format($products->total()) }}</div><div class="stat-label">Total Produk</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-check-circle"></i></div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($products->getCollection()->filter(function($p){ return $p->stock > $p->min_stock; })->count()) }}</div>
            <div class="stat-label">Stok Normal</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($products->getCollection()->filter(function($p){ return $p->stock > 0 && $p->stock <= $p->min_stock; })->count()) }}</div>
            <div class="stat-label">Stok Menipis</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-content"><div class="stat-value" style="font-size:16px;">Rp {{ number_format($totalValue,0,',','.') }}</div><div class="stat-label">Nilai Total Stok</div></div>
    </div>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET">
            <input type="hidden" name="tab" value="stok">
            <div class="filter-bar">
                <div class="search-input"><i class="fas fa-search"></i>
                    <input type="text" name="search" class="form-control" placeholder="Kode atau nama produk..." value="{{ request('search') }}">
                </div>
                <select name="category_id" class="form-control" style="width:200px;">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-control" style="width:150px;">
                    <option value="">Semua Status</option>
                    <option value="low" {{ request('status')=='low'?'selected':'' }}>⚠ Menipis</option>
                    <option value="out" {{ request('status')=='out'?'selected':'' }}>✗ Habis</option>
                </select>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                <a href="{{ route('reports.stock', ['tab'=>'stok']) }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th>No</th><th>Kode</th><th>Nama Produk</th><th>Kategori</th><th>Rak</th>
                <th style="text-align:center;">Stok</th><th style="text-align:center;">Min</th>
                <th style="text-align:right;">Harga Beli</th><th style="text-align:right;">Harga Jual</th>
                <th style="text-align:right;">Nilai Stok</th><th style="text-align:center;">Status</th>
            </tr></thead>
            <tbody>
                @forelse($products as $i => $p)
                <tr>
                    <td style="color:#94a3b8;">{{ $products->firstItem() + $i }}</td>
                    <td><code style="font-size:11px;background:#f1f5f9;padding:2px 6px;border-radius:4px;">{{ $p->code }}</code></td>
                    <td>
                        <a href="{{ route('products.show', $p) }}" style="font-weight:600;font-size:13px;color:#1e293b;text-decoration:none;">{{ $p->name }}</a>
                        @if($p->rack_location)<div style="font-size:11px;color:#94a3b8;"><i class="fas fa-map-marker-alt"></i> {{ $p->rack_location }}</div>@endif
                    </td>
                    <td><span class="badge badge-secondary">{{ $p->category->name }}</span></td>
                    <td style="font-size:12px;color:#64748b;">{{ $p->rack->full_label ?? '-' }}</td>
                    <td style="text-align:center;">
                        <strong style="font-size:15px;color:{{ $p->stock<=0?'#dc2626':($p->isLowStock()?'#d97706':'#0f172a') }};">{{ number_format($p->stock) }}</strong>
                        <span style="font-size:10px;color:#94a3b8;"> {{ $p->unit->symbol }}</span>
                    </td>
                    <td style="text-align:center;color:#64748b;font-size:13px;">{{ $p->min_stock }}</td>
                    <td style="text-align:right;font-size:13px;">Rp {{ number_format($p->purchase_price,0,',','.') }}</td>
                    <td style="text-align:right;font-size:13px;color:#10b981;font-weight:600;">Rp {{ number_format($p->selling_price,0,',','.') }}</td>
                    <td style="text-align:right;font-weight:600;font-size:13px;">Rp {{ number_format($p->stock * $p->purchase_price,0,',','.') }}</td>
                    <td style="text-align:center;">
                        @if($p->stock<=0)<span class="badge badge-danger">Habis</span>
                        @elseif($p->isLowStock())<span class="badge badge-warning">Menipis</span>
                        @else<span class="badge badge-success">Normal</span>@endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="11"><div class="empty-state"><i class="fas fa-boxes"></i><p>Tidak ada data.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <span>{{ $products->firstItem()??0 }}-{{ $products->lastItem()??0 }} dari {{ $products->total() }}</span>
        {{ $products->appends(request()->except('page'))->links('vendor.pagination.simple') }}
    </div>
</div>

{{-- ================ TAB: STOK MASUK ================ --}}
@elseif($tab === 'masuk')

<div class="grid grid-2" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-boxes"></i></div>
        <div class="stat-content"><div class="stat-value">{{ number_format($totalQtyIn) }}</div><div class="stat-label">Total Unit Masuk</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-content"><div class="stat-value" style="font-size:18px;">Rp {{ number_format($totalValueIn,0,',','.') }}</div><div class="stat-label">Total Nilai Pembelian</div></div>
    </div>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET">
            <input type="hidden" name="tab" value="masuk">
            <div class="filter-bar">
                <div class="search-input"><i class="fas fa-search"></i>
                    <input type="text" name="search" class="form-control" placeholder="No. referensi atau nama produk..." value="{{ request('search') }}">
                </div>
                <input type="date" name="date_from" class="form-control" style="width:155px;" value="{{ request('date_from') }}">
                <input type="date" name="date_to" class="form-control" style="width:155px;" value="{{ request('date_to') }}">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                <a href="{{ route('reports.stock', ['tab'=>'masuk']) }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th>No</th><th>No. Referensi</th><th>Produk</th><th>Supplier</th>
                <th style="text-align:center;">Qty</th><th style="text-align:right;">Harga Beli</th>
                <th style="text-align:right;">Total</th><th>Tanggal</th><th>Dicatat Oleh</th>
            </tr></thead>
            <tbody>
                @forelse($stockIns as $i => $si)
                <tr>
                    <td style="color:#94a3b8;">{{ $stockIns->firstItem() + $i }}</td>
                    <td><code style="font-size:11px;background:#f1f5f9;padding:2px 6px;border-radius:4px;">{{ $si->reference_number }}</code></td>
                    <td>
                        <div style="font-weight:600;font-size:13px;">{{ $si->product->name ?? '-' }}</div>
                        <div style="font-size:11px;color:#94a3b8;">{{ $si->product->code ?? '' }}</div>
                    </td>
                    <td style="font-size:13px;color:#64748b;">{{ $si->supplier->name ?? '-' }}</td>
                    <td style="text-align:center;"><span style="color:#10b981;font-weight:700;">+{{ number_format($si->quantity) }}</span></td>
                    <td style="text-align:right;font-size:13px;">Rp {{ number_format($si->purchase_price,0,',','.') }}</td>
                    <td style="text-align:right;font-weight:600;font-size:13px;">Rp {{ number_format($si->total_price,0,',','.') }}</td>
                    <td style="font-size:12px;color:#64748b;">{{ $si->transaction_date->format('d M Y') }}</td>
                    <td style="font-size:12px;color:#64748b;">{{ $si->user->name ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="9"><div class="empty-state"><i class="fas fa-inbox"></i><p>Belum ada data stok masuk.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <span>{{ $stockIns->firstItem()??0 }}-{{ $stockIns->lastItem()??0 }} dari {{ $stockIns->total() }}</span>
        {{ $stockIns->appends(request()->except('si_page'))->links('vendor.pagination.simple') }}
    </div>
</div>

{{-- ================ TAB: STOK KELUAR ================ --}}
@elseif($tab === 'keluar')

<div class="grid grid-2" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-boxes"></i></div>
        <div class="stat-content"><div class="stat-value">{{ number_format($totalQtyOut) }}</div><div class="stat-label">Total Unit Keluar</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-content"><div class="stat-value" style="font-size:18px;">Rp {{ number_format($totalValueOut,0,',','.') }}</div><div class="stat-label">Total Nilai Keluar</div></div>
    </div>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET">
            <input type="hidden" name="tab" value="keluar">
            <div class="filter-bar">
                <div class="search-input"><i class="fas fa-search"></i>
                    <input type="text" name="search" class="form-control" placeholder="No. referensi atau produk..." value="{{ request('search') }}">
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
                <a href="{{ route('reports.stock', ['tab'=>'keluar']) }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th>No</th><th>No. Referensi</th><th>Produk</th>
                <th style="text-align:center;">Tipe</th>
                <th style="text-align:center;">Qty</th>
                <th style="text-align:right;">Harga</th><th style="text-align:right;">Total</th>
                <th>Tanggal</th><th>Oleh</th>
            </tr></thead>
            <tbody>
                @forelse($stockOuts as $i => $so)
                @php $typeColors = ['sale'=>'info','return'=>'warning','damaged'=>'danger','other'=>'secondary']; @endphp
                <tr>
                    <td style="color:#94a3b8;">{{ $stockOuts->firstItem() + $i }}</td>
                    <td><code style="font-size:11px;background:#f1f5f9;padding:2px 6px;border-radius:4px;">{{ $so->reference_number }}</code></td>
                    <td>
                        <div style="font-weight:600;font-size:13px;">{{ $so->product->name ?? '-' }}</div>
                        <div style="font-size:11px;color:#94a3b8;">{{ $so->product->code ?? '' }}</div>
                    </td>
                    <td style="text-align:center;"><span class="badge badge-{{ $typeColors[$so->type]??'secondary' }}">{{ $so->type_label }}</span></td>
                    <td style="text-align:center;"><span style="color:#ef4444;font-weight:700;">-{{ number_format($so->quantity) }}</span></td>
                    <td style="text-align:right;font-size:13px;">Rp {{ number_format($so->selling_price,0,',','.') }}</td>
                    <td style="text-align:right;font-weight:600;font-size:13px;">Rp {{ number_format($so->total_price,0,',','.') }}</td>
                    <td style="font-size:12px;color:#64748b;">{{ $so->transaction_date->format('d M Y') }}</td>
                    <td style="font-size:12px;color:#64748b;">{{ $so->user->name ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="9"><div class="empty-state"><i class="fas fa-inbox"></i><p>Belum ada data stok keluar.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <span>{{ $stockOuts->firstItem()??0 }}-{{ $stockOuts->lastItem()??0 }} dari {{ $stockOuts->total() }}</span>
        {{ $stockOuts->appends(request()->except('so_page'))->links('vendor.pagination.simple') }}
    </div>
</div>

{{-- ================ TAB: PRODUK TERLARIS ================ --}}
@elseif($tab === 'terlaris')

<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET">
            <input type="hidden" name="tab" value="terlaris">
            <div class="filter-bar">
                <select name="month" class="form-control" style="width:150px;">
                    @foreach($months as $m)
                    <option value="{{ $m['value'] }}" {{ $month==$m['value']?'selected':'' }}>{{ $m['label'] }}</option>
                    @endforeach
                </select>
                <select name="year" class="form-control" style="width:100px;">
                    @foreach($years as $y)
                    <option value="{{ $y['value'] }}" {{ $year==$y['value']?'selected':'' }}>{{ $y['value'] }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tampilkan</button>
                <a href="{{ route('reports.stock', ['tab'=>'terlaris']) }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-fire" style="color:#f97316;"></i> Terlaris - {{ collect($months)->firstWhere('value', $month)['label'] }} {{ $year }}</div>
        <span style="font-size:12px;color:#94a3b8;">{{ $topProducts->count() }} produk</span>
    </div>
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th width="50" style="text-align:center;">No</th>
                <th>Produk</th><th>Kategori</th>
                <th style="text-align:center;">via POS</th>
                <th style="text-align:center;">Manual</th>
                <th style="text-align:center;">Total Terjual</th>
                <th style="text-align:right;">Pendapatan</th>
            </tr></thead>
            <tbody>
                @php $maxSold = $topProducts->max('total_sold') ?: 1; @endphp
                @forelse($topProducts as $i => $item)
                <tr>
                    <td style="text-align:center;">
                        @if($i < 3)
                            <div style="width:26px;height:26px;border-radius:50%;background:{{ ['#f59e0b','#94a3b8','#cd7c3a'][$i] }};color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;margin:auto;">{{ $i+1 }}</div>
                        @else
                            <span style="color:#94a3b8;font-size:13px;">{{ $i+1 }}</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:600;font-size:13px;">{{ $item->product->name ?? '-' }}</div>
                        <div style="height:4px;background:#f1f5f9;border-radius:2px;margin-top:4px;width:160px;">
                            <div style="height:4px;background:var(--primary);border-radius:2px;width:{{ round($item->total_sold/$maxSold*100) }}%;"></div>
                        </div>
                    </td>
                    <td><span class="badge badge-secondary">{{ $item->product->category->name ?? '-' }}</span></td>
                    <td style="text-align:center;font-weight:600;color:#6366f1;">{{ number_format($item->pos_qty) }}</td>
                    <td style="text-align:center;color:#64748b;">{{ number_format($item->manual_qty) }}</td>
                    <td style="text-align:center;"><span style="font-size:16px;font-weight:700;">{{ number_format($item->total_sold) }}</span></td>
                    <td style="text-align:right;font-weight:700;color:#10b981;">Rp {{ number_format($item->total_revenue,0,',','.') }}</td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state"><i class="fas fa-chart-bar"></i><p>Belum ada data penjualan untuk periode ini.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ================ TAB: STOK PER RAK ================ --}}
@elseif($tab === 'rak')

<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET">
            <input type="hidden" name="tab" value="rak">
            <div class="filter-bar">
                <select name="warehouse_id" class="form-control" style="width:180px;">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ request('warehouse_id')==$wh->id?'selected':'' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
                <select name="rack_id" class="form-control" style="width:160px;">
                    <option value="">Semua Rak</option>
                    @foreach($warehouses as $wh)
                        @foreach($wh->racks as $rack)
                        <option value="{{ $rack->id }}" {{ request('rack_id')==$rack->id?'selected':'' }}>{{ $wh->name }} › {{ $rack->name }}</option>
                        @endforeach
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                <a href="{{ route('reports.stock', ['tab'=>'rak']) }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th>Produk</th><th>Kategori</th><th>Gudang</th><th>Rak</th>
                <th style="text-align:center;">Stok</th>
                <th style="text-align:center;">Status</th>
                <th style="text-align:right;">Nilai Stok</th>
            </tr></thead>
            <tbody>
                @forelse($productsByRack as $p)
                <tr>
                    <td>
                        <div style="font-weight:600;font-size:13px;">{{ $p->name }}</div>
                        <div style="font-size:11px;color:#94a3b8;">{{ $p->code }}</div>
                    </td>
                    <td><span class="badge badge-secondary">{{ $p->category->name ?? '-' }}</span></td>
                    <td style="font-size:12px;color:#64748b;">{{ $p->rack->warehouse->name ?? '-' }}</td>
                    <td>
                        @if($p->rack)<span class="badge badge-info">{{ $p->rack->name }}</span>
                        @else<span style="color:#94a3b8;font-size:12px;">Belum ditentukan</span>@endif
                    </td>
                    <td style="text-align:center;">
                        <span style="font-size:15px;font-weight:700;color:{{ $p->stock<=0?'#dc2626':($p->isLowStock()?'#d97706':'#10b981') }};">{{ number_format($p->stock) }}</span>
                        <span style="font-size:10px;color:#94a3b8;"> {{ $p->unit->symbol??'' }}</span>
                    </td>
                    <td style="text-align:center;">
                        @if($p->stock<=0)<span class="badge badge-danger">Habis</span>
                        @elseif($p->isLowStock())<span class="badge badge-warning">Menipis</span>
                        @else<span class="badge badge-success">Normal</span>@endif
                    </td>
                    <td style="text-align:right;font-size:13px;">Rp {{ number_format($p->stock*$p->purchase_price,0,',','.') }}</td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state"><i class="fas fa-boxes"></i><p>Tidak ada produk untuk filter ini.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <span>{{ $productsByRack->firstItem()??0 }}-{{ $productsByRack->lastItem()??0 }} dari {{ $productsByRack->total() }}</span>
        {{ $productsByRack->appends(request()->except('rack_page'))->links('vendor.pagination.simple') }}
    </div>
</div>

@endif
@endsection
