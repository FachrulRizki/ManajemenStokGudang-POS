<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\Rack;
use App\Models\StockOutType;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'produk');

        // ── Data produk ──────────────────────────────────
        $pQuery = Product::with(['category', 'unit', 'supplier']);
        if ($request->filled('search')) {
            $pQuery->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('barcode', 'like', "%{$request->search}%");
            });
        }
        if ($request->filled('category_id')) $pQuery->where('category_id', $request->category_id);
        if ($request->filled('status')) {
            if ($request->status === 'low')      $pQuery->whereColumn('stock', '<=', 'min_stock');
            elseif ($request->status === 'out')  $pQuery->where('stock', '<=', 0);
            elseif ($request->status === 'active') $pQuery->where('is_active', true);
            elseif ($request->status === 'inactive') $pQuery->where('is_active', false);
        }
        $products   = $pQuery->latest()->paginate(12, ['*'], 'p_page')->withQueryString();
        $categories_list = Category::where('is_active', true)->get(); // for filter dropdown

        // ── Data kategori ────────────────────────────────
        $catQuery = Category::withCount('products');
        if ($request->filled('search') && $tab === 'kategori') {
            $catQuery->where(fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%"));
        }
        if ($request->filled('status') && $tab === 'kategori') {
            $catQuery->where('is_active', $request->status === 'active');
        }
        $categories = $catQuery->latest()->paginate(10, ['*'], 'cat_page')->withQueryString();

        // ── Data satuan ──────────────────────────────────
        $unitQuery = Unit::withCount('products');
        if ($request->filled('search') && $tab === 'satuan') {
            $unitQuery->where(fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('symbol', 'like', "%{$request->search}%"));
        }
        $units = $unitQuery->latest()->paginate(10, ['*'], 'unit_page')->withQueryString();

        // ── Data supplier ────────────────────────────────
        $supQuery = Supplier::withCount('products');
        if ($request->filled('search') && $tab === 'supplier') {
            $supQuery->where(fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%"));
        }
        $suppliers = $supQuery->latest()->paginate(10, ['*'], 'sup_page')->withQueryString();

        // ── Data gudang & rak ────────────────────────────
        $warehouses  = \App\Models\Warehouse::withCount('racks')->latest()->paginate(10, ['*'], 'wh_page')->withQueryString();
        $racks       = \App\Models\Rack::with('warehouse')->latest()->get();

        // ── Data tipe keluar ─────────────────────────────
        $outTypes    = \App\Models\StockOutType::withCount('stockOuts')->orderBy('sort_order')->paginate(10, ['*'], 'ot_page')->withQueryString();

        return view('master.products.index', compact(
            'tab',
            'products', 'categories_list',
            'categories',
            'units',
            'suppliers',
            'warehouses', 'racks',
            'outTypes',
        ));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        $units      = Unit::where('is_active', true)->get();
        $suppliers  = Supplier::where('is_active', true)->get();

        return view('master.products.form', compact('categories', 'units', 'suppliers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'           => ['required', 'string', 'max:50', 'unique:products,code'],
            'name'           => ['required', 'string', 'max:200'],
            'barcode'        => ['nullable', 'string', 'max:100', 'unique:products,barcode'],
            'category_id'    => ['required', 'exists:categories,id'],
            'unit_id'        => ['required', 'exists:units,id'],
            'supplier_id'    => ['nullable', 'exists:suppliers,id'],
            'description'    => ['nullable', 'string'],
            'image'          => ['nullable', 'image', 'max:2048'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price'  => ['required', 'numeric', 'min:0'],
            'stock'          => ['nullable', 'integer', 'min:0'],
            'min_stock'      => ['required', 'integer', 'min:0'],
            'rack_location'  => ['nullable', 'string', 'max:50'],
            'is_active'      => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $data['stock']     = $data['stock'] ?? 0;
        $data['is_active'] = $request->boolean('is_active', true);
        $product = Product::create($data);

        ActivityLog::log('create', 'products', "Tambah produk: {$product->name} (Kode: {$product->code})", $product, [], $data);

        // JSON response untuk request dari barcode scanner
        if ($request->expectsJson()) {
            return response()->json([
                'id'      => $product->id,
                'name'    => $product->name,
                'code'    => $product->code,
                'success' => true,
            ], 201);
        }

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'unit', 'supplier']);

        $stockIns  = $product->stockIns()->with('user', 'supplier')->latest()->limit(10)->get();
        $stockOuts = $product->stockOuts()->with('user')->latest()->limit(10)->get();

        $monthlyIn  = $product->stockIns()->whereMonth('transaction_date', now()->month)->sum('quantity');
        $monthlyOut = $product->stockOuts()->whereMonth('transaction_date', now()->month)->sum('quantity');

        return view('master.products.show', compact('product', 'stockIns', 'stockOuts', 'monthlyIn', 'monthlyOut'));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->get();
        $units      = Unit::where('is_active', true)->get();
        $suppliers  = Supplier::where('is_active', true)->get();

        return view('master.products.form', compact('product', 'categories', 'units', 'suppliers'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'code'           => ['required', 'string', 'max:50', 'unique:products,code,' . $product->id],
            'name'           => ['required', 'string', 'max:200'],
            'barcode'        => ['nullable', 'string', 'max:100', 'unique:products,barcode,' . $product->id],
            'category_id'    => ['required', 'exists:categories,id'],
            'unit_id'        => ['required', 'exists:units,id'],
            'supplier_id'    => ['nullable', 'exists:suppliers,id'],
            'description'    => ['nullable', 'string'],
            'image'          => ['nullable', 'image', 'max:2048'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price'  => ['required', 'numeric', 'min:0'],
            'min_stock'      => ['required', 'integer', 'min:0'],
            'rack_location'  => ['nullable', 'string', 'max:50'],
            'is_active'      => ['boolean'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $old = $product->toArray();
        $product->update($data);

        ActivityLog::log('update', 'products', "Ubah produk: {$product->name}", $product, $old, $data);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        // Cek apakah produk masih punya transaksi POS yang tidak di-void
        $hasPosTransactions = $product->transactionItems()
            ->whereHas('transaction', fn($q) => $q->where('status', 'paid'))
            ->exists();

        if ($hasPosTransactions) {
            return back()->with('error', "Produk \"{$product->name}\" tidak bisa dihapus karena sudah pernah digunakan dalam transaksi kasir.");
        }

        // Hapus semua catatan stok masuk & keluar terkait produk ini
        $product->stockIns()->delete();
        $product->stockOuts()->delete();

        // Reset stok ke 0 lalu hapus produk
        $product->update(['stock' => 0]);

        ActivityLog::log('delete', 'products', "Hapus produk: {$product->name}", $product, $product->toArray());
        $product->delete();

        return redirect()->route('products.index')->with('success', "Produk \"{$product->name}\" berhasil dihapus beserta riwayat stoknya.");
    }
}
