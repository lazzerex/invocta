<?php

namespace App\Actions;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\Tenant;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role as RoleModel;
use Spatie\Permission\PermissionRegistrar;

class CreateTenantRoles
{
    public function execute(Tenant $tenant): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($tenant->id);

        foreach (Permission::values() as $permission) {
            PermissionModel::findOrCreate($permission, 'web');
        }

        $registrar->forgetCachedPermissions();

        $adminRole = RoleModel::findOrCreate(Role::Admin->value, 'web');
        $managerRole = RoleModel::findOrCreate(Role::Manager->value, 'web');
        $staffRole = RoleModel::findOrCreate(Role::Staff->value, 'web');

        $adminRole->syncPermissions(Permission::adminPermissions());
        $managerRole->syncPermissions(Permission::managerPermissions());
        $staffRole->syncPermissions(Permission::staffPermissions());
    }
}
