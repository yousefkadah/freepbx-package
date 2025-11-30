<?php

namespace yousefkadah\FreePbx\Listeners;

use Illuminate\Support\Facades\Cache;
use yousefkadah\FreePbx\Events\Ami\QueueMemberStatusEvent;

class UpdateDashboardMetrics
{
    /**
     * Handle the event.
     */
    public function handle(QueueMemberStatusEvent $event): void
    {
        $queue = $event->getQueue();
        $member = $event->getMemberName();
        $tenantId = $event->tenantId;

        if (!$queue || !$member) {
            return;
        }

        // Update cached metrics
        $cacheKey = $this->getCacheKey($tenantId, $queue, $member);
        
        Cache::put($cacheKey, [
            'queue' => $queue,
            'member' => $member,
            'status' => $event->getStatus(),
            'paused' => $event->isPaused(),
            'calls_taken' => $event->getCallsTaken(),
            'interface' => $event->getInterface(),
            'updated_at' => now()->toIso8601String(),
        ], now()->addMinutes(5));

        // Invalidate dashboard cache to force refresh
        $dashboardCacheKey = "dashboard.metrics." . ($tenantId ?? 'default');
        Cache::forget($dashboardCacheKey);
    }

    /**
     * Get the cache key for a queue member.
     */
    protected function getCacheKey(?string $tenantId, string $queue, string $member): string
    {
        $prefix = $tenantId ? "tenant.{$tenantId}" : 'default';
        return "{$prefix}.queue.{$queue}.member.{$member}";
    }
}
