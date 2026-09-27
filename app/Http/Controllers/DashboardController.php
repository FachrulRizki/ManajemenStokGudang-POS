<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shift;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now   = now();
        $month = $now->month;
        $year  = $now->year;

        // ── Statistik Utama ──────────────────────────────
        $totalProducts    = Product::where('is_active', true)->count();
        $totalCategories  = Category::where('is_active', true)->count();
        $totalSuppliers   = Supplier::where('is_active', true)->count();
        $totalStockValue  = Product::sum(DB::raw('stock * purchase_price'));

        $stockInThisMonth  = StockIn::whereMonth('transaction_date', $month)->whereYear('transaction_date', $year)->sum('quantity');
        $stockOutThisMonth = StockOut::whereMonth('transaction_date', $month)->whereYear('transaction_date', $year)->sum('quantity');

        // ── Statistik POS Hari Ini ───────────────────────
        $todayRevenue = Transaction::where('status', 'paid')
            ->whereDate('transaction_at', today())
            ->sum('grand_total');

        $todayTransactions = Transaction::where('status', 'paid')
            ->whereDate('transaction_at', today())
            ->count();

        $monthRevenue = Transaction::where('status', 'paid')
            ->whereMonth('transaction_at', $month)
            ->whereYear('transaction_at', $year)
            ->sum('grand_total');

        $openShifts = Shift::where('status', 'open')->count();

        // ── Produk Terlaris Bulan Ini (gabung POS + stok keluar manual) ──
        // Via POS (transaction_items)
        $topByPos = TransactionItem::select('product_id', DB::raw('SUM(quantity) as pos_qty'), DB::raw('SUM(subtotal) as pos_revenue'))
            ->whereHas('transaction', fn($q) => $q->where('status', 'paid')
                ->whereMonth('transaction_at', $month)->whereYear('transaction_at', $year))
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        // Via stok keluar manual (type = sale)
        $topByManual = StockOut::select('product_id', DB::raw('SUM(quantity) as manual_qty'), DB::raw('SUM(total_price) as manual_revenue'))
            ->where('type', 'sale')
            ->whereMonth('transaction_date', $month)->whereYear('transaction_date', $year)
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        // Gabungkan
        $allProductIds = $topByPos->keys()->merge($topByManual->keys())->unique();
        $topProducts = $allProductIds->map(function ($id) use ($topByPos, $topByManual) {
            $posQty     = $topByPos[$id]->pos_qty ?? 0;
            $manualQty  = $topByManual[$id]->manual_qty ?? 0;
            $posRev     = $topByPos[$id]->pos_revenue ?? 0;
            $manualRev  = $topByManual[$id]->manual_revenue ?? 0;
            return [
                'product_id'   => $id,
                'total_sold'   => $posQty + $manualQty,
                'total_revenue'=> $posRev + $manualRev,
            ];
        })->sortByDesc('total_sold')->take(8)
          ->map(fn($item) => (object) array_merge($item, ['product' => Product::with('category', 'unit')->find($item['product_id'])]));

        // ── Stok Menipis ─────────────────────────────────
        $lowStockProducts = Product::with(['category', 'unit'])
            ->where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->limit(8)
            ->get();

        // ── Chart Data: 12 bulan ─────────────────────────
        $chartLabels   = [];
        $chartStockIn  = [];
        $chartStockOut = [];
        $chartRevenue  = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $chartLabels[] = $date->translatedFormat('M Y');

            $chartStockIn[] = StockIn::whereYear('transaction_date', $date->year)
                ->whereMonth('transaction_date', $date->month)->sum('quantity');

            $chartStockOut[] = StockOut::whereYear('transaction_date', $date->year)
                ->whereMonth('transaction_date', $date->month)->sum('quantity');

            $chartRevenue[] = (float) Transaction::where('status', 'paid')
                ->whereYear('transaction_at', $date->year)
                ->whereMonth('transaction_at', $date->month)->sum('grand_total');
        }

        // ── Transaksi Terbaru ────────────────────────────
        $recentStockIn = StockIn::with(['product', 'supplier', 'user'])->latest()->limit(5)->get();
        $recentTransactions = Transaction::with(['user', 'paymentMethod', 'items'])->where('status', 'paid')->latest('transaction_at')->limit(5)->get();

        // ── Distribusi Stok per Kategori ─────────────────
        $stockByCategory = Category::withCount('products')
            ->withSum('products', 'stock')
            ->having('products_count', '>', 0)->get();

        return view('dashboard.index', compact(
            'totalProducts', 'totalCategories', 'totalSuppliers', 'totalStockValue',
            'stockInThisMonth', 'stockOutThisMonth',
            'todayRevenue', 'todayTransactions', 'monthRevenue', 'openShifts',
            'topProducts',
            'lowStockProducts',
            'chartLabels', 'chartStockIn', 'chartStockOut', 'chartRevenue',
            'recentStockIn', 'recentTransactions',
            'stockByCategory',
        ));
    }
}
