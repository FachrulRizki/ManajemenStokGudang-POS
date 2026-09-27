<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Shift;
use App\Models\StockOut;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    /** Halaman utama kasir */
    public function kasir()
    {
        $user    = Auth::user();
        $shift   = $user->activeShift();
        $methods = PaymentMethod::where('is_active', true)->orderBy('sort_order')->get();

        // Jika tidak ada shift aktif, arahkan ke halaman buka shift
        if (! $shift) {
            return view('pos.open-shift', compact('user', 'methods'));
        }

        // Produk yang bisa dijual: aktif dan stok > 0
        $products = Product::with(['category', 'unit'])
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        return view('pos.kasir', compact('shift', 'products', 'methods', 'user'));
    }

    /** JSON: cari produk (autocomplete / scan barcode) */
    public function searchProduct(Request $request)
    {
        $q = $request->input('q', '');

        $products = Product::with(['unit', 'category'])
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('code', 'like', "%{$q}%")
                      ->orWhere('barcode', $q);  // exact match untuk barcode scan
            })
            ->limit(20)
            ->get()
            ->map(fn ($p) => [
                'id'           => $p->id,
                'name'         => $p->name,
                'code'         => $p->code,
                'barcode'      => $p->barcode,
                'selling_price'=> (float) $p->selling_price,
                'stock'        => $p->stock,
                'unit'         => $p->unit->symbol ?? 'pcs',
                'category'     => $p->category->name ?? '-',
                'image_url'    => $p->image ? asset('storage/' . $p->image) : null,
            ]);

        return response()->json($products);
    }

    /** Proses transaksi POS */
    public function store(Request $request)
    {
        $user  = Auth::user();
        $shift = $user->activeShift();

        if (! $shift) {
            return response()->json(['error' => 'Tidak ada shift aktif. Silakan buka shift terlebih dahulu.'], 422);
        }

        $data = $request->validate([
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => ['required', 'exists:products,id'],
            'items.*.quantity'       => ['required', 'integer', 'min:1'],
            'items.*.unit_price'     => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.discount_amount'  => ['nullable', 'numeric', 'min:0'],
            'customer_name'          => ['nullable', 'string', 'max:100'],
            'customer_phone'         => ['nullable', 'string', 'max:20'],
            'discount_percent'       => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount'        => ['nullable', 'numeric', 'min:0'],
            'tax_percent'            => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_method_id'      => ['required', 'exists:payment_methods,id'],
            'amount_paid'            => ['required', 'numeric', 'min:0'],
            'payment_reference'      => ['nullable', 'string', 'max:100'],
            'notes'                  => ['nullable', 'string'],
        ]);

        try {
            DB::beginTransaction();

            // Hitung subtotal tiap item
            $subtotal = 0;
            $itemsToCreate = [];

            foreach ($data['items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'error' => "Stok {$product->name} tidak cukup. Tersedia: {$product->stock} {$product->unit->symbol}"
                    ], 422);
                }

                $discPct    = $item['discount_percent'] ?? 0;
                $discAmt    = $item['discount_amount'] ?? ($item['unit_price'] * $item['quantity'] * $discPct / 100);
                $itemSubtotal = ($item['unit_price'] * $item['quantity']) - $discAmt;
                $subtotal  += $itemSubtotal;

                $itemsToCreate[] = [
                    'product_id'       => $product->id,
                    'product_name'     => $product->name,
                    'product_code'     => $product->code,
                    'product_barcode'  => $product->barcode,
                    'quantity'         => $item['quantity'],
                    'unit_price'       => $item['unit_price'],
                    'discount_percent' => $discPct,
                    'discount_amount'  => $discAmt,
                    'subtotal'         => $itemSubtotal,
                    'product_obj'      => $product,   // sementara, tidak disimpan ke DB
                ];
            }

            // Hitung diskon header & pajak
            $discPct    = $data['discount_percent'] ?? 0;
            $discAmt    = $data['discount_amount'] ?? ($subtotal * $discPct / 100);
            $taxPct     = $data['tax_percent'] ?? 0;
            $taxAmt     = ($subtotal - $discAmt) * $taxPct / 100;
            $grandTotal = $subtotal - $discAmt + $taxAmt;
            $change     = max(0, $data['amount_paid'] - $grandTotal);

            // Buat header transaksi
            $transaction = Transaction::create([
                'invoice_number'    => Transaction::generateInvoiceNumber(),
                'shift_id'          => $shift->id,
                'user_id'           => $user->id,
                'customer_name'     => $data['customer_name'] ?? null,
                'customer_phone'    => $data['customer_phone'] ?? null,
                'subtotal'          => $subtotal,
                'discount_percent'  => $discPct,
                'discount_amount'   => $discAmt,
                'tax_percent'       => $taxPct,
                'tax_amount'        => $taxAmt,
                'grand_total'       => $grandTotal,
                'payment_method_id' => $data['payment_method_id'],
                'amount_paid'       => $data['amount_paid'],
                'change_amount'     => $change,
                'payment_reference' => $data['payment_reference'] ?? null,
                'status'            => 'paid',
                'notes'             => $data['notes'] ?? null,
                'transaction_at'    => now(),
            ]);

            // Buat item & kurangi stok
            foreach ($itemsToCreate as $item) {
                $product = $item['product_obj'];
                unset($item['product_obj']);
                $item['transaction_id'] = $transaction->id;

                TransactionItem::create($item);
                $product->decrement('stock', $item['quantity']);

                // Catat otomatis ke stock_outs sebagai penjualan (for full audit trail)
                StockOut::create([
                    'reference_number' => 'SO-POS-' . $transaction->invoice_number . '-' . $product->id,
                    'product_id'       => $product->id,
                    'user_id'          => $user->id,
                    'quantity'         => $item['quantity'],
                    'selling_price'    => $item['unit_price'],
                    'total_price'      => $item['subtotal'],
                    'transaction_date' => now(),
                    'type'             => 'sale',
                    'customer_name'    => $data['customer_name'] ?? null,
                    'notes'            => 'POS: ' . $transaction->invoice_number,
                ]);
            }

            // Update ringkasan shift (non-blocking)
            $shift->recalculate();

            DB::commit();

            ActivityLog::log('create', 'transactions', "Transaksi POS: {$transaction->invoice_number}", $transaction, [], [
                'invoice' => $transaction->invoice_number,
                'total'   => $grandTotal,
                'items'   => count($itemsToCreate),
            ]);

            return response()->json([
                'success'        => true,
                'transaction_id' => $transaction->id,
                'invoice_number' => $transaction->invoice_number,
                'grand_total'    => $grandTotal,
                'amount_paid'    => $data['amount_paid'],
                'change_amount'  => $change,
                'receipt_url'    => route('pos.receipt', $transaction),
                'thermal_url'    => route('pos.thermal', $transaction),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /** Void / batalkan transaksi */
    public function void(Request $request, Transaction $transaction)
    {
        if ($transaction->status !== 'paid') {
            return back()->with('error', 'Transaksi tidak bisa dibatalkan.');
        }

        $data = $request->validate(['reason' => ['required', 'string']]);

        DB::beginTransaction();
        try {
            // Kembalikan stok
            foreach ($transaction->items as $item) {
                Product::where('id', $item->product_id)->increment('stock', $item->quantity);
            }

            $transaction->update([
                'status' => 'voided',
                'notes'  => 'VOID: ' . $data['reason'],
            ]);

            // Rekap shift
            $transaction->shift->recalculate();

            DB::commit();
            ActivityLog::log('update', 'transactions', "Void transaksi: {$transaction->invoice_number}", $transaction);
            return back()->with('success', "Transaksi {$transaction->invoice_number} berhasil dibatalkan.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }

    /** Riwayat transaksi POS */
    public function history(Request $request)
    {
        $query = Transaction::with(['user', 'paymentMethod', 'shift'])
            ->withCount('items');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', "%{$request->search}%")
                  ->orWhere('customer_name', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_at', '<=', $request->date_to);
        }

        if ($request->filled('payment_method_id')) {
            $query->where('payment_method_id', $request->payment_method_id);
        }

        $transactions  = $query->with(['user', 'paymentMethod', 'items.product.unit'])->latest('transaction_at')->paginate(20)->withQueryString();
        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('sort_order')->get();

        // Summary hari ini
        $todaySummary = Transaction::where('status', 'paid')
            ->whereDate('transaction_at', today())
            ->selectRaw('COUNT(*) as total_trx, SUM(grand_total) as total_revenue, SUM(discount_amount) as total_discount')
            ->first();

        return view('pos.history', compact('transactions', 'paymentMethods', 'todaySummary'));
    }

    /** Struk PDF (A5 / A4) */
    public function receipt(Transaction $transaction)
    {
        $transaction->load(['items.product', 'paymentMethod', 'user', 'shift']);
        $settings = \App\Models\AppSetting::getGroup('general');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pos.receipt-pdf', compact('transaction', 'settings'))
            ->setPaper([0, 0, 419.528, 595.276], 'portrait');  // A5

        return $pdf->stream("struk-{$transaction->invoice_number}.pdf");
    }

    /** Struk thermal (HTML untuk window.print()) */
    public function thermal(Transaction $transaction)
    {
        $transaction->load(['items.product.unit', 'paymentMethod', 'user', 'shift']);
        $settings = \App\Models\AppSetting::getGroup('general');

        return view('pos.receipt-thermal', compact('transaction', 'settings'));
    }
}
