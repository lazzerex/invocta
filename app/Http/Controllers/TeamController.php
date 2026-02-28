<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\PermissionRegistrar;

class TeamController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('view_team');

        $members = User::with('roles')
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->first()?->name ?? 'No role',
                'created_at' => $user->created_at->format('M j, Y'),
                'is_current_user' => $user->id === $request->user()->id,
            ]);

        $invitations = Invitation::with('inviter')
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->get()
            ->map(fn ($invitation) => [
                'id' => $invitation->id,
                'email' => $invitation->email,
                'role' => $invitation->role,
                'invited_by' => $invitation->inviter->name,
                'expires_at' => $invitation->expires_at->format('M j, Y'),
            ]);

        return Inertia::render('Team/Index', [
            'members' => $members,
            'invitations' => $invitations,
            'roles' => Role::values(),
            'canManageTeam' => $request->user()->can('manage_team'),
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage_team');

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot change your own role.');
        }

        $request->validate([
            'role' => ['required', 'string', 'in:' . implode(',', Role::values())],
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);

        $user->syncRoles([$request->role]);

        return back()->with('success', 'Role updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage_team');

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot remove yourself from the team.');
        }

        $user->delete();

        return back()->with('success', 'Team member removed successfully.');
    }
}
