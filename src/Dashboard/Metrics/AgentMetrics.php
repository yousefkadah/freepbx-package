<?php

namespace Yousef\FreePbx\Dashboard\Metrics;

use Illuminate\Support\Facades\Cache;
use Yousef\FreePbx\Client\FreePbxClient;

class AgentMetrics
{
    protected FreePbxClient $client;

    public function __construct(FreePbxClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get metrics for all agents.
     */
    public function get(): array
    {
        $queues = $this->client->queues()->list();
        $agentMetrics = [];

        foreach ($queues as $queue) {
            $queueId = $queue['id'] ?? $queue['queue_id'] ?? null;
            
            if (!$queueId) {
                continue;
            }

            $agents = $this->client->queues()->agents($queueId);

            foreach ($agents as $agent) {
                $extension = $agent['extension'] ?? $agent['interface'] ?? null;
                
                if (!$extension) {
                    continue;
                }

                // Get cached status from AMI events
                $cachedStatus = $this->getCachedAgentStatus($queueId, $extension);

                $agentMetrics[$extension] = array_merge(
                    $agentMetrics[$extension] ?? [],
                    [
                        'extension' => $extension,
                        'name' => $agent['name'] ?? $extension,
                        'queues' => array_merge(
                            $agentMetrics[$extension]['queues'] ?? [],
                            [$queueId]
                        ),
                        'status' => $cachedStatus['status'] ?? $this->determineStatus($agent),
                        'paused' => $cachedStatus['paused'] ?? ($agent['paused'] ?? false),
                        'calls_taken' => $cachedStatus['calls_taken'] ?? ($agent['calls_taken'] ?? 0),
                        'last_call' => $agent['last_call'] ?? null,
                    ]
                );
            }
        }

        return array_values($agentMetrics);
    }

    /**
     * Get cached agent status from AMI events.
     */
    protected function getCachedAgentStatus(string $queueId, string $extension): array
    {
        $tenantId = $this->client->getTenantManager()->getCurrentTenantId();
        $prefix = $tenantId ? "tenant.{$tenantId}" : 'default';
        $cacheKey = "{$prefix}.queue.{$queueId}.member.{$extension}";

        return Cache::get($cacheKey, []);
    }

    /**
     * Determine agent status from agent data.
     */
    protected function determineStatus(array $agent): string
    {
        $status = $agent['status'] ?? 0;

        return match ((int) $status) {
            0 => 'unknown',
            1 => 'available',
            2 => 'busy',
            3 => 'unavailable',
            4 => 'invalid',
            5 => 'unavailable',
            6 => 'ringing',
            7 => 'busy',
            8 => 'on_hold',
            default => 'unknown',
        };
    }
}
