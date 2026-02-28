<?php

namespace Database\Seeders;

use App\Actions\CreateTenantRoles;
use App\Enums\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenant1 = Tenant::create([
            'name' => 'Acme Corporation',
            'domain' => 'acme',
        ]);

        (new CreateTenantRoles)->execute($tenant1);
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant1->id);

        $user1 = User::withoutGlobalScopes()->create([
            'name' => 'John Doe',
            'email' => 'john@acme.com',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant1->id,
        ]);
        $user1->assignRole(Role::Admin->value);

        $tenant2 = Tenant::create([
            'name' => 'Stark Industries',
            'domain' => 'stark',
        ]);

        (new CreateTenantRoles)->execute($tenant2);
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant2->id);

        $user2 = User::withoutGlobalScopes()->create([
            'name' => 'Tony Stark',
            'email' => 'tony@stark.com',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant2->id,
        ]);
        $user2->assignRole(Role::Admin->value);
    }
}
