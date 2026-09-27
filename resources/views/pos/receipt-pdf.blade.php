<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #1e293b; }

.header { text-align:center; margin-bottom:16px; padding-bottom:12px; border-bottom:2px solid #0f172a; }
.shop-name { font-size:20px; font-weight:700; color:#0f172a; }
.shop-sub  { font-size:11px; color:#64748b; margin-top:2px; }

.meta-row { display:flex; justify-content:space-between; margin-bottom:4px; font-size:11.5px; }
.meta-label { color:#64748b; }
.meta-value { font-weight:600; }

.divider { border:none; border-top:1px dashed #cbd5e1; margin:10px 0; }
.divider-solid { border:none; border-top:2px solid #0f172a; margin:10px 0; }

table { width:100%; border-collapse:collapse; }
thead th { background:#f8fafc; padding:7px 10px; text-align:left; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.03em; color:#475569; border-bottom:1px solid #e2e8f0; }
tbody td { padding:8px 10px; font-size:12px; border-bottom:1px solid #f1f5f9; }
tfoot td { padding:7px 10px; font-size:12px; font-weight:600; }

.text-right { text-align:right; }
.text-center { text-align:center; }

.summary-table { width:100%; margin-top:12px; }
.summary-table td { padding:4px 0; font-size:12px; }
.summary-table .total-row td { font-size:15px; font-weight:700; padding-top:8px; border-top:2px solid #0f172a; }
.summary-table .change-row td { font-size:13px; color:#10b981; font-weight:700; }

.footer { margin-top:20px; text-align:center; font-size:10.5px; color:#94a3b8; line-height:1.7; }
.invoice-badge { display:inline-block; background:#f1f5f9; padding:3px 10px; border-radius:4px; font-family:monospace; font-size:12px; font-weight:700; }
</style>
</head>
<body>

<div class="header">
    <div class="shop-name">{{ $settings['app_name'] ?? config('app.name') }}</div>
    @if(isset($settings['app_tagline']) && $settings['app_tagline'])
    <div class="shop-sub">{{ $settings['app_tagline'] }}</div>
    @endif
</div>

<div class="meta-row"><span class="meta-label">No. Invoice</span><span class="invoice-badge">{{ $transaction->invoice_number }}</span></div>
<div class="meta-row"><span class="meta-label">Tanggal & Waktu</span><span class="meta-value">{{ $transaction->transaction_at->format('d F Y, H:i:s') }}</span></div>
<div class="meta-row"><span class="meta-label">Kasir</span><span class="meta-value">{{ $transaction->user->name }}</span></div>
<div class="meta-row"><span class="meta-label">Shift</span><span class="meta-value">{{ $transaction->shift->shift_number }}</span></div>
@if($transaction->customer_name)
<div class="meta-row"><span class="meta-label">Pelanggan</span><span class="meta-value">{{ $transaction->customer_name }}</span></div>
@endif

<hr class="divider-solid">

<table>
    <thead>
        <tr>
            <th width="40">No</th>
            <th>Produk</th>
            <th class="text-center" width="60">Qty</th>
            <th class="text-right" width="100">Harga</th>
            <th class="text-right" width="110">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transaction->items as $i => $item)
        <tr>
            <td class="text-center" style="color:#94a3b8;">{{ $i + 1 }}</td>
            <td>
                <div style="font-weight:600;">{{ $item->product_name }}</div>
                <div style="font-size:10px;color:#94a3b8;">{{ $item->product_code }}</div>
                @if($item->discount_amount > 0)
                <div style="font-size:10px;color:#ef4444;">Diskon: -Rp {{ number_format($item->discount_amount, 0, ',', '.') }}</div>
                @endif
            </td>
            <td class="text-center">{{ number_format($item->quantity) }}</td>
            <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
            <td class="text-right" style="font-weight:600;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<hr class="divider">

<table class="summary-table" style="width:60%;margin-left:auto;">
    <tr><td style="color:#64748b;">Subtotal</td><td class="text-right">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td></tr>
    @if($transaction->discount_amount > 0)
    <tr><td style="color:#10b981;">Diskon ({{ $transaction->discount_percent }}%)</td><td class="text-right" style="color:#10b981;">-Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</td></tr>
    @endif
    @if($transaction->tax_amount > 0)
    <tr><td style="color:#64748b;">Pajak ({{ $transaction->tax_percent }}%)</td><td class="text-right">Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</td></tr>
    @endif
    <tr class="total-row"><td>TOTAL</td><td class="text-right">Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</td></tr>
    <tr><td style="color:#64748b;">{{ $transaction->paymentMethod->name ?? 'Tunai' }}</td><td class="text-right">Rp {{ number_format($transaction->amount_paid, 0, ',', '.') }}</td></tr>
    <tr class="change-row"><td>Kembalian</td><td class="text-right">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</td></tr>
</table>

@if($transaction->notes)
<hr class="divider">
<div style="font-size:11px;color:#64748b;"><strong>Catatan:</strong> {{ $transaction->notes }}</div>
@endif

<div class="footer">
    ----------------------<br>
    Terima kasih atas kepercayaan Anda!<br>
    Simpan struk ini sebagai bukti pembelian.<br>
    ----------------------<br>
    Dicetak: {{ now()->format('d M Y H:i') }}
</div>

</body>
</html>
