<?php

namespace Yousef\FreePbx\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yousef\FreePbx\Dashboard\DashboardService;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get dashboard metrics.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID');
        $metrics = $this->dashboardService->getMetrics($tenantId);

        return response()->json($metrics);
    }

    /**
     * Get queue metrics.
     */
    public function queues(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID');
        $metrics = $this->dashboardService->getQueueMetrics();

        return response()->json($metrics);
    }

    /**
     * Get agent metrics.
     */
    public function agents(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID');
        $metrics = $this->dashboardService->getAgentMetrics();

        return response()->json($metrics);
    }

    /**
     * Clear dashboard cache.
     */
    public function clearCache(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID');
        $this->dashboardService->clearCache($tenantId);

        return response()->json(['message' => 'Cache cleared successfully']);
    }
}
