<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cetak Barcode — {{ $product->name }}</title>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
@php
    $labelW     = (int) \App\Models\AppSetting::get('barcode_label_width',  40);
    $labelH     = (int) \App\Models\AppSetting::get('barcode_label_height', 25);
    $colsPerRow = (int) \App\Models\AppSetting::get('barcode_cols_per_row',  4);

    // 1mm ≈ 3.7795px pada 96dpi
    $px         = 3.7795;
    $labelWpx   = round($labelW * $px);
    $labelHpx   = round($labelH * $px);

    // Proporsi konten dalam label:
    // ~25% teks atas (nama toko + produk), ~45% barcode, ~15% kode, ~15% harga
    $barcodeHpx = round($labelH * 0.38 * $px);   // barcode area
    $barcodeHmm = round($labelH * 0.38);

    // Font (pt) untuk print — 1pt ≈ 0.353mm
    $fontStorePt = max(4.5, round($labelW * 0.095, 1));
    $fontNamePt  = max(5.0, round($labelW * 0.110, 1));
    $fontCodePt  = max(4.0, round($labelW * 0.082, 1));
    $fontPricePt = max(6.0, round($labelW * 0.140, 1));

    // Font (px) untuk preview layar ≈ pt * 1.333
    $fontStorePx = max(6,  round($fontStorePt * 1.333));
    $fontNamePx  = max(7,  round($fontNamePt  * 1.333));
    $fontCodePx  = max(5,  round($fontCodePt  * 1.333));
    $fontPricePx = max(8,  round($fontPricePt * 1.333));

    // Padding dalam label (mm)
    $padV = max(1, round($labelH * 0.06));
    $padH = max(1, round($labelW * 0.05));
@endphp
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Arial', sans-serif;
    background: #f1f5f9;
    min-height: 100vh;
    padding: 20px;
}

/* ── Control bar ── */
.no-print {
    background: #1e293b;
    color: #fff;
    padding: 12px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.no-print h2  { font-size: 15px; font-weight: 600; flex: 1; min-width: 180px; }
.no-print label { font-size: 13px; color: #94a3b8; white-space: nowrap; }
.no-print input[type=number] {
    width: 60px; padding: 6px 10px; border-radius: 6px; border: none;
    text-align: center; font-size: 14px; font-weight: 700;
}
.no-print button {
    padding: 9px 18px; border-radius: 8px; border: none; cursor: pointer;
    font-size: 13.5px; font-weight: 600; display: flex; align-items: center; gap: 6px;
}
.btn-print { background: #6366f1; color: #fff; }
.btn-print:hover { background: #4f46e5; }
.btn-back  { background: rgba(255,255,255,.1); color: #fff; }
.btn-back:hover { background: rgba(255,255,255,.2); }
.size-badge {
    background: rgba(99,102,241,.25); color: #a5b4fc;
    font-size: 11px; padding: 4px 10px; border-radius: 20px;
    font-weight: 600; white-space: nowrap;
}

/* ── Label grid (preview layar) ── */
.labels-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-start;
}

/* Satu label */
.label-card {
    background: #fff;
    border: 1.5px solid #d1d5db;
    border-radius: 4px;
    width:      {{ $labelWpx }}px;
    min-height: {{ $labelHpx }}px;
    padding:    {{ $padV }}mm {{ $padH }}mm;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    overflow: hidden;
    page-break-inside: avoid;
}

/* Teks atas: nama toko & produk */
.label-header {
    width: 100%;
    text-align: center;
    line-height: 1.25;
    overflow: hidden;
}
.label-store {
    font-size:   {{ $fontStorePx }}px;
    font-weight: 800;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: .02em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
    display: block;
}
.label-name {
    font-size:   {{ $fontNamePx }}px;
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
    display: block;
}

/* Barcode */
.label-barcode {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1px;
}
.barcode-svg { width: 100%; height: {{ $barcodeHpx }}px; }
.label-code {
    font-size:   {{ $fontCodePx }}px;
    color: #6b7280;
    font-family: 'Courier New', monospace;
    letter-spacing: .02em;
    white-space: nowrap;
}

/* Harga — satu baris, tidak wrap */
.label-price {
    font-size:   {{ $fontPricePx }}px;
    font-weight: 800;
    color: #111827;
    white-space: nowrap;
    line-height: 1;
}
.label-price .unit {
    font-size:   {{ max(5, $fontPricePx - 3) }}px;
    font-weight: 500;
    color: #6b7280;
}

/* ── Print ── */
@media print {
    body { background: #fff; padding: 0; }
    .no-print { display: none !important; }

    .labels-container {
        display: grid;
        grid-template-columns: repeat({{ $colsPerRow }}, {{ $labelW }}mm);
        gap: 1mm;
        padding: 0;
    }

    .label-card {
        width:      {{ $labelW }}mm;
        min-height: {{ $labelH }}mm;
        max-height: {{ $labelH }}mm;
        border:     0.3pt solid #aaa;
        border-radius: 1px;
        padding:    {{ $padV }}mm {{ $padH }}mm;
        overflow:   hidden;
    }

    .label-store { font-size: {{ $fontStorePt }}pt; }
    .label-name  { font-size: {{ $fontNamePt }}pt; }
    .label-code  { font-size: {{ $fontCodePt }}pt; }
    .label-price { font-size: {{ $fontPricePt }}pt; }
    .label-price .unit { font-size: {{ max(3.5, $fontPricePt - 2) }}pt; }
    .barcode-svg { height: {{ $barcodeHmm }}mm; }

    @page { size: auto; margin: 5mm; }
}
</style>
</head>
<body>

<div class="no-print">
    <h2>🏷️ Cetak Barcode — {{ $product->name }}</h2>
    <span class="size-badge">{{ $labelW }} × {{ $labelH }} mm &nbsp;·&nbsp; {{ $colsPerRow }} kolom/baris</span>
    <label>Jumlah label:</label>
    <input type="number" id="qty" value="1" min="1" max="200" onchange="renderLabels()">
    <button class="btn-print" onclick="showPrintGuide()">🖨 Cetak Sekarang</button>
    <button class="btn-back"  onclick="window.close()">✕ Tutup</button>
    <a href="{{ url()->previous() }}" style="color:#94a3b8;font-size:12px;text-decoration:none;">← Kembali</a>
</div>

{{-- Panduan setting dialog print --}}
<div id="printGuide" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;padding:28px 32px;max-width:480px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.3);">
        <div style="font-size:17px;font-weight:700;color:#0f172a;margin-bottom:4px;">🖨 Setting Dialog Print Browser</div>
        <div style="font-size:12px;color:#64748b;margin-bottom:18px;">Pastikan pengaturan ini benar agar label tercetak sesuai ukuran stiker</div>

        <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:20px;">
            <div style="display:flex;align-items:flex-start;gap:12px;padding:10px 14px;background:#f8fafc;border-radius:8px;border-left:3px solid #6366f1;">
                <span style="font-size:18px;flex-shrink:0;">📄</span>
                <div>
                    <div style="font-size:12px;font-weight:700;color:#374151;">Ukuran Kertas</div>
                    <div style="font-size:12px;color:#64748b;margin-top:2px;">Pilih <strong>"Custom"</strong> lalu isi <strong>{{ $labelW }} mm × {{ $labelH }} mm</strong><br>
                    <span style="font-size:11px;color:#94a3b8;">(atau ukuran stiker yang dipakai)</span></div>
                </div>
            </div>
            <div style="display:flex;align-items:flex-start;gap:12px;padding:10px 14px;background:#f8fafc;border-radius:8px;border-left:3px solid #10b981;">
                <span style="font-size:18px;flex-shrink:0;">📐</span>
                <div>
                    <div style="font-size:12px;font-weight:700;color:#374151;">Margin</div>
                    <div style="font-size:12px;color:#64748b;margin-top:2px;">Set ke <strong>"None"</strong> atau <strong>"Minimum"</strong></div>
                </div>
            </div>
            <div style="display:flex;align-items:flex-start;gap:12px;padding:10px 14px;background:#f8fafc;border-radius:8px;border-left:3px solid #f59e0b;">
                <span style="font-size:18px;flex-shrink:0;">📏</span>
                <div>
                    <div style="font-size:12px;font-weight:700;color:#374151;">Skala</div>
                    <div style="font-size:12px;color:#64748b;margin-top:2px;">Set ke <strong>"100%"</strong> (jangan "Fit to page")</div>
                </div>
            </div>
            <div style="display:flex;align-items:flex-start;gap:12px;padding:10px 14px;background:#f8fafc;border-radius:8px;border-left:3px solid #ef4444;">
                <span style="font-size:18px;flex-shrink:0;">🎨</span>
                <div>
                    <div style="font-size:12px;font-weight:700;color:#374151;">Header & Footer</div>
                    <div style="font-size:12px;color:#64748b;margin-top:2px;">Matikan <strong>"Headers and footers"</strong></div>
                </div>
            </div>
        </div>

        <div style="font-size:11px;color:#94a3b8;margin-bottom:18px;padding:8px 12px;background:#fafafa;border-radius:6px;line-height:1.6;">
            💡 Untuk printer label khusus (Zebra, EPSON LX, Argox, dll) — pilih nama printer tersebut di dialog print. Ukuran stiker biasanya sudah terdeteksi otomatis.
        </div>

        <div style="display:flex;gap:10px;">
            <button onclick="doPrint()" style="flex:1;padding:11px;background:#6366f1;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;">
                Lanjut Cetak →
            </button>
            <button onclick="document.getElementById('printGuide').style.display='none'" style="padding:11px 18px;background:#f1f5f9;color:#374151;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;cursor:pointer;">
                Batal
            </button>
        </div>
    </div>
</div>

<div class="labels-container" id="labelsContainer"></div>

<script>
const PRODUCT = {
    name:          {!! json_encode($product->name) !!},
    barcode:       {!! json_encode($product->barcode) !!},
    selling_price: {{ (float) $product->selling_price }},
    unit:          {!! json_encode($product->unit->symbol ?? 'pcs') !!},
    store:         {!! json_encode(\App\Models\AppSetting::get('app_name', config('app.name'))) !!},
};

const LABEL_W_PX    = {{ $labelWpx }};
const BARCODE_H_PX  = {{ $barcodeHpx }};
const LABEL_W_MM    = {{ $labelW }};

function formatRp(n) {
    return 'Rp\u00A0' + new Intl.NumberFormat('id-ID').format(n);
}

// Truncate agar tidak wrap — maks karakter proporsional lebar label
function fitText(text, mmWidth) {
    // Asumsi font Arial ~0.55mm per karakter pada ukuran fontName
    const max = Math.floor(mmWidth / ({{ $fontNamePt }} * 0.38));
    return text.length > max ? text.slice(0, max - 1) + '…' : text;
}

function renderLabels() {
    const qty = Math.max(1, Math.min(200, parseInt(document.getElementById('qty').value) || 1));
    const container = document.getElementById('labelsContainer');
    container.innerHTML = '';

    for (let i = 0; i < qty; i++) {
        const card = document.createElement('div');
        card.className = 'label-card';

        // ── Header: toko + nama produk ──
        const header = document.createElement('div');
        header.className = 'label-header';

        const store = document.createElement('span');
        store.className = 'label-store';
        store.textContent = PRODUCT.store;
        header.appendChild(store);

        const name = document.createElement('span');
        name.className = 'label-name';
        name.textContent = fitText(PRODUCT.name, LABEL_W_MM);
        header.appendChild(name);
        card.appendChild(header);

        // ── Barcode + kode ──
        const barcodeWrap = document.createElement('div');
        barcodeWrap.className = 'label-barcode';

        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.classList.add('barcode-svg');
        svg.setAttribute('id', 'bc-' + i);
        barcodeWrap.appendChild(svg);

        const codeDiv = document.createElement('div');
        codeDiv.className = 'label-code';
        codeDiv.textContent = PRODUCT.barcode;
        barcodeWrap.appendChild(codeDiv);
        card.appendChild(barcodeWrap);

        // ── Harga (satu baris, no-wrap) ──
        const price = document.createElement('div');
        price.className = 'label-price';
        price.innerHTML = formatRp(PRODUCT.selling_price)
            + ' <span class="unit">/ ' + PRODUCT.unit + '</span>';
        card.appendChild(price);

        container.appendChild(card);

        // Render barcode
        try {
            JsBarcode('#bc-' + i, PRODUCT.barcode, {
                format:       'CODE128',
                // Bar width: 1px per ~60px label width — minimal 1
                width:        Math.max(1, Math.floor(LABEL_W_PX / 70)),
                height:       BARCODE_H_PX,
                displayValue: false,
                margin:       0,
                lineColor:    '#111827',
                background:   '#ffffff',
            });
        } catch (e) {
            svg.style.display = 'none';
            codeDiv.style.color = '#ef4444';
            codeDiv.textContent = 'Barcode tidak valid';
        }
    }
}

document.addEventListener('DOMContentLoaded', renderLabels);

function showPrintGuide() {
    const guide = document.getElementById('printGuide');
    guide.style.display = 'flex';
}

function doPrint() {
    document.getElementById('printGuide').style.display = 'none';
    setTimeout(() => window.print(), 150);
}

// Tutup modal kalau klik di luar box
document.getElementById('printGuide')?.addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>
</body>
</html>
