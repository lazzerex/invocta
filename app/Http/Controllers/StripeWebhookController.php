<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        Stripe::setApiKey(config('cashier.secret'));

        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $webhookSecret = config('cashier.webhook.secret');

        try {
            if ($webhookSecret) {
                $event = Webhook::constructEvent($payload, $signature, $webhookSecret);
            } else {
                $event = json_decode($payload);
            }
        } catch (SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        } catch (\Exception $e) {
            return response('Invalid payload', 400);
        }

        $method = 'handle' . str_replace('.', '', ucwords(str_replace('_', '.', $event->type), '.'));

        if (method_exists($this, $method)) {
            $this->$method($event->data->object);
        }

        return response('Webhook received', 200);
    }

    protected function handleCheckoutSessionCompleted($session): void
    {
        if (!isset($session->metadata->invoice_uuid)) {
            return;
        }

        $invoice = Invoice::withoutGlobalScopes()
            ->where('public_uuid', $session->metadata->invoice_uuid)
            ->first();

        if (!$invoice) {
            return;
        }

        if ($invoice->status === InvoiceStatus::Paid) {
            return;
        }

        if ($session->payment_status === 'paid') {
            $invoice->update([
                'status' => InvoiceStatus::Paid,
                'paid_at' => now(),
                'stripe_payment_intent_id' => $session->payment_intent,
                'amount_paid' => $session->amount_total / 100,
                'payment_method' => 'stripe',
            ]);
        }
    }

    protected function handlePaymentIntentSucceeded($paymentIntent): void
    {
        $invoice = Invoice::withoutGlobalScopes()
            ->where('stripe_payment_intent_id', $paymentIntent->id)
            ->first();

        if (!$invoice) {
            return;
        }

        if ($invoice->status !== InvoiceStatus::Paid) {
            $invoice->update([
                'status' => InvoiceStatus::Paid,
                'paid_at' => now(),
                'amount_paid' => $paymentIntent->amount / 100,
                'payment_method' => 'stripe',
            ]);
        }
    }
}
