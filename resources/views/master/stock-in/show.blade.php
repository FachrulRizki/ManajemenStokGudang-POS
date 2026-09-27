@extends('layouts.app')
@section('title', 'Detail Stok Masuk')
@push('breadcrumb_content', 'Transaksi / <a href="' . route('stock-in.index') . '" style="color:inherit;">Stok Masuk</a> / <strong>' . $stockIn->reference_number . '</strong>')
@section('content')
<div class="page-header">
    <div><div class="page-title">{{ $stockIn->reference_number }}</div><div class="page-subtitle">Detail transaksi penerimaan barang</div></div>
    <div class="btn-group">
        <a href="{{ route('stock-in.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
</div>

<div style="max-width:700px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-arrow-circle-down" style="color:#10b981"></i> Detail Transaksi</div>
        </div>
        <div class="card-body">
            @php $rows = [
                ['No. Referensi', $stockIn->reference_number],
                ['Tanggal', $stockIn->transaction_date->format('d F Y')],
                ['Produk', $stockIn->product->name ?? '-'],
                ['Kode Produk', $stockIn->product->code ?? '-'],
                ['Supplier', $stockIn->supplier->name ?? '-'],
                ['Jumlah', number_format($stockIn->quantity) . ' ' . ($stockIn->product->unit->symbol ?? '')],
                ['Harga Beli/Unit', 'Rp ' . number_format($stockIn->purchase_price, 0, ',', '.')],
                ['Total Nilai', 'Rp ' . number_format($stockIn->total_price, 0, ',', '.')],
                ['No. Invoice', $stockIn->invoice_number ?? '-'],
                ['Dicatat Oleh', $stockIn->user->name ?? '-'],
                ['Catatan', $stockIn->notes ?? '-'],
            ]; @endphp
            @foreach($rows as [$label, $value])
            <div style="display:flex;gap:16px;padding:10px 0;{{ !$loop->last ? 'border-bottom:1px solid #f1f5f9' : '' }}">
                <span style="width:160px;font-size:13px;color:#64748b;flex-shrink:0;">{{ $label }}</span>
                <span style="font-size:13px;font-weight:{{ in_array($label,['Total Nilai','No. Referensi']) ? '700' : '400' }};color:#1e293b;">{{ $value }}</span>
            </div>
            @endforeach
        </div>
        <div class="card-footer" style="display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:12px;color:#94a3b8;">Dibuat: {{ $stockIn->created_at->format('d M Y H:i') }}</div>
            <form method="POST" action="{{ route('stock-in.destroy', $stockIn) }}" onsubmit="return confirmDelete(this)">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Hapus Transaksi</button>
            </form>
        </div>
    </div>
</div>
@endsection
