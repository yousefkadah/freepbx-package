<?php

namespace yousefkadah\FreePbx\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncomingCallEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $callerId;
    public ?string $callerName;
    public string $extension;
    public ?array $contact;
    public ?string $tenantId;
    public array $callData;

    public function __construct(
        string $callerId,
        ?string $callerName,
        string $extension,
        ?array $contact = null,
        ?string $tenantId = null,
        array $callData = []
    ) {
        $this->callerId = $callerId;
        $this->callerName = $callerName;
        $this->extension = $extension;
        $this->contact = $contact;
        $this->tenantId = $tenantId;
        $this->callData = $callData;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [];

        // Broadcast to the specific extension
        $channels[] = new PrivateChannel("freepbx.extension.{$this->extension}");

        // If tenant-specific, also broadcast to tenant channel
        if ($this->tenantId) {
            $channels[] = new PrivateChannel("freepbx.tenant.{$this->tenantId}");
        }

        return $channels;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'caller_id' => $this->callerId,
            'caller_name' => $this->callerName,
            'extension' => $this->extension,
            'contact' => $this->contact,
            'call_data' => $this->callData,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'incoming.call';
    }
}
