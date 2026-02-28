<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class SetTenantFromUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            if ($tenant = $user->tenant) {
                $tenant->makeCurrent();
                app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
            }
        }

        return $next($request);
    }
}
