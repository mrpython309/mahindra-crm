<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::firstOrCreate([
            'name'       => 'leads:duplicates:view',
            'guard_name' => 'web',
        ]);

        foreach (['superadmin', 'manager'] as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role && !$role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::where('name', 'leads:duplicates:view')
            ->where('guard_name', 'web')
            ->first();

        if ($permission) {
            DB::table('role_has_permissions')
                ->where('permission_id', $permission->id)
                ->delete();

            Permission::where('id', $permission->id)->delete();
        }
    }
};
