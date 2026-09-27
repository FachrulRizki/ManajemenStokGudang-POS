@extends('layouts.app')
@section('title', 'Detail Stok Keluar')
@push('breadcrumb_content', 'Transaksi / <a href="' . route('stock-out.index') . '" style="color:inherit;">Stok Keluar</a> / <strong>' . $stockOut->reference_number . '</strong>')
@section('content')
<div class="page-header">
    <div><div class="page-title">{{ $stockOut->reference_number }}</div><div class="page-subtitle">Detail transaksi pengeluaran barang</div></div>
    <a href="{{ route('stock-out.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div style="max-width:700px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-arrow-circle-up" style="color:#ef4444"></i> Detail Transaksi</div>
            @php $typeColors = ['sale'=>'info','return'=>'warning','damaged'=>'danger','other'=>'secondary']; @endphp
            <span class="badge badge-{{ $typeColors[$stockOut->type] ?? 'secondary' }}">{{ $stockOut->type_label }}</span>
        </div>
        <div class="card-body">
            @php $rows = [
                ['No. Referensi', $stockOut->reference_number],
                ['Tanggal', $stockOut->transaction_date->format('d F Y')],
                ['Produk', $stockOut->product->name ?? '-'],
                ['Kode Produk', $stockOut->product->code ?? '-'],
                ['Tipe Keluar', $stockOut->type_label],
                ['Pelanggan', $stockOut->customer_name ?? '-'],
                ['Jumlah', number_format($stockOut->quantity) . ' ' . ($stockOut->product->unit->symbol ?? '')],
                ['Harga Jual/Unit', 'Rp ' . number_format($stockOut->selling_price, 0, ',', '.')],
                ['Total Nilai', 'Rp ' . number_format($stockOut->total_price, 0, ',', '.')],
                ['Dicatat Oleh', $stockOut->user->name ?? '-'],
                ['Catatan', $stockOut->notes ?? '-'],
            ]; @endphp
            @foreach($rows as [$label, $value])
            <div style="display:flex;gap:16px;padding:10px 0;{{ !$loop->last ? 'border-bottom:1px solid #f1f5f9' : '' }}">
                <span style="width:160px;font-size:13px;color:#64748b;flex-shrink:0;">{{ $label }}</span>
                <span style="font-size:13px;font-weight:{{ in_array($label,['Total Nilai','No. Referensi']) ? '700' : '400' }};color:#1e293b;">{{ $value }}</span>
            </div>
            @endforeach
        </div>
        <div class="card-footer" style="display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:12px;color:#94a3b8;">Dibuat: {{ $stockOut->created_at->format('d M Y H:i') }}</div>
            <form method="POST" action="{{ route('stock-out.destroy', $stockOut) }}" onsubmit="return confirmDelete(this)">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Hapus</button>
            </form>
        </div>
    </div>
</div>
@endsection
