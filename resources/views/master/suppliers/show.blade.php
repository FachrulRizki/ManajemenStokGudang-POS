@extends('layouts.app')
@section('title', 'Detail Supplier')
@push('breadcrumb_content', 'Master Data / <a href="' . route('suppliers.index') . '" style="color:inherit;">Supplier</a> / <strong>Detail</strong>')
@section('content')
<div class="page-header">
    <div><div class="page-title">{{ $supplier->name }}</div><div class="page-subtitle">{{ $supplier->code ?? 'Detail supplier' }}</div></div>
    <div class="btn-group">
        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:380px 1fr;gap:20px;">
    <div>
        <div class="card" style="margin-bottom:16px;">
            <div class="card-header"><div class="card-title"><i class="fas fa-info-circle" style="color:var(--primary)"></i> Informasi Supplier</div></div>
            <div class="card-body">
                @php $rows = [
                    ['Nama', $supplier->name],['Kode', $supplier->code ?? '-'],
                    ['Kontak', $supplier->contact_person ?? '-'],['Telepon', $supplier->phone ?? '-'],
                    ['Email', $supplier->email ?? '-'],['Kota', $supplier->city ?? '-'],
                    ['Alamat', $supplier->address ?? '-'],
                ]; @endphp
                @foreach($rows as [$label, $value])
                <div style="display:flex;gap:12px;padding:8px 0;{{ !$loop->last ? 'border-bottom:1px solid #f1f5f9' : '' }}">
                    <span style="font-size:12px;color:#94a3b8;width:90px;flex-shrink:0;">{{ $label }}</span>
                    <span style="font-size:13px;color:#1e293b;">{{ $value }}</span>
                </div>
                @endforeach
                <div style="margin-top:12px;">
                    @if($supplier->is_active)<span class="badge badge-success"><i class="fas fa-check-circle"></i> Aktif</span>
                    @else<span class="badge badge-secondary">Nonaktif</span>@endif
                </div>
            </div>
        </div>

        <div class="grid grid-2">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-exchange-alt"></i></div>
                <div class="stat-content"><div class="stat-value">{{ number_format($totalTransactions) }}</div><div class="stat-label">Transaksi</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-boxes"></i></div>
                <div class="stat-content"><div class="stat-value">{{ $supplier->products->count() }}</div><div class="stat-label">Produk</div></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-history" style="color:var(--primary)"></i> Riwayat Transaksi Masuk</div></div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Referensi</th><th>Produk</th><th style="text-align:center;">Qty</th><th>Total</th><th>Tanggal</th></tr></thead>
                <tbody>
                    @forelse($supplier->stockIns->take(20) as $si)
                    <tr>
                        <td><code style="font-size:11px;">{{ $si->reference_number }}</code></td>
                        <td style="font-size:13px;">{{ $si->product->name ?? '-' }}</td>
                        <td style="text-align:center;color:#10b981;font-weight:600;">+{{ $si->quantity }}</td>
                        <td style="font-size:13px;">Rp {{ number_format($si->total_price, 0, ',', '.') }}</td>
                        <td style="font-size:12px;color:#64748b;">{{ $si->transaction_date->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5"><div class="empty-state" style="padding:24px;"><i class="fas fa-inbox"></i><p>Belum ada transaksi</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
