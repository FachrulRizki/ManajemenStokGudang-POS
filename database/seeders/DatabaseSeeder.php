<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\Category;
use App\Models\Product;
use App\Models\Rack;
use App\Models\StockIn;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── App Settings ─────────────────────────────────
        $settings = [
            ['key' => 'app_name',        'value' => 'NestDev POS',                  'type' => 'string',  'group' => 'general',     'label' => 'Nama Aplikasi'],
            ['key' => 'app_tagline',     'value' => 'Sistem Manajemen Stok & Kasir', 'type' => 'string',  'group' => 'general',     'label' => 'Tagline'],
            ['key' => 'app_theme',       'value' => 'indigo',                        'type' => 'string',  'group' => 'general',     'label' => 'Tema'],
            ['key' => 'app_currency',    'value' => 'Rp',                            'type' => 'string',  'group' => 'general',     'label' => 'Mata Uang'],
            ['key' => 'items_per_page',  'value' => '15',                            'type' => 'integer', 'group' => 'preferences', 'label' => 'Item Per Halaman'],
            ['key' => 'low_stock_notif', 'value' => '1',                             'type' => 'boolean', 'group' => 'preferences', 'label' => 'Notifikasi Stok Menipis'],
        ];
        foreach ($settings as $s) {
            AppSetting::updateOrCreate(['key' => $s['key']], $s);
        }

        // ── Users ─────────────────────────────────────────
        $admin = User::updateOrCreate(['email' => 'admin@gudang.com'], [
            'name' => 'Administrator', 'username' => 'admin',
            'phone' => '081234567890', 'role' => 'admin',
            'is_active' => true, 'password' => Hash::make('password'),
        ]);
        User::updateOrCreate(['email' => 'manager@gudang.com'], [
            'name' => 'Budi Santoso', 'username' => 'budi',
            'phone' => '082345678901', 'role' => 'manager',
            'is_active' => true, 'password' => Hash::make('password'),
        ]);
        User::updateOrCreate(['email' => 'kasir@gudang.com'], [
            'name' => 'Siti Rahayu', 'username' => 'siti',
            'phone' => '083456789012', 'role' => 'staff',
            'is_active' => true, 'password' => Hash::make('password'),
        ]);

        // ── Satuan ────────────────────────────────────────
        $unitData = [
            ['name' => 'Pcs',      'symbol' => 'pcs',  'description' => 'Pieces / buah'],
            ['name' => 'Kilogram', 'symbol' => 'kg',   'description' => 'Kilogram'],
            ['name' => 'Liter',    'symbol' => 'ltr',  'description' => 'Liter'],
            ['name' => 'Box',      'symbol' => 'box',  'description' => 'Kotak / kardus'],
            ['name' => 'Pak',      'symbol' => 'pak',  'description' => 'Paket'],
        ];
        $units = [];
        foreach ($unitData as $u) {
            $units[$u['symbol']] = Unit::updateOrCreate(['symbol' => $u['symbol']], $u + ['is_active' => true]);
        }

        // ── Kategori ──────────────────────────────────────
        $catData = [
            ['name' => 'Elektronik',         'code' => 'ELKT'],
            ['name' => 'Alat Tulis Kantor',  'code' => 'ATK'],
            ['name' => 'Kebersihan',         'code' => 'KBR'],
            ['name' => 'Makanan & Minuman',  'code' => 'MAMIN'],
            ['name' => 'Peralatan',          'code' => 'PRLTN'],
        ];
        $cats = [];
        foreach ($catData as $c) {
            $cats[$c['code']] = Category::updateOrCreate(['code' => $c['code']], $c + ['is_active' => true]);
        }

        // ── Supplier ──────────────────────────────────────
        $supData = [
            ['name' => 'PT Maju Jaya',     'code' => 'SUP001', 'contact_person' => 'Ahmad Fauzi',   'phone' => '021-1234567', 'city' => 'Jakarta',  'address' => 'Jl. Gatot Subroto No. 10'],
            ['name' => 'CV Berkah Abadi',  'code' => 'SUP002', 'contact_person' => 'Desi Kartini',  'phone' => '022-2345678', 'city' => 'Bandung',  'address' => 'Jl. Sudirman No. 25'],
            ['name' => 'UD Sumber Rejeki', 'code' => 'SUP003', 'contact_person' => 'Hendra Wijaya', 'phone' => '031-3456789', 'city' => 'Surabaya', 'address' => 'Jl. Darmo No. 5'],
        ];
        $sups = [];
        foreach ($supData as $s) {
            $sups[$s['code']] = Supplier::updateOrCreate(['code' => $s['code']], $s + ['is_active' => true]);
        }

        // ── Gudang & Rak ──────────────────────────────────
        // Dibuat via PosSetupSeeder, ambil setelah run
        $this->call(PosSetupSeeder::class);

        $warehouse = Warehouse::where('code', 'GDG-01')->first();
        $racks     = Rack::with('warehouse')->where('warehouse_id', $warehouse->id)->get()->keyBy('code');

        // ── 10 Produk — katalog murni (tanpa harga & stok) ──
        // Skema: Produk = katalog. Harga & stok diisi via Stok Masuk.
        $productData = [
            ['code' => 'PRD001', 'barcode' => '8991234000010', 'name' => 'Laptop ASUS VivoBook 14',         'cat' => 'ELKT',  'unit' => 'pcs', 'rack' => 'A', 'description' => 'Laptop Intel Core i5, RAM 8GB, SSD 512GB'],
            ['code' => 'PRD002', 'barcode' => '8991234000020', 'name' => 'Mouse Wireless Logitech M235',    'cat' => 'ELKT',  'unit' => 'pcs', 'rack' => 'A', 'description' => 'Mouse wireless 2.4GHz, nano receiver'],
            ['code' => 'PRD003', 'barcode' => '8991234000030', 'name' => 'Keyboard Mechanical Redragon',    'cat' => 'ELKT',  'unit' => 'pcs', 'rack' => 'A', 'description' => 'Keyboard gaming mechanical switch blue RGB'],
            ['code' => 'PRD004', 'barcode' => '8991234000040', 'name' => 'Kertas HVS A4 80gr',             'cat' => 'ATK',   'unit' => 'pak', 'rack' => 'B', 'description' => '1 Rim = 500 lembar'],
            ['code' => 'PRD005', 'barcode' => '8991234000050', 'name' => 'Pulpen Ballpoint Pilot',         'cat' => 'ATK',   'unit' => 'box', 'rack' => 'B', 'description' => 'Isi 10 pcs per box, tinta hitam'],
            ['code' => 'PRD006', 'barcode' => '8991234000060', 'name' => 'Sabun Lantai Wipol 800ml',       'cat' => 'KBR',   'unit' => 'pcs', 'rack' => 'C', 'description' => 'Pembersih lantai antiseptik lavender'],
            ['code' => 'PRD007', 'barcode' => '8991234000070', 'name' => 'Tisu Multi Guna Paseo 250s',     'cat' => 'KBR',   'unit' => 'pak', 'rack' => 'C', 'description' => '250 lembar 2-ply'],
            ['code' => 'PRD008', 'barcode' => '8991234000080', 'name' => 'Mie Instant Indomie Goreng',     'cat' => 'MAMIN', 'unit' => 'pak', 'rack' => 'C', 'description' => 'Mie goreng rasa original 85gr'],
            ['code' => 'PRD009', 'barcode' => '8991234000090', 'name' => 'Air Mineral Aqua 600ml',         'cat' => 'MAMIN', 'unit' => 'pcs', 'rack' => 'C', 'description' => 'Air mineral dalam botol 600ml'],
            ['code' => 'PRD010', 'barcode' => '8991234000100', 'name' => 'Obeng Set 12 Pcs',               'cat' => 'PRLTN', 'unit' => 'pcs', 'rack' => 'C', 'description' => 'Set obeng plus minus, handle karet anti-slip'],
        ];

        $products = [];
        foreach ($productData as $pd) {
            $rack = $racks->get($pd['rack']);
            $products[$pd['code']] = Product::updateOrCreate(['code' => $pd['code']], [
                'name'           => $pd['name'],
                'barcode'        => $pd['barcode'],
                'category_id'    => $cats[$pd['cat']]->id,
                'unit_id'        => $units[$pd['unit']]->id,
                'rack_id'        => $rack?->id,
                'rack_location'  => $rack ? $rack->name : null,
                'description'    => $pd['description'],
                // Harga awal 0 — akan diupdate saat stok masuk pertama
                'purchase_price' => 0,
                'selling_price'  => 0,
                'stock'          => 0,
                'min_stock'      => 5,
                'is_active'      => true,
            ]);
        }

        // ── Stok Masuk — simulasi penerimaan barang ───────
        // Ini yang mengisi stok dan sekaligus update harga di produk
        // Setiap produk masuk 2 kali (batch berbeda bulan)
        $stockInBatches = [
            'PRD001' => [
                ['sup' => 'SUP001', 'qty' => 10, 'purchase' => 6_500_000, 'selling' => 7_800_000, 'min' => 3,  'months_ago' => 2],
                ['sup' => 'SUP001', 'qty' => 5,  'purchase' => 6_700_000, 'selling' => 8_000_000, 'min' => 3,  'months_ago' => 0],
            ],
            'PRD002' => [
                ['sup' => 'SUP001', 'qty' => 25, 'purchase' => 180_000,  'selling' => 250_000,  'min' => 8,  'months_ago' => 2],
                ['sup' => 'SUP001', 'qty' => 15, 'purchase' => 185_000,  'selling' => 260_000,  'min' => 8,  'months_ago' => 0],
            ],
            'PRD003' => [
                ['sup' => 'SUP001', 'qty' => 20, 'purchase' => 320_000,  'selling' => 450_000,  'min' => 5,  'months_ago' => 1],
            ],
            'PRD004' => [
                ['sup' => 'SUP002', 'qty' => 50, 'purchase' => 42_000,   'selling' => 55_000,   'min' => 15, 'months_ago' => 2],
                ['sup' => 'SUP002', 'qty' => 30, 'purchase' => 43_000,   'selling' => 57_000,   'min' => 15, 'months_ago' => 0],
            ],
            'PRD005' => [
                ['sup' => 'SUP002', 'qty' => 40, 'purchase' => 18_000,   'selling' => 25_000,   'min' => 10, 'months_ago' => 2],
                ['sup' => 'SUP002', 'qty' => 20, 'purchase' => 18_500,   'selling' => 26_000,   'min' => 10, 'months_ago' => 1],
            ],
            'PRD006' => [
                ['sup' => 'SUP002', 'qty' => 30, 'purchase' => 12_000,   'selling' => 18_000,   'min' => 12, 'months_ago' => 2],
                ['sup' => 'SUP002', 'qty' => 20, 'purchase' => 12_500,   'selling' => 18_500,   'min' => 12, 'months_ago' => 0],
            ],
            'PRD007' => [
                ['sup' => 'SUP002', 'qty' => 60, 'purchase' => 8_500,    'selling' => 13_000,   'min' => 20, 'months_ago' => 2],
                ['sup' => 'SUP002', 'qty' => 40, 'purchase' => 8_800,    'selling' => 13_500,   'min' => 20, 'months_ago' => 0],
            ],
            'PRD008' => [
                ['sup' => 'SUP003', 'qty' => 150, 'purchase' => 2_800,   'selling' => 3_500,    'min' => 50, 'months_ago' => 2],
                ['sup' => 'SUP003', 'qty' => 150, 'purchase' => 2_900,   'selling' => 3_600,    'min' => 50, 'months_ago' => 0],
            ],
            'PRD009' => [
                ['sup' => 'SUP003', 'qty' => 100, 'purchase' => 2_500,   'selling' => 4_000,    'min' => 30, 'months_ago' => 2],
                ['sup' => 'SUP003', 'qty' => 100, 'purchase' => 2_600,   'selling' => 4_000,    'min' => 30, 'months_ago' => 0],
            ],
            'PRD010' => [
                ['sup' => 'SUP001', 'qty' => 15, 'purchase' => 45_000,   'selling' => 65_000,   'min' => 5,  'months_ago' => 2],
                ['sup' => 'SUP001', 'qty' => 10, 'purchase' => 46_000,   'selling' => 67_000,   'min' => 5,  'months_ago' => 0],
            ],
        ];

        $now = Carbon::now();
        foreach ($stockInBatches as $code => $batches) {
            $product = $products[$code];
            foreach ($batches as $batch) {
                $date = $now->copy()->subMonths($batch['months_ago'])->startOfMonth()->addDays(rand(2, 15));
                $qty  = $batch['qty'];

                StockIn::create([
                    'reference_number' => 'SI-' . $date->format('Ymd') . '-' . str_pad(StockIn::count() + 1, 4, '0', STR_PAD_LEFT),
                    'product_id'       => $product->id,
                    'supplier_id'      => $sups[$batch['sup']]->id,
                    'user_id'          => $admin->id,
                    'quantity'         => $qty,
                    'purchase_price'   => $batch['purchase'],
                    'total_price'      => $qty * $batch['purchase'],
                    'transaction_date' => $date,
                    'notes'            => 'Stok masuk batch — ' . $date->format('M Y'),
                ]);

                // Sync ke produk: stok bertambah, harga diperbarui
                $product->increment('stock', $qty);
                $product->update([
                    'purchase_price' => $batch['purchase'],
                    'selling_price'  => $batch['selling'],
                    'min_stock'      => $batch['min'],
                    'supplier_id'    => $sups[$batch['sup']]->id,
                ]);
            }
        }

        // ── Activity Log ──────────────────────────────────
        \App\Models\ActivityLog::create([
            'user_id'     => $admin->id,
            'action'      => 'login',
            'module'      => 'auth',
            'description' => 'User Administrator berhasil login',
            'ip_address'  => '127.0.0.1',
            'user_agent'  => 'Mozilla/5.0 (Seeder)',
        ]);

        // ── Permissions ────────────────────────────────────
        $this->call(PermissionSeeder::class);

        // ── Output ────────────────────────────────────────
        $this->command->info('');
        $this->command->info('Seeder selesai!');
        $this->command->info('');
        $this->command->info('Skema data:');
        $this->command->info('  - 10 produk di katalog');
        $this->command->info('  - Setiap produk sudah punya riwayat stok masuk (harga update otomatis)');
        $this->command->info('  - Stok siap untuk Kasir POS');
        $this->command->info('');
        $this->command->info('Login:');
        $this->command->info('  Admin   : admin@gudang.com   / password');
        $this->command->info('  Manager : manager@gudang.com / password');
        $this->command->info('  Kasir   : kasir@gudang.com   / password');
    }
}
