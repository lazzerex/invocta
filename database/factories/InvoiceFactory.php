<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $issueDate = fake()->dateTimeBetween('-3 months', 'now');
        $dueDate = fake()->dateTimeBetween($issueDate, '+30 days');

        return [
            'invoice_number' => 'INV-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'status' => fake()->randomElement(InvoiceStatus::values()),
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'subtotal' => 0,
            'tax_amount' => 0,
            'total' => 0,
            'notes' => fake()->optional(0.5)->sentence(),
            'terms' => fake()->optional(0.3)->sentence(),
            'public_uuid' => Str::uuid(),
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn(array $attributes) => [
            'tenant_id' => $tenant->id,
        ]);
    }

    public function forClient(Client $client): static
    {
        return $this->state(fn(array $attributes) => [
            'client_id' => $client->id,
            'tenant_id' => $client->tenant_id,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => InvoiceStatus::Draft->value,
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => InvoiceStatus::Sent->value,
            'sent_at' => now(),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => InvoiceStatus::Paid->value,
            'sent_at' => now()->subDays(5),
            'paid_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => InvoiceStatus::Overdue->value,
            'sent_at' => now()->subDays(45),
            'due_date' => now()->subDays(15),
        ]);
    }
}
