<?php

namespace yousefkadah\FreePbx\Ami\Events;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class AmiEventHandler
{
    protected ?string $tenantId;

    public function __construct(?string $tenantId = null)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Handle an AMI event.
     */
    public function handle(array $event): void
    {
        $eventType = $event['Event'] ?? null;

        if (!$eventType || !$this->shouldHandleEvent($eventType)) {
            return;
        }

        Log::debug('AMI Event Received', [
            'event' => $eventType,
            'tenant' => $this->tenantId,
            'data' => $event,
        ]);

        // Dispatch Laravel event
        if (config('freepbx.events.dispatch_laravel_events', true)) {
            $this->dispatchLaravelEvent($eventType, $event);
        }
    }

    /**
     * Check if we should handle this event type.
     */
    protected function shouldHandleEvent(string $eventType): bool
    {
        $monitoredEvents = config('freepbx.events.ami_events', []);

        return empty($monitoredEvents) || in_array($eventType, $monitoredEvents);
    }

    /**
     * Dispatch a Laravel event for the AMI event.
     */
    protected function dispatchLaravelEvent(string $eventType, array $data): void
    {
        $eventClass = $this->getEventClass($eventType);

        if (class_exists($eventClass)) {
            Event::dispatch(new $eventClass($data, $this->tenantId));
        } else {
            // Dispatch generic AMI event
            Event::dispatch(new \yousefkadah\FreePbx\Events\Ami\GenericAmiEvent($eventType, $data, $this->tenantId));
        }
    }

    /**
     * Get the event class for an AMI event type.
     */
    protected function getEventClass(string $eventType): string
    {
        return "yousefkadah\\FreePbx\\Events\\Ami\\{$eventType}Event";
    }
}
