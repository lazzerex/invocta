<?php

namespace Tests\Feature;

use App\Actions\CreateTenantRoles;
use App\Enums\InvoiceStatus;
use App\Enums\Role;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PublicInvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected Client $client;
    protected Invoice $invoice;

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

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        $this->user->assignRole(Role::Admin->value);

        $this->client = Client::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $this->invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'status' => InvoiceStatus::Sent,
            'public_uuid' => Str::uuid(),
        ]);
    }

    public function test_public_invoice_page_loads(): void
    {
        $response = $this->get(route('public.invoice', $this->invoice->public_uuid));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Invoices/Public')
            ->has('invoice')
            ->where('invoice.invoice_number', $this->invoice->invoice_number)
        );
    }

    public function test_public_invoice_shows_stripe_disabled_when_not_configured(): void
    {
        config(['cashier.secret' => null]);

        $response = $this->get(route('public.invoice', $this->invoice->public_uuid));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('stripeEnabled', false)
        );
    }

    public function test_public_mark_as_paid_works_for_sent_invoice(): void
    {
        $response = $this->post(route('public.invoice.mark-paid', $this->invoice->public_uuid));

        $response->assertRedirect();
        $this->invoice->refresh();
        $this->assertEquals(InvoiceStatus::Paid, $this->invoice->status);
        $this->assertNotNull($this->invoice->paid_at);
    }

    public function test_cannot_mark_paid_invoice_as_paid_again(): void
    {
        $this->invoice->update(['status' => InvoiceStatus::Paid, 'paid_at' => now()]);

        $response = $this->post(route('public.invoice.mark-paid', $this->invoice->public_uuid));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_cannot_mark_draft_invoice_as_paid(): void
    {
        $this->invoice->update(['status' => InvoiceStatus::Draft]);

        $response = $this->post(route('public.invoice.mark-paid', $this->invoice->public_uuid));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_public_invoice_returns_404_for_invalid_uuid(): void
    {
        $response = $this->get(route('public.invoice', 'invalid-uuid'));

        $response->assertStatus(404);
    }

    public function test_invoice_has_uuid_when_created(): void
    {
        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'public_uuid' => null,
        ]);

        $this->assertNotNull($invoice->public_uuid);
    }
}
