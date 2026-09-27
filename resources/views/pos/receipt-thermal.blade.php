<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Struk - {{ $transaction->invoice_number }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: 'Courier New', Courier, monospace;
    font-size: 12px;
    color: #000;
    background: #fff;
    width: 80mm;
    margin: 0 auto;
    padding: 4mm;
}
.center { text-align: center; }
.bold   { font-weight: bold; }
.right  { text-align: right; }
hr { border: none; border-top: 1px dashed #000; margin: 4px 0; }
.item-row { display: flex; justify-content: space-between; margin-bottom: 2px; }
.item-name { flex: 1; }
.item-qty  { width: 30px; text-align: center; }
.item-price { width: 70px; text-align: right; }
.summary-row { display: flex; justify-content: space-between; }
.summary-label { flex: 1; }
.summary-value { width: 80px; text-align: right; font-weight: bold; }
.grand { font-size: 14px; font-weight: bold; }
.change-row { font-size: 13px; }
@media print {
    body { width: 80mm; }
    .no-print { display: none; }
    @page { size: 80mm auto; margin: 0; }
}
</style>
</head>
<body>

<div class="center bold" style="font-size:14px;margin-bottom:2px;">
    {{ $settings['app_name'] ?? config('app.name') }}
</div>
@if(isset($settings['app_tagline']) && $settings['app_tagline'])
<div class="center" style="font-size:11px;margin-bottom:4px;">{{ $settings['app_tagline'] }}</div>
@endif

<hr>
<div class="center" style="font-size:11px;">
    {{ $transaction->invoice_number }}<br>
    {{ $transaction->transaction_at->format('d/m/Y H:i:s') }}<br>
    Kasir: {{ $transaction->user->name }}<br>
    Shift: {{ $transaction->shift->shift_number }}
</div>
@if($transaction->customer_name)
<div style="font-size:11px;margin-top:3px;">Pelanggan: {{ $transaction->customer_name }}</div>
@endif
<hr>

{{-- Items --}}
@foreach($transaction->items as $item)
<div style="margin-bottom:3px;">
    <div class="bold" style="font-size:12px;">{{ $item->product_name }}</div>
    <div class="item-row" style="font-size:11px;color:#333;">
        <span>{{ number_format($item->quantity) }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
        <span class="bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
    </div>
    @if($item->discount_amount > 0)
    <div style="font-size:10px;color:#555;">  Diskon: -Rp {{ number_format($item->discount_amount, 0, ',', '.') }}</div>
    @endif
</div>
@endforeach

<hr>
{{-- Totals --}}
<div class="summary-row"><span class="summary-label">Subtotal</span><span class="summary-value">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span></div>
@if($transaction->discount_amount > 0)
<div class="summary-row"><span class="summary-label">Diskon</span><span class="summary-value">-Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span></div>
@endif
@if($transaction->tax_amount > 0)
<div class="summary-row"><span class="summary-label">Pajak ({{ $transaction->tax_percent }}%)</span><span class="summary-value">Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</span></div>
@endif
<hr>
<div class="summary-row grand"><span class="summary-label">TOTAL</span><span class="summary-value">Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</span></div>
<div class="summary-row"><span class="summary-label">{{ $transaction->paymentMethod->name }}</span><span class="summary-value">Rp {{ number_format($transaction->amount_paid, 0, ',', '.') }}</span></div>
<div class="summary-row change-row"><span class="summary-label">Kembalian</span><span class="summary-value">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span></div>
<hr>

<div class="center" style="font-size:11px;margin-top:4px;line-height:1.6;">
    Terima kasih atas kunjungan Anda!<br>
    Barang yang sudah dibeli tidak<br>
    dapat dikembalikan.
</div>

<div style="margin-top:12px;text-align:center;" class="no-print">
    <button onclick="window.print()" style="padding:8px 20px;background:#6366f1;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px;">
        🖨 Cetak Struk
    </button>
    <button onclick="window.close()" style="padding:8px 20px;background:#f1f5f9;color:#374151;border:1px solid #e2e8f0;border-radius:6px;cursor:pointer;font-size:13px;margin-left:8px;">
        Tutup
    </button>
</div>

<script>
// Auto print saat pertama kali dibuka
window.addEventListener('load', () => { window.print(); });
</script>
</body>
</html>
