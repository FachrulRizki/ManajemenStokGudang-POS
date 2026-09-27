@extends('layouts.app')
@section('title', 'Riwayat Shift')
@push('breadcrumb_content', 'POS / <strong>Riwayat Shift</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Riwayat Shift</div>
        <div class="page-subtitle">Daftar semua shift kasir</div>
    </div>
    <div class="btn-group">
        <a href="{{ route('pos.kasir') }}" class="btn btn-primary"><i class="fas fa-cash-register"></i> Ke Kasir</a>
        @if(!$myShift)
        <button class="btn btn-success" onclick="openModal('modalBukaShift')"><i class="fas fa-play"></i> Buka Shift</button>
        @else
        <button class="btn btn-danger" onclick="openModal('modalTutupShift')"><i class="fas fa-stop"></i> Tutup Shift</button>
        @endif
    </div>
</div>

@if($myShift)
<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;gap:12px;">
    <i class="fas fa-circle" style="color:#16a34a;font-size:10px;"></i>
    <div>
        <span style="font-weight:600;color:#15803d;">Shift aktif: {{ $myShift->shift_number }}</span>
        <span style="color:#64748b;font-size:12px;margin-left:12px;">Dibuka {{ $myShift->opened_at->format('d M Y H:i') }}</span>
        <span style="color:#64748b;font-size:12px;margin-left:8px;">• {{ $myShift->total_transactions }} transaksi</span>
        <span style="color:#15803d;font-weight:600;font-size:12px;margin-left:8px;">• Rp {{ number_format($myShift->total_sales, 0, ',', '.') }}</span>
    </div>
</div>
@endif

<div class="card" style="margin-bottom:16px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET"><div class="filter-bar">
            <div class="search-input"><i class="fas fa-search"></i>
                <input type="text" name="search" class="form-control" placeholder="No. shift atau kasir..." value="{{ request('search') }}">
            </div>
            <select name="status" class="form-control" style="width:140px;">
                <option value="">Semua Status</option>
                <option value="open" {{ request('status')=='open'?'selected':'' }}>Aktif</option>
                <option value="closed" {{ request('status')=='closed'?'selected':'' }}>Selesai</option>
            </select>
            <select name="user_id" class="form-control" style="width:180px;">
                <option value="">Semua Kasir</option>
                @foreach($kasirs as $k)
                <option value="{{ $k->id }}" {{ request('user_id')==$k->id?'selected':'' }}>{{ $k->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('shifts.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i></a>
        </div></form>
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr>
                <th>No. Shift</th><th>Kasir</th><th>Dibuka</th><th>Ditutup</th>
                <th style="text-align:center;">Transaksi</th>
                <th style="text-align:right;">Total Penjualan</th>
                <th style="text-align:right;">Modal Awal</th>
                <th style="text-align:right;">Selisih Kas</th>
                <th style="text-align:center;">Status</th>
                <th style="text-align:center;">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse($shifts as $shift)
                <tr>
                    <td><code style="font-size:11.5px;background:#f1f5f9;padding:2px 6px;border-radius:4px;">{{ $shift->shift_number }}</code></td>
                    <td>
                        <div style="font-weight:600;font-size:13px;">{{ $shift->user->name ?? '-' }}</div>
                        <div style="font-size:11px;color:#94a3b8;">{{ $shift->user->role_label ?? '' }}</div>
                    </td>
                    <td style="font-size:12px;white-space:nowrap;">{{ $shift->opened_at->format('d M Y H:i') }}</td>
                    <td style="font-size:12px;white-space:nowrap;color:#64748b;">{{ $shift->closed_at?->format('d M Y H:i') ?? '-' }}</td>
                    <td style="text-align:center;"><span class="badge badge-primary">{{ $shift->transactions_count }}</span></td>
                    <td style="text-align:right;font-weight:600;color:#10b981;">Rp {{ number_format($shift->total_sales, 0, ',', '.') }}</td>
                    <td style="text-align:right;font-size:13px;">Rp {{ number_format($shift->opening_cash, 0, ',', '.') }}</td>
                    <td style="text-align:right;font-size:13px;">
                        @if($shift->cash_difference !== null)
                            <span style="color:{{ $shift->cash_difference >= 0 ? '#10b981' : '#ef4444' }};font-weight:600;">
                                {{ $shift->cash_difference >= 0 ? '+' : '' }}Rp {{ number_format($shift->cash_difference, 0, ',', '.') }}
                            </span>
                        @else <span style="color:#94a3b8;">-</span> @endif
                    </td>
                    <td style="text-align:center;">
                        @if($shift->status === 'open')
                            <span class="badge badge-success"><i class="fas fa-circle" style="font-size:7px;"></i> Aktif</span>
                        @else
                            <span class="badge badge-secondary"><i class="fas fa-check"></i> Selesai</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <div class="btn-group" style="justify-content:center;">
                            <a href="{{ route('shifts.show', $shift) }}" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i></a>
                            @if($shift->status === 'open' && ($shift->user_id === auth()->id() || auth()->user()->isAdmin() || auth()->user()->isManager()))
                            <button type="button" class="btn btn-sm btn-danger" onclick="tutupShift({{ $shift->id }}, '{{ $shift->shift_number }}')"><i class="fas fa-stop"></i></button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10"><div class="empty-state"><i class="fas fa-clock"></i><p>Belum ada data shift.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <span>{{ $shifts->firstItem() ?? 0 }}-{{ $shifts->lastItem() ?? 0 }} dari {{ $shifts->total() }}</span>
        {{ $shifts->links('vendor.pagination.simple') }}
    </div>
</div>

{{-- Modal Buka Shift --}}
<div class="modal-backdrop" id="modalBukaShift">
    <div class="modal-box" style="max-width:420px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-play" style="color:#10b981"></i> Buka Shift Baru</div>
            <button class="modal-close" onclick="closeModal('modalBukaShift')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('shifts.open') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Modal Awal (Kas Awal) <span class="required">*</span></label>
                    <div style="position:relative;">
                        <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#64748b;font-size:13px;">Rp</span>
                        <input type="number" name="opening_cash" class="form-control" style="padding-left:34px;" required min="0" value="0">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalBukaShift')">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-play"></i> Buka Shift</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Tutup Shift (dari daftar) --}}
<div class="modal-backdrop" id="modalTutupShift">
    <div class="modal-box" style="max-width:420px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-stop-circle" style="color:#ef4444"></i> Tutup Shift <span id="tutupShiftNumber"></span></div>
            <button class="modal-close" onclick="closeModal('modalTutupShift')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="formTutupShift">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Uang di Laci <span class="required">*</span></label>
                    <div style="position:relative;">
                        <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#64748b;font-size:13px;">Rp</span>
                        <input type="number" name="closing_cash" class="form-control" style="padding-left:34px;" required min="0">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTutupShift')">Batal</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-stop-circle"></i> Tutup Shift</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function tutupShift(id, number) {
    document.getElementById('formTutupShift').action = '/shifts/close/' + id;
    document.getElementById('tutupShiftNumber').textContent = number;
    openModal('modalTutupShift');
}
</script>
@endpush
