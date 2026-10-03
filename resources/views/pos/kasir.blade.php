@extends('layouts.app')
@section('title', 'Kasir - POS')
@push('breadcrumb_content', 'POS / <strong>Kasir</strong>')
@push('styles')
<style>
/* -- POS Layout ------------------------------------- */
.pos-wrap {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 16px;
    height: calc(100vh - var(--header-h) - 48px);
}
.pos-left { display: flex; flex-direction: column; gap: 12px; overflow: hidden; }
.pos-right { display: flex; flex-direction: column; gap: 0; background:#fff; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden; }

/* Search bar */
.pos-search { position:relative; }
.pos-search input {
    width:100%; padding:11px 16px 11px 42px;
    border:1px solid #e2e8f0; border-radius:10px;
    font-size:14px; background:#fff;
    transition:border .15s, box-shadow .15s;
}
.pos-search input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(99,102,241,.1); }
.pos-search i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8; }

/* Product Grid */
.product-grid {
    display:grid; grid-template-columns:repeat(auto-fill, minmax(150px, 1fr));
    gap:10px; overflow-y:auto; flex:1; padding-bottom:4px;
}
.product-card {
    background:#fff; border:1.5px solid #e2e8f0; border-radius:10px;
    padding:12px; cursor:pointer; transition:all .15s; user-select:none;
    display:flex; flex-direction:column;
}
.product-card:hover { border-color:var(--primary); background:var(--primary-light); transform:translateY(-1px); box-shadow:0 4px 12px rgba(99,102,241,.15); }
.product-card.out-of-stock { opacity:.45; cursor:not-allowed; pointer-events:none; }
.product-card-img {
    width:100%; aspect-ratio:1; background:#f8fafc; border-radius:6px;
    display:flex; align-items:center; justify-content:center; margin-bottom:8px; overflow:hidden;
}
.product-card-img img { width:100%; height:100%; object-fit:cover; }
.product-card-name { font-size:12.5px; font-weight:600; color:#1e293b; line-height:1.3; margin-bottom:4px; flex:1; }
.product-card-price { font-size:13px; font-weight:700; color:var(--primary); }
.product-card-stock { font-size:10px; color:#94a3b8; margin-top:2px; }

/* Shift bar */
.shift-bar {
    display:flex; align-items:center; gap:10px; padding:8px 12px;
    background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; font-size:12px;
}

/* Cart */
.cart-header { padding:14px 16px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; }
.cart-title { font-size:14px; font-weight:700; color:#0f172a; display:flex; align-items:center; gap:8px; }
.cart-body { flex:1; overflow-y:auto; }
.cart-item {
    display:flex; align-items:center; gap:10px; padding:10px 14px;
    border-bottom:1px solid #f8fafc; transition:background .1s;
}
.cart-item:hover { background:#fafafa; }
.cart-item-name { font-size:12.5px; font-weight:600; color:#1e293b; line-height:1.3; flex:1; }
.cart-item-price { font-size:11.5px; color:#64748b; }
.qty-ctrl { display:flex; align-items:center; gap:4px; }
.qty-btn {
    width:24px; height:24px; border-radius:6px; border:1px solid #e2e8f0;
    background:#f8fafc; cursor:pointer; font-size:12px; font-weight:600;
    display:flex; align-items:center; justify-content:center; transition:all .1s;
}
.qty-btn:hover { background:var(--primary); color:#fff; border-color:var(--primary); }
.qty-val { width:32px; text-align:center; font-size:13px; font-weight:700; }
.cart-item-subtotal { font-size:13px; font-weight:700; color:#0f172a; min-width:70px; text-align:right; }
.remove-btn { color:#ef4444; cursor:pointer; padding:4px; border:none; background:none; font-size:13px; }
.remove-btn:hover { color:#dc2626; }

.cart-footer { border-top:1px solid #e2e8f0; }
.total-row { display:flex; justify-content:space-between; padding:6px 16px; font-size:13px; }
.total-row.grand { padding:10px 16px; background:#f8fafc; font-size:16px; font-weight:700; color:#0f172a; }

/* Payment panel */
.pay-section { padding:14px; border-top:1px solid #e2e8f0; }
.method-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:12px; }
.method-btn {
    padding:10px 8px; border:1.5px solid #e2e8f0; border-radius:8px;
    cursor:pointer; text-align:center; font-size:12px; font-weight:500; color:#374151;
    background:#fff; transition:all .15s;
}
.method-btn:hover, .method-btn.selected { border-color:var(--primary); background:var(--primary-light); color:var(--primary-dark); }
.method-btn i { display:block; font-size:18px; margin-bottom:4px; }

.pay-btn {
    width:100%; padding:14px; background:var(--primary); color:#fff;
    border:none; border-radius:10px; font-size:15px; font-weight:700;
    cursor:pointer; transition:background .15s; display:flex; align-items:center; justify-content:center; gap:8px;
}
.pay-btn:hover { background:var(--primary-dark); }
.pay-btn:disabled { opacity:.5; cursor:not-allowed; }

/* Empty cart */
.cart-empty { display:flex; flex-direction:column; align-items:center; justify-content:center; height:180px; color:#94a3b8; }
.cart-empty i { font-size:40px; margin-bottom:10px; opacity:.3; }
.cart-empty p { font-size:13px; }

/* Autocomplete dropdown */
.autocomplete-list {
    position:absolute; top:100%; left:0; right:0; background:#fff;
    border:1px solid #e2e8f0; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,.1);
    z-index:100;  overflow-y:auto; display:none;
}
.autocomplete-item {
    display:flex; align-items:center; gap:10px; padding:8px 12px;
    cursor:pointer; font-size:13px; border-bottom:1px solid #f8fafc;
}
.autocomplete-item:hover { background:#f8fafc; }
.autocomplete-item:last-child { border-bottom:none; }

/* Modal payment success */
.success-icon { width:64px;height:64px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;color:#16a34a; }
</style>
@endpush

@section('content')
{{-- Shift info bar --}}
<div class="shift-bar" style="margin-bottom:12px;">
    <i class="fas fa-cash-register" style="color:#16a34a;"></i>
    <span style="font-weight:600;color:#15803d;">{{ $shift->shift_number }}</span>
    <span style="color:#64748b;">•</span>
    <span style="color:#64748b;">Dibuka {{ $shift->opened_at->format('H:i') }}</span>
    <span style="color:#64748b;">•</span>
    <span style="color:#64748b;">{{ $shift->total_transactions }} transaksi</span>
    <span style="color:#64748b;">•</span>
    <span style="font-weight:600;color:#15803d;">Rp {{ number_format($shift->total_sales, 0, ',', '.') }}</span>
    <div style="margin-left:auto;display:flex;gap:8px;">
        <a href="{{ route('pos.history') }}" class="btn btn-sm btn-secondary"><i class="fas fa-history"></i> Riwayat</a>
        <button class="btn btn-sm btn-danger" onclick="openModal('modalTutupShift')"><i class="fas fa-stop-circle"></i> Tutup Shift</button>
    </div>
</div>

<div class="pos-wrap">
    {{-- -- KIRI: Produk -------------------------------- --}}
    <div class="pos-left">
        {{-- Search --}}
        <div class="pos-search" style="position:relative;">
            <i class="fas fa-search"></i>
            <input type="text" id="productSearch" placeholder="Cari produk atau scan barcode..." autocomplete="off"
                onkeyup="searchProducts(this.value)" onfocus="showDropdown()" onblur="hideDropdown()">
            <div class="autocomplete-list" id="autocompleteList"></div>
        </div>

        {{-- Product Grid --}}
        <div class="product-grid" id="productGrid">
            @foreach($products as $p)
            <div class="product-card" onclick="addToCart({{ $p->id }})">
                <div class="product-card-img">
                    @if($p->image)
                        <img src="{{ asset('storage/'.$p->image) }}" alt="{{ $p->name }}">
                    @else
                        <i class="fas fa-box" style="font-size:28px;color:#e2e8f0;"></i>
                    @endif
                </div>
                <div class="product-card-name">{{ $p->name }}</div>
                <div class="product-card-price">Rp {{ number_format($p->selling_price, 0, ',', '.') }}</div>
                <div class="product-card-stock">Stok: {{ $p->stock }} {{ $p->unit->symbol }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- -- KANAN: Keranjang & Bayar -------------------- --}}
    <div class="pos-right">
        <div class="cart-header">
            <div class="cart-title"><i class="fas fa-shopping-cart" style="color:var(--primary);"></i> Keranjang <span id="cartBadge" class="badge badge-primary" style="display:none;"></span></div>
            <button onclick="clearCart()" class="btn btn-sm btn-secondary" id="clearCartBtn" style="display:none;"><i class="fas fa-trash"></i> Kosongkan</button>
        </div>

        <div class="cart-body" id="cartBody">
            <div class="cart-empty" id="cartEmpty">
                <i class="fas fa-shopping-cart"></i>
                <p>Keranjang kosong</p>
                <p style="font-size:11px;">Pilih produk atau scan barcode</p>
            </div>
            <div id="cartItems"></div>
        </div>

        <div class="cart-footer">
            <div class="total-row"><span>Subtotal</span><span id="subtotalDisplay">Rp 0</span></div>
            <div class="total-row" id="discountRow" style="display:none;color:#10b981;"><span>Diskon</span><span id="discountDisplay">-Rp 0</span></div>
            <div class="total-row grand"><span>TOTAL</span><span id="grandTotalDisplay">Rp 0</span></div>

            <div class="pay-section">
                {{-- Metode pembayaran --}}
                <div style="font-size:11.5px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Metode Pembayaran</div>
                <div class="method-grid">
                    @foreach($methods as $m)
                    <button type="button" class="method-btn {{ $loop->first ? 'selected' : '' }}"
                        data-id="{{ $m->id }}" data-code="{{ $m->code }}" data-ref="{{ $m->requires_reference ? '1' : '0' }}"
                        onclick="selectMethod(this)">
                        <i class="{{ $m->icon_class }}"></i>
                        {{ $m->name }}
                    </button>
                    @endforeach
                </div>

                {{-- Input nominal (untuk tunai) --}}
                <div id="cashInputWrap" style="margin-bottom:10px;">
                    <label style="font-size:11.5px;font-weight:600;color:#64748b;text-transform:uppercase;margin-bottom:4px;display:block;">Bayar</label>
                    <div style="position:relative;">
                        <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#64748b;font-size:13px;">Rp</span>
                        <input type="number" id="amountPaid" style="width:100%;padding:9px 12px 9px 34px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-weight:700;"
                            placeholder="0" oninput="calcChange()" min="0">
                    </div>
                    <div style="font-size:12px;color:#64748b;margin-top:4px;">Kembalian: <strong id="changeDisplay" style="color:#10b981;">Rp 0</strong></div>
                </div>

                {{-- No. referensi (untuk non-tunai) --}}
                <div id="refInputWrap" style="display:none;margin-bottom:10px;">
                    <label style="font-size:11.5px;font-weight:600;color:#64748b;text-transform:uppercase;margin-bottom:4px;display:block;">No. Referensi</label>
                    <input type="text" id="paymentReference" style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;" placeholder="No. QRIS / Transfer / EDC">
                </div>

                <button class="pay-btn" id="payBtn" disabled onclick="processPayment()">
                    <i class="fas fa-check-circle"></i> Bayar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- -- MODAL TUTUP SHIFT ------------------------------ --}}
<div class="modal-backdrop" id="modalTutupShift">
    <div class="modal-box" style="max-width:440px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-stop-circle" style="color:#ef4444"></i> Tutup Shift</div>
            <button class="modal-close" onclick="closeModal('modalTutupShift')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('shifts.close', $shift) }}">
            @csrf
            <div class="modal-body">
                <div style="background:#f8fafc;border-radius:8px;padding:14px;margin-bottom:16px;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:13px;">
                        <div><div style="color:#94a3b8;font-size:11px;">Shift</div><div style="font-weight:600;">{{ $shift->shift_number }}</div></div>
                        <div><div style="color:#94a3b8;font-size:11px;">Dibuka</div><div style="font-weight:600;">{{ $shift->opened_at->format('H:i') }}</div></div>
                        <div><div style="color:#94a3b8;font-size:11px;">Transaksi</div><div style="font-weight:600;">{{ $shift->total_transactions }}</div></div>
                        <div><div style="color:#94a3b8;font-size:11px;">Total Penjualan</div><div style="font-weight:600;color:#10b981;">Rp {{ number_format($shift->total_sales, 0, ',', '.') }}</div></div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Uang di Laci Saat Ini <span class="required">*</span></label>
                    <div style="position:relative;">
                        <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#64748b;font-size:13px;">Rp</span>
                        <input type="number" name="closing_cash" class="form-control" style="padding-left:34px;" min="0" required placeholder="0">
                    </div>
                    <div class="form-hint">Hitung uang tunai yang ada di laci kasir</div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Catatan akhir shift..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTutupShift')">Batal</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-stop-circle"></i> Tutup Shift</button>
            </div>
        </form>
    </div>
</div>

{{-- -- MODAL SUKSES PEMBAYARAN ------------------------ --}}
<div class="modal-backdrop" id="modalSuccess">
    <div class="modal-box" style="max-width:380px;">
        <div class="modal-body" style="text-align:center;padding:28px 20px;">
            <div class="success-icon"><i class="fas fa-check"></i></div>
            <div style="font-size:18px;font-weight:700;color:#0f172a;margin-bottom:6px;">Transaksi Berhasil!</div>
            <div style="font-size:13px;color:#64748b;margin-bottom:20px;" id="successInvoice"></div>

            <div style="background:#f8fafc;border-radius:10px;padding:16px;margin-bottom:20px;text-align:left;">
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:8px;">
                    <span style="color:#64748b;">Total</span><span id="successTotal" style="font-weight:700;"></span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:8px;">
                    <span style="color:#64748b;">Bayar</span><span id="successPaid" style="font-weight:600;"></span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:14px;padding-top:8px;border-top:1px solid #e2e8f0;">
                    <span style="font-weight:600;color:#10b981;">Kembalian</span>
                    <span id="successChange" style="font-size:18px;font-weight:700;color:#10b981;"></span>
                </div>
            </div>

            <div style="display:flex;gap:8px;justify-content:center;">
                <a id="btnPrintPdf" href="#" target="_blank" class="btn btn-secondary btn-sm"><i class="fas fa-file-pdf"></i> Struk PDF</a>
                <a id="btnPrintThermal" href="#" target="_blank" class="btn btn-secondary btn-sm"><i class="fas fa-print"></i> Thermal</a>
                <button onclick="closeSuccessAndReset()" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Transaksi Baru</button>
            </div>
        </div>
    </div>
</div>

{{-- Data produk untuk JS --}}
@php
$productsJson = $products->keyBy('id')->map(function($p) {
    return [
        'id'            => $p->id,
        'name'          => $p->name,
        'code'          => $p->code,
        'barcode'       => $p->barcode,
        'selling_price' => (float) $p->selling_price,
        'stock'         => $p->stock,
        'unit'          => $p->unit->symbol ?? 'pcs',
        'image'         => $p->image ? asset('storage/'.$p->image) : null,
    ];
})->toArray();
@endphp
<script>
const PRODUCTS   = {!! json_encode($productsJson) !!};
const CSRF_TOKEN = '{{ csrf_token() }}';
const STORE_URL  = '{{ route('pos.store') }}';
const SEARCH_URL = '{{ route('pos.search-product') }}';
</script>
@endsection

@push('scripts')
<script>
// -- State ----------------------------------------------
let cart = {};   // { productId: { ...product, qty, subtotal } }
let selectedMethodId = null;
let selectedMethodCode = null;
let requiresRef = false;
let grandTotal = 0;

// Init: pilih metode pertama
document.addEventListener('DOMContentLoaded', () => {
    const firstBtn = document.querySelector('.method-btn');
    if (firstBtn) selectMethod(firstBtn);
});

// -- Pilih Metode Bayar ----------------------------------
function selectMethod(btn) {
    document.querySelectorAll('.method-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    selectedMethodId   = btn.dataset.id;
    selectedMethodCode = btn.dataset.code;
    requiresRef        = btn.dataset.ref === '1';
    document.getElementById('cashInputWrap').style.display = requiresRef ? 'none' : '';
    document.getElementById('refInputWrap').style.display  = requiresRef ? '' : 'none';
    if (!requiresRef) { document.getElementById('amountPaid').value = ''; calcChange(); }
    updatePayBtn();
}

// -- Tambah ke Keranjang ---------------------------------
function addToCart(productId) {
    const p = PRODUCTS[productId];
    if (!p) return;

    if (cart[productId]) {
        if (cart[productId].qty >= p.stock) {
            alert('Stok tidak mencukupi. Tersedia: ' + p.stock + ' ' + p.unit);
            return;
        }
        cart[productId].qty++;
    } else {
        cart[productId] = { ...p, qty: 1 };
    }
    renderCart();
}

function removeFromCart(productId) {
    delete cart[productId];
    renderCart();
}

function changeQty(productId, delta) {
    if (!cart[productId]) return;
    const newQty = cart[productId].qty + delta;
    if (newQty < 1) { removeFromCart(productId); return; }
    const p = PRODUCTS[productId];
    if (newQty > p.stock) { alert('Stok tidak mencukupi.'); return; }
    cart[productId].qty = newQty;
    renderCart();
}

function clearCart() {
    cart = {};
    renderCart();
}

// -- Render Keranjang ------------------------------------
function renderCart() {
    const keys = Object.keys(cart);
    const empty   = document.getElementById('cartEmpty');
    const items   = document.getElementById('cartItems');
    const badge   = document.getElementById('cartBadge');
    const clearBtn = document.getElementById('clearCartBtn');

    if (keys.length === 0) {
        empty.style.display   = '';
        items.innerHTML       = '';
        badge.style.display   = 'none';
        clearBtn.style.display = 'none';
        updateTotals(0, 0);
        return;
    }

    empty.style.display   = 'none';
    badge.textContent     = keys.length;
    badge.style.display   = '';
    clearBtn.style.display = '';

    let subtotal = 0;
    let html = '';

    keys.forEach(id => {
        const item = cart[id];
        const itemTotal = item.qty * item.selling_price;
        subtotal += itemTotal;
        html += `
        <div class="cart-item">
            <div style="flex:1;min-width:0;">
                <div class="cart-item-name">${item.name}</div>
                <div class="cart-item-price">Rp ${fmt(item.selling_price)} / ${item.unit}</div>
            </div>
            <div class="qty-ctrl">
                <button class="qty-btn" onclick="changeQty(${id}, -1)">−</button>
                <span class="qty-val">${item.qty}</span>
                <button class="qty-btn" onclick="changeQty(${id}, +1)">+</button>
            </div>
            <div class="cart-item-subtotal">Rp ${fmt(itemTotal)}</div>
            <button class="remove-btn" onclick="removeFromCart(${id})"><i class="fas fa-times"></i></button>
        </div>`;
    });

    items.innerHTML = html;
    updateTotals(subtotal, 0);
}

function updateTotals(subtotal, discount) {
    grandTotal = subtotal - discount;
    document.getElementById('subtotalDisplay').textContent = 'Rp ' + fmt(subtotal);
    document.getElementById('grandTotalDisplay').textContent = 'Rp ' + fmt(grandTotal);
    if (discount > 0) {
        document.getElementById('discountRow').style.display = '';
        document.getElementById('discountDisplay').textContent = '-Rp ' + fmt(discount);
    } else {
        document.getElementById('discountRow').style.display = 'none';
    }
    calcChange();
    updatePayBtn();
}

function calcChange() {
    const paid = parseFloat(document.getElementById('amountPaid').value) || 0;
    const change = Math.max(0, paid - grandTotal);
    document.getElementById('changeDisplay').textContent = 'Rp ' + fmt(change);
}

function updatePayBtn() {
    const btn = document.getElementById('payBtn');
    const hasItems = Object.keys(cart).length > 0;
    const cashOk   = requiresRef ? true : (parseFloat(document.getElementById('amountPaid').value) || 0) >= grandTotal && grandTotal > 0;
    btn.disabled   = !hasItems || (!requiresRef && !cashOk);
}

// -- Search Produk ---------------------------------------
let searchTimer = null;
function searchProducts(val) {
    clearTimeout(searchTimer);
    if (!val.trim()) { hideDropdown(); return; }
    searchTimer = setTimeout(() => {
        fetch(SEARCH_URL + '?q=' + encodeURIComponent(val), {
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => renderDropdown(data));
    }, 250);
}

function renderDropdown(products) {
    const list = document.getElementById('autocompleteList');
    if (!products.length) { list.style.display = 'none'; return; }
    list.innerHTML = products.map(p => `
        <div class="autocomplete-item" onmousedown="addToCartFromSearch(${p.id}, event)">
            ${p.image_url ? `<img src="${p.image_url}" style="width:32px;height:32px;object-fit:cover;border-radius:4px;">` : `<div style="width:32px;height:32px;background:#f1f5f9;border-radius:4px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-box" style="font-size:14px;color:#cbd5e1;"></i></div>`}
            <div style="flex:1">
                <div style="font-weight:600;font-size:13px;">${p.name}</div>
                <div style="font-size:11px;color:#94a3b8;">${p.code} • Stok: ${p.stock} ${p.unit}</div>
            </div>
            <div style="font-weight:700;font-size:13px;color:var(--primary);">Rp ${fmt(p.selling_price)}</div>
        </div>`).join('');
    list.style.display = 'block';
}

function addToCartFromSearch(id, e) {
    e.preventDefault();
    addToCart(id);
    document.getElementById('productSearch').value = '';
    hideDropdown();
}

function showDropdown() { if (document.getElementById('autocompleteList').children.length) document.getElementById('autocompleteList').style.display = 'block'; }
function hideDropdown() { setTimeout(() => { document.getElementById('autocompleteList').style.display = 'none'; }, 150); }

// -- Proses Pembayaran -----------------------------------
function processPayment() {
    const keys = Object.keys(cart);
    if (!keys.length) return;

    const amountPaid = requiresRef
        ? grandTotal
        : parseFloat(document.getElementById('amountPaid').value) || 0;

    if (!requiresRef && amountPaid < grandTotal) {
        alert('Jumlah bayar kurang dari total!');
        return;
    }

    const payRef = document.getElementById('paymentReference').value || null;

    const payload = {
        items: keys.map(id => ({
            product_id:       parseInt(id),
            quantity:         cart[id].qty,
            unit_price:       cart[id].selling_price,
            discount_percent: 0,
            discount_amount:  0,
        })),
        payment_method_id: parseInt(selectedMethodId),
        amount_paid:       amountPaid,
        payment_reference: payRef,
    };

    document.getElementById('payBtn').disabled = true;
    document.getElementById('payBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

    fetch(STORE_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
        },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
            document.getElementById('payBtn').disabled = false;
            document.getElementById('payBtn').innerHTML = '<i class="fas fa-check-circle"></i> Bayar';
            return;
        }
        showSuccess(data);
    })
    .catch(() => {
        alert('Terjadi kesalahan koneksi. Coba lagi.');
        document.getElementById('payBtn').disabled = false;
        document.getElementById('payBtn').innerHTML = '<i class="fas fa-check-circle"></i> Bayar';
    });
}

function showSuccess(data) {
    document.getElementById('successInvoice').textContent = data.invoice_number;
    document.getElementById('successTotal').textContent   = 'Rp ' + fmt(data.grand_total);
    document.getElementById('successPaid').textContent    = 'Rp ' + fmt(data.amount_paid);
    document.getElementById('successChange').textContent  = 'Rp ' + fmt(data.change_amount);
    document.getElementById('btnPrintPdf').href           = data.receipt_url;
    document.getElementById('btnPrintThermal').href       = data.thermal_url;
    openModal('modalSuccess');
}

function closeSuccessAndReset() {
    closeModal('modalSuccess');
    clearCart();
    document.getElementById('amountPaid').value = '';
    calcChange();
    // Reload halaman untuk update stok di grid
    setTimeout(() => location.reload(), 300);
}

// -- Barcode scanner (Enter key) -------------------------
document.getElementById('productSearch').addEventListener('keydown', e => {
    if (e.key === 'Enter') {
        e.preventDefault();
        const val = e.target.value.trim();
        if (!val) return;
        // Cari exact barcode match dulu
        const exact = Object.values(PRODUCTS).find(p => p.barcode === val || p.code === val);
        if (exact) { addToCart(exact.id); e.target.value = ''; hideDropdown(); }
    }
});

// -- Helper ----------------------------------------------
function fmt(n) {
    return new Intl.NumberFormat('id-ID').format(Math.round(n || 0));
}

// Input bayar realtime
document.getElementById('amountPaid').addEventListener('input', updatePayBtn);
</script>
@endpush
