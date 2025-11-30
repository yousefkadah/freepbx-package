<?php

namespace yousefkadah\FreePbx\Events\Ami;

class NewchannelEvent
{
    public array $data;
    public ?string $tenantId;

    public function __construct(array $data, ?string $tenantId = null)
    {
        $this->data = $data;
        $this->tenantId = $tenantId;
    }

    public function getChannel(): ?string
    {
        return $this->data['Channel'] ?? null;
    }

    public function getCallerIdNum(): ?string
    {
        return $this->data['CallerIDNum'] ?? null;
    }

    public function getCallerIdName(): ?string
    {
        return $this->data['CallerIDName'] ?? null;
    }

    public function getExten(): ?string
    {
        return $this->data['Exten'] ?? null;
    }
}
