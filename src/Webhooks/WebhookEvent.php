<?php

namespace Yousef\FreePbx\Webhooks;

abstract class WebhookEvent
{
    protected string $eventType;
    protected array $data;
    protected ?string $tenantId;

    public function __construct(string $eventType, array $data, ?string $tenantId = null)
    {
        $this->eventType = $eventType;
        $this->data = $data;
        $this->tenantId = $tenantId;
    }

    /**
     * Convert event to array for webhook payload.
     */
    public function toArray(): array
    {
        return [
            'event' => $this->eventType,
            'data' => $this->data,
            'tenant_id' => $this->tenantId,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the event type.
     */
    public function getEventType(): string
    {
        return $this->eventType;
    }

    /**
     * Get the event data.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get the tenant ID.
     */
    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }
}
