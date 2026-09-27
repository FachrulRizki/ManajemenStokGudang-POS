<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\StockOutType;
use Illuminate\Http\Request;

class StockOutTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = StockOutType::withCount('stockOuts');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        $types = $query->orderBy('sort_order')->orderBy('id')->paginate(15)->withQueryString();
        return view('master.stock-out-types.index', compact('types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'          => ['required', 'string', 'max:30', 'unique:stock_out_types,code'],
            'name'          => ['required', 'string', 'max:100'],
            'color'         => ['required', 'in:success,danger,warning,info,secondary,primary'],
            'icon'          => ['nullable', 'string', 'max:100'],
            'affects_stock' => ['boolean'],
            'is_active'     => ['boolean'],
            'sort_order'    => ['integer', 'min:0'],
        ]);
        $data['affects_stock'] = $request->boolean('affects_stock', true);
        $data['is_active']     = $request->boolean('is_active', true);
        $data['code']          = strtoupper($data['code']);

        $type = StockOutType::create($data);
        ActivityLog::log('create', 'stock_out_types', "Tambah tipe keluar: {$type->name}", $type, [], $data);
        return back()->with('success', "Tipe keluar \"{$type->name}\" berhasil ditambahkan.");
    }

    public function update(Request $request, StockOutType $stockOutType)
    {
        $data = $request->validate([
            'code'          => ['required', 'string', 'max:30', 'unique:stock_out_types,code,' . $stockOutType->id],
            'name'          => ['required', 'string', 'max:100'],
            'color'         => ['required', 'in:success,danger,warning,info,secondary,primary'],
            'icon'          => ['nullable', 'string', 'max:100'],
            'affects_stock' => ['boolean'],
            'is_active'     => ['boolean'],
            'sort_order'    => ['integer', 'min:0'],
        ]);
        $data['affects_stock'] = $request->boolean('affects_stock', true);
        $data['is_active']     = $request->boolean('is_active', true);
        $data['code']          = strtoupper($data['code']);

        $old = $stockOutType->toArray();
        $stockOutType->update($data);
        ActivityLog::log('update', 'stock_out_types', "Ubah tipe keluar: {$stockOutType->name}", $stockOutType, $old, $data);
        return back()->with('success', "Tipe keluar \"{$stockOutType->name}\" berhasil diperbarui.");
    }

    public function destroy(StockOutType $stockOutType)
    {
        if ($stockOutType->stockOuts()->count() > 0) {
            return back()->with('error', 'Tipe ini tidak bisa dihapus karena sudah digunakan.');
        }
        ActivityLog::log('delete', 'stock_out_types', "Hapus tipe keluar: {$stockOutType->name}", $stockOutType, $stockOutType->toArray());
        $stockOutType->delete();
        return back()->with('success', 'Tipe keluar berhasil dihapus.');
    }
}
