<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class InvoicePaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('cashier.secret'));
    }

    public function checkout(string $uuid): RedirectResponse
    {
        $invoice = Invoice::withoutGlobalScopes()
            ->where('public_uuid', $uuid)
            ->with('tenant')
            ->firstOrFail();

        if (!$invoice->status->canMarkPaid()) {
            return redirect()
                ->route('public.invoice', $uuid)
                ->with('error', 'This invoice cannot be paid.');
        }

        if (!config('cashier.secret')) {
            return redirect()
                ->route('public.invoice', $uuid)
                ->with('error', 'Payment system is not configured.');
        }

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => config('cashier.currency', 'usd'),
                    'product_data' => [
                        'name' => "Invoice #{$invoice->invoice_number}",
                        'description' => "Payment for invoice from {$invoice->tenant->name}",
                    ],
                    'unit_amount' => (int) ($invoice->total * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('public.invoice.payment.success', [
                'uuid' => $uuid,
                'session_id' => '{CHECKOUT_SESSION_ID}',
            ]),
            'cancel_url' => route('public.invoice.payment.cancel', $uuid),
            'metadata' => [
                'invoice_uuid' => $uuid,
                'invoice_number' => $invoice->invoice_number,
                'tenant_id' => $invoice->tenant_id,
            ],
            'customer_email' => $invoice->client?->email,
        ]);

        $invoice->update([
            'stripe_checkout_session_id' => $session->id,
        ]);

        return redirect()->away($session->url);
    }

    public function success(string $uuid): Response
    {
        $sessionId = request()->query('session_id');

        $invoice = Invoice::withoutGlobalScopes()
            ->where('public_uuid', $uuid)
            ->with(['client', 'items', 'tenant'])
            ->firstOrFail();

        if ($sessionId && $invoice->stripe_checkout_session_id === $sessionId) {
            try {
                $session = Session::retrieve($sessionId);

                if ($session->payment_status === 'paid') {
                    $invoice->update([
                        'status' => InvoiceStatus::Paid,
                        'paid_at' => now(),
                        'stripe_payment_intent_id' => $session->payment_intent,
                        'amount_paid' => $session->amount_total / 100,
                        'payment_method' => 'stripe',
                    ]);
                }
            } catch (\Exception $e) {
                report($e);
            }
        }

        return Inertia::render('Invoices/PaymentSuccess', [
            'invoice' => [
                'uuid' => $invoice->public_uuid,
                'invoice_number' => $invoice->invoice_number,
                'tenant' => [
                    'name' => $invoice->tenant->name,
                ],
                'total' => number_format($invoice->total, 2),
                'status' => $invoice->status->value,
                'status_label' => $invoice->status->label(),
            ],
        ]);
    }

    public function cancel(string $uuid): RedirectResponse
    {
        return redirect()
            ->route('public.invoice', $uuid)
            ->with('error', 'Payment was cancelled.');
    }
}
