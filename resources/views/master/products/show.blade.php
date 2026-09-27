@extends('layouts.app')
@section('title', $product->name)
@push('breadcrumb_content', 'Master Data / <a href="' . route('products.index') . '" style="color:inherit;">Produk</a> / <strong>Detail</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ $product->name }}</div>
        <div class="page-subtitle">{{ $product->code }} @if($product->barcode) · {{ $product->barcode }} @endif</div>
    </div>
    <div class="btn-group">
        <a href="{{ route('stock-in.create') }}?product_id={{ $product->id }}" class="btn btn-success"><i class="fas fa-plus"></i> Stok Masuk</a>
        <a href="{{ route('stock-out.create') }}?product_id={{ $product->id }}" class="btn btn-danger"><i class="fas fa-minus"></i> Stok Keluar</a>
        <a href="{{ route('products.edit', $product) }}" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
        <a href="{{ route('products.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:300px 1fr;gap:20px;margin-bottom:20px;">
    <div>
        <div class="card" style="margin-bottom:16px;">
            @if($product->image)
                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" style="width:100%;height:220px;object-fit:cover;">
            @else
                <div style="width:100%;height:220px;background:#f8fafc;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-box" style="font-size:64px;color:#e2e8f0;"></i>
                </div>
            @endif
            <div class="card-body">
                @php $statusColors = ['out'=>'danger','low'=>'warning','normal'=>'success']; @endphp
                <div style="text-align:center;margin-bottom:16px;">
                    <span class="badge badge-{{ $statusColors[$product->stock_status] }}" style="font-size:13px;padding:6px 14px;">
                        @if($product->stock <= 0) <i class="fas fa-times-circle"></i> Stok Habis
                        @elseif($product->isLowStock()) <i class="fas fa-exclamation-triangle"></i> Stok Menipis
                        @else <i class="fas fa-check-circle"></i> Stok Normal @endif
                    </span>
                </div>
                @php $info = [
                    ['Kategori', $product->category->name],
                    ['Satuan', $product->unit->name.' ('.$product->unit->symbol.')'],
                    ['Supplier', $product->supplier->name ?? '-'],
                    ['Rak', $product->rack_location ?? '-'],
                    ['Status', $product->is_active ? 'Aktif' : 'Nonaktif'],
                ]; @endphp
                @foreach($info as [$label, $value])
                <div style="display:flex;justify-content:space-between;padding:6px 0;{{ !$loop->last ? 'border-bottom:1px solid #f1f5f9;' : '' }}font-size:13px;">
                    <span style="color:#64748b;">{{ $label }}</span>
                    <span style="font-weight:500;">{{ $value }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div>
        <div class="grid grid-3" style="margin-bottom:16px;">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ede9fe;color:#7c3aed;font-size:24px;"><i class="fas fa-cubes"></i></div>
                <div class="stat-content">
                    <div class="stat-value" style="color:{{ $product->stock <= 0 ? '#dc2626' : ($product->isLowStock() ? '#d97706' : '#0f172a') }}">{{ number_format($product->stock) }}</div>
                    <div class="stat-label">Stok Tersedia</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-arrow-down"></i></div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($monthlyIn) }}</div>
                    <div class="stat-label">Masuk Bulan Ini</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-arrow-up"></i></div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($monthlyOut) }}</div>
                    <div class="stat-label">Keluar Bulan Ini</div>
                </div>
            </div>
        </div>

        <div class="grid grid-2" style="margin-bottom:16px;">
            <div class="card" style="padding:16px;">
                <div style="font-size:12px;color:#64748b;margin-bottom:4px;">Harga Beli</div>
                <div style="font-size:20px;font-weight:700;">Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</div>
            </div>
            <div class="card" style="padding:16px;">
                <div style="font-size:12px;color:#64748b;margin-bottom:4px;">Harga Jual</div>
                <div style="font-size:20px;font-weight:700;color:#10b981;">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</div>
            </div>
        </div>

        @if($product->description)
        <div class="card">
            <div class="card-body">
                <div style="font-size:12px;color:#94a3b8;margin-bottom:6px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">DESKRIPSI</div>
                <p style="font-size:13px;color:#374151;line-height:1.6;">{{ $product->description }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Transaction History --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-arrow-circle-down" style="color:#10b981"></i> Riwayat Stok Masuk</div>
            <a href="{{ route('stock-in.index') }}?search={{ $product->code }}" class="btn btn-sm btn-secondary">Semua</a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Referensi</th><th style="text-align:center;">Qty</th><th>Tanggal</th><th>Oleh</th></tr></thead>
                <tbody>
                    @forelse($stockIns as $si)
                    <tr>
                        <td><code style="font-size:11px;">{{ $si->reference_number }}</code></td>
                        <td style="text-align:center;color:#10b981;font-weight:700;">+{{ $si->quantity }}</td>
                        <td style="font-size:12px;">{{ $si->transaction_date->format('d M Y') }}</td>
                        <td style="font-size:12px;color:#64748b;">{{ $si->user->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4"><div class="empty-state" style="padding:20px;"><i class="fas fa-inbox"></i><p>Belum ada data</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-arrow-circle-up" style="color:#ef4444"></i> Riwayat Stok Keluar</div>
            <a href="{{ route('stock-out.index') }}?search={{ $product->code }}" class="btn btn-sm btn-secondary">Semua</a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Referensi</th><th style="text-align:center;">Qty</th><th>Tipe</th><th>Tanggal</th></tr></thead>
                <tbody>
                    @forelse($stockOuts as $so)
                    <tr>
                        <td><code style="font-size:11px;">{{ $so->reference_number }}</code></td>
                        <td style="text-align:center;color:#ef4444;font-weight:700;">-{{ $so->quantity }}</td>
                        <td><span class="badge badge-secondary" style="font-size:10px;">{{ $so->type_label }}</span></td>
                        <td style="font-size:12px;">{{ $so->transaction_date->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4"><div class="empty-state" style="padding:20px;"><i class="fas fa-inbox"></i><p>Belum ada data</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
