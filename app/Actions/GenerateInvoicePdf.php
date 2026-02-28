<?php

namespace App\Actions;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateInvoicePdf
{
    public function execute(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->load(['client', 'items', 'tenant']);

        return Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'tenant' => $invoice->tenant,
        ])->setPaper('a4');
    }

    public function download(Invoice $invoice): \Illuminate\Http\Response
    {
        $pdf = $this->execute($invoice);
        $filename = $this->getFilename($invoice);

        return $pdf->download($filename);
    }

    public function stream(Invoice $invoice): \Illuminate\Http\Response
    {
        $pdf = $this->execute($invoice);
        $filename = $this->getFilename($invoice);

        return $pdf->stream($filename);
    }

    public function output(Invoice $invoice): string
    {
        $pdf = $this->execute($invoice);

        return $pdf->output();
    }

    protected function getFilename(Invoice $invoice): string
    {
        return 'invoice-' . $invoice->invoice_number . '.pdf';
    }
}
