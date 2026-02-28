<?php

namespace App\Actions;

use App\Mail\InvoiceMail;
use App\Models\Invoice;
use Illuminate\Support\Facades\Mail;

class SendInvoiceEmail
{
    public function execute(Invoice $invoice): void
    {
        $invoice->load(['client', 'tenant']);

        Mail::to($invoice->client->email)->send(new InvoiceMail($invoice));

        $invoice->markAsEmailSent();
    }
}
