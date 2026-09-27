<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Rack;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $query = Warehouse::withCount('racks');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $warehouses = $query->latest()->paginate(10)->withQueryString();
        $racks = Rack::with('warehouse')->latest()->get();

        return view('master.warehouses.index', compact('warehouses', 'racks'));
    }

    // ── Warehouse CRUD (modal) ─────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'code'        => ['nullable', 'string', 'max:20', 'unique:warehouses,code'],
            'name'        => ['required', 'string', 'max:100'],
            'location'    => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $warehouse = Warehouse::create($data);
        ActivityLog::log('create', 'warehouses', "Tambah gudang: {$warehouse->name}", $warehouse, [], $data);
        return back()->with('success', "Gudang \"{$warehouse->name}\" berhasil ditambahkan.");
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $data = $request->validate([
            'code'        => ['nullable', 'string', 'max:20', 'unique:warehouses,code,' . $warehouse->id],
            'name'        => ['required', 'string', 'max:100'],
            'location'    => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $old = $warehouse->toArray();
        $warehouse->update($data);
        ActivityLog::log('update', 'warehouses', "Ubah gudang: {$warehouse->name}", $warehouse, $old, $data);
        return back()->with('success', "Gudang \"{$warehouse->name}\" berhasil diperbarui.");
    }

    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->racks()->count() > 0) {
            return back()->with('error', 'Gudang tidak bisa dihapus karena masih memiliki rak.');
        }
        ActivityLog::log('delete', 'warehouses', "Hapus gudang: {$warehouse->name}", $warehouse, $warehouse->toArray());
        $warehouse->delete();
        return back()->with('success', 'Gudang berhasil dihapus.');
    }

    // ── Rack CRUD (modal) ──────────────────────────────
    public function storeRack(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'code'         => ['nullable', 'string', 'max:20'],
            'name'         => ['required', 'string', 'max:100'],
            'row'          => ['nullable', 'string', 'max:10'],
            'column'       => ['nullable', 'string', 'max:10'],
            'description'  => ['nullable', 'string'],
            'is_active'    => ['boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $rack = Rack::create($data);
        ActivityLog::log('create', 'racks', "Tambah rak: {$rack->name}", $rack, [], $data);
        return back()->with('success', "Rak \"{$rack->name}\" berhasil ditambahkan.");
    }

    public function updateRack(Request $request, Rack $rack)
    {
        $data = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'code'         => ['nullable', 'string', 'max:20'],
            'name'         => ['required', 'string', 'max:100'],
            'row'          => ['nullable', 'string', 'max:10'],
            'column'       => ['nullable', 'string', 'max:10'],
            'description'  => ['nullable', 'string'],
            'is_active'    => ['boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $old = $rack->toArray();
        $rack->update($data);
        ActivityLog::log('update', 'racks', "Ubah rak: {$rack->name}", $rack, $old, $data);
        return back()->with('success', "Rak \"{$rack->name}\" berhasil diperbarui.");
    }

    public function destroyRack(Rack $rack)
    {
        if ($rack->products()->count() > 0) {
            return back()->with('error', 'Rak tidak bisa dihapus karena masih ada produk di rak ini.');
        }
        ActivityLog::log('delete', 'racks', "Hapus rak: {$rack->name}", $rack, $rack->toArray());
        $rack->delete();
        return back()->with('success', 'Rak berhasil dihapus.');
    }
}
