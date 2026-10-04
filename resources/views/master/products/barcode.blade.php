<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cetak Barcode — {{ $product->name }}</title>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Arial', sans-serif;
    background: #f1f5f9;
    min-height: 100vh;
    padding: 20px;
}

/* Control bar — hanya tampil saat tidak print */
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
.no-print h2 { font-size: 15px; font-weight: 600; flex: 1; }
.no-print label { font-size: 13px; color: #94a3b8; }
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

/* Label grid */
.labels-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-start;
}

/* Satu label barcode */
.label-card {
    background: #fff;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 12px;
    text-align: center;
    width: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    page-break-inside: avoid;
}
.label-store { font-size: 10px; font-weight: 700; color: #64748b; letter-spacing: .05em; text-transform: uppercase; }
.label-name  { font-size: 12px; font-weight: 700; color: #0f172a; line-height: 1.3; }
.label-code  { font-size: 9px; color: #94a3b8; font-family: monospace; }
.label-price { font-size: 16px; font-weight: 800; color: #1e293b; }
.label-price span { font-size: 11px; font-weight: 600; }
.barcode-svg  { width: 100%; max-width: 180px; height: 48px; }

@media print {
    body { background: #fff; padding: 8px; }
    .no-print { display: none !important; }
    .labels-container { gap: 4px; }
    .label-card {
        border: 1px solid #ccc;
        padding: 6px 8px;
        width: 190px;
        border-radius: 4px;
    }
    @page { margin: 8mm; size: auto; }
}
</style>
</head>
<body>

<div class="no-print">
    <h2>Cetak Barcode — {{ $product->name }}</h2>
    <label>Jumlah label:</label>
    <input type="number" id="qty" value="1" min="1" max="100" onchange="renderLabels()">
    <button class="btn-print" onclick="window.print()">
        🖨 Cetak Sekarang
    </button>
    <button class="btn-back" onclick="window.close()">← Tutup</button>
    <a href="{{ url()->previous() }}" style="color:#94a3b8;font-size:12px;text-decoration:none;margin-left:8px;">
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

function formatRp(n) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(n);
}

function renderLabels() {
    const qty  = Math.max(1, Math.min(100, parseInt(document.getElementById('qty').value) || 1));
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

        // Product name (truncate if long)
        const name = document.createElement('div');
        name.className = 'label-name';
        name.textContent = PRODUCT.name.length > 28 ? PRODUCT.name.slice(0, 28) + '…' : PRODUCT.name;
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

        // Generate barcode ke SVG
        try {
            JsBarcode('#bc-' + i, PRODUCT.barcode, {
                format:      'CODE128',
                width:       1.5,
                height:      40,
                displayValue: false,
                margin:      2,
                lineColor:   '#1e293b',
                background:  '#ffffff',
            });
        } catch (e) {
            svg.style.display = 'none';
            codeDiv.style.color = '#ef4444';
            codeDiv.textContent = 'Barcode tidak valid: ' + PRODUCT.barcode;
        }
    }
}

// Render saat halaman load
document.addEventListener('DOMContentLoaded', renderLabels);
</script>
</body>
</html>
