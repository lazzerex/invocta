<?php

namespace Tests\Feature;

use App\Actions\CreateTenantRoles;
use App\Enums\InvoiceStatus;
use App\Enums\Role;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $admin;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Test Company',
            'domain' => 'test.localhost',
        ]);

        $this->tenant->makeCurrent();
        (new CreateTenantRoles)->execute($this->tenant);

        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        $this->admin->assignRole(Role::Admin->value);

        $this->client = Client::factory()->forTenant($this->tenant)->create();
    }

    public function test_invoice_index_page_is_displayed(): void
    {
        $this->withoutVite();
        $response = $this->actingAs($this->admin)->get(route('invoices.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Invoices/Index'));
    }

    public function test_admin_can_create_invoice(): void
    {
        $response = $this->actingAs($this->admin)->post(route('invoices.store'), [
            'client_id' => $this->client->id,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'notes' => 'Test notes',
            'terms' => 'Net 30',
            'items' => [
                [
                    'description' => 'Service A',
                    'quantity' => 2,
                    'unit_price' => 100,
                    'tax_rate' => 10,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'notes' => 'Test notes',
        ]);
        $this->assertDatabaseHas('invoice_items', [
            'description' => 'Service A',
            'quantity' => 2,
            'unit_price' => 100,
        ]);
    }

    public function test_invoice_totals_are_calculated_correctly(): void
    {
        $this->actingAs($this->admin)->post(route('invoices.store'), [
            'client_id' => $this->client->id,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'items' => [
                ['description' => 'Item 1', 'quantity' => 2, 'unit_price' => 100, 'tax_rate' => 10],
                ['description' => 'Item 2', 'quantity' => 1, 'unit_price' => 50, 'tax_rate' => 0],
            ],
        ]);

        $invoice = Invoice::first();

        $this->assertEquals(250, $invoice->subtotal);
        $this->assertEquals(20, $invoice->tax_amount);
        $this->assertEquals(270, $invoice->total);
    }

    public function test_invoice_numbering_auto_increments(): void
    {
        $this->actingAs($this->admin)->post(route('invoices.store'), [
            'client_id' => $this->client->id,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'items' => [['description' => 'Service', 'quantity' => 1, 'unit_price' => 100, 'tax_rate' => 0]],
        ]);

        $this->actingAs($this->admin)->post(route('invoices.store'), [
            'client_id' => $this->client->id,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'items' => [['description' => 'Service', 'quantity' => 1, 'unit_price' => 100, 'tax_rate' => 0]],
        ]);

        $invoices = Invoice::orderBy('id')->get();
        $this->assertEquals('INV-0001', $invoices[0]->invoice_number);
        $this->assertEquals('INV-0002', $invoices[1]->invoice_number);
    }

    public function test_only_draft_invoices_can_be_edited(): void
    {
        $invoice = Invoice::create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-0001',
            'status' => InvoiceStatus::Sent,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
        ]);

        $response = $this->actingAs($this->admin)->get(route('invoices.edit', $invoice));

        $response->assertRedirect(route('invoices.show', $invoice));
    }

    public function test_admin_can_duplicate_invoice(): void
    {
        $invoice = Invoice::create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-0001',
            'status' => InvoiceStatus::Paid,
            'issue_date' => now()->subDays(30),
            'due_date' => now(),
            'notes' => 'Original notes',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Test Service',
            'quantity' => 1,
            'unit_price' => 100,
            'tax_rate' => 10,
            'amount' => 100,
        ]);

        $response = $this->actingAs($this->admin)->post(route('invoices.duplicate', $invoice));

        $response->assertRedirect();
        $this->assertDatabaseCount('invoices', 2);

        $newInvoice = Invoice::where('id', '!=', $invoice->id)->first();
        $this->assertEquals('INV-0002', $newInvoice->invoice_number);
        $this->assertEquals(InvoiceStatus::Draft, $newInvoice->status);
        $this->assertEquals('Original notes', $newInvoice->notes);
        $this->assertCount(1, $newInvoice->items);
    }

    public function test_admin_can_mark_invoice_as_sent(): void
    {
        $invoice = Invoice::create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-0001',
            'status' => InvoiceStatus::Draft,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
        ]);

        $response = $this->actingAs($this->admin)->post(route('invoices.send', $invoice));

        $response->assertRedirect();
        $invoice->refresh();
        $this->assertEquals(InvoiceStatus::Sent, $invoice->status);
        $this->assertNotNull($invoice->sent_at);
    }

    public function test_admin_can_mark_invoice_as_paid(): void
    {
        $invoice = Invoice::create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-0001',
            'status' => InvoiceStatus::Sent,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
        ]);

        $response = $this->actingAs($this->admin)->post(route('invoices.mark-paid', $invoice));

        $response->assertRedirect();
        $invoice->refresh();
        $this->assertEquals(InvoiceStatus::Paid, $invoice->status);
        $this->assertNotNull($invoice->paid_at);
    }

    public function test_admin_can_cancel_invoice(): void
    {
        $invoice = Invoice::create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-0001',
            'status' => InvoiceStatus::Draft,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
        ]);

        $response = $this->actingAs($this->admin)->post(route('invoices.cancel', $invoice));

        $response->assertRedirect();
        $invoice->refresh();
        $this->assertEquals(InvoiceStatus::Cancelled, $invoice->status);
    }

    public function test_client_billing_stats_update_with_invoices(): void
    {
        Invoice::create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-0001',
            'status' => InvoiceStatus::Paid,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'total' => 500,
            'subtotal' => 500,
        ]);

        Invoice::create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-0002',
            'status' => InvoiceStatus::Sent,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'total' => 300,
            'subtotal' => 300,
        ]);

        Invoice::create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-0003',
            'status' => InvoiceStatus::Draft,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'total' => 200,
            'subtotal' => 200,
        ]);

        $this->client->refresh();

        $this->assertEquals(800, $this->client->total_billed);
        $this->assertEquals(500, $this->client->total_paid);
        $this->assertEquals(300, $this->client->total_outstanding);
    }
}
