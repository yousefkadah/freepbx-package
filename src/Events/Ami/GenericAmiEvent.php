<?php

namespace Yousef\FreePbx\Events\Ami;

class GenericAmiEvent
{
    public string $eventType;
    public array $data;
    public ?string $tenantId;

    public function __construct(string $eventType, array $data, ?string $tenantId = null)
    {
        $this->eventType = $eventType;
        $this->data = $data;
        $this->tenantId = $tenantId;
    }
}
