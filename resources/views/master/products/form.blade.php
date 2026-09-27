@extends('layouts.app')
@section('title', isset($product) ? 'Edit Produk' : 'Tambah Produk')
@push('breadcrumb_content', 'Master Data / <a href="' . route('products.index') . '" style="color:inherit;">Produk</a> / <strong>' . (isset($product) ? 'Edit' : 'Tambah') . '</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ isset($product) ? 'Edit Produk' : 'Tambah Produk' }}</div>
        <div class="page-subtitle">{{ isset($product) ? 'Perbarui data produk: '.$product->name : 'Tambah produk baru ke sistem gudang' }}</div>
    </div>
    <a href="{{ route('products.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<form method="POST" action="{{ isset($product) ? route('products.update', $product) : route('products.store') }}" enctype="multipart/form-data">
    @csrf @if(isset($product)) @method('PUT') @endif

    <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;">
        {{-- Main --}}
        <div>
            <div class="card" style="margin-bottom:16px;">
                <div class="card-header"><div class="card-title"><i class="fas fa-info-circle" style="color:var(--primary)"></i> Informasi Produk</div></div>
                <div class="card-body">
                    <div class="form-row cols-2">
                        <div class="form-group">
                            <label class="form-label">Kode Produk <span class="required">*</span></label>
                            <input type="text" name="code" class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}"
                                value="{{ old('code', $product->code ?? '') }}" placeholder="PRD001" autofocus>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Barcode</label>
                            <div style="display:flex;gap:8px;">
                                <input type="text" name="barcode" id="barcodeInput" class="form-control {{ $errors->has('barcode') ? 'is-invalid' : '' }}"
                                    value="{{ old('barcode', $product->barcode ?? '') }}" placeholder="Scan atau input manual" style="flex:1;">
                                <button type="button" class="btn btn-secondary" title="Scan barcode kamera" onclick="openBarcodeScanner()" style="flex-shrink:0;padding:8px 12px;">
                                    <i class="fas fa-camera"></i>
                                </button>
                                <button type="button" class="btn btn-secondary" title="Generate barcode otomatis" onclick="generateBarcode()" style="flex-shrink:0;padding:8px 12px;">
                                    <i class="fas fa-magic"></i>
                                </button>
                            </div>
                            <div class="form-hint">Scan kamera, generate otomatis, atau input manual</div>
                            @error('barcode')<div class="invalid-feedback" style="display:block;">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Produk <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                            value="{{ old('name', $product->name ?? '') }}" placeholder="Nama lengkap produk">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-row cols-3">
                        <div class="form-group">
                            <label class="form-label">Kategori <span class="required">*</span></label>
                            <select name="category_id" class="form-control {{ $errors->has('category_id') ? 'is-invalid' : '' }}">
                                <option value="">-- Pilih --</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Satuan <span class="required">*</span></label>
                            <select name="unit_id" class="form-control {{ $errors->has('unit_id') ? 'is-invalid' : '' }}">
                                <option value="">-- Pilih --</option>
                                @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id', $product->unit_id ?? '') == $unit->id ? 'selected' : '' }}>{{ $unit->name }} ({{ $unit->symbol }})</option>
                                @endforeach
                            </select>
                            @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Supplier</label>
                            <select name="supplier_id" class="form-control">
                                <option value="">-- Pilih --</option>
                                @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}" {{ old('supplier_id', $product->supplier_id ?? '') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3"
                            placeholder="Deskripsi produk...">{{ old('description', $product->description ?? '') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Lokasi Rak</label>
                        <input type="text" name="rack_location" class="form-control"
                            value="{{ old('rack_location', $product->rack_location ?? '') }}" placeholder="Contoh: A-1-2">
                        <div class="form-hint">Nomor rak penyimpanan di gudang</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-tag" style="color:#10b981"></i> Harga & Stok</div></div>
                <div class="card-body">
                    <div class="form-row cols-2">
                        <div class="form-group">
                            <label class="form-label">Harga Beli <span class="required">*</span></label>
                            <input type="number" name="purchase_price" class="form-control {{ $errors->has('purchase_price') ? 'is-invalid' : '' }}"
                                value="{{ old('purchase_price', $product->purchase_price ?? 0) }}" min="0" step="100">
                            @error('purchase_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Harga Jual <span class="required">*</span></label>
                            <input type="number" name="selling_price" class="form-control {{ $errors->has('selling_price') ? 'is-invalid' : '' }}"
                                value="{{ old('selling_price', $product->selling_price ?? 0) }}" min="0" step="100">
                            @error('selling_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-row cols-2">
                        @if(!isset($product))
                        <div class="form-group">
                            <label class="form-label">Stok Awal <span class="required">*</span></label>
                            <input type="number" name="stock" class="form-control {{ $errors->has('stock') ? 'is-invalid' : '' }}"
                                value="{{ old('stock', 0) }}" min="0">
                            <div class="form-hint">Ubah stok via menu Stok Masuk/Keluar</div>
                            @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        @endif
                        <div class="form-group">
                            <label class="form-label">Minimum Stok <span class="required">*</span></label>
                            <input type="number" name="min_stock" class="form-control {{ $errors->has('min_stock') ? 'is-invalid' : '' }}"
                                value="{{ old('min_stock', $product->min_stock ?? 5) }}" min="0">
                            <div class="form-hint">Alert akan muncul saat stok ≤ nilai ini</div>
                            @error('min_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar right --}}
        <div>
            <div class="card" style="margin-bottom:16px;">
                <div class="card-header"><div class="card-title"><i class="fas fa-image" style="color:#8b5cf6"></i> Foto Produk</div></div>
                <div class="card-body">
                    <div id="imagePreview" style="width:100%;height:180px;background:#f8fafc;border:2px dashed #e2e8f0;border-radius:8px;display:flex;align-items:center;justify-content:center;margin-bottom:12px;overflow:hidden;cursor:pointer;" onclick="document.getElementById('imageInput').click()">
                        @if(isset($product) && $product->image)
                            <img src="{{ asset('storage/'.$product->image) }}" style="width:100%;height:100%;object-fit:cover;" id="previewImg">
                        @else
                            <div style="text-align:center;color:#94a3b8;" id="uploadPlaceholder">
                                <i class="fas fa-cloud-upload-alt" style="font-size:32px;margin-bottom:8px;"></i>
                                <p style="font-size:12px;">Klik untuk upload foto</p>
                            </div>
                        @endif
                    </div>
                    <input type="file" name="image" id="imageInput" accept="image/*" style="display:none;" onchange="previewImage(this)">
                    <div class="form-hint" style="text-align:center;">JPG, PNG, maks 2MB</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><div class="card-title"><i class="fas fa-cog" style="color:#64748b"></i> Pengaturan</div></div>
                <div class="card-body">
                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="form-label">Status Produk</label>
                        <div class="toggle-wrap">
                            <label class="toggle">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                            <span style="font-size:13px;color:#64748b;">Produk aktif</span>
                        </div>
                    </div>

                    <div style="padding-top:12px;border-top:1px solid #f1f5f9;display:flex;flex-direction:column;gap:8px;">
                        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                            <i class="fas fa-save"></i> {{ isset($product) ? 'Perbarui Produk' : 'Simpan Produk' }}
                        </button>
                        <a href="{{ route('products.index') }}" class="btn btn-secondary" style="width:100%;justify-content:center;">Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// -- Generate barcode otomatis --------------------------
function generateBarcode() {
    const ts   = Date.now().toString();
    const rand = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
    document.getElementById('barcodeInput').value = ts.slice(-9) + rand;
}

// -- Barcode Scanner via kamera -------------------------
let scannerStream = null;

function openBarcodeScanner() {
    openModal('modalBarcode');
    startCamera();
}

async function startCamera() {
    const video = document.getElementById('barcodeVideo');
    const status = document.getElementById('scanStatus');
    status.textContent = 'Menghidupkan kamera...';
    try {
        scannerStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } }
        });
        video.srcObject = scannerStream;
        video.play();
        status.textContent = 'Arahkan kamera ke barcode. Atau ketik barcode di bawah dan tekan Konfirmasi.';
    } catch (e) {
        status.textContent = 'Kamera tidak tersedia. Gunakan input manual di bawah.';
    }
}

function stopCamera() {
    if (scannerStream) {
        scannerStream.getTracks().forEach(t => t.stop());
        scannerStream = null;
    }
}

function confirmManualBarcode() {
    const val = document.getElementById('manualBarcodeInput').value.trim();
    if (!val) { alert('Masukkan barcode terlebih dahulu.'); return; }
    document.getElementById('barcodeInput').value = val;
    closeBarcodeModal();
}

function closeBarcodeModal() {
    stopCamera();
    closeModal('modalBarcode');
    document.getElementById('manualBarcodeInput').value = '';
}

// Capture dari video frame - hanya sebagai fallback manual trigger
function captureBarcode() {
    const input = document.getElementById('manualBarcodeInput').value.trim();
    if (input) { confirmManualBarcode(); return; }
    document.getElementById('manualBarcodeInput').focus();
    document.getElementById('scanStatus').textContent = 'Ketik barcode yang terbaca di field bawah, lalu klik Konfirmasi.';
}
</script>
@endpush

{{-- -- MODAL BARCODE SCANNER ---------------------------- --}}
<div class="modal-backdrop" id="modalBarcode">
    <div class="modal-box" style="max-width:480px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-camera" style="color:var(--primary)"></i> Scan Barcode</div>
            <button class="modal-close" onclick="closeBarcodeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" style="padding:16px;">
            {{-- Video preview --}}
            <div style="position:relative;width:100%;background:#000;border-radius:8px;overflow:hidden;margin-bottom:12px;aspect-ratio:16/9;">
                <video id="barcodeVideo" style="width:100%;height:100%;object-fit:cover;" playsinline muted></video>
                {{-- Crosshair overlay --}}
                <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none;">
                    <div style="width:220px;height:120px;border:2px solid #6366f1;border-radius:6px;box-shadow:0 0 0 9999px rgba(0,0,0,.4);">
                        <div style="position:absolute;top:0;left:0;width:20px;height:20px;border-top:3px solid #6366f1;border-left:3px solid #6366f1;border-radius:3px 0 0 0;"></div>
                        <div style="position:absolute;top:0;right:0;width:20px;height:20px;border-top:3px solid #6366f1;border-right:3px solid #6366f1;border-radius:0 3px 0 0;"></div>
                        <div style="position:absolute;bottom:0;left:0;width:20px;height:20px;border-bottom:3px solid #6366f1;border-left:3px solid #6366f1;border-radius:0 0 0 3px;"></div>
                        <div style="position:absolute;bottom:0;right:0;width:20px;height:20px;border-bottom:3px solid #6366f1;border-right:3px solid #6366f1;border-radius:0 0 3px 0;"></div>
                    </div>
                </div>
            </div>
            <div id="scanStatus" style="font-size:12px;color:#64748b;text-align:center;margin-bottom:12px;"></div>

            {{-- Input manual barcode --}}
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label" style="font-size:12px;">Input Manual / Scan USB Barcode Reader</label>
                <div style="display:flex;gap:8px;">
                    <input type="text" id="manualBarcodeInput" class="form-control" placeholder="Ketik atau scan barcode di sini..."
                        autofocus onkeydown="if(event.key==='Enter'){event.preventDefault();confirmManualBarcode();}">
                    <button type="button" class="btn btn-primary" onclick="confirmManualBarcode()">
                        <i class="fas fa-check"></i> Konfirmasi
                    </button>
                </div>
                <div class="form-hint">USB barcode reader akan otomatis mengisi field ini. Tekan Enter atau klik Konfirmasi.</div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeBarcodeModal()">Batal</button>
        </div>
    </div>
</div>
