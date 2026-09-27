<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['name', 'label', 'group', 'description', 'sort_order'];

    // ── Semua permission yang ada di sistem ───────────
    public static function allPermissions(): array
    {
        return [
            // Dashboard
            ['name' => 'dashboard.view',         'label' => 'Lihat Dashboard',         'group' => 'dashboard'],

            // Master Data - Produk
            ['name' => 'products.view',           'label' => 'Lihat Produk',            'group' => 'master_data'],
            ['name' => 'products.create',         'label' => 'Tambah Produk',           'group' => 'master_data'],
            ['name' => 'products.edit',           'label' => 'Edit Produk',             'group' => 'master_data'],
            ['name' => 'products.delete',         'label' => 'Hapus Produk',            'group' => 'master_data'],
            ['name' => 'categories.manage',       'label' => 'Kelola Kategori',         'group' => 'master_data'],
            ['name' => 'units.manage',            'label' => 'Kelola Satuan',           'group' => 'master_data'],
            ['name' => 'suppliers.manage',        'label' => 'Kelola Supplier',         'group' => 'master_data'],
            ['name' => 'warehouses.manage',       'label' => 'Kelola Gudang & Rak',     'group' => 'master_data'],
            ['name' => 'stock_out_types.manage',  'label' => 'Kelola Tipe Keluar',      'group' => 'master_data'],

            // Stok
            ['name' => 'stok.view',               'label' => 'Lihat Stok',              'group' => 'stok'],
            ['name' => 'stok.masuk',              'label' => 'Catat Stok Masuk',        'group' => 'stok'],
            ['name' => 'stok.keluar',             'label' => 'Catat Stok Keluar',       'group' => 'stok'],
            ['name' => 'stok.delete',             'label' => 'Hapus Catatan Stok',      'group' => 'stok'],

            // POS / Kasir
            ['name' => 'pos.access',              'label' => 'Akses Kasir POS',         'group' => 'pos'],
            ['name' => 'pos.history',             'label' => 'Lihat Riwayat Transaksi', 'group' => 'pos'],
            ['name' => 'pos.void',                'label' => 'Void Transaksi',          'group' => 'pos'],
            ['name' => 'shifts.manage',           'label' => 'Kelola Shift Kasir',      'group' => 'pos'],
            ['name' => 'payment_methods.manage',  'label' => 'Kelola Metode Bayar',     'group' => 'pos'],

            // Laporan
            ['name' => 'reports.view',            'label' => 'Lihat Laporan',           'group' => 'laporan'],
            ['name' => 'reports.export',          'label' => 'Export Laporan',          'group' => 'laporan'],

            // Sistem
            ['name' => 'users.view',              'label' => 'Lihat Daftar User',       'group' => 'sistem'],
            ['name' => 'users.create',            'label' => 'Tambah User',             'group' => 'sistem'],
            ['name' => 'users.edit',              'label' => 'Edit User',               'group' => 'sistem'],
            ['name' => 'users.delete',            'label' => 'Hapus User',              'group' => 'sistem'],
            ['name' => 'users.permissions',       'label' => 'Atur Permission User',    'group' => 'sistem'],
            ['name' => 'activity_logs.view',      'label' => 'Lihat Log Aktivitas',     'group' => 'sistem'],
            ['name' => 'settings.manage',         'label' => 'Kelola Pengaturan',       'group' => 'sistem'],
        ];
    }

    // Default permission per role
    public static function defaultForRole(string $role): array
    {
        return match($role) {
            'admin' => array_column(static::allPermissions(), 'name'), // semua

            'manager' => [
                'dashboard.view',
                'products.view', 'products.create', 'products.edit',
                'categories.manage', 'units.manage', 'suppliers.manage',
                'warehouses.manage', 'stock_out_types.manage',
                'stok.view', 'stok.masuk', 'stok.keluar', 'stok.delete',
                'pos.access', 'pos.history', 'pos.void', 'shifts.manage',
                'payment_methods.manage',
                'reports.view', 'reports.export',
                'users.view', 'users.create', 'users.edit',
                'activity_logs.view',
            ],

            'staff' => [
                'dashboard.view',
                'products.view',
                'stok.view', 'stok.masuk',
                'pos.access', 'pos.history', 'shifts.manage',
                'reports.view',
                'activity_logs.view',
            ],

            default => ['dashboard.view'],
        };
    }

    // ── Relations ──────────────────────────────────────
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_permissions')
            ->withPivot('granted');
    }
}
