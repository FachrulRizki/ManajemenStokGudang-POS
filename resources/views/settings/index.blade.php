@extends('layouts.app')
@section('title', 'Pengaturan Aplikasi')
@push('breadcrumb_content', 'Sistem / <strong>Pengaturan</strong>')
@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Pengaturan Aplikasi</div>
        <div class="page-subtitle">Kustomisasi nama, logo, tema, dan preferensi sistem</div>
    </div>
</div>

<form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;">
        {{-- Kiri --}}
        <div>
            {{-- Informasi Toko --}}
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-store" style="color:var(--primary)"></i> Informasi Toko / Gudang</div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Nama Aplikasi / Toko <span class="required">*</span></label>
                        <input type="text" name="app_name" class="form-control {{ $errors->has('app_name')?'is-invalid':'' }}"
                            value="{{ old('app_name', $settings['app_name']->value ?? config('app.name')) }}"
                            placeholder="Nama toko / gudang Anda">
                        @error('app_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tagline</label>
                        <input type="text" name="app_tagline" class="form-control"
                            value="{{ old('app_tagline', $settings['app_tagline']->value ?? '') }}"
                            placeholder="Slogan atau deskripsi singkat">
                    </div>
                    <div class="form-row cols-2">
                        <div class="form-group">
                            <label class="form-label">Mata Uang <span class="required">*</span></label>
                            <select name="app_currency" class="form-control">
                                @php $cur = $settings['app_currency']->value ?? 'Rp'; @endphp
                                <option value="Rp" {{ $cur=='Rp'?'selected':'' }}>Rupiah (Rp)</option>
                                <option value="USD" {{ $cur=='USD'?'selected':'' }}>US Dollar ($)</option>
                                <option value="SGD" {{ $cur=='SGD'?'selected':'' }}>Singapore Dollar (S$)</option>
                                <option value="MYR" {{ $cur=='MYR'?'selected':'' }}>Ringgit (RM)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Item Per Halaman</label>
                            <select name="items_per_page" class="form-control">
                                @php $ipp = $settings['items_per_page']->value ?? 10; @endphp
                                @foreach([10,15,20,25,50] as $n)
                                <option value="{{ $n }}" {{ $ipp==$n?'selected':'' }}>{{ $n }} item</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notifikasi --}}
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-bell" style="color:#f59e0b"></i> Notifikasi & Preferensi</div>
                </div>
                <div class="card-body">
                    <div class="form-group" style="margin-bottom:0;">
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;">
                            <div>
                                <div style="font-weight:500;font-size:13px;">Notifikasi Stok Menipis</div>
                                <div style="font-size:12px;color:#64748b;margin-top:2px;">Tampilkan alert di header jika ada produk stok di bawah minimum</div>
                            </div>
                            <label class="toggle">
                                <input type="checkbox" name="low_stock_notif" value="1"
                                    {{ old('low_stock_notif', ($settings['low_stock_notif']->value ?? '1') == '1') ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pengaturan Printer --}}
            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-print" style="color:#0ea5e9"></i> Pengaturan Printer & Cetak</div>
                </div>
                <div class="card-body">

                    {{-- Struk Thermal --}}
                    <div style="padding-bottom:16px;margin-bottom:16px;border-bottom:1px solid #f1f5f9;">
                        <div style="font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px;">
                            🧾 Struk Thermal (Kasir)
                        </div>
                        <div class="form-row cols-2">
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Lebar Kertas Thermal</label>
                                @php $tw = (int)($settings['receipt_thermal_width']->value ?? 80); @endphp
                                <div style="display:flex;gap:8px;">
                                    @foreach([58 => '58mm (Kecil)', 80 => '80mm (Standar)'] as $val => $lbl)
                                    <label style="flex:1;cursor:pointer;">
                                        <input type="radio" name="receipt_thermal_width" value="{{ $val }}" style="display:none;"
                                            {{ $tw == $val ? 'checked' : '' }}>
                                        <div class="printer-option {{ $tw == $val ? 'selected' : '' }}"
                                            data-val="{{ $val }}" data-group="thermal"
                                            style="border:2px solid {{ $tw == $val ? 'var(--primary)' : '#e2e8f0' }};border-radius:8px;padding:10px;text-align:center;font-size:12.5px;font-weight:600;transition:all .15s;color:{{ $tw == $val ? 'var(--primary)' : '#64748b' }};">
                                            <i class="fas fa-receipt" style="display:block;font-size:18px;margin-bottom:4px;"></i>
                                            {{ $lbl }}
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Tampilkan Logo di Struk</label>
                                <div style="display:flex;align-items:center;gap:10px;padding-top:6px;">
                                    <label class="toggle" style="margin:0;">
                                        <input type="checkbox" name="receipt_show_logo" value="1"
                                            {{ old('receipt_show_logo', ($settings['receipt_show_logo']->value ?? '1') == '1') ? 'checked' : '' }}>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <span style="font-size:12px;color:#64748b;">Logo toko tampil di bagian atas struk</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group" style="margin-top:12px;margin-bottom:0;">
                            <label class="form-label">Teks Footer Struk</label>
                            <input type="text" name="receipt_footer_text" class="form-control"
                                value="{{ old('receipt_footer_text', $settings['receipt_footer_text']->value ?? 'Terima kasih atas kepercayaan Anda!') }}"
                                placeholder="Teks penutup di bagian bawah struk" maxlength="300">
                            <div class="form-hint">Muncul di bagian bawah struk thermal maupun PDF</div>
                        </div>
                    </div>

                    {{-- Struk PDF --}}
                    <div style="padding-bottom:16px;margin-bottom:16px;border-bottom:1px solid #f1f5f9;">
                        <div style="font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px;">
                            📄 Struk PDF
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Ukuran Kertas PDF</label>
                            @php $pp = $settings['receipt_pdf_paper']->value ?? 'A5'; @endphp
                            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                @foreach(['A4' => 'A4 (210×297mm)', 'A5' => 'A5 (148×210mm)', 'Letter' => 'Letter (216×279mm)'] as $val => $lbl)
                                <label style="cursor:pointer;">
                                    <input type="radio" name="receipt_pdf_paper" value="{{ $val }}" style="display:none;"
                                        {{ $pp == $val ? 'checked' : '' }}>
                                    <div class="printer-option {{ $pp == $val ? 'selected' : '' }}"
                                        data-val="{{ $val }}" data-group="pdf"
                                        style="border:2px solid {{ $pp == $val ? 'var(--primary)' : '#e2e8f0' }};border-radius:8px;padding:10px 16px;text-align:center;font-size:12.5px;font-weight:600;transition:all .15s;color:{{ $pp == $val ? 'var(--primary)' : '#64748b' }};">
                                        <i class="fas fa-file-pdf" style="display:block;font-size:18px;margin-bottom:4px;"></i>
                                        {{ $lbl }}
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Label Barcode --}}
                    <div>
                        <div style="font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px;">
                            🏷️ Label Barcode Produk
                        </div>
                        <div class="form-row cols-3">
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Lebar Label (mm)</label>
                                <input type="number" name="barcode_label_width" class="form-control {{ $errors->has('barcode_label_width')?'is-invalid':'' }}"
                                    value="{{ old('barcode_label_width', $settings['barcode_label_width']->value ?? 50) }}"
                                    min="20" max="150" placeholder="50">
                                @error('barcode_label_width')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Tinggi Label (mm)</label>
                                <input type="number" name="barcode_label_height" class="form-control {{ $errors->has('barcode_label_height')?'is-invalid':'' }}"
                                    value="{{ old('barcode_label_height', $settings['barcode_label_height']->value ?? 30) }}"
                                    min="15" max="100" placeholder="30">
                                @error('barcode_label_height')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Kolom Per Baris</label>
                                <select name="barcode_cols_per_row" class="form-control">
                                    @php $cols = (int)($settings['barcode_cols_per_row']->value ?? 3); @endphp
                                    @foreach([1,2,3,4,5,6] as $n)
                                    <option value="{{ $n }}" {{ $cols == $n ? 'selected' : '' }}>{{ $n }} kolom</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div style="margin-top:10px;padding:10px 14px;background:#f8fafc;border-radius:8px;font-size:12px;color:#64748b;">
                            <i class="fas fa-info-circle" style="color:#0ea5e9;margin-right:6px;"></i>
                            Preview: label <strong id="lblPreviewSize">{{ $settings['barcode_label_width']->value ?? 50 }} × {{ $settings['barcode_label_height']->value ?? 30 }} mm</strong>
                            — <strong id="lblPreviewCols">{{ $settings['barcode_cols_per_row']->value ?? 3 }} kolom</strong> per baris saat cetak
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kanan --}}
        <div>
            {{-- Logo --}}
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header"><div class="card-title"><i class="fas fa-image" style="color:#8b5cf6"></i> Logo & Favicon</div></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Logo Aplikasi</label>
                        <div id="logoPreview" style="width:100%;height:120px;background:#f8fafc;border:2px dashed #e2e8f0;border-radius:8px;display:flex;align-items:center;justify-content:center;margin-bottom:10px;overflow:hidden;cursor:pointer;" onclick="document.getElementById('logoInput').click()">
                            @if(!empty($settings['app_logo']->value))
                                <img src="{{ asset('storage/'.$settings['app_logo']->value) }}" style="max-width:100%;object-fit:contain;" id="logoImg">
                            @else
                                <div style="text-align:center;color:#94a3b8;" id="logoPlaceholder">
                                    <i class="fas fa-image" style="font-size:28px;margin-bottom:6px;"></i>
                                    <p style="font-size:11px;">Klik upload logo</p>
                                </div>
                            @endif
                        </div>
                        <input type="file" name="app_logo" id="logoInput" accept="image/*" style="display:none;"
                            onchange="previewFile(this,'logoPreview')">
                        <div class="form-hint">PNG/JPG transparan, rekomendasi 200×200px</div>
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Favicon</label>
                        <div id="faviconPreview" style="width:64px;height:64px;background:#f8fafc;border:2px dashed #e2e8f0;border-radius:8px;display:flex;align-items:center;justify-content:center;margin-bottom:8px;overflow:hidden;cursor:pointer;" onclick="document.getElementById('faviconInput').click()">
                            @if(!empty($settings['app_favicon']->value))
                                <img src="{{ asset('storage/'.$settings['app_favicon']->value) }}" style="width:40px;height:40px;object-fit:contain;">
                            @else
                                <i class="fas fa-star" style="color:#e2e8f0;font-size:22px;"></i>
                            @endif
                        </div>
                        <input type="file" name="app_favicon" id="faviconInput" accept="image/*" style="display:none;"
                            onchange="previewFile(this,'faviconPreview')">
                        <div class="form-hint">ICO/PNG 32×32px</div>
                    </div>
                </div>
            </div>

            {{-- Tema --}}
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header"><div class="card-title"><i class="fas fa-palette" style="color:#ec4899"></i> Tema Warna</div></div>
                <div class="card-body">
                    @php
                    $themes = [
                        'indigo' => ['label'=>'Indigo','color'=>'#6366f1'],
                        'blue'   => ['label'=>'Biru','color'=>'#3b82f6'],
                        'green'  => ['label'=>'Hijau','color'=>'#10b981'],
                        'red'    => ['label'=>'Merah','color'=>'#ef4444'],
                        'orange' => ['label'=>'Oranye','color'=>'#f97316'],
                        'purple' => ['label'=>'Ungu','color'=>'#8b5cf6'],
                    ];
                    $currentTheme = $settings['app_theme']->value ?? 'indigo';
                    @endphp
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
                        @foreach($themes as $key => $t)
                        <label style="cursor:pointer;">
                            <input type="radio" name="app_theme" value="{{ $key }}" style="display:none;"
                                {{ $currentTheme==$key?'checked':'' }} onchange="applyTheme('{{ $key }}')">
                            <div class="theme-option {{ $currentTheme==$key?'selected':'' }}" data-theme="{{ $key }}"
                                style="border:2px solid {{ $currentTheme==$key?$t['color']:'#e2e8f0' }};border-radius:10px;padding:10px 6px;text-align:center;transition:all .15s;">
                                <div style="width:32px;height:32px;background:{{ $t['color'] }};border-radius:8px;margin:0 auto 6px;"></div>
                                <div style="font-size:11.5px;font-weight:600;color:#374151;">{{ $t['label'] }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                <i class="fas fa-save"></i> Simpan Semua Pengaturan
            </button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function previewFile(input, previewId) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const box = document.getElementById(previewId);
        box.innerHTML = `<img src="${e.target.result}" style="max-width:100%;object-fit:contain;">`;
    };
    reader.readAsDataURL(input.files[0]);
}

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    // Update border highlight on all theme options
    document.querySelectorAll('.theme-option').forEach(el => {
        const colors = {indigo:'#6366f1',blue:'#3b82f6',green:'#10b981',red:'#ef4444',orange:'#f97316',purple:'#8b5cf6'};
        el.style.borderColor = el.dataset.theme === theme ? colors[theme] : '#e2e8f0';
    });
}

// Printer option radio selection (visual highlight)
document.querySelectorAll('.printer-option').forEach(el => {
    el.addEventListener('click', function () {
        const group = this.dataset.group;
        // Deselect all in same group
        document.querySelectorAll(`.printer-option[data-group="${group}"]`).forEach(o => {
            o.style.borderColor = '#e2e8f0';
            o.style.color = '#64748b';
            o.classList.remove('selected');
        });
        // Select clicked
        this.style.borderColor = 'var(--primary)';
        this.style.color = 'var(--primary)';
        this.classList.add('selected');
        // Check the corresponding radio input
        const val = this.dataset.val;
        const name = group === 'thermal' ? 'receipt_thermal_width' : 'receipt_pdf_paper';
        const radio = document.querySelector(`input[name="${name}"][value="${val}"]`);
        if (radio) radio.checked = true;
    });
});

// Barcode label size preview
function updateLabelPreview() {
    const w = document.querySelector('[name="barcode_label_width"]')?.value || 50;
    const h = document.querySelector('[name="barcode_label_height"]')?.value || 30;
    const c = document.querySelector('[name="barcode_cols_per_row"]')?.value || 3;
    const sizeEl = document.getElementById('lblPreviewSize');
    const colsEl = document.getElementById('lblPreviewCols');
    if (sizeEl) sizeEl.textContent = `${w} × ${h} mm`;
    if (colsEl) colsEl.textContent = `${c} kolom`;
}

document.querySelector('[name="barcode_label_width"]')?.addEventListener('input', updateLabelPreview);
document.querySelector('[name="barcode_label_height"]')?.addEventListener('input', updateLabelPreview);
document.querySelector('[name="barcode_cols_per_row"]')?.addEventListener('change', updateLabelPreview);
</script>
@endpush
