@extends('layouts.app')
@section('title', 'Detail Shift ' . $shift->shift_number)
@push('breadcrumb_content', 'POS / <a href="' . route('shifts.index') . '" style="color:inherit;">Shift</a> / <strong>' . $shift->shift_number . '</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">{{ $shift->shift_number }}</div>
        <div class="page-subtitle">Kasir: {{ $shift->user->name }} • {{ $shift->opened_at->format('d M Y') }}</div>
    </div>
    <div class="btn-group">
        <a href="{{ route('shifts.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
</div>

{{-- Summary cards --}}
<div class="grid grid-4" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-receipt"></i></div>
        <div class="stat-content"><div class="stat-value">{{ $shift->total_transactions }}</div><div class="stat-label">Transaksi</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-content"><div class="stat-value" style="font-size:18px;">Rp {{ number_format($shift->total_sales, 0, ',', '.') }}</div><div class="stat-label">Total Penjualan</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-tag"></i></div>
        <div class="stat-content"><div class="stat-value" style="font-size:18px;">Rp {{ number_format($shift->total_discount, 0, ',', '.') }}</div><div class="stat-label">Total Diskon</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="{{ $shift->cash_difference >= 0 ? 'background:#dcfce7;color:#16a34a;' : 'background:#fee2e2;color:#dc2626;' }}"><i class="fas fa-balance-scale"></i></div>
        <div class="stat-content">
            <div class="stat-value" style="font-size:18px;color:{{ $shift->cash_difference >= 0 ? '#16a34a' : '#dc2626' }}">
                {{ $shift->cash_difference >= 0 ? '+' : '' }}Rp {{ number_format($shift->cash_difference ?? 0, 0, ',', '.') }}
            </div>
            <div class="stat-label">Selisih Kas</div>
        </div>
    </div>
</div>

<div class="grid grid-2" style="margin-bottom:20px;">
    {{-- Detail shift --}}
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-info-circle" style="color:var(--primary)"></i> Info Shift</div></div>
        <div class="card-body">
            @php $rows = [
                ['No. Shift',      $shift->shift_number],
                ['Kasir',          $shift->user->name ?? '-'],
                ['Status',         $shift->status === 'open' ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Selesai</span>'],
                ['Dibuka',         $shift->opened_at->format('d M Y H:i')],
                ['Ditutup',        $shift->closed_at?->format('d M Y H:i') ?? '-'],
                ['Durasi',         $shift->duration],
                ['Modal Awal',     'Rp ' . number_format($shift->opening_cash, 0, ',', '.')],
                ['Kas Akhir Setor', $shift->closing_cash !== null ? 'Rp ' . number_format($shift->closing_cash, 0, ',', '.') : '-'],
                ['Kas Seharusnya', $shift->expected_cash !== null ? 'Rp ' . number_format($shift->expected_cash, 0, ',', '.') : '-'],
                ['Total Kas',      'Rp ' . number_format($shift->total_cash, 0, ',', '.')],
                ['Total Non-Kas',  'Rp ' . number_format($shift->total_non_cash, 0, ',', '.')],
                ['Catatan',        $shift->notes ?? '-'],
            ]; @endphp
            @foreach($rows as [$label, $value])
            <div class="detail-row">
                <span class="detail-label">{{ $label }}</span>
                <span class="detail-value">{!! $value !!}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Rekap per metode bayar --}}
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-wallet" style="color:#10b981"></i> Rekap Pembayaran</div></div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Metode</th><th style="text-align:center;">Transaksi</th><th style="text-align:right;">Total</th></tr></thead>
                <tbody>
                    @php
                    $byMethod = $shift->transactions->where('status','paid')->groupBy('payment_method_id');
                    @endphp
                    @forelse($byMethod as $methodId => $txns)
                    <tr>
                        <td>{{ $txns->first()->paymentMethod->name ?? '-' }}</td>
                        <td style="text-align:center;"><span class="badge badge-primary">{{ $txns->count() }}</span></td>
                        <td style="text-align:right;font-weight:600;">Rp {{ number_format($txns->sum('grand_total'), 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:20px;">Belum ada transaksi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Daftar transaksi shift --}}
<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-list" style="color:var(--primary)"></i> Transaksi Shift</div></div>
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th>Invoice</th><th>Pelanggan</th>
                <th style="text-align:center;">Item</th>
                <th>Metode Bayar</th>
                <th style="text-align:right;">Total</th>
                <th style="text-align:center;">Status</th>
                <th>Waktu</th>
                <th style="text-align:center;">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse($shift->transactions as $t)
                <tr>
                    <td><code style="font-size:11px;">{{ $t->invoice_number }}</code></td>
                    <td style="font-size:13px;">{{ $t->customer_name ?? '-' }}</td>
                    <td style="text-align:center;"><span class="badge badge-secondary">{{ $t->items->count() }}</span></td>
                    <td style="font-size:13px;">{{ $t->paymentMethod->name ?? '-' }}</td>
                    <td style="text-align:right;font-weight:700;color:{{ $t->status==='voided'?'#94a3b8':($t->grand_total>0?'#10b981':'#374151') }};">
                        {{ $t->status === 'voided' ? '-' : 'Rp ' . number_format($t->grand_total, 0, ',', '.') }}
                    </td>
                    <td style="text-align:center;"><span class="badge badge-{{ $t->status_color }}">{{ $t->status_label }}</span></td>
                    <td style="font-size:12px;color:#64748b;white-space:nowrap;">{{ $t->transaction_at->format('H:i:s') }}</td>
                    <td style="text-align:center;">
                        <div class="btn-group" style="justify-content:center;">
                            <a href="{{ route('pos.receipt', $t) }}" target="_blank" class="btn btn-sm btn-secondary" title="Struk PDF"><i class="fas fa-file-pdf"></i></a>
                            @if($t->status === 'paid')
                            <form method="POST" action="{{ route('pos.void', $t) }}" onsubmit="return confirm('Yakin void transaksi ini?')">
                                @csrf
                                <input type="hidden" name="reason" value="Void dari detail shift">
                                <button type="submit" class="btn btn-sm btn-warning" title="Void"><i class="fas fa-ban"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8"><div class="empty-state" style="padding:24px;"><i class="fas fa-inbox"></i><p>Belum ada transaksi</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
