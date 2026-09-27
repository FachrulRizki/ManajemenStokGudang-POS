<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockInController extends Controller
{
    public function index(Request $request)
    {
        $query = StockIn::with(['product.category', 'supplier', 'user']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('reference_number', 'like', "%{$request->search}%")
                  ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$request->search}%"))
                  ->orWhere('invoice_number', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $stockIns   = $query->latest('transaction_date')->paginate(15)->withQueryString();
        $totalQty   = StockIn::sum('quantity');
        $totalValue = StockIn::sum('total_price');

        return view('master.stock-in.index', compact('stockIns', 'totalQty', 'totalValue'));
    }

    // ── Halaman Scan Barcode ─────────────────────────────
    public function scan()
    {
        $suppliers  = Supplier::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $units      = Unit::where('is_active', true)->orderBy('name')->get();

        return view('master.stock-in.scan', compact('suppliers', 'categories', 'units'));
    }

    // ── API: Cari produk berdasarkan barcode ─────────────
    public function findByBarcode(Request $request)
    {
        $barcode = trim($request->input('barcode', ''));

        if (empty($barcode)) {
            return response()->json(['found' => false, 'message' => 'Barcode kosong.']);
        }

        // Cari by barcode exact, atau by kode produk
        $product = Product::with(['category', 'unit', 'supplier'])
            ->where('barcode', $barcode)
            ->orWhere('code', $barcode)
            ->first();

        if (! $product) {
            return response()->json([
                'found'   => false,
                'barcode' => $barcode,
                'message' => 'Produk dengan barcode "' . $barcode . '" tidak ditemukan.',
            ]);
        }

        return response()->json([
            'found'   => true,
            'product' => [
                'id'             => $product->id,
                'code'           => $product->code,
                'name'           => $product->name,
                'barcode'        => $product->barcode,
                'category_id'    => $product->category_id,
                'category_name'  => $product->category?->name,
                'unit_id'        => $product->unit_id,
                'unit_name'      => $product->unit?->name,
                'unit_symbol'    => $product->unit?->symbol,
                'supplier_id'    => $product->supplier_id,
                'supplier_name'  => $product->supplier?->name,
                'purchase_price' => $product->purchase_price,
                'selling_price'  => $product->selling_price,
                'stock'          => $product->stock,
                'min_stock'      => $product->min_stock,
                'rack_location'  => $product->rack_location,
                'is_new'         => false,
            ],
        ]);
    }

    public function create()
    {
        $products  = Product::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $refNumber = StockIn::generateReferenceNumber();

        return view('master.stock-in.form', compact('products', 'suppliers', 'refNumber'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'       => ['required', 'exists:products,id'],
            'supplier_id'      => ['nullable', 'exists:suppliers,id'],
            'quantity'         => ['required', 'integer', 'min:1'],
            'purchase_price'   => ['required', 'numeric', 'min:0'],
            'transaction_date' => ['required', 'date'],
            'invoice_number'   => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data) {
            $data['reference_number'] = StockIn::generateReferenceNumber();
            $data['user_id']          = auth()->id();
            $data['total_price']      = $data['quantity'] * $data['purchase_price'];

            $stockIn = StockIn::create($data);

            $product = Product::findOrFail($data['product_id']);
            $product->increment('stock', $data['quantity']);
            $product->update(['purchase_price' => $data['purchase_price']]);

            ActivityLog::log(
                'create', 'stock_in',
                "Stok masuk: {$product->name} +{$data['quantity']} unit (Ref: {$stockIn->reference_number})",
                $stockIn, [], $data
            );
        });

        // Response berbeda untuk request dari scanner (AJAX) vs form biasa
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Stok masuk berhasil dicatat.']);
        }

        return redirect()->route('stock-in.index')->with('success', 'Stok masuk berhasil dicatat.');
    }

    public function show(StockIn $stockIn)
    {
        $stockIn->load(['product.category', 'product.unit', 'supplier', 'user']);
        return view('master.stock-in.show', compact('stockIn'));
    }

    public function destroy(StockIn $stockIn)
    {
        DB::transaction(function () use ($stockIn) {
            $product = $stockIn->product;

            if ($product->stock < $stockIn->quantity) {
                throw new \Exception('Stok produk tidak cukup untuk membatalkan transaksi ini.');
            }

            $product->decrement('stock', $stockIn->quantity);

            ActivityLog::log(
                'delete', 'stock_in',
                "Hapus stok masuk: {$product->name} -{$stockIn->quantity} unit (Ref: {$stockIn->reference_number})",
                $stockIn, $stockIn->toArray()
            );

            $stockIn->delete();
        });

        return redirect()->route('stock-in.index')->with('success', 'Transaksi stok masuk berhasil dihapus.');
    }
}
