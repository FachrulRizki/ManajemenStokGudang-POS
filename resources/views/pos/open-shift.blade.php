@extends('layouts.app')
@section('title', 'Buka Shift')
@push('breadcrumb_content', 'POS / <strong>Buka Shift</strong>')
@section('content')
<div style="max-width:460px;margin:40px auto;">
    <div style="text-align:center;margin-bottom:28px;">
        <div style="width:72px;height:72px;background:var(--primary-light);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <i class="fas fa-cash-register" style="font-size:32px;color:var(--primary);"></i>
        </div>
        <h2 style="font-size:22px;font-weight:700;color:#0f172a;margin-bottom:6px;">Buka Shift Kasir</h2>
        <p style="font-size:13px;color:#64748b;">Masukkan modal awal untuk memulai shift Anda</p>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('shifts.open') }}">
                @csrf

                <div style="display:flex;align-items:center;gap:12px;padding:12px;background:#f8fafc;border-radius:8px;margin-bottom:20px;">
                    <img src="{{ $user->avatar_url }}" alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                    <div>
                        <div style="font-weight:600;font-size:13.5px;">{{ $user->name }}</div>
                        <div style="font-size:11px;color:#64748b;">{{ $user->role_label }}</div>
                    </div>
                    <div style="margin-left:auto;font-size:12px;color:#94a3b8;">{{ now()->format('d M Y, H:i') }}</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Modal Awal / Kas Awal <span class="required">*</span></label>
                    <div style="position:relative;">
                        <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#64748b;font-size:13px;font-weight:500;">Rp</span>
                        <input type="number" name="opening_cash" class="form-control {{ $errors->has('opening_cash') ? 'is-invalid' : '' }}"
                            style="padding-left:36px;font-size:16px;font-weight:600;" value="{{ old('opening_cash', 0) }}" min="0" step="1000" required autofocus>
                    </div>
                    @error('opening_cash')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-hint">Uang tunai yang tersedia di laci kasir saat shift dimulai</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan (opsional)</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Catatan untuk shift ini...">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px;font-size:15px;">
                    <i class="fas fa-play"></i> Mulai Shift
                </button>
            </form>
        </div>
    </div>

    <div style="text-align:center;margin-top:16px;">
        <a href="{{ route('dashboard') }}" style="font-size:13px;color:#64748b;text-decoration:none;">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection
