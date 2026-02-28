<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 10);
        $unitPrice = fake()->randomFloat(2, 10, 500);

        return [
            'description' => fake()->sentence(3),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax_rate' => fake()->randomElement([0, 5, 10, 15, 20]),
            'amount' => $quantity * $unitPrice,
            'sort_order' => 0,
        ];
    }

    public function forInvoice(Invoice $invoice): static
    {
        return $this->state(fn(array $attributes) => [
            'invoice_id' => $invoice->id,
        ]);
    }
}
