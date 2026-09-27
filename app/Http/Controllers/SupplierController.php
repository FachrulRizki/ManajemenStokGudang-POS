<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::withCount('products');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('contact_person', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $suppliers = $query->latest()->paginate(10)->withQueryString();
        return view('master.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('master.suppliers.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'code'           => ['nullable', 'string', 'max:20', 'unique:suppliers,code'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'email'          => ['nullable', 'email'],
            'address'        => ['nullable', 'string'],
            'city'           => ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string'],
            'is_active'      => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $supplier = Supplier::create($data);

        ActivityLog::log('create', 'suppliers', "Tambah supplier: {$supplier->name}", $supplier, [], $data);

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['products', 'stockIns.product']);
        $totalTransactions = $supplier->stockIns()->count();
        $totalValue        = $supplier->stockIns()->sum('total_price');

        return view('master.suppliers.show', compact('supplier', 'totalTransactions', 'totalValue'));
    }

    public function edit(Supplier $supplier)
    {
        return view('master.suppliers.form', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'code'           => ['nullable', 'string', 'max:20', 'unique:suppliers,code,' . $supplier->id],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'email'          => ['nullable', 'email'],
            'address'        => ['nullable', 'string'],
            'city'           => ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string'],
            'is_active'      => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $old = $supplier->toArray();
        $supplier->update($data);

        ActivityLog::log('update', 'suppliers', "Ubah supplier: {$supplier->name}", $supplier, $old, $data);

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->products()->count() > 0) {
            return back()->with('error', 'Supplier tidak bisa dihapus karena masih terkait dengan produk.');
        }

        ActivityLog::log('delete', 'suppliers', "Hapus supplier: {$supplier->name}", $supplier, $supplier->toArray());
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus.');
    }
}
