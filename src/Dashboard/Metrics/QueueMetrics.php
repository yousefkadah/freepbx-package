<?php

namespace yousefkadah\FreePbx\Dashboard\Metrics;

use yousefkadah\FreePbx\Client\FreePbxClient;

class QueueMetrics
{
    protected FreePbxClient $client;

    public function __construct(FreePbxClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get metrics for all queues.
     */
    public function get(): array
    {
        $queues = $this->client->queues()->list();
        $metrics = [];

        foreach ($queues as $queue) {
            $queueId = $queue['id'] ?? $queue['queue_id'] ?? null;
            
            if (!$queueId) {
                continue;
            }

            $stats = $this->client->queues()->stats($queueId);
            
            $metrics[] = [
                'queue_id' => $queueId,
                'name' => $queue['name'] ?? $queueId,
                'waiting_calls' => $stats['waiting'] ?? 0,
                'completed_calls' => $stats['completed'] ?? 0,
                'abandoned_calls' => $stats['abandoned'] ?? 0,
                'average_wait_time' => $stats['avg_wait_time'] ?? 0,
                'service_level' => $stats['service_level'] ?? 0,
                'agents_available' => $stats['agents_available'] ?? 0,
                'agents_busy' => $stats['agents_busy'] ?? 0,
                'agents_total' => $stats['agents_total'] ?? 0,
            ];
        }

        return $metrics;
    }

    /**
     * Get metrics for a specific queue.
     */
    public function getForQueue(string $queueId): ?array
    {
        $stats = $this->client->queues()->stats($queueId);
        $queue = $this->client->queues()->get($queueId);

        if (!$queue) {
            return null;
        }

        return [
            'queue_id' => $queueId,
            'name' => $queue['name'] ?? $queueId,
            'waiting_calls' => $stats['waiting'] ?? 0,
            'completed_calls' => $stats['completed'] ?? 0,
            'abandoned_calls' => $stats['abandoned'] ?? 0,
            'average_wait_time' => $stats['avg_wait_time'] ?? 0,
            'service_level' => $stats['service_level'] ?? 0,
            'agents_available' => $stats['agents_available'] ?? 0,
            'agents_busy' => $stats['agents_busy'] ?? 0,
            'agents_total' => $stats['agents_total'] ?? 0,
        ];
    }
}
