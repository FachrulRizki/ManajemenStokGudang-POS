<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $printerSettings = [
            ['key' => 'receipt_thermal_width', 'value' => '80',  'type' => 'integer', 'group' => 'printer', 'label' => 'Lebar Struk Thermal (mm)'],
            ['key' => 'receipt_pdf_paper',     'value' => 'A5',  'type' => 'string',  'group' => 'printer', 'label' => 'Ukuran Kertas Struk PDF'],
            ['key' => 'barcode_label_width',   'value' => '50',  'type' => 'integer', 'group' => 'printer', 'label' => 'Lebar Label Barcode (mm)'],
            ['key' => 'barcode_label_height',  'value' => '30',  'type' => 'integer', 'group' => 'printer', 'label' => 'Tinggi Label Barcode (mm)'],
            ['key' => 'barcode_cols_per_row',  'value' => '3',   'type' => 'integer', 'group' => 'printer', 'label' => 'Kolom Label Per Baris'],
            ['key' => 'receipt_show_logo',     'value' => '1',   'type' => 'boolean', 'group' => 'printer', 'label' => 'Tampilkan Logo di Struk'],
            ['key' => 'receipt_footer_text',   'value' => 'Terima kasih atas kepercayaan Anda!', 'type' => 'string', 'group' => 'printer', 'label' => 'Teks Footer Struk'],
        ];

        foreach ($printerSettings as $setting) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    public function down(): void
    {
        DB::table('app_settings')->whereIn('key', [
            'receipt_thermal_width',
            'receipt_pdf_paper',
            'barcode_label_width',
            'barcode_label_height',
            'barcode_cols_per_row',
            'receipt_show_logo',
            'receipt_footer_text',
        ])->delete();
    }
};
