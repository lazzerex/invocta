<?php

namespace App\Mail;

use App\Actions\GenerateInvoicePdf;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invoice {$this->invoice->invoice_number} from {$this->invoice->tenant->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invoice',
            with: [
                'invoiceNumber' => $this->invoice->invoice_number,
                'clientName' => $this->invoice->client->name,
                'tenantName' => $this->invoice->tenant->name,
                'total' => number_format($this->invoice->total, 2),
                'dueDate' => $this->invoice->due_date->format('F j, Y'),
                'viewUrl' => route('public.invoice', $this->invoice->public_uuid),
            ],
        );
    }

    public function attachments(): array
    {
        $generator = app(GenerateInvoicePdf::class);

        return [
            Attachment::fromData(
                fn () => $generator->output($this->invoice),
                "invoice-{$this->invoice->invoice_number}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}
