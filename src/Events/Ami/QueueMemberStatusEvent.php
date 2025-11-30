<?php

namespace yousefkadah\FreePbx\Events\Ami;

class QueueMemberStatusEvent
{
    public array $data;
    public ?string $tenantId;

    public function __construct(array $data, ?string $tenantId = null)
    {
        $this->data = $data;
        $this->tenantId = $tenantId;
    }

    public function getQueue(): ?string
    {
        return $this->data['Queue'] ?? null;
    }

    public function getMemberName(): ?string
    {
        return $this->data['MemberName'] ?? null;
    }

    public function getInterface(): ?string
    {
        return $this->data['Interface'] ?? null;
    }

    public function getStatus(): ?int
    {
        return isset($this->data['Status']) ? (int) $this->data['Status'] : null;
    }

    public function isPaused(): bool
    {
        return ($this->data['Paused'] ?? '0') === '1';
    }

    public function getCallsTaken(): int
    {
        return (int) ($this->data['CallsTaken'] ?? 0);
    }
}
