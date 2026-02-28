<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('view_clients');

        $query = Client::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        $allowedSorts = ['name', 'email', 'created_at'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'desc' ? 'desc' : 'asc');
        }

        $clients = $query->paginate(15)->withQueryString();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => [
                'search' => $request->search,
                'sort' => $sortField,
                'direction' => $sortDirection,
            ],
            'canCreate' => $request->user()->can('create_clients'),
            'canEdit' => $request->user()->can('edit_clients'),
            'canDelete' => $request->user()->can('delete_clients'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create_clients');

        return Inertia::render('Clients/Create');
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $client = Client::create([
            'tenant_id' => $request->user()->tenant_id,
            ...$request->validated(),
        ]);

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client created successfully.');
    }

    public function show(Request $request, Client $client): Response
    {
        $this->authorize('view_clients');

        $stats = [
            'total_billed' => $client->total_billed,
            'total_paid' => $client->total_paid,
            'total_outstanding' => $client->total_outstanding,
            'invoice_count' => 0,
        ];

        return Inertia::render('Clients/Show', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'address' => $client->address,
                'tax_id' => $client->tax_id,
                'created_at' => $client->created_at->format('M j, Y'),
            ],
            'stats' => $stats,
            'recentInvoices' => [],
            'canEdit' => $request->user()->can('edit_clients'),
            'canDelete' => $request->user()->can('delete_clients'),
        ]);
    }

    public function edit(Client $client): Response
    {
        $this->authorize('edit_clients');

        return Inertia::render('Clients/Edit', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'address' => $client->address,
                'tax_id' => $client->tax_id,
            ],
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->validated());

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client updated successfully.');
    }

    public function destroy(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('delete_clients');

        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client deleted successfully.');
    }
}
