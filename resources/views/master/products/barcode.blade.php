<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cetak Barcode — {{ $product->name }}</title>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
@php
    $labelW    = (int)  \App\Models\AppSetting::get('barcode_label_width',   50);
    $labelH    = (int)  \App\Models\AppSetting::get('barcode_label_height',  30);
    $colsPerRow = (int) \App\Models\AppSetting::get('barcode_cols_per_row',   3);
    // Ukuran label untuk preview layar: 1mm ≈ 3.78px (96dpi)
    $labelWpx  = round($labelW  * 3.78);
    $labelHpx  = round($labelH  * 3.78);
    // Tinggi barcode: ~50% tinggi label
    $barcodeH  = max(20, round($labelH * 0.5 * 3.78));
    // Font sizes proporsional
    $fontStore = max(7,  round($labelW * 0.16));
    $fontName  = max(8,  round($labelW * 0.20));
    $fontCode  = max(6,  round($labelW * 0.14));
    $fontPrice = max(9,  round($labelW * 0.26));
@endphp
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Arial', sans-serif;
    background: #f1f5f9;
    min-height: 100vh;
    padding: 20px;
}

/* Control bar */
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
.no-print h2 { font-size: 15px; font-weight: 600; flex: 1; min-width: 200px; }
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
    background: rgba(99,102,241,.25);
    color: #a5b4fc;
    font-size: 11px;
    padding: 4px 10px;
    border-radius: 20px;
    font-weight: 600;
    white-space: nowrap;
}

/* Label grid */
.labels-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-start;
}

/* Satu label barcode — ukuran dari PHP setting */
.label-card {
    background: #fff;
    border: 1.5px solid #e2e8f0;
    border-radius: 6px;
    padding: 5px 6px;
    text-align: center;
    width: {{ $labelWpx }}px;
    min-height: {{ $labelHpx }}px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    page-break-inside: avoid;
}
.label-store { font-size: {{ $fontStore }}px; font-weight: 700; color: #64748b; letter-spacing: .04em; text-transform: uppercase; line-height: 1.2; }
.label-name  { font-size: {{ $fontName }}px; font-weight: 700; color: #0f172a; line-height: 1.2; }
.label-code  { font-size: {{ $fontCode }}px; color: #94a3b8; font-family: monospace; }
.label-price { font-size: {{ $fontPrice }}px; font-weight: 800; color: #1e293b; line-height: 1.2; }
.label-price span { font-size: {{ max(7, $fontPrice - 4) }}px; font-weight: 600; }
.barcode-svg { width: 100%; height: {{ $barcodeH }}px; }

@media print {
    body { background: #fff; padding: 4mm; }
    .no-print { display: none !important; }
    .labels-container {
        gap: 2mm;
        /* Grid dengan jumlah kolom dari setting */
        display: grid;
        grid-template-columns: repeat({{ $colsPerRow }}, {{ $labelW }}mm);
    }
    .label-card {
        border: 0.5pt solid #ccc;
        border-radius: 2px;
        padding: 1.5mm 2mm;
        width: {{ $labelW }}mm;
        min-height: {{ $labelH }}mm;
    }
    .label-store { font-size: {{ max(5, $fontStore - 1) }}pt; }
    .label-name  { font-size: {{ max(6, $fontName - 1) }}pt; }
    .label-code  { font-size: {{ max(5, $fontCode - 1) }}pt; }
    .label-price { font-size: {{ max(7, $fontPrice - 2) }}pt; }
    .label-price span { font-size: {{ max(5, $fontPrice - 6) }}pt; }
    .barcode-svg { height: {{ max(12, round($labelH * 0.45)) }}mm; }
    @page { margin: 8mm; size: auto; }
}
</style>
</head>
<body>

<div class="no-print">
    <h2>🏷️ Cetak Barcode — {{ $product->name }}</h2>
    <span class="size-badge">{{ $labelW }} × {{ $labelH }} mm · {{ $colsPerRow }} kolom/baris</span>
    <label>Jumlah label:</label>
    <input type="number" id="qty" value="1" min="1" max="200" onchange="renderLabels()">
    <button class="btn-print" onclick="window.print()">
        🖨 Cetak Sekarang
    </button>
    <button class="btn-back" onclick="window.close()">✕ Tutup</button>
    <a href="{{ url()->previous() }}" style="color:#94a3b8;font-size:12px;text-decoration:none;margin-left:4px;">
        ← Kembali ke Produk
    </a>
</div>

<div class="labels-container" id="labelsContainer"></div>

<script>
const PRODUCT = {
    name:          {!! json_encode($product->name) !!},
    code:          {!! json_encode($product->code) !!},
    barcode:       {!! json_encode($product->barcode) !!},
    selling_price: {{ (float) $product->selling_price }},
    unit:          {!! json_encode($product->unit->symbol ?? 'pcs') !!},
    store:         {!! json_encode(\App\Models\AppSetting::get('app_name', config('app.name'))) !!},
};

// Ukuran dari setting (server-side)
const LABEL_W_PX   = {{ $labelWpx }};
const BARCODE_H_PX = {{ $barcodeH }};

function formatRp(n) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(n);
}

// Truncate nama produk proporsional terhadap lebar label
function truncateName(name) {
    const maxChars = Math.max(12, Math.floor({{ $labelW }} / 2.2));
    return name.length > maxChars ? name.slice(0, maxChars) + '…' : name;
}

function renderLabels() {
    const qty = Math.max(1, Math.min(200, parseInt(document.getElementById('qty').value) || 1));
    const container = document.getElementById('labelsContainer');
    container.innerHTML = '';

    for (let i = 0; i < qty; i++) {
        const card = document.createElement('div');
        card.className = 'label-card';

        // Store name
        const store = document.createElement('div');
        store.className = 'label-store';
        store.textContent = PRODUCT.store;
        card.appendChild(store);

        // Product name
        const name = document.createElement('div');
        name.className = 'label-name';
        name.textContent = truncateName(PRODUCT.name);
        card.appendChild(name);

        // Barcode SVG
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.classList.add('barcode-svg');
        svg.setAttribute('id', 'bc-' + i);
        card.appendChild(svg);

        // Code text below barcode
        const codeDiv = document.createElement('div');
        codeDiv.className = 'label-code';
        codeDiv.textContent = PRODUCT.barcode;
        card.appendChild(codeDiv);

        // Price
        const price = document.createElement('div');
        price.className = 'label-price';
        price.innerHTML = formatRp(PRODUCT.selling_price) + ' <span>/ ' + PRODUCT.unit + '</span>';
        card.appendChild(price);

        container.appendChild(card);

        // Generate barcode ke SVG — tinggi menyesuaikan ukuran label
        try {
            JsBarcode('#bc-' + i, PRODUCT.barcode, {
                format:       'CODE128',
                width:        Math.max(1, Math.floor(LABEL_W_PX / 80)),
                height:       BARCODE_H_PX,
                displayValue: false,
                margin:       1,
                lineColor:    '#1e293b',
                background:   '#ffffff',
            });
        } catch (e) {
            svg.style.display = 'none';
            codeDiv.style.color = '#ef4444';
            codeDiv.textContent = 'Barcode tidak valid: ' + PRODUCT.barcode;
        }
    }
}

document.addEventListener('DOMContentLoaded', renderLabels);
</script>
</body>
</html>
