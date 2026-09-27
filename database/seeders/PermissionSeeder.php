<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Isi tabel permissions
        $allPerms = Permission::allPermissions();
        $groups = ['dashboard','master_data','stok','pos','laporan','sistem'];
        $groupOrder = array_flip($groups);

        foreach ($allPerms as $i => $perm) {
            Permission::updateOrCreate(
                ['name' => $perm['name']],
                [
                    'label'       => $perm['label'],
                    'group'       => $perm['group'],
                    'sort_order'  => ($groupOrder[$perm['group']] ?? 99) * 100 + $i,
                ]
            );
        }

        // Isi role_permissions dari default per role
        DB::table('role_permissions')->truncate();
        $perms = Permission::all()->keyBy('name');

        foreach (['admin', 'manager', 'staff'] as $role) {
            $defaults = Permission::defaultForRole($role);
            foreach ($defaults as $permName) {
                if (isset($perms[$permName])) {
                    DB::table('role_permissions')->insertOrIgnore([
                        'role'          => $role,
                        'permission_id' => $perms[$permName]->id,
                    ]);
                }
            }
        }

        $this->command->info('Permissions seeded: ' . count($allPerms) . ' permissions, 3 roles configured.');
    }
}
