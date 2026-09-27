<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;
use App\Models\StockOutType;
use App\Models\Warehouse;
use App\Models\Rack;

class PosSetupSeeder extends Seeder
{
    public function run(): void
    {
        // ── Metode Pembayaran ──────────────────────────────
        $methods = [
            ['code' => 'CASH',     'name' => 'Tunai',          'type' => 'cash',    'icon' => 'fas fa-money-bill-wave', 'requires_reference' => false, 'sort_order' => 1],
            ['code' => 'QRIS',     'name' => 'QRIS',           'type' => 'digital', 'icon' => 'fas fa-qrcode',          'requires_reference' => true,  'sort_order' => 2],
            ['code' => 'TRANSFER', 'name' => 'Transfer Bank',  'type' => 'digital', 'icon' => 'fas fa-university',      'requires_reference' => true,  'sort_order' => 3],
            ['code' => 'EDC',      'name' => 'Kartu Debit/Kredit', 'type' => 'card', 'icon' => 'fas fa-credit-card',    'requires_reference' => true,  'sort_order' => 4],
        ];

        foreach ($methods as $m) {
            PaymentMethod::firstOrCreate(['code' => $m['code']], $m);
        }

        // ── Tipe Stok Keluar (non-penjualan) ───────────────
        $types = [
            ['code' => 'DAMAGED',    'name' => 'Barang Rusak',      'color' => 'danger',    'icon' => 'fas fa-times-circle',       'affects_stock' => true, 'sort_order' => 1],
            ['code' => 'RETURN',     'name' => 'Retur ke Supplier', 'color' => 'warning',   'icon' => 'fas fa-undo',               'affects_stock' => true, 'sort_order' => 2],
            ['code' => 'EXPIRED',    'name' => 'Barang Kadaluarsa', 'color' => 'danger',    'icon' => 'fas fa-calendar-times',     'affects_stock' => true, 'sort_order' => 3],
            ['code' => 'ADJUSTMENT', 'name' => 'Penyesuaian Stok',  'color' => 'info',      'icon' => 'fas fa-sliders-h',          'affects_stock' => true, 'sort_order' => 4],
            ['code' => 'PROMO',      'name' => 'Sampel / Promo',    'color' => 'secondary', 'icon' => 'fas fa-gift',               'affects_stock' => true, 'sort_order' => 5],
            ['code' => 'OTHER',      'name' => 'Lainnya',           'color' => 'secondary', 'icon' => 'fas fa-ellipsis-h',         'affects_stock' => true, 'sort_order' => 6],
        ];

        foreach ($types as $t) {
            StockOutType::firstOrCreate(['code' => $t['code']], $t);
        }

        // ── Gudang & Rak Default ────────────────────────────
        $warehouse = Warehouse::firstOrCreate(
            ['code' => 'GDG-01'],
            [
                'name'     => 'Gudang Utama',
                'location' => 'Lantai 1',
                'is_active' => true,
            ]
        );

        $racks = [
            ['code' => 'A', 'name' => 'Rak A', 'row' => '1'],
            ['code' => 'B', 'name' => 'Rak B', 'row' => '1'],
            ['code' => 'C', 'name' => 'Rak C', 'row' => '2'],
        ];

        foreach ($racks as $r) {
            Rack::firstOrCreate(
                ['warehouse_id' => $warehouse->id, 'code' => $r['code']],
                array_merge($r, ['warehouse_id' => $warehouse->id, 'is_active' => true])
            );
        }
    }
}
