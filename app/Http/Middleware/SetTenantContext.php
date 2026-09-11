<?php

namespace App\Http\Middleware;

use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    protected TenantManager $tenantManager;

    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $orgId = null;

        // 1. Resolve via custom header (priority for API/testing)
        if ($request->hasHeader('X-Organization-ID')) {
            $orgId = $request->header('X-Organization-ID');
        } 
        // 2. Resolve via session (for quick switching during local dev/demo)
        elseif (session()->has('selected_org_id')) {
            $orgId = session('selected_org_id');
        }
        // 3. Resolve via subdomain
        else {
            $host = $request->getHost();
            $parts = explode('.', $host);
            
            // Assume format acme.bpdes.app or acme.localhost
            // Subdomain is the first part if there are 3 parts or more
            if (count($parts) >= 2 && !in_array($parts[0], ['www', 'localhost', '127', '127.0.0.1'])) {
                $subdomain = $parts[0];
                
                try {
                    // Check if DB is connected and table exists
                    if (Schema::hasTable('organizations')) {
                        $orgId = DB::table('organizations')
                            ->where('subdomain', $subdomain)
                            ->value('id');
                    }
                } catch (\Exception $e) {
                    // DB not ready or not PostgreSQL
                }
            }
        }

        // 3. Fallback to first organization in database (for local dev convenience)
        if (!$orgId && app()->environment('local')) {
            try {
                if (Schema::hasTable('organizations')) {
                    $orgId = DB::table('organizations')->value('id');
                }
            } catch (\Exception $e) {
                // Ignore
            }
        }

        // Set context in TenantManager (which executes SET app.current_org_id = '...' in PostgreSQL)
        if ($orgId) {
            $this->tenantManager->setTenantId($orgId);
        }

        return $next($request);
    }
}
