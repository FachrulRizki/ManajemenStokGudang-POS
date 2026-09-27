@extends('layouts.app')
@section('title', 'Detail Log')
@push('breadcrumb_content', 'Sistem / <a href="' . route('activity-logs.index') . '" style="color:inherit;">Log Aktivitas</a> / <strong>Detail</strong>')
@section('content')
<div class="page-header">
    <div><div class="page-title">Detail Log Aktivitas</div></div>
    <a href="{{ route('activity-logs.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div style="max-width:800px;">
    <div class="card" style="margin-bottom:16px;">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-info-circle" style="color:var(--primary)"></i> Informasi Log</div>
            <span class="badge badge-{{ $activityLog->action_color }}" style="font-size:12px;">{{ $activityLog->action_label }}</span>
        </div>
        <div class="card-body">
            @php $rows = [
                ['Waktu', $activityLog->created_at->format('d F Y, H:i:s')],
                ['User', $activityLog->user?->name ?? 'Sistem'],
                ['Role', $activityLog->user?->role_label ?? '-'],
                ['Aksi', $activityLog->action_label],
                ['Modul', $activityLog->module],
                ['Deskripsi', $activityLog->description],
                ['Model', $activityLog->model_type ? class_basename($activityLog->model_type).' #'.$activityLog->model_id : '-'],
                ['IP Address', $activityLog->ip_address ?? '-'],
                ['Browser', $activityLog->user_agent ? Str::limit($activityLog->user_agent, 80) : '-'],
            ]; @endphp
            @foreach($rows as [$l, $v])
            <div style="display:flex;gap:16px;padding:9px 0;{{ !$loop->last?'border-bottom:1px solid #f1f5f9':'' }}">
                <span style="width:120px;font-size:12px;color:#64748b;flex-shrink:0;">{{ $l }}</span>
                <span style="font-size:13px;color:#1e293b;">{{ $v }}</span>
            </div>
            @endforeach
        </div>
    </div>

    @if($activityLog->old_values || $activityLog->new_values)
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        @if($activityLog->old_values)
        <div class="card">
            <div class="card-header">
                <div class="card-title" style="color:#dc2626;"><i class="fas fa-minus-circle"></i> Data Sebelum</div>
            </div>
            <div class="card-body" style="padding:0;">
                @foreach($activityLog->old_values as $key => $value)
                <div style="display:flex;gap:12px;padding:8px 16px;{{ !$loop->last?'border-bottom:1px solid #f1f5f9':'' }}font-size:12.5px;">
                    <span style="width:130px;color:#64748b;flex-shrink:0;font-weight:500;">{{ $key }}</span>
                    <span style="color:#dc2626;word-break:break-all;">{{ is_array($value) ? json_encode($value) : ($value ?? '-') }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($activityLog->new_values)
        <div class="card">
            <div class="card-header">
                <div class="card-title" style="color:#16a34a;"><i class="fas fa-plus-circle"></i> Data Sesudah</div>
            </div>
            <div class="card-body" style="padding:0;">
                @foreach($activityLog->new_values as $key => $value)
                <div style="display:flex;gap:12px;padding:8px 16px;{{ !$loop->last?'border-bottom:1px solid #f1f5f9':'' }}font-size:12.5px;">
                    <span style="width:130px;color:#64748b;flex-shrink:0;font-weight:500;">{{ $key }}</span>
                    <span style="color:#16a34a;word-break:break-all;">{{ is_array($value) ? json_encode($value) : ($value ?? '-') }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif
</div>
@endsection
