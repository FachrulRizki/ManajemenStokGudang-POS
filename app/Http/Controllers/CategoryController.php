<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount('products');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $categories = $query->latest()->paginate(10)->withQueryString();
        return view('master.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('master.categories.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'code'        => ['nullable', 'string', 'max:20', 'unique:categories,code'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $category = Category::create($data);

        ActivityLog::log('create', 'categories', "Tambah kategori: {$category->name}", $category, [], $data);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('master.categories.form', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'code'        => ['nullable', 'string', 'max:20', 'unique:categories,code,' . $category->id],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $old = $category->toArray();
        $category->update($data);

        ActivityLog::log('update', 'categories', "Ubah kategori: {$category->name}", $category, $old, $data);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih memiliki produk.');
        }

        ActivityLog::log('delete', 'categories', "Hapus kategori: {$category->name}", $category, $category->toArray());
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
