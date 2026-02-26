<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class DomainTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?Tenant
    {
        $host = $request->getHost();
        
        // Extract subdomain from host
        // For example: tenant1.invocta.test -> tenant1
        $parts = explode('.', $host);
        
        // If localhost or IP address, check for domain parameter
        if (count($parts) <= 2 || $host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            // For development, you can check query parameter or session
            $domain = $request->input('tenant') ?? session('tenant_domain');
            if ($domain) {
                return Tenant::where('domain', $domain)->first();
            }
            return null;
        }
        
        // Get the subdomain (first part)
        $subdomain = $parts[0];
        
        return Tenant::where('domain', $subdomain)->first();
    }
}
