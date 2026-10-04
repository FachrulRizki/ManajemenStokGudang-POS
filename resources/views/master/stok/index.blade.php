@extends('layouts.app')
@section('title', 'Manajemen Stok')
@push('breadcrumb_content', 'Master Data / <strong>Stok</strong>')

@push('styles')
<style>
.stok-tab-nav { display:flex; gap:0; border-bottom:2px solid #e2e8f0; margin-bottom:20px; }
.stok-tab-btn {
    padding:10px 22px; font-size:13.5px; font-weight:500; color:#64748b;
    background:none; border:none; border-bottom:2px solid transparent;
    margin-bottom:-2px; cursor:pointer; white-space:nowrap; transition:all .15s;
    display:flex; align-items:center; gap:8px; text-decoration:none;
}
.stok-tab-btn.active { color:var(--primary); border-bottom-color:var(--primary); }
.stok-tab-btn:hover:not(.active) { color:#374151; background:#f8fafc; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Manajemen Stok</div>
        <div class="page-subtitle">
            @if($tab === 'masuk')
                Catat penerimaan barang dari supplier — harga produk diperbarui otomatis
            @else
                SO (Stok Opname) — penyesuaian stok: rusak, retur, expired, dll
            @endif
        </div>
    </div>
    @if($tab === 'masuk')
        <button class="btn btn-success" onclick="openModal('modalTambahMasuk')">
            <i class="fas fa-plus"></i> Catat Stok Masuk
        </button>
    @else
        <button class="btn btn-warning" onclick="openModal('modalTambahKeluar')">
            <i class="fas fa-clipboard-list"></i> Catat SO
        </button>
    @endif
</div>

{{-- Tab Navigation --}}
<div class="stok-tab-nav">
    <a href="{{ route('stok.index', array_merge(request()->except(['tab','si_page','so_page']), ['tab'=>'masuk'])) }}"
        class="stok-tab-btn {{ $tab==='masuk' ? 'active' : '' }}">
        <i class="fas fa-arrow-circle-down" style="color:#10b981;"></i> Stok Masuk
    </a>
    <a href="{{ route('stok.index', array_merge(request()->except(['tab','si_page','so_page']), ['tab'=>'so'])) }}"
        class="stok-tab-btn {{ $tab==='so' ? 'active' : '' }}">
        <i class="fas fa-clipboard-check" style="color:#f59e0b;"></i> SO (Stok Opname)
    </a>
</div>

{{-- ============ TAB STOK MASUK ============ --}}
@if($tab === 'masuk')

<div class="grid grid-2" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-boxes"></i></div>
        <div class="stat-content"><div class="stat-value">{{ number_format($totalQtyIn) }}</div><div class="stat-label">Total Unit Diterima</div></div>
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
                <a href="{{ route('stok.index', ['tab'=>'masuk']) }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
            </div>
        </form>
    </div>
</div>

@php
$stockInJson = $stockIns->keyBy('id')->map(function($si) {
    return [
        'id'                  => $si->id,
        'reference_number'    => $si->reference_number,
        'transaction_date'    => $si->transaction_date->format('Y-m-d'),
        'transaction_date_lbl'=> $si->transaction_date->format('d F Y'),
        'product_name'        => $si->product->name ?? '-',
        'product_code'        => $si->product->code ?? '-',
        'unit_symbol'         => $si->product->unit->symbol ?? '',
        'supplier_id'         => $si->supplier_id,
        'supplier_name'       => $si->supplier->name ?? '-',
        'quantity'            => $si->quantity,
        'quantity_fmt'        => number_format($si->quantity),
        'purchase_price'      => (float)$si->purchase_price,
        'selling_price'       => (float)($si->product->selling_price ?? 0),
        'purchase_price_fmt'  => 'Rp ' . number_format($si->purchase_price, 0, ',', '.'),
        'total_price'         => 'Rp ' . number_format($si->total_price, 0, ',', '.'),
        'invoice_number'      => $si->invoice_number ?? '',
        'user_name'           => $si->user->name ?? '-',
        'notes'               => $si->notes ?? '-',
        'created_at'          => $si->created_at->format('d M Y H:i'),
        'destroy_url'         => route('stok.masuk.destroy', $si->id),
        'update_url'          => route('stok.masuk.update', $si->id),
    ];
})->toArray();
@endphp
<script>const stockInData = {!! json_encode($stockInJson) !!};</script>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th>No</th><th>No. Referensi</th><th>Produk</th><th>Supplier</th>
                <th style="text-align:center;">Qty</th>
                <th style="text-align:right;">Harga Beli</th>
                <th style="text-align:right;">Total</th>
                <th>Tanggal</th><th>Oleh</th>
                <th style="text-align:center;">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse($stockIns as $i => $si)
                <tr>
                    <td style="color:#94a3b8;">{{ $stockIns->firstItem() + $i }}</td>
                    <td>
                        <button type="button" style="background:#f1f5f9;border:none;padding:2px 6px;border-radius:4px;font-family:monospace;font-size:11.5px;color:#374151;cursor:pointer;"
                            onclick="showDetailMasuk({{ $si->id }})">{{ $si->reference_number }}</button>
                    </td>
                    <td>
                        <div style="font-weight:600;font-size:13px;">{{ $si->product->name ?? '-' }}</div>
                        <div style="font-size:11px;color:#94a3b8;">{{ $si->product->code ?? '' }}</div>
                    </td>
                    <td style="font-size:13px;color:#64748b;">{{ $si->supplier->name ?? '-' }}</td>
                    <td style="text-align:center;"><span style="color:#10b981;font-weight:700;">+{{ number_format($si->quantity) }}</span></td>
                    <td style="text-align:right;font-size:13px;">Rp {{ number_format($si->purchase_price,0,',','.') }}</td>
                    <td style="text-align:right;font-weight:600;font-size:13px;">Rp {{ number_format($si->total_price,0,',','.') }}</td>
                    <td style="font-size:12px;color:#64748b;white-space:nowrap;">{{ $si->transaction_date->format('d M Y') }}</td>
                    <td style="font-size:12px;color:#64748b;">{{ $si->user->name ?? '-' }}</td>
                    <td style="text-align:center;">
                        <div class="btn-group" style="justify-content:center;">
                            <button type="button" class="btn btn-sm btn-warning" title="Edit" onclick="editMasuk({{ $si->id }})"><i class="fas fa-edit"></i></button>
                            <button type="button" class="btn btn-sm btn-secondary" title="Detail" onclick="showDetailMasuk({{ $si->id }})"><i class="fas fa-eye"></i></button>
                            <form method="POST" action="{{ route('stok.masuk.destroy', $si) }}" onsubmit="return confirmDelete(this)">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
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
        <span>{{ $stockIns->firstItem()??0 }}-{{ $stockIns->lastItem()??0 }} dari {{ $stockIns->total() }}</span>
        {{ $stockIns->appends(request()->except('si_page'))->links('vendor.pagination.simple') }}
    </div>
</div>

{{-- ============ TAB SO / STOK OPNAME ============ --}}
@elseif($tab === 'so')

<div class="alert alert-info" style="margin-bottom:16px;">
    <i class="fas fa-info-circle"></i>
    <div>
        <strong>SO (Stok Opname)</strong> — Catat perbedaan stok antara fisik dan sistem.
        Gunakan untuk: barang rusak, kadaluarsa, retur ke supplier, atau penyesuaian lainnya.
        Penjualan ke pelanggan tercatat otomatis via <a href="{{ route('pos.kasir') }}" style="font-weight:600;">Kasir POS</a>.
    </div>
</div>

<div class="grid grid-2" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-clipboard-check"></i></div>
        <div class="stat-content"><div class="stat-value">{{ number_format($totalQtyOut) }}</div><div class="stat-label">Total Unit Disesuaikan</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-content"><div class="stat-value" style="font-size:18px;">Rp {{ number_format($totalValueOut,0,',','.') }}</div><div class="stat-label">Total Nilai Penyesuaian</div></div>
    </div>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET">
            <input type="hidden" name="tab" value="so">
            <div class="filter-bar">
                <div class="search-input"><i class="fas fa-search"></i>
                    <input type="text" name="search" class="form-control" placeholder="No. referensi atau nama produk..." value="{{ request('search') }}">
                </div>
                <select name="so_type_id" class="form-control" style="width:180px;">
                    <option value="">Semua Kategori SO</option>
                    @foreach($outTypes as $ot)
                    <option value="{{ $ot->id }}" {{ request('so_type_id')==$ot->id?'selected':'' }}>{{ $ot->name }}</option>
                    @endforeach
                </select>
                <input type="date" name="date_from" class="form-control" style="width:155px;" value="{{ request('date_from') }}">
                <input type="date" name="date_to" class="form-control" style="width:155px;" value="{{ request('date_to') }}">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                <a href="{{ route('stok.index', ['tab'=>'so']) }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
            </div>
        </form>
    </div>
</div>

@php
$stockOutJson = $stockOuts->keyBy('id')->map(function($so) {
    return [
        'id'               => $so->id,
        'reference_number' => $so->reference_number,
        'transaction_date' => $so->transaction_date->format('d F Y'),
        'product_name'     => $so->product->name ?? '-',
        'product_code'     => $so->product->code ?? '-',
        'unit_symbol'      => $so->product->unit->symbol ?? '',
        'so_type'          => $so->stockOutType->name ?? '-',
        'so_type_color'    => $so->stockOutType->color ?? 'secondary',
        'customer_name'    => $so->customer_name ?? '-',
        'quantity'         => number_format($so->quantity),
        'total_price'      => 'Rp ' . number_format($so->total_price, 0, ',', '.'),
        'user_name'        => $so->user->name ?? '-',
        'notes'            => $so->notes ?? '-',
        'created_at'       => $so->created_at->format('d M Y H:i'),
        'destroy_url'      => route('stok.keluar.destroy', $so->id),
    ];
})->toArray();
@endphp
<script>const stockOutData = {!! json_encode($stockOutJson) !!};</script>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th>No</th><th>No. Referensi</th><th>Produk</th>
                <th style="text-align:center;">Kategori SO</th>
                <th style="text-align:center;">Qty</th>
                <th style="text-align:right;">Nilai</th>
                <th>Tanggal</th><th>Oleh</th>
                <th style="text-align:center;">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse($stockOuts as $i => $so)
                <tr>
                    <td style="color:#94a3b8;">{{ $stockOuts->firstItem() + $i }}</td>
                    <td>
                        <button type="button" style="background:#f1f5f9;border:none;padding:2px 6px;border-radius:4px;font-family:monospace;font-size:11.5px;color:#374151;cursor:pointer;"
                            onclick="showDetailSO({{ $so->id }})">{{ $so->reference_number }}</button>
                    </td>
                    <td>
                        <div style="font-weight:600;font-size:13px;">{{ $so->product->name ?? '-' }}</div>
                        <div style="font-size:11px;color:#94a3b8;">{{ $so->product->code ?? '' }}</div>
                    </td>
                    <td style="text-align:center;">
                        @if($so->stockOutType)
                            <span class="badge badge-{{ $so->stockOutType->color }}">{{ $so->stockOutType->name }}</span>
                        @else
                            <span class="badge badge-secondary">-</span>
                        @endif
                    </td>
                    <td style="text-align:center;"><span style="color:#ef4444;font-weight:700;">-{{ number_format($so->quantity) }}</span></td>
                    <td style="text-align:right;font-weight:600;font-size:13px;">Rp {{ number_format($so->total_price,0,',','.') }}</td>
                    <td style="font-size:12px;color:#64748b;white-space:nowrap;">{{ $so->transaction_date->format('d M Y') }}</td>
                    <td style="font-size:12px;color:#64748b;">{{ $so->user->name ?? '-' }}</td>
                    <td style="text-align:center;">
                        <div class="btn-group" style="justify-content:center;">
                            <button type="button" class="btn btn-sm btn-secondary" title="Detail" onclick="showDetailSO({{ $so->id }})"><i class="fas fa-eye"></i></button>
                            <form method="POST" action="{{ route('stok.keluar.destroy', $so) }}" onsubmit="return confirmDelete(this)">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9"><div class="empty-state"><i class="fas fa-clipboard-check"></i><p>Belum ada catatan SO.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <span>{{ $stockOuts->firstItem()??0 }}-{{ $stockOuts->lastItem()??0 }} dari {{ $stockOuts->total() }}</span>
        {{ $stockOuts->appends(request()->except('so_page'))->links('vendor.pagination.simple') }}
    </div>
</div>
@endif

{{-- ======== MODAL FORM STOK MASUK ======== --}}
<div class="modal-backdrop" id="modalTambahMasuk">
    <div class="modal-box" style="max-width:660px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-arrow-circle-down" style="color:#10b981"></i> Catat Stok Masuk</div>
            <button class="modal-close" onclick="closeModal('modalTambahMasuk')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('stok.masuk.store') }}">
            @csrf
            <div class="modal-body">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;padding:8px 12px;background:#f0fdf4;border-radius:8px;border:1px solid #bbf7d0;">
                    <span style="font-size:12px;color:#15803d;font-weight:500;"><i class="fas fa-hashtag"></i> No. Referensi Auto-Generate</span>
                    <span style="font-family:monospace;font-weight:700;color:#15803d;font-size:13px;">{{ $siRef }}</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Produk <span class="required">*</span></label>
                    <select name="product_id" id="siProduct" class="form-control" required onchange="loadSiProduct(this)">
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $p)
                        <option value="{{ $p->id }}"
                            data-purchase="{{ $p->purchase_price }}"
                            data-selling="{{ $p->selling_price }}"
                            data-stock="{{ $p->stock }}"
                            data-minstok="{{ $p->min_stock }}"
                            data-unit="{{ $p->unit->symbol ?? '' }}">
                            {{ $p->code }} - {{ $p->name }} (Stok: {{ $p->stock }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div id="siProductInfo" style="display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 14px;margin-bottom:14px;">
                    <div style="display:flex;gap:20px;font-size:13px;">
                        <div>Stok sekarang: <strong id="siCurrentStock" style="color:#16a34a;"></strong></div>
                        <div>Harga beli terakhir: <strong id="siLastPrice"></strong></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-control">
                        <option value="">-- Pilih Supplier (opsional) --</option>
                        @foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                    </select>
                </div>

                {{-- Harga beli, harga jual, jumlah --}}
                <div class="form-row cols-3">
                    <div class="form-group">
                        <label class="form-label">Harga Beli/Unit <span class="required">*</span></label>
                        <input type="number" name="purchase_price" id="siPurchasePrice" class="form-control" min="0" step="100" value="0" required oninput="calcSiTotal()">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Harga Jual/Unit <span class="required">*</span></label>
                        <input type="number" name="selling_price" id="siSellingPrice" class="form-control" min="0" step="100" value="0" required>
                        <div class="form-hint">Akan diperbarui di produk</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jumlah <span class="required">*</span></label>
                        <input type="number" name="quantity" id="siQty" class="form-control" min="1" value="1" required oninput="calcSiTotal()">
                    </div>
                </div>

                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 14px;margin-bottom:14px;text-align:right;">
                    Total Nilai Pembelian: <strong id="siTotal" style="font-size:16px;color:#16a34a;">Rp 0</strong>
                </div>

                <div class="form-row cols-3">
                    <div class="form-group">
                        <label class="form-label">Min. Stok Alert</label>
                        <input type="number" name="min_stock" id="siMinStock" class="form-control" min="0" value="5">
                        <div class="form-hint">Akan diperbarui di produk</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal <span class="required">*</span></label>
                        <input type="date" name="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. Invoice / Faktur</label>
                        <input type="text" name="invoice_number" class="form-control" placeholder="INV-001">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahMasuk')">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan Stok Masuk</button>
            </div>
        </form>
    </div>
</div>

{{-- ======== MODAL FORM SO (STOK OPNAME) ======== --}}
<div class="modal-backdrop" id="modalTambahKeluar">
    <div class="modal-box" style="max-width:580px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-clipboard-check" style="color:#f59e0b"></i> Catat SO (Stok Opname)</div>
            <button class="modal-close" onclick="closeModal('modalTambahKeluar')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('stok.keluar.store') }}">
            @csrf
            <div class="modal-body">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;padding:8px 12px;background:#fffbeb;border-radius:8px;border:1px solid #fde68a;">
                    <span style="font-size:12px;color:#d97706;font-weight:500;"><i class="fas fa-hashtag"></i> No. Referensi Auto-Generate</span>
                    <span style="font-family:monospace;font-weight:700;color:#d97706;font-size:13px;">{{ $soRef }}</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Produk <span class="required">*</span></label>
                    <select name="product_id" id="soProduct" class="form-control" required onchange="loadSoProduct(this)">
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $p)
                        <option value="{{ $p->id }}" data-stock="{{ $p->stock }}" data-unit="{{ $p->unit->symbol ?? '' }}" data-price="{{ $p->selling_price }}">
                            {{ $p->code }} - {{ $p->name }} (Stok: {{ $p->stock }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div id="soProductInfo" style="display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:13px;">
                    Stok tersedia saat ini: <strong id="soCurrentStock" style="color:#dc2626;"></strong>
                </div>

                <div class="form-group">
                    <label class="form-label">Kategori SO <span class="required">*</span></label>
                    <select name="stock_out_type_id" class="form-control" required>
                        <option value="">-- Pilih Kategori SO --</option>
                        @foreach($outTypes as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-hint">Wajib diisi — tentukan jenis penyesuaian stok ini</div>
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Jumlah <span class="required">*</span></label>
                        <input type="number" name="quantity" id="soQty" class="form-control" min="1" value="1" required oninput="calcSoTotal()">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nilai Referensi/Unit</label>
                        <input type="number" name="selling_price" id="soPrice" class="form-control" min="0" step="100" value="0" oninput="calcSoTotal()">
                        <div class="form-hint">Opsional — estimasi nilai kerugian</div>
                    </div>
                </div>

                <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px 14px;margin-bottom:14px;text-align:right;">
                    Estimasi Nilai Penyesuaian: <strong id="soTotal" style="font-size:16px;color:#d97706;">Rp 0</strong>
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Tanggal <span class="required">*</span></label>
                        <input type="date" name="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">PIC / Penanggung Jawab</label>
                        <input type="text" name="customer_name" class="form-control" placeholder="Nama PIC (opsional)">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Alasan / Catatan <span class="required">*</span></label>
                    <textarea name="notes" class="form-control" rows="3"
                        placeholder="Jelaskan alasan penyesuaian stok ini secara detail..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahKeluar')">Batal</button>
                <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Simpan SO</button>
            </div>
        </form>
    </div>
</div>

{{-- ======== MODAL DETAIL ======== --}}
<div class="modal-backdrop" id="modalDetail">
    <div class="modal-box" style="max-width:500px;">
        <div class="modal-header">
            <div class="modal-title" id="detailTitle">Detail</div>
            <button class="modal-close" onclick="closeModal('modalDetail')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="detailBody"></div>
        <div class="modal-footer" id="detailFooter"></div>
    </div>
</div>

{{-- ======== MODAL EDIT STOK MASUK ======== --}}
<div class="modal-backdrop" id="modalEditMasuk">
    <div class="modal-box" style="max-width:640px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-edit" style="color:#f59e0b"></i> Edit Stok Masuk</div>
            <button class="modal-close" onclick="closeModal('modalEditMasuk')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="formEditMasuk">
            @csrf @method('PUT')
            <div class="modal-body">
                <div id="editMasukRefBar" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;padding:8px 12px;background:#fef3c7;border-radius:8px;border:1px solid #fde68a;">
                    <span style="font-size:12px;color:#d97706;font-weight:500;"><i class="fas fa-edit"></i> Edit Catatan</span>
                    <span id="editMasukRef" style="font-family:monospace;font-weight:700;color:#d97706;font-size:13px;"></span>
                </div>

                <div class="alert alert-warning" style="margin-bottom:16px;font-size:12.5px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    Mengubah jumlah akan otomatis menyesuaikan stok produk. Selisih qty akan ditambah/dikurangi dari stok saat ini.
                </div>

                <div id="editMasukProductInfo" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:13px;display:none;">
                    Produk: <strong id="editMasukProductName"></strong> &bull; Stok saat ini: <strong id="editMasukCurrentStock" style="color:#10b981;"></strong>
                </div>

                <div class="form-group">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" id="emSupplierId" class="form-control">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                    </select>
                </div>

                <div class="form-row cols-3">
                    <div class="form-group">
                        <label class="form-label">Harga Beli/Unit <span class="required">*</span></label>
                        <input type="number" name="purchase_price" id="emPurchasePrice" class="form-control" min="0" step="100" required oninput="calcEmTotal()">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Harga Jual/Unit <span class="required">*</span></label>
                        <input type="number" name="selling_price" id="emSellingPrice" class="form-control" min="0" step="100" required>
                        <div class="form-hint">Diperbarui di produk</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jumlah <span class="required">*</span></label>
                        <input type="number" name="quantity" id="emQuantity" class="form-control" min="1" required oninput="calcEmTotal()">
                    </div>
                </div>

                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 14px;margin-bottom:14px;text-align:right;">
                    Total Nilai: <strong id="emTotal" style="font-size:16px;color:#16a34a;">Rp 0</strong>
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Tanggal <span class="required">*</span></label>
                        <input type="date" name="transaction_date" id="emDate" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. Invoice</label>
                        <input type="text" name="invoice_number" id="emInvoice" class="form-control">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" id="emNotes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditMasuk')">Batal</button>
                <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Perbarui Stok Masuk</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// -- Form stok masuk ------------------------------------
function loadSiProduct(sel) {
    const opt = sel.selectedOptions[0];
    if (!opt.value) { document.getElementById('siProductInfo').style.display='none'; return; }
    document.getElementById('siCurrentStock').textContent    = opt.dataset.stock + ' ' + opt.dataset.unit;
    document.getElementById('siLastPrice').textContent       = 'Rp ' + parseInt(opt.dataset.purchase).toLocaleString('id-ID');
    document.getElementById('siPurchasePrice').value         = opt.dataset.purchase;
    document.getElementById('siSellingPrice').value          = opt.dataset.selling;
    document.getElementById('siMinStock').value              = opt.dataset.minstok;
    document.getElementById('siProductInfo').style.display  = '';
    calcSiTotal();
}
function calcSiTotal() {
    const q = parseInt(document.getElementById('siQty').value) || 0;
    const p = parseInt(document.getElementById('siPurchasePrice').value) || 0;
    document.getElementById('siTotal').textContent = 'Rp ' + (q*p).toLocaleString('id-ID');
}

// -- Form SO ------------------------------------------
function loadSoProduct(sel) {
    const opt = sel.selectedOptions[0];
    if (!opt.value) { document.getElementById('soProductInfo').style.display='none'; return; }
    document.getElementById('soCurrentStock').textContent   = opt.dataset.stock + ' ' + opt.dataset.unit;
    document.getElementById('soPrice').value                = opt.dataset.price;
    document.getElementById('soProductInfo').style.display = '';
    calcSoTotal();
}
function calcSoTotal() {
    const q = parseInt(document.getElementById('soQty').value) || 0;
    const p = parseInt(document.getElementById('soPrice').value) || 0;
    document.getElementById('soTotal').textContent = 'Rp ' + (q*p).toLocaleString('id-ID');
}

// -- Detail modal stok masuk ---------------------------
function showDetailMasuk(id) {
    const d = stockInData[id]; if (!d) return;
    document.getElementById('detailTitle').innerHTML =
        '<i class="fas fa-arrow-circle-down" style="color:#10b981"></i> ' + d.reference_number;
    const rows = [
        ['Tanggal',         d.transaction_date_lbl || d.transaction_date],
        ['Produk',          '<strong>' + d.product_name + '</strong>'],
        ['Kode',            d.product_code],
        ['Supplier',        d.supplier_name],
        ['Jumlah Diterima', '<span style="color:#10b981;font-weight:700;">+' + d.quantity_fmt + ' ' + d.unit_symbol + '</span>'],
        ['Harga Beli/Unit', d.purchase_price_fmt],
        ['Total Nilai',     '<strong>' + d.total_price + '</strong>'],
        ['No. Invoice',     d.invoice_number || '-'],
        ['Dicatat Oleh',    d.user_name],
        ['Catatan',         d.notes],
    ];
    document.getElementById('detailBody').innerHTML = rows.map(function(r) {
        return '<div class="detail-row"><span class="detail-label">' + r[0] + '</span><span class="detail-value">' + r[1] + '</span></div>';
    }).join('') + '<div style="margin-top:10px;font-size:11px;color:#94a3b8;">Dibuat: ' + d.created_at + '</div>';
    document.getElementById('detailFooter').innerHTML =
        '<button type="button" class="btn btn-secondary" onclick="closeModal(\'modalDetail\')">Tutup</button>' +
        '<button type="button" class="btn btn-warning btn-sm" onclick="closeModal(\'modalDetail\');editMasuk(' + id + ')"><i class="fas fa-edit"></i> Edit</button>' +
        '<form method="POST" action="' + d.destroy_url + '" onsubmit="return confirmDelete(this)" style="display:inline;">' +
        '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
        '<input type="hidden" name="_method" value="DELETE">' +
        '<button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Hapus</button></form>';
    openModal('modalDetail');
}

// -- Edit stok masuk -----------------------------------
function editMasuk(id) {
    const d = stockInData[id]; if (!d) return;
    document.getElementById('formEditMasuk').action = d.update_url;
    document.getElementById('editMasukRef').textContent = d.reference_number;
    document.getElementById('emSupplierId').value       = d.supplier_id || '';
    document.getElementById('emPurchasePrice').value    = d.purchase_price;
    document.getElementById('emSellingPrice').value     = d.selling_price;
    document.getElementById('emQuantity').value         = d.quantity;
    document.getElementById('emDate').value             = d.transaction_date;
    document.getElementById('emInvoice').value          = (d.invoice_number === '-' ? '' : d.invoice_number);
    document.getElementById('emNotes').value            = (d.notes === '-' ? '' : d.notes);
    document.getElementById('editMasukProductName').textContent = d.product_name;
    document.getElementById('editMasukProductInfo').style.display = '';
    calcEmTotal();
    openModal('modalEditMasuk');
}
function calcEmTotal() {
    const q = parseInt(document.getElementById('emQuantity').value) || 0;
    const p = parseInt(document.getElementById('emPurchasePrice').value) || 0;
    document.getElementById('emTotal').textContent = 'Rp ' + (q * p).toLocaleString('id-ID');
}

// -- Detail modal SO -----------------------------------
function showDetailSO(id) {
    const d = stockOutData[id]; if (!d) return;
    document.getElementById('detailTitle').innerHTML =
        '<i class="fas fa-clipboard-check" style="color:#f59e0b"></i> ' + d.reference_number;
    const rows = [
        ['Tanggal',        d.transaction_date],
        ['Produk',         '<strong>' + d.product_name + '</strong>'],
        ['Kode',           d.product_code],
        ['Kategori SO',    '<span class="badge badge-' + d.so_type_color + '">' + d.so_type + '</span>'],
        ['PIC',            d.customer_name],
        ['Jumlah Disesuaikan', '<span style="color:#ef4444;font-weight:700;">-' + d.quantity + ' ' + d.unit_symbol + '</span>'],
        ['Nilai Penyesuaian', '<strong>' + d.total_price + '</strong>'],
        ['Dicatat Oleh',   d.user_name],
        ['Alasan',         d.notes],
    ];
    document.getElementById('detailBody').innerHTML = rows.map(function(r) {
        return '<div class="detail-row"><span class="detail-label">' + r[0] + '</span><span class="detail-value">' + r[1] + '</span></div>';
    }).join('') + '<div style="margin-top:10px;font-size:11px;color:#94a3b8;">Dibuat: ' + d.created_at + '</div>';
    document.getElementById('detailFooter').innerHTML =
        '<button type="button" class="btn btn-secondary" onclick="closeModal(\'modalDetail\')">Tutup</button>' +
        '<form method="POST" action="' + d.destroy_url + '" onsubmit="return confirmDelete(this)" style="display:inline;">' +
        '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
        '<input type="hidden" name="_method" value="DELETE">' +
        '<button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Hapus</button></form>';
    openModal('modalDetail');
}
</script>
@endpush
