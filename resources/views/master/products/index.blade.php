@extends('layouts.app')
@section('title', 'Master Data Produk')
@push('breadcrumb_content', 'Master Data / <strong>Produk</strong>')

@push('styles')
<style>
.master-tab-nav { display:flex; gap:0; border-bottom:2px solid #e2e8f0; margin-bottom:20px; overflow-x:auto; }
.master-tab-btn {
    padding:10px 16px; font-size:13px; font-weight:500; color:#64748b;
    background:none; border:none; border-bottom:2px solid transparent;
    margin-bottom:-2px; cursor:pointer; white-space:nowrap; transition:all .15s;
    display:flex; align-items:center; gap:7px; text-decoration:none;
}
.master-tab-btn.active { color:var(--primary); border-bottom-color:var(--primary); }
.master-tab-btn:hover:not(.active) { color:#374151; background:#f8fafc; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Master Data Produk</div>
        <div class="page-subtitle">Kelola produk, kategori, satuan, supplier, gudang, dan tipe keluar</div>
    </div>
    {{-- tombol aksi berubah tergantung tab --}}
    @if($tab === 'produk')
        <button class="btn btn-primary" onclick="openModal('modalTambahProduk')"><i class="fas fa-plus"></i> Tambah Produk</button>
    @elseif($tab === 'kategori')
        <button class="btn btn-primary" onclick="openModal('modalTambahKategori')"><i class="fas fa-plus"></i> Tambah Kategori</button>
    @elseif($tab === 'satuan')
        <button class="btn btn-primary" onclick="openModal('modalTambahSatuan')"><i class="fas fa-plus"></i> Tambah Satuan</button>
    @elseif($tab === 'supplier')
        <button class="btn btn-primary" onclick="openModal('modalTambahSupplier')"><i class="fas fa-plus"></i> Tambah Supplier</button>
    @elseif($tab === 'gudang')
        <div class="btn-group">
            <button class="btn btn-primary" id="btnTambahGudang" onclick="openModal('modalTambahGudang')"><i class="fas fa-warehouse"></i> Tambah Gudang</button>
            <button class="btn btn-success" id="btnTambahRak" onclick="openModal('modalTambahRak')"><i class="fas fa-th-large"></i> Tambah Rak</button>
        </div>
    @elseif($tab === 'tipekeluar')
        <button class="btn btn-primary" onclick="openModal('modalTambahTipe')"><i class="fas fa-plus"></i> Tambah Tipe</button>
    @endif
</div>

{{-- Tab Navigation --}}
<div class="master-tab-nav">
    <a href="{{ route('products.index', array_merge(request()->except(['tab','p_page','cat_page','unit_page','sup_page','wh_page','ot_page']), ['tab'=>'produk'])) }}"
        class="master-tab-btn {{ $tab==='produk' ? 'active' : '' }}">
        <i class="fas fa-boxes"></i> Produk
    </a>
    <a href="{{ route('products.index', ['tab'=>'kategori']) }}" class="master-tab-btn {{ $tab==='kategori' ? 'active' : '' }}">
        <i class="fas fa-tag"></i> Kategori
    </a>
    <a href="{{ route('products.index', ['tab'=>'satuan']) }}" class="master-tab-btn {{ $tab==='satuan' ? 'active' : '' }}">
        <i class="fas fa-ruler"></i> Satuan
    </a>
    <a href="{{ route('products.index', ['tab'=>'supplier']) }}" class="master-tab-btn {{ $tab==='supplier' ? 'active' : '' }}">
        <i class="fas fa-truck"></i> Supplier
    </a>
    <a href="{{ route('products.index', ['tab'=>'gudang']) }}" class="master-tab-btn {{ $tab==='gudang' ? 'active' : '' }}">
        <i class="fas fa-warehouse"></i> Gudang & Rak
    </a>
    <a href="{{ route('products.index', ['tab'=>'tipekeluar']) }}" class="master-tab-btn {{ $tab==='tipekeluar' ? 'active' : '' }}">
        <i class="fas fa-tags"></i> Tipe Keluar
    </a>
</div>

{{-- ============ TAB PRODUK ============ --}}
@if($tab === 'produk')
<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET">
            <input type="hidden" name="tab" value="produk">
            <div class="filter-bar">
                <div class="search-input"><i class="fas fa-search"></i>
                    <input type="text" name="search" class="form-control" placeholder="Cari kode, nama, barcode..." value="{{ request('search') }}">
                </div>
                <select name="category_id" class="form-control" style="width:180px;">
                    <option value="">Semua Kategori</option>
                    @foreach($categories_list as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-control" style="width:150px;">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status')=='active'?'selected':'' }}>Aktif</option>
                    <option value="low" {{ request('status')=='low'?'selected':'' }}>⚠ Menipis</option>
                    <option value="out" {{ request('status')=='out'?'selected':'' }}>✗ Habis</option>
                    <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Nonaktif</option>
                </select>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
            </div>
        </form>
    </div>
</div>
<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th width="50">No</th><th>Produk</th><th>Kategori</th>
                <th style="text-align:right;">Harga Beli</th>
                <th style="text-align:right;">Harga Jual</th>
                <th style="text-align:center;">Stok</th>
                <th style="text-align:center;">Status</th>
                <th style="text-align:center;">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse($products as $i => $product)
                <tr>
                    <td style="color:#94a3b8;">{{ $products->firstItem() + $i }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            @if($product->image)
                                <img src="{{ asset('storage/'.$product->image) }}" alt="" style="width:36px;height:36px;border-radius:6px;object-fit:cover;">
                            @else
                                <div style="width:36px;height:36px;background:#f1f5f9;border-radius:6px;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-box" style="color:#94a3b8;font-size:14px;"></i>
                                </div>
                            @endif
                            <div>
                                <div style="font-weight:600;font-size:13px;">{{ $product->name }}</div>
                                <div style="font-size:11px;color:#94a3b8;">{{ $product->code }}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge badge-secondary">{{ $product->category->name }}</span></td>
                    <td style="text-align:right;font-size:13px;">Rp {{ number_format($product->purchase_price,0,',','.') }}</td>
                    <td style="text-align:right;font-size:13px;color:#10b981;font-weight:600;">Rp {{ number_format($product->selling_price,0,',','.') }}</td>
                    <td style="text-align:center;">
                        <div style="font-weight:700;font-size:16px;color:{{ $product->stock<=0?'#dc2626':($product->isLowStock()?'#d97706':'#0f172a') }};">{{ number_format($product->stock) }}</div>
                        <div style="font-size:10px;color:#94a3b8;">{{ $product->unit->symbol }}</div>
                    </td>
                    <td style="text-align:center;">
                        @if($product->stock<=0)<span class="badge badge-danger">Habis</span>
                        @elseif($product->isLowStock())<span class="badge badge-warning">Menipis</span>
                        @elseif(!$product->is_active)<span class="badge badge-secondary">Nonaktif</span>
                        @else<span class="badge badge-success">Normal</span>@endif
                    </td>
                    <td style="text-align:center;">
                        <div class="btn-group" style="justify-content:center;">
                            <button type="button" class="btn btn-sm btn-secondary" title="Detail" onclick="viewProduk({{ $product->id }})"><i class="fas fa-eye"></i></button>
                            <a href="{{ route('products.barcode', $product) }}" target="_blank" class="btn btn-sm btn-info" title="Cetak Barcode"><i class="fas fa-barcode"></i></a>
                            <button type="button" class="btn btn-sm btn-warning" title="Edit" onclick="editProduk({{ $product->id }})"><i class="fas fa-edit"></i></button>
                            <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirmDelete(this)">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8"><div class="empty-state"><i class="fas fa-boxes"></i><p>Belum ada produk. <a href="{{ route('products.create') }}">Tambah sekarang</a></p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <span>{{ $products->firstItem()??0 }}-{{ $products->lastItem()??0 }} dari {{ $products->total() }} produk</span>
        {{ $products->appends(request()->except('p_page'))->links('vendor.pagination.simple') }}
    </div>
</div>

{{-- ============ TAB KATEGORI ============ --}}
@elseif($tab === 'kategori')
<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET">
            <input type="hidden" name="tab" value="kategori">
            <div class="filter-bar">
                <div class="search-input"><i class="fas fa-search"></i>
                    <input type="text" name="search" class="form-control" placeholder="Cari kategori..." value="{{ request('search') }}">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
                <a href="{{ route('products.index', ['tab'=>'kategori']) }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
            </div>
        </form>
    </div>
</div>
<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr><th>No</th><th>Nama Kategori</th><th>Kode</th><th>Deskripsi</th>
                <th style="text-align:center;">Produk</th><th style="text-align:center;">Status</th><th style="text-align:center;">Aksi</th></tr></thead>
            <tbody>
                @forelse($categories as $i => $cat)
                <tr>
                    <td style="color:#94a3b8;">{{ $categories->firstItem() + $i }}</td>
                    <td style="font-weight:600;">{{ $cat->name }}</td>
                    <td><code style="font-size:12px;background:#f1f5f9;padding:2px 6px;border-radius:4px;">{{ $cat->code ?? '-' }}</code></td>
                    <td style="color:#64748b;font-size:13px;">{{ Str::limit($cat->description, 50) ?? '-' }}</td>
                    <td style="text-align:center;"><a href="{{ route('products.index', ['category_id'=>$cat->id]) }}" class="badge badge-primary" style="text-decoration:none;">{{ $cat->products_count }}</a></td>
                    <td style="text-align:center;">
                        @if($cat->is_active)<span class="badge badge-success">Aktif</span>
                        @else<span class="badge badge-secondary">Nonaktif</span>@endif
                    </td>
                    <td style="text-align:center;">
                        <div class="btn-group" style="justify-content:center;">
                            <button type="button" class="btn btn-sm btn-warning"
                                onclick="editKategori({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ addslashes($cat->code ?? '') }}', '{{ addslashes($cat->description ?? '') }}', {{ $cat->is_active ? 'true' : 'false' }})">
                                <i class="fas fa-edit"></i></button>
                            <form method="POST" action="{{ route('categories.destroy', $cat) }}" onsubmit="return confirmDelete(this)">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state"><i class="fas fa-tags"></i><p>Belum ada kategori.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <span>{{ $categories->firstItem()??0 }}-{{ $categories->lastItem()??0 }} dari {{ $categories->total() }}</span>
        {{ $categories->appends(['tab'=>'kategori'])->links('vendor.pagination.simple') }}
    </div>
</div>

{{-- ============ TAB SATUAN ============ --}}
@elseif($tab === 'satuan')
<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr><th>No</th><th>Nama Satuan</th><th>Simbol</th><th>Deskripsi</th>
                <th style="text-align:center;">Produk</th><th style="text-align:center;">Status</th><th style="text-align:center;">Aksi</th></tr></thead>
            <tbody>
                @forelse($units as $i => $unit)
                <tr>
                    <td style="color:#94a3b8;">{{ $units->firstItem() + $i }}</td>
                    <td style="font-weight:600;">{{ $unit->name }}</td>
                    <td><span class="badge badge-primary" style="font-size:13px;">{{ $unit->symbol }}</span></td>
                    <td style="color:#64748b;font-size:13px;">{{ $unit->description ?? '-' }}</td>
                    <td style="text-align:center;"><span class="badge badge-secondary">{{ $unit->products_count }}</span></td>
                    <td style="text-align:center;">
                        @if($unit->is_active)<span class="badge badge-success">Aktif</span>
                        @else<span class="badge badge-secondary">Nonaktif</span>@endif
                    </td>
                    <td style="text-align:center;">
                        <div class="btn-group" style="justify-content:center;">
                            <button type="button" class="btn btn-sm btn-warning"
                                onclick="editSatuan({{ $unit->id }}, '{{ addslashes($unit->name) }}', '{{ addslashes($unit->symbol) }}', '{{ addslashes($unit->description ?? '') }}', {{ $unit->is_active ? 'true' : 'false' }})">
                                <i class="fas fa-edit"></i></button>
                            <form method="POST" action="{{ route('units.destroy', $unit) }}" onsubmit="return confirmDelete(this)">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state"><i class="fas fa-ruler"></i><p>Belum ada satuan.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <span>{{ $units->firstItem()??0 }}-{{ $units->lastItem()??0 }} dari {{ $units->total() }}</span>
        {{ $units->appends(['tab'=>'satuan'])->links('vendor.pagination.simple') }}
    </div>
</div>

{{-- ============ TAB SUPPLIER ============ --}}
@elseif($tab === 'supplier')
<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET">
            <input type="hidden" name="tab" value="supplier">
            <div class="filter-bar">
                <div class="search-input"><i class="fas fa-search"></i>
                    <input type="text" name="search" class="form-control" placeholder="Cari supplier..." value="{{ request('search') }}">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
                <a href="{{ route('products.index', ['tab'=>'supplier']) }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
            </div>
        </form>
    </div>
</div>

@php
$supplierJson = $suppliers->keyBy('id')->map(function($s) {
    return ['id'=>$s->id,'name'=>$s->name,'code'=>$s->code,'contact_person'=>$s->contact_person,
        'phone'=>$s->phone,'email'=>$s->email,'city'=>$s->city,'address'=>$s->address,
        'notes'=>$s->notes,'is_active'=>$s->is_active,'products_count'=>$s->products_count];
})->toArray();
@endphp
<script>const suppliersData = {!! json_encode($supplierJson) !!};</script>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr><th>No</th><th>Supplier</th><th>Kontak</th><th>Kota</th>
                <th style="text-align:center;">Produk</th><th style="text-align:center;">Status</th><th style="text-align:center;">Aksi</th></tr></thead>
            <tbody>
                @forelse($suppliers as $i => $sup)
                <tr>
                    <td style="color:#94a3b8;">{{ $suppliers->firstItem() + $i }}</td>
                    <td><div style="font-weight:600;">{{ $sup->name }}</div><div style="font-size:11px;color:#94a3b8;">{{ $sup->code ?? '' }}</div></td>
                    <td><div style="font-size:13px;">{{ $sup->contact_person ?? '-' }}</div><div style="font-size:11px;color:#64748b;">{{ $sup->phone ?? '' }}</div></td>
                    <td style="font-size:13px;color:#64748b;">{{ $sup->city ?? '-' }}</td>
                    <td style="text-align:center;"><span class="badge badge-primary">{{ $sup->products_count }}</span></td>
                    <td style="text-align:center;">
                        @if($sup->is_active)<span class="badge badge-success">Aktif</span>
                        @else<span class="badge badge-secondary">Nonaktif</span>@endif
                    </td>
                    <td style="text-align:center;">
                        <div class="btn-group" style="justify-content:center;">
                            <button type="button" class="btn btn-sm btn-warning" onclick="editSupplier({{ $sup->id }})"><i class="fas fa-edit"></i></button>
                            <form method="POST" action="{{ route('suppliers.destroy', $sup) }}" onsubmit="return confirmDelete(this)">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state"><i class="fas fa-truck"></i><p>Belum ada supplier.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <span>{{ $suppliers->firstItem()??0 }}-{{ $suppliers->lastItem()??0 }} dari {{ $suppliers->total() }}</span>
        {{ $suppliers->appends(['tab'=>'supplier'])->links('vendor.pagination.simple') }}
    </div>
</div>

{{-- ============ TAB GUDANG & RAK ============ --}}
@elseif($tab === 'gudang')
@php
$warehouseJson = $warehouses->keyBy('id')->map(function($w) {
    return ['id'=>$w->id,'code'=>$w->code,'name'=>$w->name,'location'=>$w->location,'description'=>$w->description,'is_active'=>$w->is_active];
})->toArray();
$rackJson = $racks->keyBy('id')->map(function($r) {
    return ['id'=>$r->id,'warehouse_id'=>$r->warehouse_id,'code'=>$r->code,'name'=>$r->name,'row'=>$r->row,'column'=>$r->column,'description'=>$r->description,'is_active'=>$r->is_active];
})->toArray();
@endphp
<script>const warehousesData={!! json_encode($warehouseJson) !!};const racksData={!! json_encode($rackJson) !!};</script>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
{{-- Gudang --}}
<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-warehouse" style="color:var(--primary)"></i> Gudang</div></div>
    <div class="table-wrapper">
        <table>
            <thead><tr><th>Nama Gudang</th><th>Kode</th><th>Lokasi</th><th style="text-align:center;">Rak</th><th style="text-align:center;">Status</th><th style="text-align:center;">Aksi</th></tr></thead>
            <tbody>
                @forelse($warehouses as $wh)
                <tr>
                    <td style="font-weight:600;">{{ $wh->name }}</td>
                    <td><code style="font-size:11px;background:#f1f5f9;padding:2px 5px;border-radius:4px;">{{ $wh->code ?? '-' }}</code></td>
                    <td style="font-size:12px;color:#64748b;">{{ $wh->location ?? '-' }}</td>
                    <td style="text-align:center;"><span class="badge badge-info">{{ $wh->racks_count }}</span></td>
                    <td style="text-align:center;">
                        @if($wh->is_active)<span class="badge badge-success">Aktif</span>
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
                <tr><td colspan="6"><div class="empty-state" style="padding:24px;"><i class="fas fa-warehouse"></i><p>Belum ada gudang.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">{{ $warehouses->appends(['tab'=>'gudang'])->links('vendor.pagination.simple') }}</div>
</div>

{{-- Rak --}}
<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-th-large" style="color:#10b981"></i> Rak</div></div>
    <div class="table-wrapper">
        <table>
            <thead><tr><th>Nama Rak</th><th>Gudang</th><th>Kode</th><th>Baris/Kol</th><th style="text-align:center;">Status</th><th style="text-align:center;">Aksi</th></tr></thead>
            <tbody>
                @forelse($racks as $rack)
                <tr>
                    <td style="font-weight:600;">{{ $rack->name }}</td>
                    <td><span class="badge badge-secondary">{{ $rack->warehouse->name ?? '-' }}</span></td>
                    <td style="font-size:12px;">{{ $rack->code ?? '-' }}</td>
                    <td style="font-size:12px;color:#64748b;">{{ $rack->row ? 'B'.$rack->row : '' }}{{ $rack->column ? ('/K'.$rack->column) : '-' }}</td>
                    <td style="text-align:center;">
                        @if($rack->is_active)<span class="badge badge-success">Aktif</span>
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
                <tr><td colspan="6"><div class="empty-state" style="padding:24px;"><i class="fas fa-th-large"></i><p>Belum ada rak.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>

{{-- ============ TAB TIPE KELUAR ============ --}}
@elseif($tab === 'tipekeluar')
@php
$tipeJson = $outTypes->keyBy('id')->map(function($t) {
    return ['id'=>$t->id,'code'=>$t->code,'name'=>$t->name,'color'=>$t->color,'icon'=>$t->icon,'affects_stock'=>$t->affects_stock,'is_active'=>$t->is_active,'sort_order'=>$t->sort_order];
})->toArray();
@endphp
<script>const typesData = {!! json_encode($tipeJson) !!};</script>
<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr><th>No</th><th>Nama</th><th>Kode</th><th style="text-align:center;">Badge</th>
                <th style="text-align:center;">Kurangi Stok</th><th style="text-align:center;">Digunakan</th>
                <th style="text-align:center;">Status</th><th style="text-align:center;">Aksi</th></tr></thead>
            <tbody>
                @forelse($outTypes as $i => $t)
                <tr>
                    <td style="color:#94a3b8;">{{ $outTypes->firstItem() + $i }}</td>
                    <td><div style="display:flex;align-items:center;gap:8px;">@if($t->icon)<i class="{{ $t->icon }}" style="width:14px;color:#64748b;"></i>@endif<span style="font-weight:600;">{{ $t->name }}</span></div></td>
                    <td><code style="font-size:11px;background:#f1f5f9;padding:2px 5px;border-radius:4px;">{{ $t->code }}</code></td>
                    <td style="text-align:center;"><span class="badge badge-{{ $t->color }}">{{ $t->name }}</span></td>
                    <td style="text-align:center;">@if($t->affects_stock)<span class="badge badge-danger">Ya</span>@else<span class="badge badge-secondary">Tidak</span>@endif</td>
                    <td style="text-align:center;"><span class="badge badge-secondary">{{ $t->stock_outs_count }}x</span></td>
                    <td style="text-align:center;">@if($t->is_active)<span class="badge badge-success">Aktif</span>@else<span class="badge badge-secondary">Nonaktif</span>@endif</td>
                    <td style="text-align:center;">
                        <div class="btn-group" style="justify-content:center;">
                            <button type="button" class="btn btn-sm btn-warning" onclick="editTipe({{ $t->id }})"><i class="fas fa-edit"></i></button>
                            <form method="POST" action="{{ route('stock-out-types.destroy', $t) }}" onsubmit="return confirmDelete(this)">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8"><div class="empty-state"><i class="fas fa-tags"></i><p>Belum ada tipe keluar.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <span>{{ $outTypes->firstItem()??0 }}-{{ $outTypes->lastItem()??0 }} dari {{ $outTypes->total() }}</span>
        {{ $outTypes->appends(['tab'=>'tipekeluar'])->links('vendor.pagination.simple') }}
    </div>
</div>
@endif

{{-- ======== MODAL KATEGORI ======== --}}
<div class="modal-backdrop" id="modalTambahKategori">
    <div class="modal-box" style="max-width:500px;">
        <div class="modal-header"><div class="modal-title"><i class="fas fa-tag" style="color:var(--primary)"></i> Tambah Kategori</div><button class="modal-close" onclick="closeModal('modalTambahKategori')"><i class="fas fa-times"></i></button></div>
        <form method="POST" action="{{ route('categories.store') }}">@csrf
            <div class="modal-body">
                <div class="form-group"><label class="form-label">Nama <span class="required">*</span></label><input type="text" name="name" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Kode</label><input type="text" name="code" class="form-control" style="text-transform:uppercase;"></div>
                <div class="form-group"><label class="form-label">Deskripsi</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                <div class="toggle-wrap"><label class="toggle"><input type="checkbox" name="is_active" value="1" checked><span class="toggle-slider"></span></label><span style="font-size:13px;color:#64748b;">Aktif</span></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahKategori')">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button></div>
        </form>
    </div>
</div>
<div class="modal-backdrop" id="modalEditKategori">
    <div class="modal-box" style="max-width:500px;">
        <div class="modal-header"><div class="modal-title"><i class="fas fa-edit" style="color:#f59e0b"></i> Edit Kategori</div><button class="modal-close" onclick="closeModal('modalEditKategori')"><i class="fas fa-times"></i></button></div>
        <form method="POST" id="formEditKategori">@csrf @method('PUT')
            <div class="modal-body">
                <div class="form-group"><label class="form-label">Nama <span class="required">*</span></label><input type="text" name="name" id="ekName" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Kode</label><input type="text" name="code" id="ekCode" class="form-control" style="text-transform:uppercase;"></div>
                <div class="form-group"><label class="form-label">Deskripsi</label><textarea name="description" id="ekDesc" class="form-control" rows="2"></textarea></div>
                <div class="toggle-wrap"><label class="toggle"><input type="checkbox" name="is_active" id="ekActive" value="1"><span class="toggle-slider"></span></label><span style="font-size:13px;color:#64748b;">Aktif</span></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('modalEditKategori')">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Perbarui</button></div>
        </form>
    </div>
</div>

{{-- ======== MODAL SATUAN ======== --}}
<div class="modal-backdrop" id="modalTambahSatuan">
    <div class="modal-box" style="max-width:460px;">
        <div class="modal-header"><div class="modal-title"><i class="fas fa-ruler" style="color:var(--primary)"></i> Tambah Satuan</div><button class="modal-close" onclick="closeModal('modalTambahSatuan')"><i class="fas fa-times"></i></button></div>
        <form method="POST" action="{{ route('units.store') }}">@csrf
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group"><label class="form-label">Nama <span class="required">*</span></label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Simbol <span class="required">*</span></label><input type="text" name="symbol" class="form-control" required></div>
                </div>
                <div class="form-group"><label class="form-label">Deskripsi</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                <div class="toggle-wrap"><label class="toggle"><input type="checkbox" name="is_active" value="1" checked><span class="toggle-slider"></span></label><span style="font-size:13px;color:#64748b;">Aktif</span></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahSatuan')">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button></div>
        </form>
    </div>
</div>
<div class="modal-backdrop" id="modalEditSatuan">
    <div class="modal-box" style="max-width:460px;">
        <div class="modal-header"><div class="modal-title"><i class="fas fa-edit" style="color:#f59e0b"></i> Edit Satuan</div><button class="modal-close" onclick="closeModal('modalEditSatuan')"><i class="fas fa-times"></i></button></div>
        <form method="POST" id="formEditSatuan">@csrf @method('PUT')
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group"><label class="form-label">Nama <span class="required">*</span></label><input type="text" name="name" id="esName" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Simbol <span class="required">*</span></label><input type="text" name="symbol" id="esSymbol" class="form-control" required></div>
                </div>
                <div class="form-group"><label class="form-label">Deskripsi</label><textarea name="description" id="esDesc" class="form-control" rows="2"></textarea></div>
                <div class="toggle-wrap"><label class="toggle"><input type="checkbox" name="is_active" id="esActive" value="1"><span class="toggle-slider"></span></label><span style="font-size:13px;color:#64748b;">Aktif</span></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('modalEditSatuan')">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Perbarui</button></div>
        </form>
    </div>
</div>

{{-- ======== MODAL SUPPLIER ======== --}}
<div class="modal-backdrop" id="modalTambahSupplier">
    <div class="modal-box" style="max-width:620px;">
        <div class="modal-header"><div class="modal-title"><i class="fas fa-truck" style="color:var(--primary)"></i> Tambah Supplier</div><button class="modal-close" onclick="closeModal('modalTambahSupplier')"><i class="fas fa-times"></i></button></div>
        <form method="POST" action="{{ route('suppliers.store') }}">@csrf
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group"><label class="form-label">Nama <span class="required">*</span></label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Kode</label><input type="text" name="code" class="form-control"></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group"><label class="form-label">Nama Kontak</label><input type="text" name="contact_person" class="form-control"></div>
                    <div class="form-group"><label class="form-label">Telepon</label><input type="text" name="phone" class="form-control"></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
                    <div class="form-group"><label class="form-label">Kota</label><input type="text" name="city" class="form-control"></div>
                </div>
                <div class="form-group"><label class="form-label">Alamat</label><textarea name="address" class="form-control" rows="2"></textarea></div>
                <div class="toggle-wrap"><label class="toggle"><input type="checkbox" name="is_active" value="1" checked><span class="toggle-slider"></span></label><span style="font-size:13px;color:#64748b;">Aktif</span></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahSupplier')">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button></div>
        </form>
    </div>
</div>
<div class="modal-backdrop" id="modalEditSupplier">
    <div class="modal-box" style="max-width:620px;">
        <div class="modal-header"><div class="modal-title"><i class="fas fa-edit" style="color:#f59e0b"></i> Edit Supplier</div><button class="modal-close" onclick="closeModal('modalEditSupplier')"><i class="fas fa-times"></i></button></div>
        <form method="POST" id="formEditSupplier">@csrf @method('PUT')
            <div class="modal-body">
                <div class="form-row cols-2">
                    <div class="form-group"><label class="form-label">Nama <span class="required">*</span></label><input type="text" name="name" id="espName" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Kode</label><input type="text" name="code" id="espCode" class="form-control"></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group"><label class="form-label">Nama Kontak</label><input type="text" name="contact_person" id="espContact" class="form-control"></div>
                    <div class="form-group"><label class="form-label">Telepon</label><input type="text" name="phone" id="espPhone" class="form-control"></div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" id="espEmail" class="form-control"></div>
                    <div class="form-group"><label class="form-label">Kota</label><input type="text" name="city" id="espCity" class="form-control"></div>
                </div>
                <div class="form-group"><label class="form-label">Alamat</label><textarea name="address" id="espAddress" class="form-control" rows="2"></textarea></div>
                <div class="toggle-wrap"><label class="toggle"><input type="checkbox" name="is_active" id="espActive" value="1"><span class="toggle-slider"></span></label><span style="font-size:13px;color:#64748b;">Aktif</span></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('modalEditSupplier')">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Perbarui</button></div>
        </form>
    </div>
</div>

{{-- ======== MODAL GUDANG & RAK ======== --}}
<div class="modal-backdrop" id="modalTambahGudang">
    <div class="modal-box" style="max-width:480px;">
        <div class="modal-header"><div class="modal-title"><i class="fas fa-warehouse" style="color:var(--primary)"></i> Tambah Gudang</div><button class="modal-close" onclick="closeModal('modalTambahGudang')"><i class="fas fa-times"></i></button></div>
        <form method="POST" action="{{ route('warehouses.store') }}">@csrf
            <div class="modal-body">
                <div class="form-row cols-2"><div class="form-group"><label class="form-label">Nama <span class="required">*</span></label><input type="text" name="name" class="form-control" required></div><div class="form-group"><label class="form-label">Kode</label><input type="text" name="code" class="form-control"></div></div>
                <div class="form-group"><label class="form-label">Lokasi</label><input type="text" name="location" class="form-control"></div>
                <div class="form-group"><label class="form-label">Deskripsi</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                <div class="toggle-wrap"><label class="toggle"><input type="checkbox" name="is_active" value="1" checked><span class="toggle-slider"></span></label><span style="font-size:13px;color:#64748b;">Aktif</span></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahGudang')">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button></div>
        </form>
    </div>
</div>
<div class="modal-backdrop" id="modalEditGudang">
    <div class="modal-box" style="max-width:480px;">
        <div class="modal-header"><div class="modal-title"><i class="fas fa-edit" style="color:#f59e0b"></i> Edit Gudang</div><button class="modal-close" onclick="closeModal('modalEditGudang')"><i class="fas fa-times"></i></button></div>
        <form method="POST" id="formEditGudang">@csrf @method('PUT')
            <div class="modal-body">
                <div class="form-row cols-2"><div class="form-group"><label class="form-label">Nama <span class="required">*</span></label><input type="text" name="name" id="egName" class="form-control" required></div><div class="form-group"><label class="form-label">Kode</label><input type="text" name="code" id="egCode" class="form-control"></div></div>
                <div class="form-group"><label class="form-label">Lokasi</label><input type="text" name="location" id="egLocation" class="form-control"></div>
                <div class="form-group"><label class="form-label">Deskripsi</label><textarea name="description" id="egDesc" class="form-control" rows="2"></textarea></div>
                <div class="toggle-wrap"><label class="toggle"><input type="checkbox" name="is_active" id="egActive" value="1"><span class="toggle-slider"></span></label><span style="font-size:13px;color:#64748b;">Aktif</span></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('modalEditGudang')">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Perbarui</button></div>
        </form>
    </div>
</div>
<div class="modal-backdrop" id="modalTambahRak">
    <div class="modal-box" style="max-width:480px;">
        <div class="modal-header"><div class="modal-title"><i class="fas fa-th-large" style="color:#10b981"></i> Tambah Rak</div><button class="modal-close" onclick="closeModal('modalTambahRak')"><i class="fas fa-times"></i></button></div>
        <form method="POST" action="{{ route('racks.store') }}">@csrf
            <div class="modal-body">
                <div class="form-group"><label class="form-label">Gudang <span class="required">*</span></label>
                    <select name="warehouse_id" class="form-control" required>
                        <option value="">-- Pilih Gudang --</option>
                        @foreach($warehouses as $wh)<option value="{{ $wh->id }}">{{ $wh->name }}</option>@endforeach
                    </select>
                </div>
                <div class="form-row cols-2"><div class="form-group"><label class="form-label">Nama <span class="required">*</span></label><input type="text" name="name" class="form-control" required></div><div class="form-group"><label class="form-label">Kode</label><input type="text" name="code" class="form-control"></div></div>
                <div class="form-row cols-2"><div class="form-group"><label class="form-label">Baris</label><input type="text" name="row" class="form-control"></div><div class="form-group"><label class="form-label">Kolom</label><input type="text" name="column" class="form-control"></div></div>
                <div class="toggle-wrap"><label class="toggle"><input type="checkbox" name="is_active" value="1" checked><span class="toggle-slider"></span></label><span style="font-size:13px;color:#64748b;">Aktif</span></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahRak')">Batal</button><button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button></div>
        </form>
    </div>
</div>
<div class="modal-backdrop" id="modalEditRak">
    <div class="modal-box" style="max-width:480px;">
        <div class="modal-header"><div class="modal-title"><i class="fas fa-edit" style="color:#f59e0b"></i> Edit Rak</div><button class="modal-close" onclick="closeModal('modalEditRak')"><i class="fas fa-times"></i></button></div>
        <form method="POST" id="formEditRak">@csrf @method('PUT')
            <div class="modal-body">
                <div class="form-group"><label class="form-label">Gudang <span class="required">*</span></label>
                    <select name="warehouse_id" id="erWarehouse" class="form-control" required>
                        <option value="">-- Pilih Gudang --</option>
                        @foreach($warehouses as $wh)<option value="{{ $wh->id }}">{{ $wh->name }}</option>@endforeach
                    </select>
                </div>
                <div class="form-row cols-2"><div class="form-group"><label class="form-label">Nama <span class="required">*</span></label><input type="text" name="name" id="erName" class="form-control" required></div><div class="form-group"><label class="form-label">Kode</label><input type="text" name="code" id="erCode" class="form-control"></div></div>
                <div class="form-row cols-2"><div class="form-group"><label class="form-label">Baris</label><input type="text" name="row" id="erRow" class="form-control"></div><div class="form-group"><label class="form-label">Kolom</label><input type="text" name="column" id="erCol" class="form-control"></div></div>
                <div class="toggle-wrap"><label class="toggle"><input type="checkbox" name="is_active" id="erActive" value="1"><span class="toggle-slider"></span></label><span style="font-size:13px;color:#64748b;">Aktif</span></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('modalEditRak')">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Perbarui</button></div>
        </form>
    </div>
</div>

{{-- ======== MODAL TIPE KELUAR ======== --}}
@php $colorOpts = ['success'=>'Hijau','danger'=>'Merah','warning'=>'Kuning','info'=>'Biru','secondary'=>'Abu-abu','primary'=>'Ungu']; @endphp
<div class="modal-backdrop" id="modalTambahTipe">
    <div class="modal-box" style="max-width:460px;">
        <div class="modal-header"><div class="modal-title"><i class="fas fa-tag" style="color:var(--primary)"></i> Tambah Tipe Keluar</div><button class="modal-close" onclick="closeModal('modalTambahTipe')"><i class="fas fa-times"></i></button></div>
        <form method="POST" action="{{ route('stock-out-types.store') }}">@csrf
            <div class="modal-body">
                <div class="form-row cols-2"><div class="form-group"><label class="form-label">Nama <span class="required">*</span></label><input type="text" name="name" class="form-control" required></div><div class="form-group"><label class="form-label">Kode <span class="required">*</span></label><input type="text" name="code" class="form-control" required style="text-transform:uppercase;"></div></div>
                <div class="form-row cols-2">
                    <div class="form-group"><label class="form-label">Warna Badge</label><select name="color" class="form-control">@foreach($colorOpts as $v=>$l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></div>
                    <div class="form-group"><label class="form-label">Urutan</label><input type="number" name="sort_order" class="form-control" value="0" min="0"></div>
                </div>
                <div class="form-group"><label class="form-label">Icon (FA class)</label><input type="text" name="icon" class="form-control" placeholder="fas fa-times-circle"></div>
                <div style="display:flex;gap:16px;">
                    <div class="toggle-wrap"><label class="toggle"><input type="checkbox" name="affects_stock" value="1" checked><span class="toggle-slider"></span></label><span style="font-size:13px;color:#64748b;">Kurangi stok</span></div>
                    <div class="toggle-wrap"><label class="toggle"><input type="checkbox" name="is_active" value="1" checked><span class="toggle-slider"></span></label><span style="font-size:13px;color:#64748b;">Aktif</span></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahTipe')">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button></div>
        </form>
    </div>
</div>
<div class="modal-backdrop" id="modalEditTipe">
    <div class="modal-box" style="max-width:460px;">
        <div class="modal-header"><div class="modal-title"><i class="fas fa-edit" style="color:#f59e0b"></i> Edit Tipe Keluar</div><button class="modal-close" onclick="closeModal('modalEditTipe')"><i class="fas fa-times"></i></button></div>
        <form method="POST" id="formEditTipe">@csrf @method('PUT')
            <div class="modal-body">
                <div class="form-row cols-2"><div class="form-group"><label class="form-label">Nama <span class="required">*</span></label><input type="text" name="name" id="etName" class="form-control" required></div><div class="form-group"><label class="form-label">Kode <span class="required">*</span></label><input type="text" name="code" id="etCode" class="form-control" required style="text-transform:uppercase;"></div></div>
                <div class="form-row cols-2">
                    <div class="form-group"><label class="form-label">Warna Badge</label><select name="color" id="etColor" class="form-control">@foreach($colorOpts as $v=>$l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></div>
                    <div class="form-group"><label class="form-label">Urutan</label><input type="number" name="sort_order" id="etSort" class="form-control" min="0"></div>
                </div>
                <div class="form-group"><label class="form-label">Icon (FA class)</label><input type="text" name="icon" id="etIcon" class="form-control"></div>
                <div style="display:flex;gap:16px;">
                    <div class="toggle-wrap"><label class="toggle"><input type="checkbox" name="affects_stock" id="etAffects" value="1"><span class="toggle-slider"></span></label><span style="font-size:13px;color:#64748b;">Kurangi stok</span></div>
                    <div class="toggle-wrap"><label class="toggle"><input type="checkbox" name="is_active" id="etActive" value="1"><span class="toggle-slider"></span></label><span style="font-size:13px;color:#64748b;">Aktif</span></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('modalEditTipe')">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Perbarui</button></div>
        </form>
    </div>
</div>




{{-- ======== MODAL VIEW PRODUK ======== --}}
<div class="modal-backdrop" id="modalViewProduk">
    <div class="modal-box" style="max-width:520px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-box" style="color:var(--primary)"></i> <span id="viewProdukTitle">Detail Produk</span></div>
            <button class="modal-close" onclick="closeModal('modalViewProduk')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="viewProdukBody"></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modalViewProduk')">Tutup</button>
            <button type="button" class="btn btn-warning" id="btnEditFromView"><i class="fas fa-edit"></i> Edit</button>
        </div>
    </div>
</div>

{{-- ======== MODAL TAMBAH PRODUK ======== --}}
{{-- ======== MODAL TAMBAH & EDIT PRODUK (data embed di sini agar JS bisa akses) ======== --}}
@php
$allCategories = \App\Models\Category::where('is_active',true)->orderBy('name')->get();
$allUnits      = \App\Models\Unit::where('is_active',true)->orderBy('name')->get();
$allSuppliers  = \App\Models\Supplier::where('is_active',true)->orderBy('name')->get();
$allRacks      = \App\Models\Rack::with('warehouse')->where('is_active',true)->get();

// Data produk untuk JS modal view/edit
$prodDataJs = $products->keyBy('id')->map(function($p) {
    return [
        'id' => $p->id, 'code' => $p->code, 'barcode' => $p->barcode,
        'name' => $p->name,
        'category_id' => $p->category_id, 'category_name' => $p->category->name ?? '-',
        'unit_id' => $p->unit_id, 'unit_name' => $p->unit->name ?? '-', 'unit_symbol' => $p->unit->symbol ?? '',
        'description' => $p->description,
        'purchase_price' => (float)$p->purchase_price, 'selling_price' => (float)$p->selling_price,
        'stock' => $p->stock, 'min_stock' => $p->min_stock,
        'rack_location' => $p->rack_location, 'is_active' => $p->is_active,
        'image_url' => $p->image ? asset('storage/' . $p->image) : null,
    ];
})->toArray();
@endphp

{{-- DATA JS - harus SEBELUM modal supaya JS bisa akses --}}
<script>
var productsData = {!! json_encode($prodDataJs) !!};

function fmt(n) { return new Intl.NumberFormat('id-ID').format(Math.round(n || 0)); }

function viewProduk(id) {
    const p = productsData[id]; if (!p) { alert('Data tidak ditemukan'); return; }
    const sc = p.stock <= 0 ? '#dc2626' : (p.stock <= p.min_stock ? '#d97706' : '#10b981');
    const sl = p.stock <= 0 ? 'Habis' : (p.stock <= p.min_stock ? 'Menipis' : 'Normal');
    const badgeClass = p.stock <= 0 ? 'danger' : (p.stock <= p.min_stock ? 'warning' : 'success');
    const rows = [
        ['Kode Produk', p.code],
        ['Barcode',     p.barcode || '-'],
        ['Nama Produk', '<strong>' + p.name + '</strong>'],
        ['Kategori',    p.category_name || '-'],
        ['Satuan',      p.unit_name],
        ['Harga Beli',  'Rp ' + fmt(p.purchase_price)],
        ['Harga Jual',  '<span style="color:#10b981;font-weight:700;">Rp ' + fmt(p.selling_price) + '</span>'],
        ['Stok Saat Ini', '<span style="font-size:16px;font-weight:700;color:' + sc + ';">' + p.stock + ' ' + p.unit_symbol + '</span> <span class="badge badge-' + badgeClass + '">' + sl + '</span>'],
        ['Min. Stok',   p.min_stock],
        ['Lokasi Rak',  p.rack_location || '-'],
        ['Status',      p.is_active ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Nonaktif</span>'],
    ];
    document.getElementById('viewProdukTitle').textContent = p.name;
    document.getElementById('viewProdukBody').innerHTML = rows.map(function(r) {
        return '<div class="detail-row"><span class="detail-label">' + r[0] + '</span><span class="detail-value">' + r[1] + '</span></div>';
    }).join('');
    document.getElementById('btnEditFromView').onclick = function() { closeModal('modalViewProduk'); editProduk(id); };
    openModal('modalViewProduk');
}

function editProduk(id) {
    const p = productsData[id]; if (!p) { alert('Data tidak ditemukan'); return; }
    document.getElementById('epForm').action = '/products/' + id;
    document.getElementById('epCode').value          = p.code || '';
    document.getElementById('epName').value          = p.name || '';
    document.getElementById('epCategoryId').value    = p.category_id || '';
    document.getElementById('epUnitId').value        = p.unit_id || '';
    document.getElementById('epDesc').value          = p.description || '';
    document.getElementById('epPurchasePrice').value = p.purchase_price || 0;
    document.getElementById('epSellingPrice').value  = p.selling_price || 0;
    document.getElementById('epMinStock').value      = p.min_stock || 5;
    document.getElementById('epRackLocation').value  = p.rack_location || '';
    document.getElementById('epIsActive').checked    = !!p.is_active;
    loadEditImage(p.image_url || null);
    openModal('modalEditProduk');
}

function autoGenerateCode() {
    const ts   = Date.now().toString().slice(-6);
    const rand = Math.floor(Math.random()*100).toString().padStart(2,'0');
    document.getElementById('npCode').value = 'PRD' + ts + rand;
}

// Scan barcode via USB scanner (langsung input ke field)
document.addEventListener('DOMContentLoaded', function() {
    autoGenerateCode();
});
</script>

{{-- ======== MODAL VIEW PRODUK ======== --}}
<div class="modal-backdrop" id="modalViewProduk">
    <div class="modal-box" style="max-width:520px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-box" style="color:var(--primary)"></i> <span id="viewProdukTitle">Detail Produk</span></div>
            <button class="modal-close" onclick="closeModal('modalViewProduk')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="viewProdukBody"></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modalViewProduk')">Tutup</button>
            <button type="button" class="btn btn-warning" id="btnEditFromView"><i class="fas fa-edit"></i> Edit</button>
        </div>
    </div>
</div>

{{-- ======== MODAL TAMBAH PRODUK (disederhanakan: nama/kategori/satuan/deskripsi/rak) ======== --}}
<div class="modal-backdrop" id="modalTambahProduk">
    <div class="modal-box" style="max-width:580px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-plus-circle" style="color:var(--primary)"></i> Tambah Produk Baru</div>
            <button class="modal-close" onclick="closeModal('modalTambahProduk')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                {{-- Foto Produk --}}
                <div class="form-group">
                    <label class="form-label">Foto Produk</label>
                    <div style="display:flex;gap:12px;align-items:flex-start;">
                        <div id="npImagePreview"
                            style="width:88px;height:88px;background:#f8fafc;border:2px dashed #e2e8f0;border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;overflow:hidden;flex-shrink:0;transition:border-color .15s;"
                            onclick="document.getElementById('npImageInput').click()"
                            onmouseover="this.style.borderColor='var(--primary)'"
                            onmouseout="this.style.borderColor='#e2e8f0'">
                            <div style="text-align:center;color:#94a3b8;" id="npImgPlaceholder">
                                <i class="fas fa-image" style="font-size:24px;display:block;margin-bottom:4px;"></i>
                                <span style="font-size:10px;">Klik upload</span>
                            </div>
                        </div>
                        <div style="flex:1;">
                            <input type="file" name="image" id="npImageInput" accept="image/*" style="display:none;" onchange="previewNewImage(this)">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('npImageInput').click()">
                                <i class="fas fa-upload"></i> Pilih Foto
                            </button>
                            <div style="font-size:11px;color:#94a3b8;margin-top:6px;">JPG, PNG, maks 2MB. Opsional.</div>
                        </div>
                    </div>
                </div>

                {{-- Kode / Barcode --}}
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 14px;margin-bottom:16px;">
                    <div style="font-size:12px;color:#15803d;font-weight:600;margin-bottom:8px;">
                        <i class="fas fa-barcode"></i> Kode Produk
                    </div>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <input type="text" name="code" id="npCode" class="form-control"
                            placeholder="Scan barcode atau ketik kode"
                            style="flex:1;font-family:monospace;font-weight:600;"
                            onkeydown="if(event.key==='Enter'){event.preventDefault();document.getElementById('npName').focus();}">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="autoGenerateCode()">
                            <i class="fas fa-sync-alt"></i> Generate
                        </button>
                    </div>
                    <div style="font-size:11px;color:#64748b;margin-top:5px;">USB scanner: scan ke field ini lalu Enter</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Produk <span class="required">*</span></label>
                    <input type="text" name="name" id="npName" class="form-control" required placeholder="Nama lengkap produk">
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Kategori <span class="required">*</span></label>
                        <select name="category_id" class="form-control" required>
                            <option value="">-- Pilih --</option>
                            @foreach($allCategories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Satuan <span class="required">*</span></label>
                        <select name="unit_id" class="form-control" required>
                            <option value="">-- Pilih --</option>
                            @foreach($allUnits as $u)<option value="{{ $u->id }}">{{ $u->name }} ({{ $u->symbol }})</option>@endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Lokasi Rak</label>
                    <select name="rack_id" class="form-control">
                        <option value="">-- Pilih Rak (opsional) --</option>
                        @foreach($allRacks as $rack)
                        <option value="{{ $rack->id }}">{{ $rack->warehouse->name }} - {{ $rack->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Deskripsi singkat produk..."></textarea>
                </div>

                <input type="hidden" name="purchase_price" value="0">
                <input type="hidden" name="selling_price" value="0">
                <input type="hidden" name="stock" value="0">
                <input type="hidden" name="min_stock" value="5">
                <input type="hidden" name="is_active" value="1">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahProduk')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

{{-- ======== MODAL EDIT PRODUK ======== --}}
<div class="modal-backdrop" id="modalEditProduk">
    <div class="modal-box" style="max-width:700px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-edit" style="color:#f59e0b"></i> Edit Produk</div>
            <button class="modal-close" onclick="closeModal('modalEditProduk')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="epForm" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="modal-body">
                {{-- Foto Produk --}}
                <div class="form-group">
                    <label class="form-label">Foto Produk</label>
                    <div style="display:flex;gap:12px;align-items:flex-start;">
                        <div id="epImagePreview"
                            style="width:88px;height:88px;background:#f8fafc;border:2px dashed #e2e8f0;border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;overflow:hidden;flex-shrink:0;transition:border-color .15s;"
                            onclick="document.getElementById('epImageInput').click()"
                            onmouseover="this.style.borderColor='var(--primary)'"
                            onmouseout="this.style.borderColor='#e2e8f0'">
                            <div style="text-align:center;color:#94a3b8;" id="epImgPlaceholder">
                                <i class="fas fa-image" style="font-size:24px;display:block;margin-bottom:4px;"></i>
                                <span style="font-size:10px;">Klik ganti</span>
                            </div>
                        </div>
                        <div style="flex:1;">
                            <input type="file" name="image" id="epImageInput" accept="image/*" style="display:none;" onchange="previewEditImage(this)">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('epImageInput').click()">
                                <i class="fas fa-upload"></i> Ganti Foto
                            </button>
                            <div style="font-size:11px;color:#94a3b8;margin-top:6px;">Kosongkan jika tidak ingin mengubah foto.</div>
                        </div>
                    </div>
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Kode Produk <span class="required">*</span></label>
                        <input type="text" name="code" id="epCode" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama Produk <span class="required">*</span></label>
                        <input type="text" name="name" id="epName" class="form-control" required>
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Kategori <span class="required">*</span></label>
                        <select name="category_id" id="epCategoryId" class="form-control" required>
                            <option value="">-- Pilih --</option>
                            @foreach($allCategories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Satuan <span class="required">*</span></label>
                        <select name="unit_id" id="epUnitId" class="form-control" required>
                            <option value="">-- Pilih --</option>
                            @foreach($allUnits as $u)<option value="{{ $u->id }}">{{ $u->name }} ({{ $u->symbol }})</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Harga Beli</label>
                        <input type="number" name="purchase_price" id="epPurchasePrice" class="form-control" min="0" step="100">
                        <div class="form-hint">Update otomatis saat Stok Masuk</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Harga Jual</label>
                        <input type="number" name="selling_price" id="epSellingPrice" class="form-control" min="0" step="100">
                        <div class="form-hint">Update otomatis saat Stok Masuk</div>
                    </div>
                </div>
                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Min. Stok Alert</label>
                        <input type="number" name="min_stock" id="epMinStock" class="form-control" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lokasi Rak</label>
                        <input type="text" name="rack_location" id="epRackLocation" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" id="epDesc" class="form-control" rows="2"></textarea>
                </div>
                <div class="toggle-wrap">
                    <label class="toggle"><input type="checkbox" name="is_active" id="epIsActive" value="1"><span class="toggle-slider"></span></label>
                    <span style="font-size:13px;color:#64748b;">Produk aktif</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditProduk')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Perbarui Produk</button>
            </div>
        </form>
    </div>
</div>

@endsection


@push('scripts')
<script>
// ---- Kategori modal functions ----
function editKategori(id, name, code, desc, active) {
    document.getElementById('formEditKategori').action = '/categories/' + id;
    document.getElementById('ekName').value = name;
    document.getElementById('ekCode').value = code;
    document.getElementById('ekDesc').value = desc;
    document.getElementById('ekActive').checked = active;
    openModal('modalEditKategori');
}

// ---- Satuan modal functions ----
function editSatuan(id, name, symbol, desc, active) {
    document.getElementById('formEditSatuan').action = '/units/' + id;
    document.getElementById('esName').value   = name;
    document.getElementById('esSymbol').value = symbol;
    document.getElementById('esDesc').value   = desc;
    document.getElementById('esActive').checked = active;
    openModal('modalEditSatuan');
}

// ---- Supplier modal functions ----
function editSupplier(id) {
    const s = suppliersData[id]; if (!s) return;
    document.getElementById('formEditSupplier').action = '/suppliers/' + id;
    document.getElementById('espName').value    = s.name || '';
    document.getElementById('espCode').value    = s.code || '';
    document.getElementById('espContact').value = s.contact_person || '';
    document.getElementById('espPhone').value   = s.phone || '';
    document.getElementById('espEmail').value   = s.email || '';
    document.getElementById('espCity').value    = s.city || '';
    document.getElementById('espAddress').value = s.address || '';
    document.getElementById('espActive').checked = !!s.is_active;
    openModal('modalEditSupplier');
}

// ---- Gudang & Rak modal functions ----
function editGudang(id) {
    const w = warehousesData[id]; if (!w) return;
    document.getElementById('formEditGudang').action = '/warehouses/' + id;
    document.getElementById('egName').value     = w.name || '';
    document.getElementById('egCode').value     = w.code || '';
    document.getElementById('egLocation').value = w.location || '';
    document.getElementById('egDesc').value     = w.description || '';
    document.getElementById('egActive').checked = !!w.is_active;
    openModal('modalEditGudang');
}
function editRak(id) {
    const r = racksData[id]; if (!r) return;
    document.getElementById('formEditRak').action = '/racks/' + id;
    document.getElementById('erWarehouse').value = r.warehouse_id;
    document.getElementById('erName').value  = r.name || '';
    document.getElementById('erCode').value  = r.code || '';
    document.getElementById('erRow').value   = r.row || '';
    document.getElementById('erCol').value   = r.column || '';
    document.getElementById('erActive').checked = !!r.is_active;
    openModal('modalEditRak');
}

// ---- Tipe Keluar modal functions ----
function editTipe(id) {
    const t = typesData[id]; if (!t) return;
    document.getElementById('formEditTipe').action = '/stock-out-types/' + id;
    document.getElementById('etName').value   = t.name || '';
    document.getElementById('etCode').value   = t.code || '';
    document.getElementById('etColor').value  = t.color || 'secondary';
    document.getElementById('etSort').value   = t.sort_order || 0;
    document.getElementById('etIcon').value   = t.icon || '';
    document.getElementById('etAffects').checked = !!t.affects_stock;
    document.getElementById('etActive').checked  = !!t.is_active;
    openModal('modalEditTipe');
}

// ---- Foto produk preview ----
function previewNewImage(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('npImagePreview');
        document.getElementById('npImgPlaceholder').style.display = 'none';
        // Remove old img if any
        const oldImg = preview.querySelector('img.preview-img');
        if (oldImg) oldImg.remove();
        const img = document.createElement('img');
        img.src = e.target.result;
        img.className = 'preview-img';
        img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
        preview.appendChild(img);
    };
    reader.readAsDataURL(input.files[0]);
}

function previewEditImage(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('epImagePreview');
        document.getElementById('epImgPlaceholder').style.display = 'none';
        const oldImg = preview.querySelector('img.preview-img');
        if (oldImg) oldImg.remove();
        const img = document.createElement('img');
        img.src = e.target.result;
        img.className = 'preview-img';
        img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
        preview.appendChild(img);
    };
    reader.readAsDataURL(input.files[0]);
}

// Tampilkan foto existing saat modal edit dibuka
function loadEditImage(imageUrl) {
    const preview = document.getElementById('epImagePreview');
    const placeholder = document.getElementById('epImgPlaceholder');
    const oldImg = preview.querySelector('img.preview-img');
    if (oldImg) oldImg.remove();

    if (imageUrl) {
        placeholder.style.display = 'none';
        const img = document.createElement('img');
        img.src = imageUrl;
        img.className = 'preview-img';
        img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
        preview.appendChild(img);
    } else {
        placeholder.style.display = '';
    }
}
</script>
@endpush
