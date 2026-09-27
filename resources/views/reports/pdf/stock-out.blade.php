<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    *{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'DejaVu Sans',sans-serif;font-size:10px;color:#1e293b;}
    .header{background:#0f172a;color:#fff;padding:14px 20px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;}
    .header h1{font-size:16px;font-weight:700;}
    .header p{font-size:10px;color:#94a3b8;margin-top:2px;}
    .meta{display:flex;gap:24px;margin-bottom:12px;padding:0 4px;font-size:10px;color:#64748b;}
    table{width:100%;border-collapse:collapse;}
    thead th{background:#ef4444;color:#fff;padding:7px 10px;text-align:left;font-size:9px;text-transform:uppercase;letter-spacing:.04em;}
    tbody tr:nth-child(even){background:#f8fafc;}
    tbody td{padding:6px 10px;border-bottom:1px solid #f1f5f9;font-size:9.5px;}
    tfoot td{padding:8px 10px;font-weight:700;background:#f1f5f9;}
    .text-right{text-align:right;}.text-center{text-align:center;}
    .footer{margin-top:16px;text-align:center;font-size:9px;color:#94a3b8;}
</style>
</head>
<body>
<div class="header">
    <div>
        <h1>Laporan Stok Keluar</h1>
        <p>{{ \App\Models\AppSetting::get('app_name', config('app.name')) }} · Dicetak: {{ now()->format('d F Y H:i') }}</p>
        @if($request->date_from || $request->date_to)
        <p>Periode: {{ $request->date_from ?? '...' }} s/d {{ $request->date_to ?? '...' }}</p>
        @endif
    </div>
    <div style="text-align:right;font-size:10px;color:#94a3b8;">
        <div>Total Unit Keluar: <strong style="color:#fff;">{{ number_format($totalQty) }}</strong></div>
        <div>Total Nilai: <strong style="color:#fff;font-size:13px;">Rp {{ number_format($totalValue,0,',','.') }}</strong></div>
    </div>
</div>
<div class="meta">
    <span>Total Transaksi: <strong>{{ $stockOuts->count() }}</strong></span>
    <span>Dicetak oleh: <strong>{{ auth()->user()->name }}</strong></span>
</div>
<table>
    <thead><tr>
        <th width="30">No</th><th>No. Referensi</th><th>Tanggal</th>
        <th>Produk</th><th class="text-center">Tipe</th><th>Pelanggan</th>
        <th class="text-center">Qty</th>
        <th class="text-right">Harga Jual</th>
        <th class="text-right">Total</th>
        <th>Oleh</th>
    </tr></thead>
    <tbody>
        @foreach($stockOuts as $i => $so)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $so->reference_number }}</td>
            <td>{{ $so->transaction_date->format('d/m/Y') }}</td>
            <td>{{ $so->product->name??'-' }}</td>
            <td class="text-center">{{ $so->type_label }}</td>
            <td>{{ $so->customer_name??'-' }}</td>
            <td class="text-center" style="color:#ef4444;font-weight:700;">-{{ number_format($so->quantity) }}</td>
            <td class="text-right">Rp {{ number_format($so->selling_price,0,',','.') }}</td>
            <td class="text-right" style="font-weight:600;">Rp {{ number_format($so->total_price,0,',','.') }}</td>
            <td>{{ $so->user->name??'-' }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot><tr>
        <td colspan="6" class="text-right">Total:</td>
        <td class="text-center" style="color:#ef4444;">{{ number_format($totalQty) }}</td>
        <td></td>
        <td class="text-right" style="color:#6366f1;">Rp {{ number_format($totalValue,0,',','.') }}</td>
        <td></td>
    </tr></tfoot>
</table>
<div class="footer">Laporan ini digenerate secara otomatis oleh sistem {{ \App\Models\AppSetting::get('app_name', config('app.name')) }}</div>
</body>
</html>
