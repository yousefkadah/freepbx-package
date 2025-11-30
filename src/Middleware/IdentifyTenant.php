<?php

namespace Yousef\FreePbx\Middleware;

use Closure;
use Illuminate\Http\Request;
use Yousef\FreePbx\Tenancy\TenantManager;

class IdentifyTenant
{
    protected TenantManager $tenantManager;

    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!config('freepbx.multi_tenant.enabled', false)) {
            return $next($request);
        }

        $tenantId = $this->tenantManager->getCurrentTenantId();

        if ($tenantId) {
            $this->tenantManager->setTenant($tenantId);
        }

        return $next($request);
    }
}
