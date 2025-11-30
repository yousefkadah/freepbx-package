<?php

namespace yousefkadah\FreePbx\Webhooks;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use yousefkadah\FreePbx\Jobs\DispatchWebhookJob;
use yousefkadah\FreePbx\Webhooks\WebhookEvent;

class WebhookDispatcher
{
    /**
     * Dispatch a webhook event.
     */
    public function dispatch(string $url, WebhookEvent $event, ?string $tenantId = null): void
    {
        if (!config('freepbx.webhooks.enabled', true)) {
            return;
        }

        $queue = config('freepbx.webhooks.queue', 'default');

        DispatchWebhookJob::dispatch($url, $event, $tenantId)
            ->onQueue($queue);

        Log::info('Webhook Dispatched', [
            'url' => $url,
            'event' => get_class($event),
            'tenant' => $tenantId,
        ]);
    }

    /**
     * Dispatch webhook synchronously (for testing).
     */
    public function dispatchNow(string $url, WebhookEvent $event, ?string $tenantId = null): array
    {
        $payload = $event->toArray();
        $signature = $this->sign($payload);

        $client = new Client([
            'timeout' => config('freepbx.webhooks.timeout', 10),
        ]);

        $headers = [
            'Content-Type' => 'application/json',
            'User-Agent' => 'FreePBX-Laravel-Integrator/1.0',
        ];

        if (config('freepbx.webhooks.signing.enabled', true) && $signature) {
            $headerName = config('freepbx.webhooks.signing.header', 'X-FreePBX-Signature');
            $headers[$headerName] = $signature;
        }

        try {
            $response = $client->post($url, [
                'headers' => $headers,
                'json' => $payload,
            ]);

            return [
                'success' => true,
                'status_code' => $response->getStatusCode(),
                'response' => (string) $response->getBody(),
            ];
        } catch (\Exception $e) {
            Log::error('Webhook Delivery Failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Sign the webhook payload.
     */
    protected function sign(array $payload): ?string
    {
        if (!config('freepbx.webhooks.signing.enabled', true)) {
            return null;
        }

        $secret = config('freepbx.webhooks.signing.secret');

        if (!$secret) {
            return null;
        }

        return hash_hmac('sha256', json_encode($payload), $secret);
    }
}
