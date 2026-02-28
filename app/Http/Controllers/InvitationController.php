<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvitationRequest;
use App\Mail\TeamInvitationMail;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\PermissionRegistrar;

class InvitationController extends Controller
{
    public function store(StoreInvitationRequest $request): RedirectResponse
    {
        $invitation = Invitation::create([
            'tenant_id' => $request->user()->tenant_id,
            'invited_by' => $request->user()->id,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        Mail::to($invitation->email)->send(new TeamInvitationMail($invitation));

        return back()->with('success', 'Invitation sent successfully.');
    }

    public function show(string $token): Response|RedirectResponse
    {
        $invitation = Invitation::withoutGlobalScopes()
            ->with('tenant')
            ->where('token', $token)
            ->first();

        if (!$invitation || $invitation->isExpired()) {
            return Inertia::render('Invitations/Invalid');
        }

        if ($invitation->isAccepted()) {
            return redirect()->route('login')
                ->with('message', 'This invitation has already been accepted.');
        }

        return Inertia::render('Invitations/Accept', [
            'invitation' => [
                'token' => $invitation->token,
                'email' => $invitation->email,
                'role' => $invitation->role,
                'tenant_name' => $invitation->tenant->name,
                'expires_at' => $invitation->expires_at->format('F j, Y'),
            ],
        ]);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = Invitation::withoutGlobalScopes()
            ->with('tenant')
            ->where('token', $token)
            ->first();

        if (!$invitation || $invitation->isExpired() || $invitation->isAccepted()) {
            return redirect()->route('login')
                ->with('error', 'This invitation is no longer valid.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        DB::transaction(function () use ($request, $invitation) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($invitation->tenant_id);

            $user = User::withoutGlobalScopes()->create([
                'name' => $request->name,
                'email' => $invitation->email,
                'password' => Hash::make($request->password),
                'tenant_id' => $invitation->tenant_id,
            ]);

            $user->assignRole($invitation->role);

            $invitation->update(['accepted_at' => now()]);

            Auth::login($user);
        });

        return redirect()->route('dashboard')
            ->with('success', "Welcome to {$invitation->tenant->name}!");
    }

    public function destroy(Invitation $invitation): RedirectResponse
    {
        $this->authorize('manage_team');

        $invitation->delete();

        return back()->with('success', 'Invitation cancelled.');
    }

    public function resend(Invitation $invitation): RedirectResponse
    {
        $this->authorize('manage_team');

        if ($invitation->isAccepted()) {
            return back()->with('error', 'This invitation has already been accepted.');
        }

        $invitation->update([
            'token' => \Illuminate\Support\Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($invitation->email)->send(new TeamInvitationMail($invitation));

        return back()->with('success', 'Invitation resent successfully.');
    }
}
