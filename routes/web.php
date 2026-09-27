<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\StockOutTypeController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Redirect root
Route::get('/', fn() => redirect()->route('dashboard'));

// Authenticated routes
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Master Data
    // Produk (multi-tab: produk, kategori, satuan, supplier, gudang&rak, tipe keluar)
    Route::resource('products', ProductController::class);
    // Sub-resources yang masih perlu modal POST dari halaman produk
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('units', UnitController::class)->except(['show']);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('warehouses', WarehouseController::class)->except(['show', 'create', 'edit']);
    Route::post('/racks', [WarehouseController::class, 'storeRack'])->name('racks.store');
    Route::put('/racks/{rack}', [WarehouseController::class, 'updateRack'])->name('racks.update');
    Route::delete('/racks/{rack}', [WarehouseController::class, 'destroyRack'])->name('racks.destroy');
    Route::resource('stock-out-types', StockOutTypeController::class)->except(['show', 'create', 'edit']);

    // Stok (multi-tab: masuk + keluar non-penjualan)
    Route::get('/stok', [StokController::class, 'index'])->name('stok.index');
    Route::post('/stok/masuk', [StokController::class, 'storeMasuk'])->name('stok.masuk.store');
    Route::delete('/stok/masuk/{stockIn}', [StokController::class, 'destroyMasuk'])->name('stok.masuk.destroy');
    Route::post('/stok/keluar', [StokController::class, 'storeKeluar'])->name('stok.keluar.store');
    Route::delete('/stok/keluar/{stockOut}', [StokController::class, 'destroyKeluar'])->name('stok.keluar.destroy');

    // Metode Pembayaran
    Route::resource('payment-methods', PaymentMethodController::class)->except(['show', 'create', 'edit']);

    // POS Kasir
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/kasir',               [PosController::class, 'kasir'])->name('kasir');
        Route::get('/search-product',      [PosController::class, 'searchProduct'])->name('search-product');
        Route::post('/transaksi',          [PosController::class, 'store'])->name('store');
        Route::post('/void/{transaction}', [PosController::class, 'void'])->name('void');
        Route::get('/riwayat',             [PosController::class, 'history'])->name('history');
        Route::get('/struk/{transaction}', [PosController::class, 'receipt'])->name('receipt');
        Route::get('/thermal/{transaction}', [PosController::class, 'thermal'])->name('thermal');
    });

    // Shift
    Route::prefix('shifts')->name('shifts.')->group(function () {
        Route::get('/',                [ShiftController::class, 'index'])->name('index');
        Route::post('/open',           [ShiftController::class, 'open'])->name('open');
        Route::post('/close/{shift}',  [ShiftController::class, 'close'])->name('close');
        Route::get('/{shift}',         [ShiftController::class, 'show'])->name('show');
    });

    // Stok Masuk
    Route::resource('stock-in', StockInController::class)->except(['edit', 'update']);
    Route::get('/stock-in-scan', [StockInController::class, 'scan'])->name('stock-in.scan');
    Route::get('/api/product-by-barcode', [StockInController::class, 'findByBarcode'])->name('api.product-by-barcode');
    Route::resource('stock-out', StockOutController::class)->except(['edit', 'update']);

    // Laporan
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/stock',         [ReportController::class, 'stock'])->name('stock');
        Route::get('/stock/pdf',     [ReportController::class, 'stockPdf'])->name('stock.pdf');
        Route::get('/stock/excel',   [ReportController::class, 'stockExcel'])->name('stock.excel');

        Route::get('/stock-in',        [ReportController::class, 'stockIn'])->name('stock-in');
        Route::get('/stock-in/pdf',    [ReportController::class, 'stockInPdf'])->name('stock-in.pdf');
        Route::get('/stock-in/excel',  [ReportController::class, 'stockInExcel'])->name('stock-in.excel');

        Route::get('/stock-out',       [ReportController::class, 'stockOut'])->name('stock-out');
        Route::get('/stock-out/pdf',   [ReportController::class, 'stockOutPdf'])->name('stock-out.pdf');
        Route::get('/stock-out/excel', [ReportController::class, 'stockOutExcel'])->name('stock-out.excel');

        // Laporan POS
        Route::get('/produk-terlaris', [ReportController::class, 'topProducts'])->name('top-products');
        Route::get('/per-kasir',       [ReportController::class, 'kasir'])->name('kasir');
        Route::get('/stok-per-rak',    [ReportController::class, 'stockByRack'])->name('stock-by-rack');
    });

    // Profil
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');

    // User Management (admin & manager only)
    Route::middleware('role:admin,manager')->group(function () {
        Route::resource('users', UserController::class);
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::put('/users/{user}/permissions', [UserController::class, 'updatePermissions'])->name('users.permissions');
    });

    // Activity Logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/activity-logs/{activityLog}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
    Route::post('/activity-logs/clear', [ActivityLogController::class, 'clearOld'])->name('activity-logs.clear')->middleware('role:admin');

    // Settings (admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
