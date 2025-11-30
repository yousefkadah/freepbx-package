<?php

namespace yousefkadah\FreePbx\Listeners;

use yousefkadah\FreePbx\Events\IncomingCallEvent;

class BroadcastIncomingCall
{
    /**
     * Handle the event.
     */
    public function handle(IncomingCallEvent $event): void
    {
        // The event itself implements ShouldBroadcast
        // This listener can be used for additional processing if needed
        // For example, logging, notifications, etc.
    }
}
