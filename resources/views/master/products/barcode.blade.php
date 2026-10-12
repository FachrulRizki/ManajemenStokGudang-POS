<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cetak Barcode — {{ $product->name }}</title>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
@php
    $labelW     = (int) \App\Models\AppSetting::get('barcode_label_width',  100);
    $labelH     = (int) \App\Models\AppSetting::get('barcode_label_height',  33);
    $colsPerRow = (int) \App\Models\AppSetting::get('barcode_cols_per_row',   1);

    // 1mm = 3.7795px @ 96dpi
    $px        = 3.7795;
    $labelWpx  = round($labelW * $px);
    $labelHpx  = round($labelH * $px);

    // Distribusi tinggi (dalam mm):
    //   padding atas+bawah : 2 × 1.5mm = 3mm
    //   nama toko           : ~22% tinggi label (dalam pt, 1pt=0.353mm)
    //   nama produk         : ~25%
    //   gap antar elemen    : ~3mm total
    //   barcode             : sisa — harga dan kode sudah ditetapkan di bawah
    //   kode barcode        : ~17%
    //   harga               : ~26%

    $padV = 1.5;   // mm, atas & bawah — fixed agar predictable
    $padH = max(2, round($labelW * 0.025, 1));

    // Font dalam pt (1pt = 0.353mm)
    // Dikecilkan sedikit agar ada ruang nafas, tidak mepet
    $fontStorePt = round($labelH * 0.19, 1);   // ~6.3pt pada 33mm
    $fontNamePt  = round($labelH * 0.21, 1);   // ~6.9pt
    $fontCodePt  = round($labelH * 0.15, 1);   // ~5.0pt
    $fontPricePt = round($labelH * 0.25, 1);   // ~8.3pt

    // Estimasi tinggi setiap elemen (mm) = font-size-pt × 0.353 × line-height(1.25)
    $lh          = 1.25;
    $hStore      = round($fontStorePt * 0.353 * $lh, 2);
    $hName       = round($fontNamePt  * 0.353 * $lh, 2);
    $hCode       = round($fontCodePt  * 0.353 * $lh, 2);
    $hPrice      = round($fontPricePt * 0.353 * $lh, 2);
    $hGaps       = 1.5;  // total gap kecil antar elemen

    // Sisa untuk barcode setelah semua elemen + padding
    $barcodeHmm  = max(8, round(
        $labelH - ($padV * 2) - $hStore - $hName - $hCode - $hPrice - $hGaps
    , 1));

    // px untuk preview layar
    $barcodeHpx  = round($barcodeHmm * $px);
    $fontStorePx = round($fontStorePt * 1.333);
    $fontNamePx  = round($fontNamePt  * 1.333);
    $fontCodePx  = round($fontCodePt  * 1.333);
    $fontPricePx = round($fontPricePt * 1.333);
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
.no-print h2 { font-size: 15px; font-weight: 600; flex: 1; min-width: 180px; }
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

.label-header {
    width: 100%;
    text-align: center;
    line-height: 1.3;
}
.label-store {
    font-size:   {{ $fontStorePx }}px;
    font-weight: 800;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: .04em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
}
.label-name {
    font-size:   {{ $fontNamePx }}px;
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
}

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
    letter-spacing: .03em;
    white-space: nowrap;
}

.label-price {
    font-size:   {{ $fontPricePx }}px;
    font-weight: 800;
    color: #111827;
    white-space: nowrap;
    line-height: 1;
}
.label-price .unit {
    font-size:   {{ max(7, $fontPricePx - 4) }}px;
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
        width:    {{ $labelW }}mm;
        height:   {{ $labelH }}mm;
        border:   0.3pt solid #999;
        border-radius: 1px;
        padding:  {{ $padV }}mm {{ $padH }}mm;
        /* Jangan overflow:hidden — biarkan konten tampil, hanya potong kalau benar-benar keluar */
        overflow: visible;
        /* Pastikan tidak ada page break di tengah label */
        page-break-inside: avoid;
        break-inside: avoid;
    }

    .label-store { font-size: {{ $fontStorePt }}pt; line-height: 1.25; }
    .label-name  { font-size: {{ $fontNamePt }}pt;  line-height: 1.25; }
    .label-code  { font-size: {{ $fontCodePt }}pt;  line-height: 1.25; }
    .label-price { font-size: {{ $fontPricePt }}pt; line-height: 1.25; }
    .label-price .unit { font-size: {{ max(4, $fontPricePt - 3) }}pt; }
    .barcode-svg { height: {{ $barcodeHmm }}mm; }

    /* Gap antar elemen diperkecil saat print */
    .label-barcode { gap: 0.5px; }
    .label-header  { line-height: 1.2; }

    @page { size: {{ $labelW }}mm {{ $labelH }}mm; margin: 0; }
}
</style>
</head>
<body>

<div class="no-print">
    <h2>🏷️ Cetak Barcode — {{ $product->name }}</h2>
    <span class="size-badge">{{ $labelW }} × {{ $labelH }} mm &nbsp;·&nbsp; {{ $colsPerRow }} kolom/baris</span>
    <label>Jumlah label:</label>
    <input type="number" id="qty" value="1" min="1" max="200" onchange="renderLabels()">
    <button class="btn-print" onclick="window.print()">🖨 Cetak Sekarang</button>
    <button class="btn-back"  onclick="window.close()">✕ Tutup</button>
    <a href="{{ url()->previous() }}" style="color:#94a3b8;font-size:12px;text-decoration:none;">← Kembali</a>
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

const LABEL_W_PX   = {{ $labelWpx }};
const BARCODE_H_PX = {{ $barcodeHpx }};
const LABEL_W_MM   = {{ $labelW }};
const FONT_NAME_PT = {{ $fontNamePt }};

function formatRp(n) {
    return 'Rp\u00A0' + new Intl.NumberFormat('id-ID').format(n);
}

function fitText(text) {
    // estimasi karakter maks berdasar lebar dan font
    const max = Math.floor(LABEL_W_MM / (FONT_NAME_PT * 0.42));
    return text.length > max ? text.slice(0, max - 1) + '…' : text;
}

function renderLabels() {
    const qty = Math.max(1, Math.min(200, parseInt(document.getElementById('qty').value) || 1));
    const container = document.getElementById('labelsContainer');
    container.innerHTML = '';

    for (let i = 0; i < qty; i++) {
        const card = document.createElement('div');
        card.className = 'label-card';

        // Header
        const header = document.createElement('div');
        header.className = 'label-header';

        const store = document.createElement('span');
        store.className = 'label-store';
        store.textContent = PRODUCT.store;
        header.appendChild(store);

        const name = document.createElement('span');
        name.className = 'label-name';
        name.textContent = fitText(PRODUCT.name);
        header.appendChild(name);
        card.appendChild(header);

        // Barcode
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

        // Harga
        const price = document.createElement('div');
        price.className = 'label-price';
        price.innerHTML = formatRp(PRODUCT.selling_price)
            + ' <span class="unit">/ ' + PRODUCT.unit + '</span>';
        card.appendChild(price);

        container.appendChild(card);

        try {
            JsBarcode('#bc-' + i, PRODUCT.barcode, {
                format:       'CODE128',
                width:        Math.max(1.2, LABEL_W_PX / 120),
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
</script>
</body>
</html>
