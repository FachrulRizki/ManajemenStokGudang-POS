<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1e293b; }
    .header { background: #0f172a; color: #fff; padding: 14px 20px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; }
    .header h1 { font-size: 16px; font-weight: 700; }
    .header p { font-size: 10px; color: #94a3b8; margin-top: 2px; }
    .meta { display: flex; gap: 24px; margin-bottom: 12px; padding: 0 4px; font-size: 10px; color: #64748b; }
    table { width: 100%; border-collapse: collapse; }
    thead th { background: #6366f1; color: #fff; padding: 7px 10px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: .04em; }
    tbody tr:nth-child(even) { background: #f8fafc; }
    tbody td { padding: 6px 10px; border-bottom: 1px solid #f1f5f9; font-size: 9.5px; }
    tfoot td { padding: 8px 10px; font-weight: 700; background: #f1f5f9; }
    .badge-normal { background: #dcfce7; color: #16a34a; padding: 2px 6px; border-radius: 99px; }
    .badge-low    { background: #fef3c7; color: #d97706; padding: 2px 6px; border-radius: 99px; }
    .badge-out    { background: #fee2e2; color: #dc2626; padding: 2px 6px; border-radius: 99px; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .footer { margin-top: 16px; text-align: center; font-size: 9px; color: #94a3b8; }
</style>
</head>
<body>
<div class="header">
    <div>
        <h1>Laporan Stok Barang</h1>
        <p>{{ \App\Models\AppSetting::get('app_name', config('app.name')) }} · Dicetak: {{ now()->format('d F Y H:i') }}</p>
    </div>
    <div style="text-align:right;font-size:10px;color:#94a3b8;">
        <div>Total Nilai Stok</div>
        <div style="font-size:14px;font-weight:700;color:#fff;">Rp {{ number_format($totalValue,0,',','.') }}</div>
    </div>
</div>

<div class="meta">
    <span>Total Produk: <strong>{{ $products->count() }}</strong></span>
    <span>Dicetak oleh: <strong>{{ auth()->user()->name }}</strong></span>
</div>

<table>
    <thead>
        <tr>
            <th width="30">No</th>
            <th>Kode</th>
            <th>Nama Produk</th>
            <th>Kategori</th>
            <th>Satuan</th>
            <th class="text-center">Stok</th>
            <th class="text-center">Min</th>
            <th class="text-right">Harga Beli</th>
            <th class="text-right">Harga Jual</th>
            <th class="text-right">Nilai Stok</th>
            <th class="text-center">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($products as $i => $p)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $p->code }}</td>
            <td>{{ $p->name }}</td>
            <td>{{ $p->category->name }}</td>
            <td>{{ $p->unit->symbol }}</td>
            <td class="text-center" style="font-weight:700;color:{{ $p->stock<=0?'#dc2626':($p->isLowStock()?'#d97706':'inherit') }};">{{ number_format($p->stock) }}</td>
            <td class="text-center">{{ $p->min_stock }}</td>
            <td class="text-right">Rp {{ number_format($p->purchase_price,0,',','.') }}</td>
            <td class="text-right">Rp {{ number_format($p->selling_price,0,',','.') }}</td>
            <td class="text-right" style="font-weight:600;">Rp {{ number_format($p->stock*$p->purchase_price,0,',','.') }}</td>
            <td class="text-center">
                @if($p->stock<=0)<span class="badge-out">Habis</span>
                @elseif($p->isLowStock())<span class="badge-low">Menipis</span>
                @else<span class="badge-normal">Normal</span>@endif
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="9" class="text-right">Total Nilai Stok:</td>
            <td class="text-right" style="color:#6366f1;font-size:11px;">Rp {{ number_format($totalValue,0,',','.') }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>
<div class="footer">Laporan ini digenerate secara otomatis oleh sistem {{ \App\Models\AppSetting::get('app_name', config('app.name')) }}</div>
</body>
</html>
