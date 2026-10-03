@extends('layouts.app')
@section('title', 'Scan Barcode - Stok Masuk')
@push('breadcrumb_content', 'Transaksi / <a href="' . route('stock-in.index') . '" style="color:inherit;">Stok Masuk</a> / <strong>Scan Barcode</strong>')
@push('styles')
<style>
/* -- Scanner area --------------------------------------- */
.scanner-wrapper {
    position: relative;
    width: 100%;
    max-width: 480px;
    margin: 0 auto;
    border-radius: 16px;
    overflow: hidden;
    background: #000;
    aspect-ratio: 4/3;
}
#scannerVideo {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.scanner-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}
.scanner-frame {
    width: 240px;
    height: 150px;
    position: relative;
}
.scanner-frame::before, .scanner-frame::after,
.scanner-frame > span::before, .scanner-frame > span::after {
    content: '';
    position: absolute;
    width: 24px; height: 24px;
    border-color: #6366f1;
    border-style: solid;
}
.scanner-frame::before  { top:0;    left:0;  border-width: 3px 0 0 3px; border-radius: 4px 0 0 0; }
.scanner-frame::after   { top:0;    right:0; border-width: 3px 3px 0 0; border-radius: 0 4px 0 0; }
.scanner-frame > span::before { bottom:0; left:0;  border-width: 0 0 3px 3px; border-radius: 0 0 0 4px; }
.scanner-frame > span::after  { bottom:0; right:0; border-width: 0 3px 3px 0; border-radius: 0 0 4px 0; }

.scanner-line {
    position: absolute;
    left: 0; right: 0;
    height: 2px;
    background: linear-gradient(to right, transparent, #6366f1, transparent);
    animation: scanAnim 2s ease-in-out infinite;
    top: 0;
}
@keyframes scanAnim {
    0%   { top: 0;   opacity: 1; }
    50%  { top: 100%; opacity: 1; }
    100% { top: 0;   opacity: 0; }
}

.scanner-status {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    background: linear-gradient(to top, rgba(0,0,0,.7), transparent);
    padding: 20px 16px 12px;
    text-align: center;
    color: #fff;
    font-size: 13px;
}
.scanner-status .pulse {
    display: inline-block;
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #10b981;
    margin-right: 6px;
    animation: pulse 1s infinite;
}
@keyframes pulse {
    0%,100% { opacity: 1; transform: scale(1); }
    50%      { opacity: .5; transform: scale(1.3); }
}

/* -- Modal --------------------------------------------- */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.55);
    z-index: 2000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    opacity: 0;
    pointer-events: none;
    transition: opacity .2s;
}
.modal-backdrop.open {
    opacity: 1;
    pointer-events: all;
}
.modal-box {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-width: 580px;
    
    overflow-y: auto;
    box-shadow: 0 25px 60px rgba(0,0,0,.25);
    transform: translateY(24px);
    transition: transform .2s;
}
.modal-backdrop.open .modal-box {
    transform: translateY(0);
}
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 22px 14px;
    border-bottom: 1px solid #f1f5f9;
    position: sticky;
    top: 0;
    background: #fff;
    z-index: 1;
}
.modal-title { font-size: 16px; font-weight: 700; color: #0f172a; }
.modal-close {
    background: #f1f5f9; border: none; cursor: pointer;
    width: 32px; height: 32px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    color: #64748b; font-size: 14px;
    transition: background .15s;
}
.modal-close:hover { background: #e2e8f0; color: #1e293b; }
.modal-body { padding: 20px 22px; }
.modal-footer {
    padding: 14px 22px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    position: sticky;
    bottom: 0;
    background: #fff;
}

/* -- Product info card in modal ------------------------ */
.product-found-card {
    display: flex;
    gap: 12px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 18px;
}
.product-not-found-card {
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 18px;
    font-size: 13px;
    color: #92400e;
}
.pf-icon {
    width: 44px; height: 44px;
    background: #10b981;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    color: #fff; font-size: 18px;
}
.pf-info { flex: 1; min-width: 0; }
.pf-name { font-weight: 700; font-size: 14px; color: #0f172a; }
.pf-meta { font-size: 11.5px; color: #64748b; margin-top: 3px; }
.pf-stock { font-size: 12px; color: #059669; font-weight: 600; margin-top: 4px; }

/* -- Scan History ------------------------------------- */
.scan-history-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
}
.scan-history-item:last-child { border-bottom: none; }
.scan-badge-ok  { width: 8px; height: 8px; background: #10b981; border-radius: 50%; flex-shrink: 0; }
.scan-badge-new { width: 8px; height: 8px; background: #f59e0b; border-radius: 50%; flex-shrink: 0; }

/* -- Manual input ------------------------------------- */
.manual-input-wrap {
    display: flex; gap: 8px; align-items: center;
    margin-bottom: 12px;
}
.manual-input-wrap input {
    flex: 1;
    font-family: 'Courier New', monospace;
    font-size: 14px;
    letter-spacing: .05em;
}
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Scan Barcode - Stok Masuk</div>
        <div class="page-subtitle">Arahkan kamera ke barcode produk, atau input manual</div>
    </div>
    <div class="btn-group">
        <a href="{{ route('stock-in.create') }}" class="btn btn-secondary">
            <i class="fas fa-keyboard"></i> Input Manual
        </a>
        <a href="{{ route('stock-in.index') }}" class="btn btn-secondary">
            <i class="fas fa-list"></i> Daftar Stok Masuk
        </a>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start;">

    {{-- -- Kiri: Scanner ------------------------------- --}}
    <div>
        <div class="card" style="margin-bottom:16px;">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-camera" style="color:var(--primary)"></i>
                    Kamera Scanner
                </div>
                <div style="display:flex;gap:8px;">
                    <button id="btnStartScan" class="btn btn-primary btn-sm" onclick="startScanner()">
                        <i class="fas fa-play"></i> Mulai Scan
                    </button>
                    <button id="btnStopScan" class="btn btn-secondary btn-sm" onclick="stopScanner()" style="display:none;">
                        <i class="fas fa-stop"></i> Stop
                    </button>
                </div>
            </div>
            <div class="card-body" style="padding:16px;">
                <div class="scanner-wrapper" id="scannerWrapper">
                    {{-- Placeholder sebelum kamera aktif --}}
                    <div id="scannerPlaceholder" style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;">
                        <div style="width:72px;height:72px;background:rgba(99,102,241,.1);border-radius:16px;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-barcode" style="font-size:36px;color:var(--primary);"></i>
                        </div>
                        <p style="color:#64748b;font-size:13px;text-align:center;">Klik <strong>Mulai Scan</strong> untuk mengaktifkan kamera</p>
                    </div>
                    <video id="scannerVideo" autoplay playsinline style="display:none;"></video>
                    <div class="scanner-overlay" id="scannerOverlay" style="display:none;">
                        <div class="scanner-frame">
                            <span></span>
                            <div class="scanner-line"></div>
                        </div>
                    </div>
                    <div class="scanner-status" id="scannerStatus" style="display:none;">
                        <span class="pulse"></span> Mendeteksi barcode...
                    </div>
                </div>

                {{-- Pilih kamera (jika ada lebih dari 1) --}}
                <div id="cameraSelect" style="margin-top:12px;display:none;">
                    <label class="form-label" style="font-size:12px;">Pilih Kamera:</label>
                    <select id="cameraList" class="form-control" onchange="switchCamera(this.value)" style="font-size:13px;"></select>
                </div>
            </div>
        </div>

        {{-- Manual Input --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-keyboard" style="color:#64748b"></i>
                    Input Barcode Manual
                </div>
            </div>
            <div class="card-body" style="padding:16px;">
                <p style="font-size:12.5px;color:#64748b;margin-bottom:12px;">
                    Jika scanner tidak berfungsi, ketik atau tempel kode barcode di sini.
                </p>
                <div class="manual-input-wrap">
                    <input type="text" id="manualBarcodeInput" class="form-control"
                        placeholder="Scan atau ketik barcode / kode produk..."
                        autofocus autocomplete="off"
                        onkeydown="if(event.key==='Enter'){lookupBarcode(this.value);}">
                    <button class="btn btn-primary" onclick="lookupBarcode(document.getElementById('manualBarcodeInput').value)">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </div>
                <p style="font-size:11.5px;color:#94a3b8;">
                    <i class="fas fa-lightbulb" style="color:#f59e0b;"></i>
                    Bisa juga pakai barcode scanner fisik (USB/Bluetooth) - otomatis terbaca di sini.
                </p>
            </div>
        </div>
    </div>

    {{-- -- Kanan: Riwayat Scan Session ---------------- --}}
    <div>
        <div class="card" style="margin-bottom:16px;">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-history" style="color:#8b5cf6"></i>
                    Riwayat Scan Sesi Ini
                </div>
                <button onclick="clearHistory()" class="btn btn-sm btn-secondary">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div id="scanHistoryList" style="padding:0 16px;min-height:80px;">
                <div style="text-align:center;padding:32px 0;color:#94a3b8;font-size:13px;" id="historyEmpty">
                    <i class="fas fa-barcode" style="font-size:28px;margin-bottom:8px;opacity:.3;"></i>
                    <p>Belum ada scan</p>
                </div>
            </div>
        </div>

        {{-- Panduan --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-info-circle" style="color:#3b82f6"></i> Panduan</div>
            </div>
            <div class="card-body" style="font-size:12.5px;color:#64748b;line-height:1.7;">
                <div style="display:flex;gap:8px;margin-bottom:8px;">
                    <span style="width:20px;height:20px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:10px;font-weight:700;color:#16a34a;">1</span>
                    <span>Klik <strong>Mulai Scan</strong> untuk mengaktifkan kamera.</span>
                </div>
                <div style="display:flex;gap:8px;margin-bottom:8px;">
                    <span style="width:20px;height:20px;background:#dbeafe;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:10px;font-weight:700;color:#2563eb;">2</span>
                    <span>Arahkan kamera ke barcode pada kemasan produk.</span>
                </div>
                <div style="display:flex;gap:8px;margin-bottom:8px;">
                    <span style="width:20px;height:20px;background:#ede9fe;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:10px;font-weight:700;color:#7c3aed;">3</span>
                    <span>Sistem otomatis mendeteksi & membuka form isi stok masuk.</span>
                </div>
                <div style="display:flex;gap:8px;">
                    <span style="width:20px;height:20px;background:#fef3c7;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:10px;font-weight:700;color:#d97706;">4</span>
                    <span>Jika barcode belum terdaftar, form akan memungkinkan Anda <strong>mendaftarkan produk baru</strong>.</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- =================================================== --}}
{{-- MODAL: Detail Stok Masuk                           --}}
{{-- =================================================== --}}
<div class="modal-backdrop" id="modalBackdrop">
    <div class="modal-box" id="modalBox">
        <div class="modal-header">
            <div class="modal-title" id="modalTitle">Stok Masuk</div>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>

        <div class="modal-body">
            {{-- Info produk yang ditemukan --}}
            <div id="productFoundCard" class="product-found-card" style="display:none;">
                <div class="pf-icon"><i class="fas fa-box-open"></i></div>
                <div class="pf-info">
                    <div class="pf-name" id="pfName">-</div>
                    <div class="pf-meta" id="pfMeta">-</div>
                    <div class="pf-stock" id="pfStock">-</div>
                </div>
            </div>

            {{-- Alert produk baru (barcode belum terdaftar) --}}
            <div id="productNewAlert" class="product-not-found-card" style="display:none;">
                <i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i>
                <strong>Produk baru</strong> - barcode <code id="newBarcodeCode" style="font-size:12px;"></code> belum terdaftar.
                Isi data di bawah untuk mendaftarkan produk baru sekaligus.
            </div>

            <form id="scanStockForm">
                @csrf
                <input type="hidden" id="f_product_id" name="product_id">
                <input type="hidden" id="f_is_new" value="0">

                {{-- Bagian produk baru (hanya tampil jika produk belum ada) --}}
                <div id="newProductSection" style="display:none;">
                    <div style="font-size:12px;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:.04em;margin-bottom:12px;padding-bottom:6px;border-bottom:2px dashed #fde68a;">
                        <i class="fas fa-plus-circle"></i> Data Produk Baru
                    </div>
                    <div class="form-row cols-2">
                        <div class="form-group">
                            <label class="form-label">Nama Produk <span class="required">*</span></label>
                            <input type="text" name="new_name" id="f_new_name" class="form-control" placeholder="Nama lengkap produk">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kode Produk <span class="required">*</span></label>
                            <input type="text" name="new_code" id="f_new_code" class="form-control" placeholder="PRD-001">
                        </div>
                    </div>
                    <div class="form-row cols-3">
                        <div class="form-group">
                            <label class="form-label">Kategori <span class="required">*</span></label>
                            <select name="new_category_id" id="f_new_category" class="form-control">
                                <option value="">-- Pilih --</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Satuan <span class="required">*</span></label>
                            <select name="new_unit_id" id="f_new_unit" class="form-control">
                                <option value="">-- Pilih --</option>
                                @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->symbol }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Min. Stok</label>
                            <input type="number" name="new_min_stock" id="f_new_min_stock" class="form-control" value="5" min="0">
                        </div>
                    </div>
                    <div class="form-row cols-2">
                        <div class="form-group">
                            <label class="form-label">Harga Jual</label>
                            <input type="number" name="new_selling_price" id="f_new_selling" class="form-control" value="0" min="0" step="100">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Lokasi Rak</label>
                            <input type="text" name="new_rack_location" id="f_new_rack" class="form-control" placeholder="A-1-1">
                        </div>
                    </div>
                    <div style="border-top:1px solid #f1f5f9;margin:4px 0 16px;"></div>
                </div>

                {{-- Data stok masuk (selalu tampil) --}}
                <div style="font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.04em;margin-bottom:12px;">
                    <i class="fas fa-arrow-circle-down" style="color:#10b981;"></i> Data Stok Masuk
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Jumlah <span class="required">*</span></label>
                        <input type="number" name="quantity" id="f_quantity" class="form-control"
                            value="1" min="1" oninput="calcModalTotal()">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Harga Beli/Unit <span class="required">*</span></label>
                        <input type="number" name="purchase_price" id="f_price" class="form-control"
                            value="0" min="0" step="100" oninput="calcModalTotal()">
                    </div>
                </div>

                {{-- Total display --}}
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 14px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;color:#64748b;">Total Nilai:</span>
                    <strong id="modalTotal" style="font-size:18px;color:#0f172a;">Rp 0</strong>
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">Tanggal <span class="required">*</span></label>
                        <input type="date" name="transaction_date" id="f_date" class="form-control"
                            value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" id="f_supplier" class="form-control">
                            <option value="">-- Pilih Supplier --</option>
                            @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label">No. Invoice</label>
                        <input type="text" name="invoice_number" id="f_invoice" class="form-control" placeholder="INV-001">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Catatan</label>
                        <input type="text" name="notes" id="f_notes" class="form-control" placeholder="Opsional">
                    </div>
                </div>
            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal()">
                <i class="fas fa-times"></i> Batal
            </button>
            <button type="button" class="btn btn-success" onclick="submitStockIn()" id="btnSubmitScan">
                <i class="fas fa-check"></i> Simpan Stok Masuk
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- ZXing barcode scanner library --}}
<script src="https://cdn.jsdelivr.net/npm/@zxing/browser@0.1.1/umd/index.min.js"></script>

<script>
const CSRF   = document.querySelector('meta[name="csrf-token"]').content;
const LOOKUP = "{{ route('api.product-by-barcode') }}";
const STORE  = "{{ route('stock-in.store') }}";

let codeReader    = null;
let selectedCamera = null;
let isScanning    = false;
let lastScanned   = '';   // debounce
let scanHistory   = [];

// -- Scanner ---------------------------------------------
async function startScanner() {
    try {
        codeReader = new ZXingBrowser.BrowserMultiFormatReader();
        const devices = await ZXingBrowser.BrowserCodeReader.listVideoInputDevices();

        if (devices.length === 0) {
            alert('Tidak ada kamera yang ditemukan di perangkat ini.');
            return;
        }

        // Tampilkan dropdown kamera jika > 1
        if (devices.length > 1) {
            const sel = document.getElementById('cameraList');
            sel.innerHTML = '';
            devices.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.deviceId;
                opt.text  = d.label || `Kamera ${d.deviceId.slice(0,8)}`;
                sel.appendChild(opt);
            });
            document.getElementById('cameraSelect').style.display = 'block';
            // Prefer kamera belakang
            const backCam = devices.find(d => /back|rear|environment/i.test(d.label));
            selectedCamera = backCam ? backCam.deviceId : devices[0].deviceId;
            sel.value = selectedCamera;
        } else {
            selectedCamera = devices[0].deviceId;
        }

        await activateCamera(selectedCamera);

    } catch (err) {
        console.error(err);
        showScannerError('Gagal mengakses kamera: ' + err.message);
    }
}

async function activateCamera(deviceId) {
    const video = document.getElementById('scannerVideo');
    document.getElementById('scannerPlaceholder').style.display = 'none';
    video.style.display = 'block';
    document.getElementById('scannerOverlay').style.display = 'flex';
    document.getElementById('scannerStatus').style.display = 'block';
    document.getElementById('btnStartScan').style.display = 'none';
    document.getElementById('btnStopScan').style.display = 'flex';

    isScanning = true;
    lastScanned = '';

    try {
        await codeReader.decodeFromVideoDevice(deviceId, video, (result, err) => {
            if (result && isScanning) {
                const code = result.getText();
                if (code !== lastScanned) {
                    lastScanned = code;
                    // Brief pause before allowing next scan
                    setTimeout(() => { lastScanned = ''; }, 3000);
                    onBarcodeDetected(code);
                }
            }
        });
    } catch (err) {
        showScannerError('Error kamera: ' + err.message);
    }
}

function stopScanner() {
    isScanning = false;
    if (codeReader) {
        codeReader.reset();
        codeReader = null;
    }
    const video = document.getElementById('scannerVideo');
    video.style.display = 'none';
    document.getElementById('scannerOverlay').style.display = 'none';
    document.getElementById('scannerStatus').style.display = 'none';
    document.getElementById('scannerPlaceholder').style.display = 'flex';
    document.getElementById('btnStartScan').style.display = 'flex';
    document.getElementById('btnStopScan').style.display = 'none';
    document.getElementById('cameraSelect').style.display = 'none';
}

async function switchCamera(deviceId) {
    if (codeReader) codeReader.reset();
    selectedCamera = deviceId;
    await activateCamera(deviceId);
}

function showScannerError(msg) {
    stopScanner();
    document.getElementById('scannerPlaceholder').innerHTML = `
        <div style="text-align:center;color:#ef4444;">
            <i class="fas fa-exclamation-triangle" style="font-size:32px;margin-bottom:8px;"></i>
            <p style="font-size:13px;">${msg}</p>
            <p style="font-size:12px;color:#94a3b8;margin-top:6px;">Gunakan input manual di bawah.</p>
        </div>`;
    document.getElementById('scannerPlaceholder').style.display = 'flex';
}

// -- Barcode Detected → Lookup ---------------------------
async function onBarcodeDetected(barcode) {
    // Feedback visual
    flashScanner();
    await lookupBarcode(barcode);
}

function flashScanner() {
    const wrapper = document.getElementById('scannerWrapper');
    wrapper.style.outline = '3px solid #10b981';
    setTimeout(() => { wrapper.style.outline = ''; }, 400);
}

// -- Manual lookup ---------------------------------------
async function lookupBarcode(barcode) {
    barcode = barcode.trim();
    if (!barcode) return;

    document.getElementById('manualBarcodeInput').value = barcode;

    const res  = await fetch(`${LOOKUP}?barcode=${encodeURIComponent(barcode)}`, {
        headers: { 'X-CSRF-TOKEN': CSRF }
    });
    const data = await res.json();

    addToHistory(barcode, data.found, data.product?.name);

    if (data.found) {
        openModalWithProduct(data.product);
    } else {
        openModalNewProduct(barcode);
    }
}

// -- Open Modal helpers ----------------------------------
function openModalWithProduct(product) {
    // Reset form
    resetModal();

    document.getElementById('modalTitle').textContent = 'Stok Masuk - ' + product.name;
    document.getElementById('f_product_id').value = product.id;
    document.getElementById('f_is_new').value = '0';

    // Product info card
    document.getElementById('pfName').textContent  = product.name;
    document.getElementById('pfMeta').textContent  = `${product.category_name} · ${product.unit_name} · ${product.code}`;
    document.getElementById('pfStock').textContent = `Stok saat ini: ${product.stock} ${product.unit_symbol}`;
    document.getElementById('productFoundCard').style.display = 'flex';
    document.getElementById('productNewAlert').style.display  = 'none';
    document.getElementById('newProductSection').style.display = 'none';

    // Pre-fill harga
    document.getElementById('f_price').value = product.purchase_price;

    // Pre-fill supplier
    if (product.supplier_id) {
        document.getElementById('f_supplier').value = product.supplier_id;
    }

    calcModalTotal();
    openModal();
}

function openModalNewProduct(barcode) {
    resetModal();

    document.getElementById('modalTitle').textContent = 'Produk Baru - ' + barcode;
    document.getElementById('f_product_id').value = '';
    document.getElementById('f_is_new').value = '1';

    document.getElementById('productFoundCard').style.display  = 'none';
    document.getElementById('productNewAlert').style.display   = 'block';
    document.getElementById('newBarcodeCode').textContent      = barcode;
    document.getElementById('newProductSection').style.display = 'block';

    // Generate auto code suggestion
    document.getElementById('f_new_code').value = 'PRD-' + Date.now().toString().slice(-5);

    calcModalTotal();
    openModal();
}

function resetModal() {
    document.getElementById('scanStockForm').reset();
    document.getElementById('f_date').value     = new Date().toISOString().split('T')[0];
    document.getElementById('f_quantity').value = 1;
    document.getElementById('f_price').value    = 0;
    document.getElementById('modalTotal').textContent = 'Rp 0';
}

function openModal()  { document.getElementById('modalBackdrop').classList.add('open'); }
function closeModal() { document.getElementById('modalBackdrop').classList.remove('open'); }

// Close modal on backdrop click
document.getElementById('modalBackdrop').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// -- Hitung total di modal -------------------------------
function calcModalTotal() {
    const qty   = parseInt(document.getElementById('f_quantity').value)  || 0;
    const price = parseInt(document.getElementById('f_price').value)     || 0;
    document.getElementById('modalTotal').textContent = 'Rp ' + (qty * price).toLocaleString('id-ID');
}

// -- Submit stok masuk dari modal -----------------------
async function submitStockIn() {
    const btn    = document.getElementById('btnSubmitScan');
    const isNew  = document.getElementById('f_is_new').value === '1';

    // Basic validation
    const qty   = parseInt(document.getElementById('f_quantity').value) || 0;
    const price = parseFloat(document.getElementById('f_price').value)  || 0;
    const date  = document.getElementById('f_date').value;

    if (qty < 1)  { alert('Jumlah minimal 1.'); return; }
    if (!date)    { alert('Tanggal wajib diisi.'); return; }

    let productId = document.getElementById('f_product_id').value;

    // Jika produk baru, daftarkan dulu
    if (isNew) {
        const newName     = document.getElementById('f_new_name').value.trim();
        const newCode     = document.getElementById('f_new_code').value.trim();
        const newCategory = document.getElementById('f_new_category').value;
        const newUnit     = document.getElementById('f_new_unit').value;
        const barcode     = document.getElementById('newBarcodeCode').textContent;

        if (!newName)     { alert('Nama produk wajib diisi.'); return; }
        if (!newCode)     { alert('Kode produk wajib diisi.'); return; }
        if (!newCategory) { alert('Kategori wajib dipilih.'); return; }
        if (!newUnit)     { alert('Satuan wajib dipilih.'); return; }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan produk...';

        // Register new product via fetch
        const prodRes = await fetch("{{ route('products.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                name:          newName,
                code:          newCode,
                barcode:       barcode,
                category_id:   newCategory,
                unit_id:       newUnit,
                supplier_id:   document.getElementById('f_supplier').value || null,
                purchase_price: price,
                selling_price: parseInt(document.getElementById('f_new_selling').value) || 0,
                stock:         0,
                min_stock:     parseInt(document.getElementById('f_new_min_stock').value) || 5,
                rack_location: document.getElementById('f_new_rack').value || null,
                is_active:     true,
            }),
        });

        if (!prodRes.ok) {
            const err = await prodRes.json();
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Simpan Stok Masuk';
            alert('Gagal mendaftarkan produk: ' + (err.message || JSON.stringify(err.errors)));
            return;
        }

        const prodData = await prodRes.json();
        productId = prodData.id;
    }

    // Submit stok masuk
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

    const formData = new FormData();
    formData.append('product_id',       productId);
    formData.append('supplier_id',      document.getElementById('f_supplier').value  || '');
    formData.append('quantity',         document.getElementById('f_quantity').value);
    formData.append('purchase_price',   document.getElementById('f_price').value);
    formData.append('transaction_date', document.getElementById('f_date').value);
    formData.append('invoice_number',   document.getElementById('f_invoice').value   || '');
    formData.append('notes',            document.getElementById('f_notes').value     || '');
    formData.append('_token',           CSRF);

    const res  = await fetch(STORE, { method: 'POST', body: formData });
    const data = await res.json().catch(() => null);

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-check"></i> Simpan Stok Masuk';

    if (res.ok && data?.success) {
        closeModal();
        showToast('✅ Stok masuk berhasil dicatat!', 'success');
        document.getElementById('manualBarcodeInput').value = '';
        document.getElementById('manualBarcodeInput').focus();
        lastScanned = ''; // allow re-scan same barcode
    } else {
        const msg = data?.message || 'Gagal menyimpan stok masuk.';
        alert(msg);
    }
}

// -- Scan History ----------------------------------------
function addToHistory(barcode, found, productName) {
    scanHistory.unshift({ barcode, found, name: productName || 'Produk Baru', time: new Date() });
    if (scanHistory.length > 20) scanHistory.pop();
    renderHistory();
}

function renderHistory() {
    const list  = document.getElementById('scanHistoryList');
    const empty = document.getElementById('historyEmpty');

    if (scanHistory.length === 0) {
        empty.style.display = 'block';
        list.querySelectorAll('.scan-history-item').forEach(el => el.remove());
        return;
    }

    empty.style.display = 'none';
    list.querySelectorAll('.scan-history-item').forEach(el => el.remove());

    scanHistory.forEach(item => {
        const hh = item.time.getHours().toString().padStart(2,'0');
        const mm = item.time.getMinutes().toString().padStart(2,'0');
        const ss = item.time.getSeconds().toString().padStart(2,'0');
        const div = document.createElement('div');
        div.className = 'scan-history-item';
        div.innerHTML = `
            <div class="scan-badge-${item.found ? 'ok' : 'new'}"></div>
            <div style="flex:1;min-width:0;">
                <div style="font-weight:600;font-size:12.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${item.name}</div>
                <div style="font-size:11px;color:#94a3b8;font-family:monospace;">${item.barcode}</div>
            </div>
            <div style="font-size:10.5px;color:#94a3b8;flex-shrink:0;">${hh}:${mm}:${ss}</div>
        `;
        list.appendChild(div);
    });
}

function clearHistory() {
    scanHistory = [];
    renderHistory();
}

// -- Toast notification ----------------------------------
function showToast(msg, type = 'success') {
    const colors = { success: '#10b981', error: '#ef4444', info: '#6366f1' };
    const toast = document.createElement('div');
    toast.style.cssText = `
        position:fixed;bottom:24px;right:24px;
        background:${colors[type]};color:#fff;
        padding:12px 20px;border-radius:10px;
        font-size:14px;font-weight:500;
        box-shadow:0 8px 24px rgba(0,0,0,.2);
        z-index:9999;
        animation:slideIn .3s ease;
    `;
    toast.textContent = msg;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}

// -- Global keyboard: Enter on manual input --------------
document.getElementById('manualBarcodeInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        lookupBarcode(this.value);
    }
});
</script>

<style>
@keyframes slideIn {
    from { transform: translateY(20px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}
</style>
@endpush
