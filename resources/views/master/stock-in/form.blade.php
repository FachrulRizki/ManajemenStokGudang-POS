@extends('layouts.app')
@section('title', 'Catat Stok Masuk')
@push('breadcrumb_content', 'Transaksi / <a href="' . route('stock-in.index') . '" style="color:inherit;">Stok Masuk</a> / <strong>Tambah</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Catat Stok Masuk</div>
        <div class="page-subtitle">Tambah penerimaan barang baru dari supplier</div>
    </div>
    <a href="{{ route('stock-in.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    <a href="{{ route('stock-in.scan') }}" class="btn btn-primary"><i class="fas fa-barcode"></i> Scan Barcode</a>
</div>

<div style="max-width:720px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-arrow-circle-down" style="color:#10b981"></i> Form Stok Masuk</div>
            <span style="font-size:12px;color:#94a3b8;">No. Ref: <strong>{{ $refNumber }}</strong></span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('stock-in.store') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label">Produk <span class="required">*</span></label>
                    <select name="product_id" id="productSelect" class="form-control {{ $errors->has('product_id') ? 'is-invalid' : '' }}" onchange="loadProductInfo(this)">
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $p)
                        <option value="{{ $p->id }}"
                            data-stock="{{ $p->stock }}"
                            data-unit="{{ $p->unit->symbol ?? '' }}"
                            data-price="{{ $p->purchase_price }}"
                            {{ (old('product_id', request('product_id')) == $p->id) ? 'selected' : '' }}>
                            {{ $p->code }} - {{ $p->name }} (Stok: {{ $p->stock }} {{ $p->unit->symbol ?? '' }})
                        </option>
                        @endforeach
                    </select>
                    @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div id="productInfo" style="display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px;margin-bottom:16px;">
                    <div style="display:flex;gap:24px;font-size:13px;">
                        <div><span style="color:#64748b;">Stok saat ini:</span> <strong id="infoStock" style="color:#10b981;"></strong></div>
                        <div><span style="color:#64748b;">Satuan:</span> <strong id="infoUnit"></strong></div>
                        <div><span style="color:#64748b;">Harga beli terakhir:</span> <strong id="infoPrice"></strong></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-control">
                        <option value="">-- Pilih Supplier (opsional) --</option>
                        @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Jumlah <span class="required">*</span></label>
                        <input type="number" name="quantity" id="qtyInput" class="form-control {{ $errors->has('quantity') ? 'is-invalid' : '' }}"
                            value="{{ old('quantity', 1) }}" min="1" oninput="calcTotal()">
                        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Harga Beli/Unit <span class="required">*</span></label>
                        <input type="number" name="purchase_price" id="priceInput" class="form-control {{ $errors->has('purchase_price') ? 'is-invalid' : '' }}"
                            value="{{ old('purchase_price', 0) }}" min="0" step="100" oninput="calcTotal()">
                        @error('purchase_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px;margin-bottom:16px;text-align:right;">
                    <span style="font-size:13px;color:#64748b;">Total Nilai: </span>
                    <strong id="totalDisplay" style="font-size:18px;color:#0f172a;">Rp 0</strong>
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Tanggal Transaksi <span class="required">*</span></label>
                        <input type="date" name="transaction_date" class="form-control {{ $errors->has('transaction_date') ? 'is-invalid' : '' }}"
                            value="{{ old('transaction_date', date('Y-m-d')) }}">
                        @error('transaction_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. Invoice / Faktur</label>
                        <input type="text" name="invoice_number" class="form-control"
                            value="{{ old('invoice_number') }}" placeholder="INV-001">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:8px;border-top:1px solid #f1f5f9;margin-top:8px;">
                    <a href="{{ route('stock-in.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" id="submitBtn" class="btn btn-success"><i class="fas fa-save"></i> Simpan Stok Masuk</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function loadProductInfo(sel) {
    const opt = sel.selectedOptions[0];
    const info = document.getElementById('productInfo');
    if (!opt.value) { info.style.display = 'none'; return; }
    document.getElementById('infoStock').textContent = opt.dataset.stock + ' ' + opt.dataset.unit;
    document.getElementById('infoUnit').textContent = opt.dataset.unit;
    document.getElementById('infoPrice').textContent = 'Rp ' + parseInt(opt.dataset.price).toLocaleString('id-ID');
    document.getElementById('priceInput').value = opt.dataset.price;
    info.style.display = 'block';
    calcTotal();
}
function calcTotal() {
    const qty = parseInt(document.getElementById('qtyInput').value) || 0;
    const price = parseInt(document.getElementById('priceInput').value) || 0;
    document.getElementById('totalDisplay').textContent = 'Rp ' + (qty * price).toLocaleString('id-ID');
}
// Auto-load if product_id pre-filled
window.addEventListener('load', () => {
    const sel = document.getElementById('productSelect');
    if (sel.value) loadProductInfo(sel);
});

// Cegah double submit
document.querySelector('form').addEventListener('submit', function () {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
});
</script>
@endpush
