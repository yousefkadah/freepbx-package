<?php

namespace yousefkadah\FreePbx\Listeners;

use Illuminate\Support\Facades\Log;
use yousefkadah\FreePbx\CallPopup\CallPopupService;
use yousefkadah\FreePbx\Events\Ami\NewchannelEvent;

class DetectIncomingCall
{
    protected CallPopupService $callPopupService;

    public function __construct(CallPopupService $callPopupService)
    {
        $this->callPopupService = $callPopupService;
    }

    /**
     * Handle the event.
     */
    public function handle(NewchannelEvent $event): void
    {
        // Only process incoming calls
        if (!$this->isIncomingCall($event)) {
            return;
        }

        $callerId = $event->getCallerIdNum();
        $callerName = $event->getCallerIdName();
        $extension = $event->getExten();

        if (!$callerId || !$extension) {
            return;
        }

        Log::info('Incoming Call Detected', [
            'caller_id' => $callerId,
            'extension' => $extension,
            'tenant' => $event->tenantId,
        ]);

        // Trigger call popup
        $this->callPopupService->handleIncomingCall(
            $callerId,
            $callerName,
            $extension,
            $event->tenantId,
            $event->data
        );
    }

    /**
     * Determine if this is an incoming call.
     */
    protected function isIncomingCall(NewchannelEvent $event): bool
    {
        $channel = $event->getChannel();
        
        // Basic heuristic: incoming calls typically have certain channel patterns
        // This may need to be adjusted based on your FreePBX configuration
        return $channel && (
            str_contains($channel, 'from-trunk') ||
            str_contains($channel, 'from-pstn') ||
            str_contains($channel, 'from-did')
        );
    }
}
