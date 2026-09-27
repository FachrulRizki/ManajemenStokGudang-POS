<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Shift;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockReportExport;
use App\Exports\StockInExport;
use App\Exports\StockOutExport;

class ReportController extends Controller
{
    // ── Laporan Stok (multi-tab: stok, masuk, keluar, terlaris, per rak) ──
    public function stock(Request $request)
    {
        $tab = $request->input('tab', 'stok');
        $categories = Category::where('is_active', true)->get();
        $warehouses = \App\Models\Warehouse::with('racks')->where('is_active', true)->get();

        // ── Tab: Stok Barang ─────────────────────────────
        $query = Product::with(['category', 'unit', 'supplier', 'rack.warehouse']);
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        if ($request->filled('status')) {
            if ($request->status === 'low') $query->whereColumn('stock', '<=', 'min_stock');
            if ($request->status === 'out') $query->where('stock', '<=', 0);
        }
        if ($request->filled('search')) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%"));
        }
        $products   = $query->orderBy('name')->paginate(20, ['*'], 'page')->withQueryString();
        $totalValue = Product::sum(DB::raw('stock * purchase_price'));

        // ── Tab: Stok Masuk ──────────────────────────────
        $siQuery = StockIn::with(['product.category', 'supplier', 'user']);
        $this->applyDateFilters($siQuery, $request, 'transaction_date');
        if ($request->filled('search')) {
            $siQuery->where(fn($q) => $q->where('reference_number', 'like', "%{$request->search}%")
                ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$request->search}%")));
        }
        $stockIns     = $siQuery->latest('transaction_date')->paginate(20, ['*'], 'si_page')->withQueryString();
        $totalQtyIn   = StockIn::sum('quantity');
        $totalValueIn = StockIn::sum('total_price');

        // ── Tab: Stok Keluar ─────────────────────────────
        $soQuery = StockOut::with(['product.category', 'user', 'stockOutType']);
        $this->applyDateFilters($soQuery, $request, 'transaction_date');
        if ($request->filled('type')) $soQuery->where('type', $request->type);
        if ($request->filled('search')) {
            $soQuery->where(fn($q) => $q->where('reference_number', 'like', "%{$request->search}%")
                ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$request->search}%")));
        }
        $stockOuts     = $soQuery->latest('transaction_date')->paginate(20, ['*'], 'so_page')->withQueryString();
        $totalQtyOut   = StockOut::sum('quantity');
        $totalValueOut = StockOut::sum('total_price');

        // ── Tab: Produk Terlaris ─────────────────────────
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year', now()->year);

        $posData = TransactionItem::select('product_id',
                DB::raw('SUM(quantity) as pos_qty'),
                DB::raw('SUM(subtotal) as pos_revenue'))
            ->whereHas('transaction', fn($q) => $q->where('status', 'paid')
                ->whereMonth('transaction_at', $month)->whereYear('transaction_at', $year))
            ->groupBy('product_id')->get()->keyBy('product_id');

        $manualData = StockOut::select('product_id',
                DB::raw('SUM(quantity) as manual_qty'),
                DB::raw('SUM(total_price) as manual_revenue'))
            ->where('type', 'sale')
            ->whereMonth('transaction_date', $month)->whereYear('transaction_date', $year)
            ->groupBy('product_id')->get()->keyBy('product_id');

        $allIds = $posData->keys()->merge($manualData->keys())->unique();
        $topProductsList = Product::with(['category', 'unit'])->whereIn('id', $allIds)->get()->keyBy('id');

        $topProducts = $allIds->map(function ($id) use ($posData, $manualData, $topProductsList) {
            $pos    = $posData[$id] ?? null;
            $manual = $manualData[$id] ?? null;
            return (object) [
                'product'        => $topProductsList[$id] ?? null,
                'pos_qty'        => $pos->pos_qty ?? 0,
                'manual_qty'     => $manual->manual_qty ?? 0,
                'total_sold'     => ($pos->pos_qty ?? 0) + ($manual->manual_qty ?? 0),
                'total_revenue'  => ($pos->pos_revenue ?? 0) + ($manual->manual_revenue ?? 0),
            ];
        })->sortByDesc('total_sold')->values();

        $months = collect(range(1, 12))->map(fn($m) => ['value' => $m, 'label' => now()->setMonth($m)->translatedFormat('F')]);
        $years  = collect(range(now()->year - 2, now()->year))->map(fn($y) => ['value' => $y, 'label' => $y]);

        // ── Tab: Stok per Rak ────────────────────────────
        $rackQuery = Product::with(['rack.warehouse', 'category', 'unit'])->where('is_active', true);
        if ($request->filled('warehouse_id')) {
            $rackQuery->whereHas('rack', fn($r) => $r->where('warehouse_id', $request->warehouse_id));
        }
        if ($request->filled('rack_id')) $rackQuery->where('rack_id', $request->rack_id);
        $productsByRack = $rackQuery->orderBy('name')->paginate(25, ['*'], 'rack_page')->withQueryString();

        return view('reports.stock', compact(
            'tab', 'categories', 'warehouses',
            'products', 'totalValue',
            'stockIns', 'totalQtyIn', 'totalValueIn',
            'stockOuts', 'totalQtyOut', 'totalValueOut',
            'topProducts', 'month', 'year', 'months', 'years',
            'productsByRack',
        ));
    }

    public function stockPdf(Request $request)
    {
        $query = Product::with(['category', 'unit', 'supplier']);
        $this->applyStockFilters($query, $request);
        $products   = $query->orderBy('name')->get();
        $totalValue = $products->sum(fn($p) => $p->stock * $p->purchase_price);

        $pdf = Pdf::loadView('reports.pdf.stock', compact('products', 'totalValue'))
            ->setPaper('a4', 'landscape');

        ActivityLog::log('export', 'reports', 'Export laporan stok ke PDF');
        return $pdf->download('laporan-stok-' . date('Ymd') . '.pdf');
    }

    public function stockExcel(Request $request)
    {
        ActivityLog::log('export', 'reports', 'Export laporan stok ke Excel');
        return Excel::download(new StockReportExport($request->all()), 'laporan-stok-' . date('Ymd') . '.xlsx');
    }

    // ── Laporan Stok Masuk ───────────────────────────────
    public function stockIn(Request $request)
    {
        $query = StockIn::with(['product.category', 'supplier', 'user']);
        $this->applyDateFilters($query, $request, 'transaction_date');

        if ($request->filled('search')) {
            $query->where(fn($q) => $q->where('reference_number', 'like', "%{$request->search}%")
                ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$request->search}%")));
        }

        $stockIns   = $query->latest('transaction_date')->paginate(20)->withQueryString();
        $totalQty   = $query->sum('quantity');
        $totalValue = $query->sum('total_price');

        return view('reports.stock-in', compact('stockIns', 'totalQty', 'totalValue'));
    }

    public function stockInPdf(Request $request)
    {
        $query = StockIn::with(['product.category', 'supplier', 'user']);
        $this->applyDateFilters($query, $request, 'transaction_date');
        $stockIns   = $query->latest('transaction_date')->get();
        $totalQty   = $stockIns->sum('quantity');
        $totalValue = $stockIns->sum('total_price');

        $pdf = Pdf::loadView('reports.pdf.stock-in', compact('stockIns', 'totalQty', 'totalValue', 'request'))
            ->setPaper('a4', 'landscape');
        ActivityLog::log('export', 'reports', 'Export laporan stok masuk ke PDF');
        return $pdf->download('laporan-stok-masuk-' . date('Ymd') . '.pdf');
    }

    public function stockInExcel(Request $request)
    {
        ActivityLog::log('export', 'reports', 'Export laporan stok masuk ke Excel');
        return Excel::download(new StockInExport($request->all()), 'laporan-stok-masuk-' . date('Ymd') . '.xlsx');
    }

    // ── Laporan Stok Keluar (non-POS) ────────────────────
    public function stockOut(Request $request)
    {
        $query = StockOut::with(['product.category', 'user', 'stockOutType']);
        $this->applyDateFilters($query, $request, 'transaction_date');

        if ($request->filled('search')) {
            $query->where(fn($q) => $q->where('reference_number', 'like', "%{$request->search}%")
                ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$request->search}%"))
                ->orWhere('customer_name', 'like', "%{$request->search}%"));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $stockOuts  = $query->latest('transaction_date')->paginate(20)->withQueryString();
        $totalQty   = $query->sum('quantity');
        $totalValue = $query->sum('total_price');

        return view('reports.stock-out', compact('stockOuts', 'totalQty', 'totalValue'));
    }

    public function stockOutPdf(Request $request)
    {
        $query = StockOut::with(['product.category', 'user']);
        $this->applyDateFilters($query, $request, 'transaction_date');
        $stockOuts  = $query->latest('transaction_date')->get();
        $totalQty   = $stockOuts->sum('quantity');
        $totalValue = $stockOuts->sum('total_price');

        $pdf = Pdf::loadView('reports.pdf.stock-out', compact('stockOuts', 'totalQty', 'totalValue', 'request'))
            ->setPaper('a4', 'landscape');
        ActivityLog::log('export', 'reports', 'Export laporan stok keluar ke PDF');
        return $pdf->download('laporan-stok-keluar-' . date('Ymd') . '.pdf');
    }

    public function stockOutExcel(Request $request)
    {
        ActivityLog::log('export', 'reports', 'Export laporan stok keluar ke Excel');
        return Excel::download(new StockOutExport($request->all()), 'laporan-stok-keluar-' . date('Ymd') . '.xlsx');
    }

    // ── Laporan Produk Terlaris ──────────────────────────
    public function topProducts(Request $request)
    {
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year', now()->year);
        $limit = $request->integer('limit', 20);

        // Ambil dari POS
        $posData = TransactionItem::select('product_id',
                DB::raw('SUM(quantity) as pos_qty'),
                DB::raw('SUM(subtotal) as pos_revenue'))
            ->whereHas('transaction', fn($q) => $q->where('status', 'paid')
                ->whereMonth('transaction_at', $month)->whereYear('transaction_at', $year))
            ->groupBy('product_id')->get()->keyBy('product_id');

        // Ambil dari stok keluar manual (penjualan)
        $manualData = StockOut::select('product_id',
                DB::raw('SUM(quantity) as manual_qty'),
                DB::raw('SUM(total_price) as manual_revenue'))
            ->where('type', 'sale')
            ->whereMonth('transaction_date', $month)->whereYear('transaction_date', $year)
            ->groupBy('product_id')->get()->keyBy('product_id');

        $allIds = $posData->keys()->merge($manualData->keys())->unique();

        $products = Product::with(['category', 'unit'])->whereIn('id', $allIds)->get()->keyBy('id');

        $topProducts = $allIds->map(function ($id) use ($posData, $manualData, $products) {
            $pos    = $posData[$id] ?? null;
            $manual = $manualData[$id] ?? null;
            return (object) [
                'product'       => $products[$id] ?? null,
                'pos_qty'       => $pos->pos_qty ?? 0,
                'manual_qty'    => $manual->manual_qty ?? 0,
                'total_sold'    => ($pos->pos_qty ?? 0) + ($manual->manual_qty ?? 0),
                'pos_revenue'   => $pos->pos_revenue ?? 0,
                'manual_revenue'=> $manual->manual_revenue ?? 0,
                'total_revenue' => ($pos->pos_revenue ?? 0) + ($manual->manual_revenue ?? 0),
            ];
        })->sortByDesc('total_sold')->take($limit)->values();

        $months = collect(range(1, 12))->map(fn($m) => ['value' => $m, 'label' => now()->setMonth($m)->translatedFormat('F')]);
        $years  = collect(range(now()->year - 2, now()->year))->map(fn($y) => ['value' => $y, 'label' => $y]);

        return view('reports.top-products', compact('topProducts', 'month', 'year', 'months', 'years'));
    }

    // ── Laporan Per Kasir / Per Shift ────────────────────
    public function kasir(Request $request)
    {
        $query = Transaction::with(['user', 'paymentMethod', 'shift'])
            ->where('status', 'paid');

        $this->applyDateFilters($query, $request, 'transaction_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('payment_method_id')) {
            $query->where('payment_method_id', $request->payment_method_id);
        }

        // Ringkasan per kasir
        $summaryQuery = Transaction::select('user_id',
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('SUM(grand_total) as total_revenue'),
                DB::raw('SUM(discount_amount) as total_discount'))
            ->where('status', 'paid');

        if ($request->filled('date_from')) $summaryQuery->whereDate('transaction_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $summaryQuery->whereDate('transaction_at', '<=', $request->date_to);

        $kasirSummary = $summaryQuery->groupBy('user_id')->with('user')->get();

        $transactions   = $query->with(['user', 'paymentMethod', 'shift', 'items.product.unit'])->withCount('items')->latest('transaction_at')->paginate(20)->withQueryString();
        $kasirs         = User::where('is_active', true)->orderBy('name')->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('sort_order')->get();

        return view('reports.kasir', compact('transactions', 'kasirSummary', 'kasirs', 'paymentMethods'));
    }

    // ── Laporan Stok Per Rak ─────────────────────────────
    public function stockByRack(Request $request)
    {
        $products = Product::with(['rack.warehouse', 'category', 'unit'])
            ->where('is_active', true)
            ->when($request->filled('warehouse_id'), fn($q) => $q->whereHas('rack', fn($r) => $r->where('warehouse_id', $request->warehouse_id)))
            ->when($request->filled('rack_id'), fn($q) => $q->where('rack_id', $request->rack_id))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $warehouses = \App\Models\Warehouse::with('racks')->where('is_active', true)->get();

        return view('reports.stock-by-rack', compact('products', 'warehouses'));
    }

    // ── Private Helpers ──────────────────────────────────
    private function applyDateFilters($query, Request $request, string $column): void
    {
        if ($request->filled('date_from')) {
            $query->whereDate($column, '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate($column, '<=', $request->date_to);
        }
    }

    private function applyStockFilters($query, Request $request): void
    {
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        if ($request->filled('status')) {
            if ($request->status === 'low') $query->whereColumn('stock', '<=', 'min_stock');
            if ($request->status === 'out') $query->where('stock', '<=', 0);
        }
    }
}
