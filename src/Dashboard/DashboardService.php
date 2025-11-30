<?php

namespace yousefkadah\FreePbx\Dashboard;

use Illuminate\Support\Facades\Cache;
use yousefkadah\FreePbx\Ami\AmiManager;
use yousefkadah\FreePbx\Client\FreePbxClient;
use yousefkadah\FreePbx\Dashboard\Metrics\AgentMetrics;
use yousefkadah\FreePbx\Dashboard\Metrics\QueueMetrics;

class DashboardService
{
    protected FreePbxClient $client;
    protected AmiManager $amiManager;

    public function __construct(FreePbxClient $client, AmiManager $amiManager)
    {
        $this->client = $client;
        $this->amiManager = $amiManager;
    }

    /**
     * Get all dashboard metrics.
     */
    public function getMetrics(?string $tenantId = null): array
    {
        $cacheKey = "dashboard.metrics." . ($tenantId ?? 'default');
        $cacheTtl = config('freepbx.dashboard.cache.ttl', 30);

        if (config('freepbx.dashboard.cache.enabled', true)) {
            return Cache::remember($cacheKey, $cacheTtl, function () {
                return $this->fetchMetrics();
            });
        }

        return $this->fetchMetrics();
    }

    /**
     * Fetch fresh metrics.
     */
    protected function fetchMetrics(): array
    {
        return [
            'queues' => $this->getQueueMetrics(),
            'agents' => $this->getAgentMetrics(),
            'summary' => $this->getSummaryMetrics(),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get queue metrics.
     */
    public function getQueueMetrics(): array
    {
        $queueMetrics = new QueueMetrics($this->client);
        return $queueMetrics->get();
    }

    /**
     * Get agent metrics.
     */
    public function getAgentMetrics(): array
    {
        $agentMetrics = new AgentMetrics($this->client);
        return $agentMetrics->get();
    }

    /**
     * Get summary metrics.
     */
    protected function getSummaryMetrics(): array
    {
        $queues = $this->getQueueMetrics();
        $agents = $this->getAgentMetrics();

        return [
            'total_queues' => count($queues),
            'total_agents' => count($agents),
            'available_agents' => collect($agents)->where('status', 'available')->count(),
            'busy_agents' => collect($agents)->where('status', 'busy')->count(),
            'paused_agents' => collect($agents)->where('paused', true)->count(),
            'total_waiting_calls' => collect($queues)->sum('waiting_calls'),
        ];
    }

    /**
     * Clear dashboard cache.
     */
    public function clearCache(?string $tenantId = null): void
    {
        $cacheKey = "dashboard.metrics." . ($tenantId ?? 'default');
        Cache::forget($cacheKey);
    }
}
