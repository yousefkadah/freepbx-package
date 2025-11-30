<?php

namespace yousefkadah\FreePbx\CallPopup;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yousefkadah\FreePbx\Events\IncomingCallEvent;

class CallPopupService
{
    /**
     * Handle an incoming call and trigger popup.
     */
    public function handleIncomingCall(
        string $callerId,
        ?string $callerName,
        string $extension,
        ?string $tenantId = null,
        array $callData = []
    ): void {
        if (!config('freepbx.call_popup.enabled', true)) {
            return;
        }

        // Look up contact information
        $contact = $this->lookupContact($callerId, $tenantId);

        // Dispatch event (which will broadcast to frontend)
        event(new IncomingCallEvent(
            $callerId,
            $callerName,
            $extension,
            $contact,
            $tenantId,
            $callData
        ));

        Log::info('Call Popup Triggered', [
            'caller_id' => $callerId,
            'extension' => $extension,
            'contact_found' => $contact !== null,
            'tenant' => $tenantId,
        ]);
    }

    /**
     * Look up contact by phone number.
     */
    protected function lookupContact(string $phoneNumber, ?string $tenantId): ?array
    {
        if (!config('freepbx.call_popup.contact_lookup.enabled', true)) {
            return null;
        }

        $modelClass = config('freepbx.call_popup.contact_lookup.model');
        
        if (!$modelClass || !class_exists($modelClass)) {
            return null;
        }

        $searchFields = config('freepbx.call_popup.contact_lookup.search_fields', ['phone']);
        $normalizedPhone = $this->normalizePhoneNumber($phoneNumber);

        try {
            $query = $modelClass::query();

            // Add tenant scope if multi-tenant
            if ($tenantId && method_exists($modelClass, 'scopeForTenant')) {
                $query->forTenant($tenantId);
            }

            // Search across multiple phone fields
            $query->where(function ($q) use ($searchFields, $normalizedPhone, $phoneNumber) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, $phoneNumber)
                      ->orWhere($field, $normalizedPhone);
                }
            });

            $contact = $query->first();

            return $contact ? $contact->toArray() : null;
        } catch (\Exception $e) {
            Log::error('Contact Lookup Failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Normalize phone number for matching.
     */
    protected function normalizePhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        return preg_replace('/[^0-9]/', '', $phone);
    }
}
