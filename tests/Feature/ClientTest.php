<?php

namespace Tests\Feature;

use App\Actions\CreateTenantRoles;
use App\Enums\Role;
use App\Models\Client;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $adminUser;
    protected User $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Test Company',
            'domain' => 'test.localhost',
        ]);

        $this->tenant->makeCurrent();

        app(PermissionRegistrar::class)->setPermissionsTeamId($this->tenant->id);
        app(CreateTenantRoles::class)->execute($this->tenant);

        $this->adminUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        $this->adminUser->assignRole(Role::Admin->value);

        $this->staffUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        $this->staffUser->assignRole(Role::Staff->value);
    }

    public function test_admin_can_view_clients_index(): void
    {
        Client::factory()->count(3)->forTenant($this->tenant)->create();

        $response = $this->actingAs($this->adminUser)->get(route('clients.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn($page) => $page
            ->component('Clients/Index')
            ->has('clients.data', 3)
        );
    }

    public function test_admin_can_create_client(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('clients.store'), [
            'name' => 'New Client',
            'email' => 'client@example.com',
            'phone' => '123-456-7890',
            'address' => '123 Main St',
            'tax_id' => '12-3456789',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clients', [
            'tenant_id' => $this->tenant->id,
            'name' => 'New Client',
            'email' => 'client@example.com',
        ]);
    }

    public function test_admin_can_update_client(): void
    {
        $client = Client::factory()->forTenant($this->tenant)->create();

        $response = $this->actingAs($this->adminUser)->put(route('clients.update', $client), [
            'name' => 'Updated Client Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Updated Client Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_admin_can_delete_client(): void
    {
        $client = Client::factory()->forTenant($this->tenant)->create();

        $response = $this->actingAs($this->adminUser)->delete(route('clients.destroy', $client));

        $response->assertRedirect(route('clients.index'));
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    public function test_staff_can_view_clients_but_cannot_create(): void
    {
        Client::factory()->count(2)->forTenant($this->tenant)->create();

        $viewResponse = $this->actingAs($this->staffUser)->get(route('clients.index'));
        $viewResponse->assertStatus(200);

        $createResponse = $this->actingAs($this->staffUser)->post(route('clients.store'), [
            'name' => 'Unauthorized Client',
        ]);
        $createResponse->assertForbidden();
    }

    public function test_client_search_works(): void
    {
        Client::factory()->forTenant($this->tenant)->create(['name' => 'Alpha Company']);
        Client::factory()->forTenant($this->tenant)->create(['name' => 'Beta Corporation']);
        Client::factory()->forTenant($this->tenant)->create(['name' => 'Gamma Inc']);

        $response = $this->actingAs($this->adminUser)->get(route('clients.index', ['search' => 'Beta']));

        $response->assertStatus(200);
        $response->assertInertia(fn($page) => $page
            ->component('Clients/Index')
            ->has('clients.data', 1)
        );
    }

    public function test_client_email_must_be_unique_per_tenant(): void
    {
        Client::factory()->forTenant($this->tenant)->create(['email' => 'duplicate@example.com']);

        $response = $this->actingAs($this->adminUser)->post(route('clients.store'), [
            'name' => 'Another Client',
            'email' => 'duplicate@example.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_tenant_isolation_works(): void
    {
        $otherTenant = Tenant::create([
            'name' => 'Other Company',
            'domain' => 'other.localhost',
        ]);

        Client::factory()->create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Tenant Client',
        ]);

        Client::factory()->forTenant($this->tenant)->create(['name' => 'My Client']);

        $response = $this->actingAs($this->adminUser)->get(route('clients.index'));

        $response->assertInertia(fn($page) => $page
            ->component('Clients/Index')
            ->has('clients.data', 1)
            ->where('clients.data.0.name', 'My Client')
        );
    }
}
