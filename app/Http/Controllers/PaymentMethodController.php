<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        $query = PaymentMethod::withCount('transactions');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $methods = $query->orderBy('sort_order')->orderBy('id')->paginate(15)->withQueryString();
        return view('master.payment-methods.index', compact('methods'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'               => ['required', 'string', 'max:20', 'unique:payment_methods,code'],
            'name'               => ['required', 'string', 'max:100'],
            'type'               => ['required', 'in:cash,digital,card'],
            'description'        => ['nullable', 'string'],
            'icon'               => ['nullable', 'string', 'max:100'],
            'requires_reference' => ['boolean'],
            'is_active'          => ['boolean'],
            'sort_order'         => ['integer', 'min:0'],
        ]);
        $data['requires_reference'] = $request->boolean('requires_reference');
        $data['is_active']          = $request->boolean('is_active', true);
        $data['code']               = strtoupper($data['code']);

        $method = PaymentMethod::create($data);
        ActivityLog::log('create', 'payment_methods', "Tambah metode bayar: {$method->name}", $method, [], $data);
        return back()->with('success', "Metode pembayaran \"{$method->name}\" berhasil ditambahkan.");
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $data = $request->validate([
            'code'               => ['required', 'string', 'max:20', 'unique:payment_methods,code,' . $paymentMethod->id],
            'name'               => ['required', 'string', 'max:100'],
            'type'               => ['required', 'in:cash,digital,card'],
            'description'        => ['nullable', 'string'],
            'icon'               => ['nullable', 'string', 'max:100'],
            'requires_reference' => ['boolean'],
            'is_active'          => ['boolean'],
            'sort_order'         => ['integer', 'min:0'],
        ]);
        $data['requires_reference'] = $request->boolean('requires_reference');
        $data['is_active']          = $request->boolean('is_active', true);
        $data['code']               = strtoupper($data['code']);

        $old = $paymentMethod->toArray();
        $paymentMethod->update($data);
        ActivityLog::log('update', 'payment_methods', "Ubah metode bayar: {$paymentMethod->name}", $paymentMethod, $old, $data);
        return back()->with('success', "Metode pembayaran \"{$paymentMethod->name}\" berhasil diperbarui.");
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->transactions()->count() > 0) {
            return back()->with('error', 'Metode pembayaran tidak bisa dihapus karena sudah digunakan pada transaksi.');
        }
        ActivityLog::log('delete', 'payment_methods', "Hapus metode bayar: {$paymentMethod->name}", $paymentMethod, $paymentMethod->toArray());
        $paymentMethod->delete();
        return back()->with('success', 'Metode pembayaran berhasil dihapus.');
    }
}
