@extends('layouts.app')
@section('title', 'Catat Stok Keluar')
@push('breadcrumb_content', 'Transaksi / <a href="' . route('stock-out.index') . '" style="color:inherit;">Stok Keluar</a> / <strong>Tambah</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Catat Stok Keluar</div>
        <div class="page-subtitle">Pencatatan pengeluaran barang non-penjualan (rusak, retur, penyesuaian, dll)</div>
    </div>
    <div class="btn-group">
        <a href="{{ route('pos.kasir') }}" class="btn btn-primary"><i class="fas fa-cash-register"></i> Ke Kasir POS</a>
        <a href="{{ route('stock-out.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
</div>

<div style="max-width:720px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-arrow-circle-up" style="color:#ef4444"></i> Form Stok Keluar</div>
            <span style="font-size:12px;color:#94a3b8;">No. Ref: <strong>{{ $refNumber }}</strong></span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('stock-out.store') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label">Produk <span class="required">*</span></label>
                    <select name="product_id" id="productSelect" class="form-control {{ $errors->has('product_id') ? 'is-invalid' : '' }}" onchange="loadProductInfo(this)">
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $p)
                        <option value="{{ $p->id }}"
                            data-stock="{{ $p->stock }}"
                            data-unit="{{ $p->unit->symbol ?? '' }}"
                            data-price="{{ $p->selling_price }}"
                            {{ (old('product_id', request('product_id')) == $p->id) ? 'selected' : '' }}>
                            {{ $p->code }} - {{ $p->name }} (Stok: {{ $p->stock }} {{ $p->unit->symbol ?? '' }})
                        </option>
                        @endforeach
                    </select>
                    @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div id="productInfo" style="display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px;margin-bottom:16px;">
                    <div style="display:flex;gap:24px;font-size:13px;">
                        <div><span style="color:#64748b;">Stok tersedia:</span> <strong id="infoStock" style="color:#ef4444;"></strong></div>
                        <div><span style="color:#64748b;">Satuan:</span> <strong id="infoUnit"></strong></div>
                        <div><span style="color:#64748b;">Harga jual:</span> <strong id="infoPrice"></strong></div>
                    </div>
                </div>

                <div class="form-row cols-2">
                    {{-- Tipe Keluar --}}
                    <div class="form-group">
                        <label class="form-label">Tipe Keluar <span class="required">*</span></label>
                        <select name="type" id="typeSelect" class="form-control {{ $errors->has('type') ? 'is-invalid' : '' }}">
                            <option value="return" {{ old('type','return')=='return'?'selected':'' }}>Retur ke Supplier</option>
                            <option value="damaged" {{ old('type')=='damaged'?'selected':'' }}>Rusak / Hilang</option>
                            <option value="other" {{ old('type')=='other'?'selected':'' }}>Lainnya</option>
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Tipe Detail dari Master --}}
                    <div class="form-group">
                        <label class="form-label">Kategori Keluar (Detail)</label>
                        <select name="stock_out_type_id" class="form-control">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($stockOutTypes as $t)
                            <option value="{{ $t->id }}" {{ old('stock_out_type_id') == $t->id ? 'selected' : '' }}>
                                {{ $t->name }}
                            </option>
                            @endforeach
                        </select>
                        <div class="form-hint">Opsional - untuk kategorisasi laporan yang lebih detail</div>
                    </div>
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Jumlah <span class="required">*</span></label>
                        <input type="number" name="quantity" id="qtyInput" class="form-control {{ $errors->has('quantity') ? 'is-invalid' : '' }}"
                            value="{{ old('quantity', 1) }}" min="1" oninput="calcTotal()">
                        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nilai/Unit</label>
                        <input type="number" name="selling_price" id="priceInput" class="form-control {{ $errors->has('selling_price') ? 'is-invalid' : '' }}"
                            value="{{ old('selling_price', 0) }}" min="0" step="100" oninput="calcTotal()">
                        <div class="form-hint">Harga jual untuk pencatatan nilai kerugian</div>
                        @error('selling_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px;margin-bottom:16px;text-align:right;">
                    <span style="font-size:13px;color:#64748b;">Nilai Kerugian: </span>
                    <strong id="totalDisplay" style="font-size:18px;color:#dc2626;">Rp 0</strong>
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Tanggal Transaksi <span class="required">*</span></label>
                        <input type="date" name="transaction_date" class="form-control {{ $errors->has('transaction_date') ? 'is-invalid' : '' }}"
                            value="{{ old('transaction_date', date('Y-m-d')) }}">
                        @error('transaction_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama Pelanggan / PIC</label>
                        <input type="text" name="customer_name" class="form-control"
                            value="{{ old('customer_name') }}" placeholder="Opsional">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan <span class="required">*</span></label>
                    <textarea name="notes" class="form-control {{ $errors->has('notes') ? 'is-invalid' : '' }}" rows="3"
                        placeholder="Jelaskan alasan pengeluaran barang ini...">{{ old('notes') }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:8px;border-top:1px solid #f1f5f9;margin-top:8px;">
                    <a href="{{ route('stock-out.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-save"></i> Simpan Stok Keluar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function loadProductInfo(sel) {
    const opt  = sel.selectedOptions[0];
    const info = document.getElementById('productInfo');
    if (!opt.value) { info.style.display = 'none'; return; }
    document.getElementById('infoStock').textContent = opt.dataset.stock + ' ' + opt.dataset.unit;
    document.getElementById('infoUnit').textContent  = opt.dataset.unit;
    document.getElementById('infoPrice').textContent = 'Rp ' + parseInt(opt.dataset.price).toLocaleString('id-ID');
    document.getElementById('priceInput').value      = opt.dataset.price;
    info.style.display = 'block';
    calcTotal();
}
function calcTotal() {
    const qty   = parseInt(document.getElementById('qtyInput').value)  || 0;
    const price = parseInt(document.getElementById('priceInput').value) || 0;
    document.getElementById('totalDisplay').textContent = 'Rp ' + (qty * price).toLocaleString('id-ID');
}
window.addEventListener('load', () => {
    const sel = document.getElementById('productSelect');
    if (sel.value) loadProductInfo(sel);
});
</script>
@endpush
