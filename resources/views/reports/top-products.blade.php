@extends('layouts.app')
@section('title', 'Produk Terlaris')
@push('breadcrumb_content', 'Laporan / <strong>Produk Terlaris</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Produk Terlaris</div>
        <div class="page-subtitle">Ranking produk berdasarkan volume penjualan</div>
    </div>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET"><div class="filter-bar">
            <select name="month" class="form-control" style="width:150px;">
                @foreach($months as $m)
                <option value="{{ $m['value'] }}" {{ $month == $m['value'] ? 'selected' : '' }}>{{ $m['label'] }}</option>
                @endforeach
            </select>
            <select name="year" class="form-control" style="width:110px;">
                @foreach($years as $y)
                <option value="{{ $y['value'] }}" {{ $year == $y['value'] ? 'selected' : '' }}>{{ $y['value'] }}</option>
                @endforeach
            </select>
            <select name="limit" class="form-control" style="width:120px;">
                <option value="10" {{ request('limit',20)==10?'selected':'' }}>Top 10</option>
                <option value="20" {{ request('limit',20)==20?'selected':'' }}>Top 20</option>
                <option value="50" {{ request('limit',20)==50?'selected':'' }}>Top 50</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tampilkan</button>
            <a href="{{ route('reports.top-products') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
        </div></form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-fire" style="color:#f97316"></i> Terlaris - {{ collect($months)->firstWhere('value', $month)['label'] }} {{ $year }}</div>
        <span style="font-size:12px;color:#94a3b8;">{{ $topProducts->count() }} produk</span>
    </div>
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th width="50" style="text-align:center;">No</th>
                <th>Produk</th>
                <th>Kategori</th>
                <th style="text-align:center;">via POS</th>
                <th style="text-align:center;">Manual</th>
                <th style="text-align:center;">Total Terjual</th>
                <th style="text-align:right;">Pendapatan</th>
                <th style="text-align:right;">% dari Total</th>
            </tr></thead>
            <tbody>
                @php $maxSold = $topProducts->max('total_sold') ?: 1; $grandRev = $topProducts->sum('total_revenue'); @endphp
                @forelse($topProducts as $i => $item)
                @php $pct = $grandRev > 0 ? round($item->total_revenue / $grandRev * 100, 1) : 0; @endphp
                <tr>
                    <td style="text-align:center;">
                        @if($i < 3)
                            <div style="width:28px;height:28px;border-radius:50%;background:{{ ['#f59e0b','#94a3b8','#cd7c3a'][$i] }};color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;margin:auto;">{{ $i+1 }}</div>
                        @else
                            <span style="color:#94a3b8;font-size:13px;">{{ $i+1 }}</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:600;font-size:13px;">{{ $item->product->name ?? '-' }}</div>
                        <div style="font-size:11px;color:#94a3b8;">{{ $item->product->code ?? '' }}</div>
                        {{-- Bar --}}
                        <div style="height:4px;background:#f1f5f9;border-radius:2px;margin-top:4px;width:160px;">
                            <div style="height:4px;background:var(--primary);border-radius:2px;width:{{ round($item->total_sold/$maxSold*100) }}%;"></div>
                        </div>
                    </td>
                    <td><span class="badge badge-secondary">{{ $item->product->category->name ?? '-' }}</span></td>
                    <td style="text-align:center;font-weight:600;color:#6366f1;">{{ number_format($item->pos_qty) }} {{ $item->product->unit->symbol ?? '' }}</td>
                    <td style="text-align:center;color:#64748b;">{{ number_format($item->manual_qty) }} {{ $item->product->unit->symbol ?? '' }}</td>
                    <td style="text-align:center;"><span style="font-size:16px;font-weight:700;color:#0f172a;">{{ number_format($item->total_sold) }}</span> <span style="font-size:11px;color:#94a3b8;">{{ $item->product->unit->symbol ?? '' }}</span></td>
                    <td style="text-align:right;font-weight:700;color:#10b981;">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</td>
                    <td style="text-align:right;">
                        <span style="font-size:13px;font-weight:600;color:#64748b;">{{ $pct }}%</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8"><div class="empty-state"><i class="fas fa-chart-bar"></i><p>Belum ada data penjualan untuk periode ini.</p></div></td></tr>
                @endforelse
            </tbody>
            @if($topProducts->count())
            <tfoot>
                <tr style="background:#f8fafc;">
                    <td colspan="5" style="padding:10px 16px;font-weight:700;font-size:13px;color:#374151;">Total</td>
                    <td style="padding:10px 16px;text-align:center;font-weight:700;">{{ number_format($topProducts->sum('total_sold')) }}</td>
                    <td style="padding:10px 16px;text-align:right;font-weight:700;color:#10b981;">Rp {{ number_format($topProducts->sum('total_revenue'), 0, ',', '.') }}</td>
                    <td style="padding:10px 16px;text-align:right;font-weight:700;">100%</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
