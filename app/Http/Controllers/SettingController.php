<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = AppSetting::all()->keyBy('key');
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            // General
            'app_name'       => ['required', 'string', 'max:100'],
            'app_tagline'    => ['nullable', 'string', 'max:200'],
            'app_logo'       => ['nullable', 'image', 'max:2048'],
            'app_favicon'    => ['nullable', 'image', 'max:512'],
            'app_theme'      => ['required', 'in:blue,indigo,green,red,orange,purple'],
            'app_currency'   => ['required', 'string', 'max:10'],
            'low_stock_notif'=> ['boolean'],
            'items_per_page' => ['required', 'integer', 'min:5', 'max:100'],
            // Printer
            'receipt_thermal_width' => ['required', 'in:58,80'],
            'receipt_pdf_paper'     => ['required', 'in:A4,A5,Letter'],
            'barcode_label_width'   => ['required', 'integer', 'min:20', 'max:150'],
            'barcode_label_height'  => ['required', 'integer', 'min:15', 'max:100'],
            'barcode_cols_per_row'  => ['required', 'integer', 'min:1', 'max:6'],
            'receipt_show_logo'     => ['boolean'],
            'receipt_footer_text'   => ['nullable', 'string', 'max:300'],
        ]);

        // Handle logo upload
        if ($request->hasFile('app_logo')) {
            $data['app_logo'] = $request->file('app_logo')->store('settings', 'public');
        } else {
            unset($data['app_logo']);
        }

        // Handle favicon upload
        if ($request->hasFile('app_favicon')) {
            $data['app_favicon'] = $request->file('app_favicon')->store('settings', 'public');
        } else {
            unset($data['app_favicon']);
        }

        $data['low_stock_notif']  = $request->boolean('low_stock_notif');
        $data['receipt_show_logo'] = $request->boolean('receipt_show_logo');

        $typeMap = [
            'low_stock_notif'       => 'boolean',
            'receipt_show_logo'     => 'boolean',
            'items_per_page'        => 'integer',
            'receipt_thermal_width' => 'integer',
            'barcode_label_width'   => 'integer',
            'barcode_label_height'  => 'integer',
            'barcode_cols_per_row'  => 'integer',
        ];

        $labelMap = [
            'app_name'              => 'Nama Aplikasi',
            'app_tagline'           => 'Tagline',
            'app_logo'              => 'Logo',
            'app_favicon'           => 'Favicon',
            'app_theme'             => 'Tema Warna',
            'app_currency'          => 'Mata Uang',
            'low_stock_notif'       => 'Notifikasi Stok Menipis',
            'items_per_page'        => 'Item Per Halaman',
            'receipt_thermal_width' => 'Lebar Struk Thermal (mm)',
            'receipt_pdf_paper'     => 'Ukuran Kertas Struk PDF',
            'barcode_label_width'   => 'Lebar Label Barcode (mm)',
            'barcode_label_height'  => 'Tinggi Label Barcode (mm)',
            'barcode_cols_per_row'  => 'Kolom Label Per Baris',
            'receipt_show_logo'     => 'Tampilkan Logo di Struk',
            'receipt_footer_text'   => 'Teks Footer Struk',
        ];

        foreach ($data as $key => $value) {
            $type  = $typeMap[$key] ?? 'string';
            $label = $labelMap[$key] ?? $key;
            $group = match (true) {
                str_starts_with($key, 'app_') => 'general',
                str_starts_with($key, 'receipt_') || str_starts_with($key, 'barcode_') => 'printer',
                default => 'preferences',
            };
            AppSetting::set($key, $value, $type, $group, $label);
        }

        ActivityLog::log('update', 'settings', 'Perbarui pengaturan aplikasi');

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
