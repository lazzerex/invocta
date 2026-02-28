<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            Client::factory()
                ->count(10)
                ->forTenant($tenant)
                ->create();
        }
    }
}
