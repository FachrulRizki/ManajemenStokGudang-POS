<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\StockOut;
use App\Models\StockOutType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOutController extends Controller
{
    public function index(Request $request)
    {
        $query = StockOut::with(['product.category', 'product.unit', 'user', 'stockOutType']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('reference_number', 'like', "%{$request->search}%")
                  ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$request->search}%"))
                  ->orWhere('customer_name', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('stock_out_type_id')) {
            $query->where('stock_out_type_id', $request->stock_out_type_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $stockOuts  = $query->latest('transaction_date')->paginate(15)->withQueryString();

        // Hanya hitung nilai untuk stok keluar non-penjualan (penjualan sudah ada di POS)
        // Tampilkan semua tapi pisahkan di summary
        $totalQty     = StockOut::sum('quantity');
        $totalValue   = StockOut::sum('total_price');
        $stockOutTypes = StockOutType::where('is_active', true)->orderBy('sort_order')->get();

        return view('master.stock-out.index', compact('stockOuts', 'totalQty', 'totalValue', 'stockOutTypes'));
    }

    public function create()
    {
        // Stok keluar manual hanya untuk non-penjualan (rusak, retur, adjustment, dll)
        // Penjualan lewat POS
        $products      = Product::where('is_active', true)->where('stock', '>', 0)->orderBy('name')->get();
        $stockOutTypes = StockOutType::where('is_active', true)->orderBy('sort_order')->get();
        $refNumber     = StockOut::generateReferenceNumber();

        return view('master.stock-out.form', compact('products', 'stockOutTypes', 'refNumber'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'        => ['required', 'exists:products,id'],
            'quantity'          => ['required', 'integer', 'min:1'],
            'selling_price'     => ['required', 'numeric', 'min:0'],
            'transaction_date'  => ['required', 'date'],
            'customer_name'     => ['nullable', 'string', 'max:150'],
            'type'              => ['required', 'in:sale,return,damaged,other'],
            'stock_out_type_id' => ['nullable', 'exists:stock_out_types,id'],
            'notes'             => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data) {
            $product = Product::findOrFail($data['product_id']);

            if ($product->stock < $data['quantity']) {
                throw new \Exception("Stok tidak cukup. Stok tersedia: {$product->stock}");
            }

            $data['reference_number'] = StockOut::generateReferenceNumber();
            $data['user_id']          = auth()->id();
            $data['total_price']      = $data['quantity'] * $data['selling_price'];

            $stockOut = StockOut::create($data);
            $product->decrement('stock', $data['quantity']);

            ActivityLog::log(
                'create', 'stock_out',
                "Stok keluar: {$product->name} -{$data['quantity']} unit (Ref: {$stockOut->reference_number})",
                $stockOut, [], $data
            );
        });

        return redirect()->route('stock-out.index')->with('success', 'Stok keluar berhasil dicatat.');
    }

    public function show(StockOut $stockOut)
    {
        $stockOut->load(['product.category', 'product.unit', 'user', 'stockOutType']);
        return view('master.stock-out.show', compact('stockOut'));
    }

    public function destroy(StockOut $stockOut)
    {
        DB::transaction(function () use ($stockOut) {
            $product = $stockOut->product;
            $product->increment('stock', $stockOut->quantity);

            ActivityLog::log(
                'delete', 'stock_out',
                "Hapus stok keluar: {$product->name} +{$stockOut->quantity} unit (Ref: {$stockOut->reference_number})",
                $stockOut, $stockOut->toArray()
            );

            $stockOut->delete();
        });

        return redirect()->route('stock-out.index')->with('success', 'Transaksi stok keluar berhasil dihapus.');
    }
}
