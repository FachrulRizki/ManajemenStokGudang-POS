@extends('layouts.app')
@section('title', 'Dashboard')
@push('breadcrumb_content', '<strong>Dashboard</strong>')
@section('content')
@php
    $currency = \App\Models\AppSetting::get('app_currency', 'Rp');
    $month    = \Carbon\Carbon::now()->translatedFormat('F Y');
@endphp

{{-- -- Page Header ---------------------------------------- --}}
<div class="page-header">
    <div>
        <div class="page-title">Dashboard</div>
        <div class="page-subtitle">Ringkasan stok & aktivitas gudang - {{ $month }}</div>
    </div>
    <div class="btn-group">
        <a href="{{ route('reports.stock') }}" class="btn btn-outline">
            <i class="fas fa-chart-bar"></i> Lihat Laporan
        </a>
    </div>
</div>

{{-- -- Stat Cards ------------------------------------------ --}}
<div class="grid grid-4" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-boxes"></i></div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($totalProducts) }}</div>
            <div class="stat-label">Total Produk</div>
            <div class="stat-change {{ $lowStockProducts->count() > 0 ? 'down' : 'up' }}">
                <i class="fas fa-{{ $lowStockProducts->count() > 0 ? 'exclamation-triangle' : 'check' }}"></i>
                {{ $lowStockProducts->count() > 0 ? $lowStockProducts->count().' menipis' : 'Stok aman' }}
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-arrow-circle-down"></i></div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($stockInThisMonth) }}</div>
            <div class="stat-label">Stok Masuk Bulan Ini</div>
            <div class="stat-change up"><i class="fas fa-arrow-up"></i> unit diterima</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-arrow-circle-up"></i></div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($stockOutThisMonth) }}</div>
            <div class="stat-label">Stok Keluar Bulan Ini</div>
            <div class="stat-change down"><i class="fas fa-arrow-down"></i> unit terpakai</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-content">
            <div class="stat-value" style="font-size:18px;">{{ $currency }} {{ number_format($totalStockValue, 0, ',', '.') }}</div>
            <div class="stat-label">Nilai Total Stok</div>
            <div class="stat-change up"><i class="fas fa-warehouse"></i> {{ $totalCategories }} kategori</div>
        </div>
    </div>
</div>

{{-- -- POS Stat Cards -------------------------------------- --}}
<div class="grid grid-4" style="margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-cash-register"></i></div>
        <div class="stat-content">
            <div class="stat-value">{{ number_format($todayTransactions) }}</div>
            <div class="stat-label">Transaksi Hari Ini</div>
            <div class="stat-change up"><i class="fas fa-receipt"></i> via POS</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-content">
            <div class="stat-value" style="font-size:18px;">{{ $currency }} {{ number_format($todayRevenue, 0, ',', '.') }}</div>
            <div class="stat-label">Pendapatan Hari Ini</div>
            <div class="stat-change up"><i class="fas fa-arrow-up"></i> dari kasir</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-chart-line"></i></div>
        <div class="stat-content">
            <div class="stat-value" style="font-size:18px;">{{ $currency }} {{ number_format($monthRevenue, 0, ',', '.') }}</div>
            <div class="stat-label">Pendapatan Bulan Ini</div>
            <div class="stat-change up"><i class="fas fa-calendar"></i> {{ $month }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:{{ $openShifts > 0 ? '#dcfce7' : '#f1f5f9' }};color:{{ $openShifts > 0 ? '#16a34a' : '#64748b' }};"><i class="fas fa-user-clock"></i></div>
        <div class="stat-content">
            <div class="stat-value">{{ $openShifts }}</div>
            <div class="stat-label">Shift Aktif</div>
            <div class="stat-change {{ $openShifts > 0 ? 'up' : 'down' }}">
                <i class="fas fa-{{ $openShifts > 0 ? 'circle' : 'stop-circle' }}"></i>
                {{ $openShifts > 0 ? 'kasir bertugas' : 'tidak ada shift' }}
            </div>
        </div>
    </div>
</div>        </div>
    </div>
</div>

{{-- -- Row 2: Chart + Top Products ----------------------- --}}
<div style="display:grid;grid-template-columns:1fr 380px;gap:20px;margin-bottom:24px;">
    {{-- Chart Stok Masuk vs Keluar --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-chart-line" style="color:var(--primary)"></i>
                Grafik Stok Masuk & Keluar (12 Bulan Terakhir)
            </div>
        </div>
        <div class="card-body" style="padding:16px 20px;">
            <canvas id="stockChart" height="90"></canvas>
        </div>
    </div>

    {{-- Top Products --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-fire" style="color:#f97316"></i>
                Barang Terlaris Bulan Ini
            </div>
        </div>
        @if($topProducts->count())
        <div style="padding:8px 0;">
            @foreach($topProducts as $i => $item)
            <div style="display:flex;align-items:center;gap:12px;padding:10px 20px;{{ !$loop->last ? 'border-bottom:1px solid #f1f5f9' : '' }}">
                <div style="width:28px;height:28px;background:{{ ['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6'][$i % 5] }};border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:700;flex-shrink:0;">
                    {{ $i + 1 }}
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:13px;font-weight:600;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $item->product->name ?? '-' }}
                    </div>
                    <div style="font-size:11px;color:#94a3b8;">
                        {{ $item->product->category->name ?? '-' }}
                    </div>
                </div>
                <div style="text-align:right;flex-shrink:0;">
                    <div style="font-size:14px;font-weight:700;color:#0f172a;">{{ number_format($item->total_sold) }}</div>
                    <div style="font-size:10px;color:#94a3b8;">unit terjual</div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-state" style="padding:32px;">
            <i class="fas fa-inbox"></i>
            <p>Belum ada data penjualan bulan ini</p>
        </div>
        @endif
        <div class="card-footer" style="text-align:center;">
            <a href="{{ route('reports.stock-out') }}" style="font-size:12px;color:var(--primary);text-decoration:none;font-weight:500;">
                Lihat semua laporan <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

{{-- -- Row 3: Pie Chart + Low Stock ---------------------- --}}
<div style="display:grid;grid-template-columns:340px 1fr;gap:20px;margin-bottom:24px;">
    {{-- Distribusi Kategori --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-chart-pie" style="color:#8b5cf6"></i>
                Stok per Kategori
            </div>
        </div>
        <div class="card-body" style="padding:16px;">
            <canvas id="categoryChart" height="180"></canvas>
        </div>
    </div>

    {{-- Stok Menipis --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-exclamation-triangle" style="color:#f59e0b"></i>
                Stok Menipis / Habis
            </div>
            @if($lowStockProducts->count())
            <span class="badge badge-warning">{{ $lowStockProducts->count() }} produk</span>
            @endif
        </div>
        @if($lowStockProducts->count())
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th style="text-align:center;">Stok</th>
                        <th style="text-align:center;">Min</th>
                        <th style="text-align:center;">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStockProducts as $product)
                    <tr>
                        <td>
                            <div style="font-weight:600;font-size:13px;">{{ $product->name }}</div>
                            <div style="font-size:11px;color:#94a3b8;">{{ $product->code }}</div>
                        </td>
                        <td><span class="badge badge-secondary">{{ $product->category->name }}</span></td>
                        <td style="text-align:center;">
                            <span style="font-weight:700;color:{{ $product->stock <= 0 ? '#dc2626' : '#d97706' }};">
                                {{ $product->stock }}
                            </span>
                            <span style="font-size:11px;color:#94a3b8;"> {{ $product->unit->symbol }}</span>
                        </td>
                        <td style="text-align:center;color:#64748b;">{{ $product->min_stock }}</td>
                        <td style="text-align:center;">
                            @if($product->stock <= 0)
                                <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Habis</span>
                            @else
                                <span class="badge badge-warning"><i class="fas fa-exclamation"></i> Menipis</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('stock-in.create') }}?product_id={{ $product->id }}" class="btn btn-sm btn-success">
                                <i class="fas fa-plus"></i> Isi
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state" style="padding:40px;">
            <i class="fas fa-check-circle" style="color:#10b981;opacity:1;"></i>
            <p style="color:#10b981;font-weight:600;margin-top:8px;">Semua stok dalam kondisi aman</p>
        </div>
        @endif
        @if($lowStockProducts->count())
        <div class="card-footer" style="text-align:right;">
            <a href="{{ route('products.index', ['status'=>'low']) }}" style="font-size:12px;color:var(--primary);text-decoration:none;font-weight:500;">
                Lihat semua produk menipis <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        @endif
    </div>
</div>

{{-- -- Row 4: Recent Transactions ------------------------ --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    {{-- Recent Stock In --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-arrow-circle-down" style="color:#10b981"></i>
                Stok Masuk Terbaru
            </div>
            <a href="{{ route('stock-in.index') }}" class="btn btn-sm btn-secondary">Lihat Semua</a>
        </div>
        @if($recentStockIn->count())
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th style="text-align:center;">Qty</th>
                        <th>Supplier</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentStockIn as $item)
                    <tr>
                        <td>
                            <div style="font-weight:600;font-size:13px;">{{ Str::limit($item->product->name, 25) }}</div>
                            <div style="font-size:11px;color:#94a3b8;">{{ $item->reference_number }}</div>
                        </td>
                        <td style="text-align:center;">
                            <span style="color:#10b981;font-weight:700;">+{{ number_format($item->quantity) }}</span>
                        </td>
                        <td style="font-size:12px;color:#64748b;">{{ $item->supplier->name ?? '-' }}</td>
                        <td style="font-size:12px;color:#64748b;">{{ $item->transaction_date->format('d M Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state"><i class="fas fa-inbox"></i><p>Belum ada transaksi</p></div>
        @endif
    </div>

    {{-- Recent Transactions POS --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-receipt" style="color:#6366f1"></i> Transaksi POS Terbaru</div>
            <a href="{{ route('pos.history') }}" class="btn btn-sm btn-secondary">Lihat Semua</a>
        </div>
        @if($recentTransactions->count())
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Invoice</th><th>Kasir</th><th style="text-align:center;">Item</th><th>Metode</th><th style="text-align:right;">Total</th></tr></thead>
                <tbody>
                    @foreach($recentTransactions as $t)
                    <tr>
                        <td>
                            <div style="font-size:11.5px;font-family:monospace;background:#f1f5f9;padding:2px 6px;border-radius:4px;display:inline-block;">{{ $t->invoice_number }}</div>
                            <div style="font-size:10px;color:#94a3b8;">{{ $t->transaction_at->format('d M H:i') }}</div>
                        </td>
                        <td style="font-size:12px;color:#64748b;">{{ $t->user->name ?? '-' }}</td>
                        <td style="text-align:center;"><span class="badge badge-secondary">{{ $t->items->count() }}</span></td>
                        <td style="font-size:12px;">{{ $t->paymentMethod->name ?? '-' }}</td>
                        <td style="text-align:right;font-weight:700;color:#10b981;font-size:13px;">Rp {{ number_format($t->grand_total, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state" style="padding:32px;"><i class="fas fa-receipt"></i><p>Belum ada transaksi POS</p></div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// -- Chart Stok Masuk vs Keluar --------------------------
const stockCtx = document.getElementById('stockChart').getContext('2d');
new Chart(stockCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [
            {
                label: 'Stok Masuk',
                data: {!! json_encode($chartStockIn) !!},
                backgroundColor: 'rgba(16,185,129,0.8)',
                borderColor: '#10b981',
                borderWidth: 1,
                borderRadius: 4,
            },
            {
                label: 'Stok Keluar',
                data: {!! json_encode($chartStockOut) !!},
                backgroundColor: 'rgba(239,68,68,0.8)',
                borderColor: '#ef4444',
                borderWidth: 1,
                borderRadius: 4,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { position: 'top', labels: { font: { size: 12, family: 'Inter' }, boxWidth: 12 } },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString('id-ID')} unit`
                }
            }
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 } } },
            y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } } }
        }
    }
});

// -- Pie Chart Kategori ----------------------------------
const catCtx = document.getElementById('categoryChart').getContext('2d');
const catColors = ['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6','#3b82f6','#ec4899','#14b8a6'];
new Chart(catCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($stockByCategory->pluck('name')) !!},
        datasets: [{
            data: {!! json_encode($stockByCategory->pluck('products_sum_stock')) !!},
            backgroundColor: catColors,
            borderWidth: 2,
            borderColor: '#fff',
        }]
    },
    options: {
        responsive: true,
        cutout: '60%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: { font: { size: 11, family: 'Inter' }, boxWidth: 10, padding: 10 }
            },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.label}: ${ctx.parsed.toLocaleString('id-ID')} unit`
                }
            }
        }
    }
});
</script>
@endpush
