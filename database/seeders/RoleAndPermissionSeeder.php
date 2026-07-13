<?php

namespace Database\Seeders;

use App\Domain\Access\PermissionMatrix;
use App\Enums\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionMatrix::permissions() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (Role::cases() as $role) {
            $spatieRole = SpatieRole::findOrCreate($role->value, 'web');
            $spatieRole->syncPermissions(PermissionMatrix::forRole($role));
        }
    }
}
