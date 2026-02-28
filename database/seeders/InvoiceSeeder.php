<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all();

        foreach ($clients as $client) {
            $invoiceCount = rand(1, 5);

            for ($i = 0; $i < $invoiceCount; $i++) {
                $invoice = Invoice::factory()
                    ->forClient($client)
                    ->create([
                        'invoice_number' => Invoice::generateNumber($client->tenant_id),
                    ]);

                $itemCount = rand(1, 5);
                for ($j = 0; $j < $itemCount; $j++) {
                    InvoiceItem::factory()
                        ->forInvoice($invoice)
                        ->create(['sort_order' => $j]);
                }

                $invoice->refresh();
                $invoice->calculateTotals();
                $invoice->save();
            }
        }
    }
}
