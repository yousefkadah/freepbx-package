<?php

namespace yousefkadah\FreePbx\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use yousefkadah\FreePbx\Models\WebhookLog;
use yousefkadah\FreePbx\Webhooks\WebhookEvent;

class DispatchWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;
    public int $backoff;

    protected string $url;
    protected array $payload;
    protected ?string $signature;
    protected ?string $tenantId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $url, WebhookEvent $event, ?string $tenantId = null)
    {
        $this->url = $url;
        $this->payload = $event->toArray();
        $this->tenantId = $tenantId;
        $this->tries = config('freepbx.webhooks.retry.times', 3);
        
        // Calculate backoff
        $backoffType = config('freepbx.webhooks.retry.backoff', 'exponential');
        $this->backoff = $backoffType === 'exponential' ? 60 : 30;

        // Sign payload
        $this->signature = $this->sign($this->payload);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $client = new Client([
            'timeout' => config('freepbx.webhooks.timeout', 10),
        ]);

        $headers = [
            'Content-Type' => 'application/json',
            'User-Agent' => 'FreePBX-Laravel-Integrator/1.0',
        ];

        if ($this->signature) {
            $headerName = config('freepbx.webhooks.signing.header', 'X-FreePBX-Signature');
            $headers[$headerName] = $this->signature;
        }

        $startTime = microtime(true);

        try {
            $response = $client->post($this->url, [
                'headers' => $headers,
                'json' => $this->payload,
            ]);

            $duration = (microtime(true) - $startTime) * 1000;

            $this->logWebhook(true, $response->getStatusCode(), (string) $response->getBody(), $duration);

            Log::info('Webhook Delivered Successfully', [
                'url' => $this->url,
                'status' => $response->getStatusCode(),
                'duration_ms' => round($duration, 2),
            ]);
        } catch (\Exception $e) {
            $duration = (microtime(true) - $startTime) * 1000;

            $this->logWebhook(false, 0, $e->getMessage(), $duration);

            Log::error('Webhook Delivery Failed', [
                'url' => $this->url,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Log webhook attempt.
     */
    protected function logWebhook(bool $success, int $statusCode, string $response, float $duration): void
    {
        if (!config('freepbx.webhooks.logging.enabled', true)) {
            return;
        }

        WebhookLog::create([
            'tenant_id' => $this->tenantId,
            'url' => $this->url,
            'payload' => $this->payload,
            'success' => $success,
            'status_code' => $statusCode,
            'response' => $response,
            'duration_ms' => round($duration, 2),
            'attempt' => $this->attempts(),
        ]);
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

    /**
     * Calculate the number of seconds to wait before retrying.
     */
    public function backoff(): array
    {
        $backoffType = config('freepbx.webhooks.retry.backoff', 'exponential');

        if ($backoffType === 'exponential') {
            return [60, 120, 240]; // 1min, 2min, 4min
        }

        return [30, 30, 30]; // Linear 30s
    }
}
