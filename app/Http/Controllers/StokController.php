<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\StockOutType;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StokController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'masuk');

        // Normalise: 'keluar' alias -> 'so' for backward compat
        if ($tab === 'keluar') $tab = 'so';

        // ── Riwayat Stok Masuk ───────────────────────────
        $siQuery = StockIn::with(['product.category', 'product.unit', 'supplier', 'user']);
        if ($request->filled('search')) {
            $siQuery->where(fn($q) => $q
                ->where('reference_number', 'like', "%{$request->search}%")
                ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$request->search}%")));
        }
        if ($request->filled('date_from')) $siQuery->whereDate('transaction_date', '>=', $request->date_from);
        if ($request->filled('date_to'))   $siQuery->whereDate('transaction_date', '<=', $request->date_to);

        $stockIns     = $siQuery->latest('transaction_date')->paginate(15, ['*'], 'si_page')->withQueryString();
        $totalQtyIn   = StockIn::sum('quantity');
        $totalValueIn = StockIn::sum('total_price');

        // ── Riwayat SO (Stok Opname) — non-penjualan ────
        $soQuery = StockOut::with(['product.category', 'product.unit', 'user', 'stockOutType'])
            ->where('type', '!=', 'sale'); // exclude penjualan POS
        if ($request->filled('search')) {
            $soQuery->where(fn($q) => $q
                ->where('reference_number', 'like', "%{$request->search}%")
                ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$request->search}%")));
        }
        if ($request->filled('date_from')) $soQuery->whereDate('transaction_date', '>=', $request->date_from);
        if ($request->filled('date_to'))   $soQuery->whereDate('transaction_date', '<=', $request->date_to);
        if ($request->filled('so_type_id')) $soQuery->where('stock_out_type_id', $request->so_type_id);

        $stockOuts     = $soQuery->latest('transaction_date')->paginate(15, ['*'], 'so_page')->withQueryString();
        $totalQtyOut   = (clone $soQuery)->sum('quantity');
        $totalValueOut = (clone $soQuery)->sum('total_price');

        // ── Data untuk form ──────────────────────────────
        $products  = Product::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $outTypes  = StockOutType::where('is_active', true)->orderBy('sort_order')->get();
        $siRef     = StockIn::generateReferenceNumber();
        $soRef     = StockOut::generateReferenceNumber();

        return view('master.stok.index', compact(
            'tab',
            'stockIns', 'totalQtyIn', 'totalValueIn',
            'stockOuts', 'totalQtyOut', 'totalValueOut',
            'products', 'suppliers', 'outTypes',
            'siRef', 'soRef',
        ));
    }

    // ── Simpan Stok Masuk ────────────────────────────────
    // Sekaligus update harga beli, harga jual, dan min stok di produk
    public function storeMasuk(Request $request)
    {
        $data = $request->validate([
            'product_id'       => ['required', 'exists:products,id'],
            'supplier_id'      => ['nullable', 'exists:suppliers,id'],
            'quantity'         => ['required', 'integer', 'min:1'],
            'purchase_price'   => ['required', 'numeric', 'min:0'],
            'selling_price'    => ['required', 'numeric', 'min:0'],
            'min_stock'        => ['nullable', 'integer', 'min:0'],
            'transaction_date' => ['required', 'date'],
            'invoice_number'   => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string'],
        ]);

        try {
            DB::transaction(function () use ($data) {
                $data['reference_number'] = StockIn::generateReferenceNumber();
                $data['user_id']          = auth()->id();
                $data['total_price']      = $data['quantity'] * $data['purchase_price'];

                // Buat record stok masuk (tanpa selling_price & min_stock — bukan kolom di stock_ins)
                $stockInData = collect($data)->except(['selling_price', 'min_stock'])->toArray();
                $stockIn     = StockIn::create($stockInData);

                // Sync ke produk: tambah stok + update harga beli/jual + min stok
                $product = Product::findOrFail($data['product_id']);
                $product->increment('stock', $data['quantity']);

                $updateProduct = ['purchase_price' => $data['purchase_price'],
                                   'selling_price'  => $data['selling_price']];
                if (!empty($data['min_stock'])) {
                    $updateProduct['min_stock'] = $data['min_stock'];
                }
                // Update supplier jika ada
                if (!empty($data['supplier_id'])) {
                    $updateProduct['supplier_id'] = $data['supplier_id'];
                }
                $product->update($updateProduct);

                ActivityLog::log('create', 'stock_in',
                    "Stok masuk: {$product->name} +{$data['quantity']} unit @ Rp " .
                    number_format($data['purchase_price'], 0, ',', '.') . " (Ref: {$stockIn->reference_number})",
                    $stockIn, [], $data);
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('stok.index', ['tab' => 'masuk'])
            ->with('success', 'Stok masuk berhasil dicatat. Harga produk telah diperbarui.');
    }

    // ── Edit Stok Masuk ──────────────────────────────────
    public function updateMasuk(Request $request, StockIn $stockIn)
    {
        $data = $request->validate([
            'quantity'         => ['required', 'integer', 'min:1'],
            'purchase_price'   => ['required', 'numeric', 'min:0'],
            'selling_price'    => ['required', 'numeric', 'min:0'],
            'supplier_id'      => ['nullable', 'exists:suppliers,id'],
            'transaction_date' => ['required', 'date'],
            'invoice_number'   => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string'],
        ]);

        try {
            DB::transaction(function () use ($data, $stockIn) {
                $product    = $stockIn->product;
                $oldQty     = $stockIn->quantity;
                $newQty     = (int) $data['quantity'];
                $qtyDiff    = $newQty - $oldQty; // positif = tambah, negatif = kurangi

                // Cek apakah pengurangan tidak bikin stok negatif
                if ($qtyDiff < 0 && $product->stock < abs($qtyDiff)) {
                    throw new \Exception(
                        "Tidak bisa mengurangi: stok produk ({$product->stock}) lebih kecil dari selisih ({$qtyDiff})."
                    );
                }

                // Update record stok masuk
                $stockIn->update([
                    'quantity'         => $newQty,
                    'purchase_price'   => $data['purchase_price'],
                    'total_price'      => $newQty * $data['purchase_price'],
                    'supplier_id'      => $data['supplier_id'] ?? $stockIn->supplier_id,
                    'transaction_date' => $data['transaction_date'],
                    'invoice_number'   => $data['invoice_number'] ?? null,
                    'notes'            => $data['notes'] ?? null,
                ]);

                // Koreksi stok produk berdasarkan selisih qty
                if ($qtyDiff > 0) {
                    $product->increment('stock', $qtyDiff);
                } elseif ($qtyDiff < 0) {
                    $product->decrement('stock', abs($qtyDiff));
                }

                // Update harga di produk
                $product->update([
                    'purchase_price' => $data['purchase_price'],
                    'selling_price'  => $data['selling_price'],
                ]);

                ActivityLog::log('update', 'stock_in',
                    "Edit stok masuk: {$product->name} qty {$oldQty} → {$newQty} (Ref: {$stockIn->reference_number})",
                    $stockIn, ['quantity' => $oldQty], $data);
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('stok.index', ['tab' => 'masuk'])
            ->with('success', 'Stok masuk berhasil diperbarui. Stok produk telah disesuaikan.');
    }

    // ── Hapus Stok Masuk ─────────────────────────────────
    public function destroyMasuk(StockIn $stockIn)
    {
        DB::transaction(function () use ($stockIn) {
            $product = $stockIn->product;
            if ($product->stock < $stockIn->quantity) {
                throw new \Exception('Stok tidak cukup untuk membatalkan transaksi ini.');
            }
            $product->decrement('stock', $stockIn->quantity);
            ActivityLog::log('delete', 'stock_in',
                "Hapus stok masuk: {$product->name} (Ref: {$stockIn->reference_number})",
                $stockIn, $stockIn->toArray());
            $stockIn->delete();
        });

        return redirect()->route('stok.index', ['tab' => 'masuk'])
            ->with('success', 'Stok masuk berhasil dihapus dan stok dikembalikan.');
    }

    // ── Simpan SO / Stok Opname ──────────────────────────
    // Kategori SO (stock_out_type_id) wajib diisi
    // Tidak ada field "type" dari user — default 'other' (non-penjualan)
    public function storeKeluar(Request $request)
    {
        $data = $request->validate([
            'product_id'        => ['required', 'exists:products,id'],
            'quantity'          => ['required', 'integer', 'min:1'],
            'selling_price'     => ['nullable', 'numeric', 'min:0'],
            'transaction_date'  => ['required', 'date'],
            'customer_name'     => ['nullable', 'string', 'max:150'],
            'stock_out_type_id' => ['required', 'exists:stock_out_types,id'],
            'notes'             => ['required', 'string', 'max:500'],
        ], [
            'stock_out_type_id.required' => 'Kategori SO wajib dipilih.',
            'notes.required'             => 'Catatan/alasan wajib diisi.',
        ]);

        // Type selalu 'other' untuk SO manual (bukan sale via POS)
        $data['type']          = 'other';
        $data['selling_price'] = $data['selling_price'] ?? 0;

        try {
            DB::transaction(function () use ($data) {
                $product = Product::findOrFail($data['product_id']);
                if ($product->stock < $data['quantity']) {
                    throw new \Exception("Stok {$product->name} tidak cukup. Tersedia: {$product->stock}");
                }
                $data['reference_number'] = StockOut::generateReferenceNumber();
                $data['user_id']          = auth()->id();
                $data['total_price']      = $data['quantity'] * $data['selling_price'];

                $stockOut = StockOut::create($data);
                $product->decrement('stock', $data['quantity']);

                $soType = StockOutType::find($data['stock_out_type_id']);
                ActivityLog::log('create', 'stock_out',
                    "SO: {$product->name} -{$data['quantity']} unit [{$soType?->name}] (Ref: {$stockOut->reference_number})",
                    $stockOut, [], $data);
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('stok.index', ['tab' => 'so'])
            ->with('success', 'Stok Opname berhasil dicatat.');
    }

    // ── Hapus SO ─────────────────────────────────────────
    public function destroyKeluar(StockOut $stockOut)
    {
        DB::transaction(function () use ($stockOut) {
            $product = $stockOut->product;
            $product->increment('stock', $stockOut->quantity);
            ActivityLog::log('delete', 'stock_out',
                "Hapus SO: {$product->name} (Ref: {$stockOut->reference_number})",
                $stockOut, $stockOut->toArray());
            $stockOut->delete();
        });

        return redirect()->route('stok.index', ['tab' => 'so'])
            ->with('success', 'Catatan SO dihapus dan stok dikembalikan.');
    }
}
