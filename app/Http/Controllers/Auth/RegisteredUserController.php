<?php

namespace App\Http\Controllers\Auth;

use App\Actions\CreateTenantRoles;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\PermissionRegistrar;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        DB::transaction(function () use ($request) {
            $domain = Str::slug($request->company_name);
            $baseDomain = $domain;
            $counter = 1;

            while (Tenant::where('domain', $domain)->exists()) {
                $domain = $baseDomain . '-' . $counter;
                $counter++;
            }

            $tenant = Tenant::create([
                'name' => $request->company_name,
                'domain' => $domain,
            ]);

            (new CreateTenantRoles)->execute($tenant);

            app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);

            $user = User::withoutGlobalScopes()->create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'tenant_id' => $tenant->id,
            ]);

            $user->assignRole(Role::Admin->value);

            event(new Registered($user));

            Auth::login($user);
        });

        return redirect(route('dashboard', absolute: false));
    }
}
