<?php

namespace App\Http\Controllers;

use App\Actions\GenerateInvoicePdf;
use App\Actions\SendInvoiceEmail;
use App\Enums\InvoiceStatus;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('view_invoices');

        $query = Invoice::with('client');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('client', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $allowedSorts = ['invoice_number', 'issue_date', 'due_date', 'total', 'status', 'created_at'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        }

        $invoices = $query->paginate(15)->withQueryString();

        $invoices->through(fn ($invoice) => [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'client' => [
                'id' => $invoice->client->id,
                'name' => $invoice->client->name,
            ],
            'status' => $invoice->status->value,
            'status_label' => $invoice->status->label(),
            'status_color' => $invoice->status->color(),
            'issue_date' => $invoice->issue_date->format('M j, Y'),
            'due_date' => $invoice->due_date->format('M j, Y'),
            'total' => number_format($invoice->total, 2),
            'is_overdue' => $invoice->is_overdue,
            'created_at' => $invoice->created_at,
        ]);

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'client_id' => $request->client_id,
                'sort' => $sortField,
                'direction' => $sortDirection,
            ],
            'statuses' => collect(InvoiceStatus::cases())->map(fn ($status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ]),
            'canCreate' => $request->user()->can('create_invoices'),
            'canEdit' => $request->user()->can('edit_invoices'),
            'canDelete' => $request->user()->can('delete_invoices'),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create_invoices');

        $clients = Client::orderBy('name')->get(['id', 'name', 'email']);

        return Inertia::render('Invoices/Create', [
            'clients' => $clients,
            'nextInvoiceNumber' => Invoice::generateNumber($request->user()->tenant_id),
            'defaultIssueDate' => now()->format('Y-m-d'),
            'defaultDueDate' => now()->addDays(30)->format('Y-m-d'),
        ]);
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $invoice = DB::transaction(function () use ($request) {
            $invoice = Invoice::create([
                'tenant_id' => $request->user()->tenant_id,
                'client_id' => $request->client_id,
                'invoice_number' => Invoice::generateNumber($request->user()->tenant_id),
                'status' => InvoiceStatus::Draft,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'notes' => $request->notes,
                'terms' => $request->terms,
            ]);

            foreach ($request->items as $index => $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'sort_order' => $index,
                ]);
            }

            $invoice->refresh();
            $invoice->calculateTotals();
            $invoice->save();

            return $invoice;
        });

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function show(Request $request, Invoice $invoice): Response
    {
        $this->authorize('view_invoices');

        $invoice->load(['client', 'items']);

        return Inertia::render('Invoices/Show', [
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'public_uuid' => $invoice->public_uuid,
                'client' => [
                    'id' => $invoice->client->id,
                    'name' => $invoice->client->name,
                    'email' => $invoice->client->email,
                    'phone' => $invoice->client->phone,
                    'address' => $invoice->client->address,
                    'tax_id' => $invoice->client->tax_id,
                ],
                'status' => $invoice->status->value,
                'status_label' => $invoice->status->label(),
                'status_color' => $invoice->status->color(),
                'issue_date' => $invoice->issue_date->format('M j, Y'),
                'due_date' => $invoice->due_date->format('M j, Y'),
                'subtotal' => number_format($invoice->subtotal, 2),
                'tax_amount' => number_format($invoice->tax_amount, 2),
                'total' => number_format($invoice->total, 2),
                'notes' => $invoice->notes,
                'terms' => $invoice->terms,
                'sent_at' => $invoice->sent_at?->format('M j, Y g:i A'),
                'email_sent_at' => $invoice->email_sent_at?->format('M j, Y g:i A'),
                'email_sent_count' => $invoice->email_sent_count,
                'paid_at' => $invoice->paid_at?->format('M j, Y g:i A'),
                'is_overdue' => $invoice->is_overdue,
                'items' => $invoice->items->map(fn ($item) => [
                    'id' => $item->id,
                    'description' => $item->description,
                    'quantity' => number_format($item->quantity, 2),
                    'unit_price' => number_format($item->unit_price, 2),
                    'tax_rate' => number_format($item->tax_rate, 2),
                    'amount' => number_format($item->amount, 2),
                    'tax_amount' => number_format($item->tax_amount, 2),
                    'total' => number_format($item->total, 2),
                ]),
                'can_edit' => $invoice->status->canEdit(),
                'can_send' => $invoice->status->canSend(),
                'can_mark_paid' => $invoice->status->canMarkPaid(),
                'can_cancel' => $invoice->status->canCancel(),
            ],
            'canEdit' => $request->user()->can('edit_invoices'),
            'canDelete' => $request->user()->can('delete_invoices'),
            'canSend' => $request->user()->can('send_invoices'),
        ]);
    }

    public function edit(Request $request, Invoice $invoice): Response|RedirectResponse
    {
        $this->authorize('edit_invoices');

        if (!$invoice->status->canEdit()) {
            return redirect()
                ->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        $invoice->load(['client', 'items']);
        $clients = Client::orderBy('name')->get(['id', 'name', 'email']);

        return Inertia::render('Invoices/Edit', [
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_id' => $invoice->client_id,
                'issue_date' => $invoice->issue_date->format('Y-m-d'),
                'due_date' => $invoice->due_date->format('Y-m-d'),
                'notes' => $invoice->notes,
                'terms' => $invoice->terms,
                'items' => $invoice->items->map(fn ($item) => [
                    'id' => $item->id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'tax_rate' => $item->tax_rate,
                ]),
            ],
            'clients' => $clients,
        ]);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        DB::transaction(function () use ($request, $invoice) {
            $invoice->update([
                'client_id' => $request->client_id,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'notes' => $request->notes,
                'terms' => $request->terms,
            ]);

            $existingItemIds = collect($request->items)
                ->pluck('id')
                ->filter()
                ->toArray();

            $invoice->items()
                ->whereNotIn('id', $existingItemIds)
                ->delete();

            foreach ($request->items as $index => $itemData) {
                if (!empty($itemData['id'])) {
                    $item = $invoice->items()->find($itemData['id']);
                    if ($item) {
                        $item->update([
                            'description' => $itemData['description'],
                            'quantity' => $itemData['quantity'],
                            'unit_price' => $itemData['unit_price'],
                            'tax_rate' => $itemData['tax_rate'] ?? 0,
                            'sort_order' => $index,
                        ]);
                    }
                } else {
                    $invoice->items()->create([
                        'description' => $itemData['description'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'tax_rate' => $itemData['tax_rate'] ?? 0,
                        'sort_order' => $index,
                    ]);
                }
            }

            $invoice->refresh();
            $invoice->calculateTotals();
            $invoice->save();
        });

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('delete_invoices');

        $invoice->delete();

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    public function duplicate(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('create_invoices');

        $newInvoice = DB::transaction(function () use ($request, $invoice) {
            $newInvoice = Invoice::create([
                'tenant_id' => $request->user()->tenant_id,
                'client_id' => $invoice->client_id,
                'invoice_number' => Invoice::generateNumber($request->user()->tenant_id),
                'status' => InvoiceStatus::Draft,
                'issue_date' => now(),
                'due_date' => now()->addDays(30),
                'notes' => $invoice->notes,
                'terms' => $invoice->terms,
            ]);

            foreach ($invoice->items as $item) {
                $newInvoice->items()->create([
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'tax_rate' => $item->tax_rate,
                    'sort_order' => $item->sort_order,
                ]);
            }

            $newInvoice->refresh();
            $newInvoice->calculateTotals();
            $newInvoice->save();

            return $newInvoice;
        });

        return redirect()
            ->route('invoices.edit', $newInvoice)
            ->with('success', 'Invoice duplicated successfully.');
    }

    public function markAsSent(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('send_invoices');

        if (!$invoice->status->canSend()) {
            return redirect()->back()->with('error', 'This invoice cannot be sent.');
        }

        $invoice->markAsSent();

        return redirect()->back()->with('success', 'Invoice marked as sent.');
    }

    public function sendEmail(Invoice $invoice, SendInvoiceEmail $sender): RedirectResponse
    {
        $this->authorize('send_invoices');

        if (!$invoice->status->canSend()) {
            return redirect()->back()->with('error', 'This invoice cannot be sent.');
        }

        $sender->execute($invoice);

        return redirect()->back()->with('success', 'Invoice email sent successfully.');
    }

    public function markAsPaid(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('edit_invoices');

        if (!$invoice->status->canMarkPaid()) {
            return redirect()->back()->with('error', 'This invoice cannot be marked as paid.');
        }

        $invoice->markAsPaid();

        return redirect()->back()->with('success', 'Invoice marked as paid.');
    }

    public function markAsCancelled(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('edit_invoices');

        if (!$invoice->status->canCancel()) {
            return redirect()->back()->with('error', 'This invoice cannot be cancelled.');
        }

        $invoice->markAsCancelled();

        return redirect()->back()->with('success', 'Invoice cancelled.');
    }

    public function downloadPdf(Invoice $invoice, GenerateInvoicePdf $generator): HttpResponse
    {
        $this->authorize('view_invoices');

        return $generator->download($invoice);
    }

    public function previewPdf(Invoice $invoice, GenerateInvoicePdf $generator): HttpResponse
    {
        $this->authorize('view_invoices');

        return $generator->stream($invoice);
    }

    public function publicView(string $uuid): Response
    {
        $invoice = Invoice::withoutGlobalScopes()
            ->where('public_uuid', $uuid)
            ->with(['client', 'items', 'tenant'])
            ->firstOrFail();

        return Inertia::render('Invoices/Public', [
            'invoice' => [
                'invoice_number' => $invoice->invoice_number,
                'tenant' => [
                    'name' => $invoice->tenant->name,
                ],
                'client' => [
                    'name' => $invoice->client->name,
                    'email' => $invoice->client->email,
                    'phone' => $invoice->client->phone,
                    'address' => $invoice->client->address,
                    'tax_id' => $invoice->client->tax_id,
                ],
                'status' => $invoice->status->value,
                'status_label' => $invoice->status->label(),
                'status_color' => $invoice->status->color(),
                'issue_date' => $invoice->issue_date->format('M j, Y'),
                'due_date' => $invoice->due_date->format('M j, Y'),
                'subtotal' => number_format($invoice->subtotal, 2),
                'tax_amount' => number_format($invoice->tax_amount, 2),
                'total' => number_format($invoice->total, 2),
                'notes' => $invoice->notes,
                'terms' => $invoice->terms,
                'items' => $invoice->items->map(fn ($item) => [
                    'description' => $item->description,
                    'quantity' => number_format($item->quantity, 2),
                    'unit_price' => number_format($item->unit_price, 2),
                    'tax_rate' => number_format($item->tax_rate, 2),
                    'amount' => number_format($item->amount, 2),
                ]),
            ],
        ]);
    }
}
