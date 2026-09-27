<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $query = Unit::withCount('products');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('symbol', 'like', "%{$request->search}%");
            });
        }

        $units = $query->latest()->paginate(10)->withQueryString();
        return view('master.units.index', compact('units'));
    }

    public function create()
    {
        return view('master.units.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:50'],
            'symbol'      => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $unit = Unit::create($data);

        ActivityLog::log('create', 'units', "Tambah satuan: {$unit->name}", $unit, [], $data);

        return redirect()->route('units.index')->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function edit(Unit $unit)
    {
        return view('master.units.form', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:50'],
            'symbol'      => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $old = $unit->toArray();
        $unit->update($data);

        ActivityLog::log('update', 'units', "Ubah satuan: {$unit->name}", $unit, $old, $data);

        return redirect()->route('units.index')->with('success', 'Satuan berhasil diperbarui.');
    }

    public function destroy(Unit $unit)
    {
        if ($unit->products()->count() > 0) {
            return back()->with('error', 'Satuan tidak bisa dihapus karena masih digunakan produk.');
        }

        ActivityLog::log('delete', 'units', "Hapus satuan: {$unit->name}", $unit, $unit->toArray());
        $unit->delete();

        return redirect()->route('units.index')->with('success', 'Satuan berhasil dihapus.');
    }
}
