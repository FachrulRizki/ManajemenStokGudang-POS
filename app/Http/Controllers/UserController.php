<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('username', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'username'  => ['required', 'string', 'max:50', 'unique:users,username', 'alpha_dash'],
            'email'     => ['nullable', 'email', 'unique:users,email'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'role'      => ['required', 'in:admin,manager,staff'],
            'password'  => ['required', 'confirmed', Password::min(8)],
            'is_active' => ['boolean'],
        ]);

        $data['password']  = Hash::make($data['password']);
        $data['is_active'] = $request->boolean('is_active', true);

        $user = User::create($data);

        ActivityLog::log('create', 'users', "Tambah user: {$user->name} ({$user->role})", $user, [], [
            'name' => $user->name, 'username' => $user->username, 'role' => $user->role,
        ]);

        return redirect()->route('users.index')->with('success', "User {$user->name} berhasil ditambahkan.");
    }

    public function show(User $user)
    {
        $recentActivity = ActivityLog::where('user_id', $user->id)->latest()->limit(20)->get();
        $totalStockIn   = $user->stockIns()->count();
        $totalStockOut  = $user->stockOuts()->count();

        // Permission data untuk tab permission
        $allPermissions = Permission::orderBy('sort_order')->get()->groupBy('group');
        $defaultPerms   = Permission::defaultForRole($user->role);
        $overrides      = $user->permissionOverrides()->get()->keyBy('name');
        $effectivePerms = $user->effectivePermissions();

        return view('users.show', compact(
            'user', 'recentActivity', 'totalStockIn', 'totalStockOut',
            'allPermissions', 'defaultPerms', 'overrides', 'effectivePerms'
        ));
    }

    public function edit(User $user)
    {
        return view('users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'username'  => ['required', 'string', 'max:50', 'unique:users,username,' . $user->id, 'alpha_dash'],
            'email'     => ['nullable', 'email', 'unique:users,email,' . $user->id],
            'phone'     => ['nullable', 'string', 'max:20'],
            'role'      => ['required', 'in:admin,manager,staff'],
            'password'  => ['nullable', 'confirmed', Password::min(8)],
            'is_active' => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $old = $user->toArray();
        $user->update($data);

        ActivityLog::log('update', 'users', "Ubah user: {$user->name}", $user, $old, [
            'name' => $user->name, 'username' => $user->username, 'role' => $user->role,
        ]);

        return redirect()->route('users.show', $user)->with('success', "User {$user->name} berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        ActivityLog::log('delete', 'users', "Hapus user: {$user->name}", $user, $user->toArray());
        $user->delete();

        return redirect()->route('users.index')->with('success', "User {$user->name} berhasil dihapus.");
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => ! $user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        ActivityLog::log('update', 'users', "User {$user->name} {$status}", $user);

        return back()->with('success', "User {$user->name} berhasil {$status}.");
    }

    /** Simpan override permission per user */
    public function updatePermissions(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat mengubah permission akun sendiri.');
        }

        $allPerms  = Permission::all()->keyBy('name');
        $defaults  = array_flip(Permission::defaultForRole($user->role));
        $submitted = $request->input('permissions', []);  // ['products.create' => '1', ...]

        // Hapus semua override lama
        DB::table('user_permissions')->where('user_id', $user->id)->delete();

        $inserts = [];
        foreach ($allPerms as $name => $perm) {
            $hasInSubmit  = isset($submitted[$name]);
            $hasInDefault = isset($defaults[$name]);

            // Hanya catat jika berbeda dari default role
            if ($hasInSubmit && ! $hasInDefault) {
                // Extra grant
                $inserts[] = ['user_id' => $user->id, 'permission_id' => $perm->id, 'granted' => true];
            } elseif (! $hasInSubmit && $hasInDefault) {
                // Revoke dari default
                $inserts[] = ['user_id' => $user->id, 'permission_id' => $perm->id, 'granted' => false];
            }
            // Sama dengan default = tidak perlu override
        }

        if (! empty($inserts)) {
            DB::table('user_permissions')->insert($inserts);
        }

        // Reset cache
        $user->_cachedPermissions = null;

        ActivityLog::log('update', 'users',
            "Update permission user: {$user->name}",
            $user, [], ['overrides_count' => count($inserts)]
        );

        return back()->with('success', "Permission {$user->name} berhasil diperbarui.");
    }

    public function profile()
    {
        $user = auth()->user();
        return view('users.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username,' . $user->id, 'alpha_dash'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'avatar'   => ['nullable', 'image', 'max:2048'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        ActivityLog::log('update', 'profile', "Update profil: {$user->name}", $user);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
