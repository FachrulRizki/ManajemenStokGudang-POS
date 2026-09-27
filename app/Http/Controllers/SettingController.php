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
            'app_name'       => ['required', 'string', 'max:100'],
            'app_tagline'    => ['nullable', 'string', 'max:200'],
            'app_logo'       => ['nullable', 'image', 'max:2048'],
            'app_favicon'    => ['nullable', 'image', 'max:512'],
            'app_theme'      => ['required', 'in:blue,indigo,green,red,orange,purple'],
            'app_currency'   => ['required', 'string', 'max:10'],
            'low_stock_notif'=> ['boolean'],
            'items_per_page' => ['required', 'integer', 'min:5', 'max:100'],
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

        $data['low_stock_notif'] = $request->boolean('low_stock_notif');

        foreach ($data as $key => $value) {
            $type = match ($key) {
                'low_stock_notif' => 'boolean',
                'items_per_page'  => 'integer',
                default           => 'string',
            };

            $label = match ($key) {
                'app_name'        => 'Nama Aplikasi',
                'app_tagline'     => 'Tagline',
                'app_logo'        => 'Logo',
                'app_favicon'     => 'Favicon',
                'app_theme'       => 'Tema Warna',
                'app_currency'    => 'Mata Uang',
                'low_stock_notif' => 'Notifikasi Stok Menipis',
                'items_per_page'  => 'Item Per Halaman',
                default           => $key,
            };

            $group = match (true) {
                str_starts_with($key, 'app_') => 'general',
                default                        => 'preferences',
            };

            AppSetting::set($key, $value, $type, $group, $label);
        }

        ActivityLog::log('update', 'settings', 'Perbarui pengaturan aplikasi');

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
