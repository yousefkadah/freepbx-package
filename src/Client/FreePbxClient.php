<?php

namespace yousefkadah\FreePbx\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use yousefkadah\FreePbx\Client\Resources\ExtensionResource;
use yousefkadah\FreePbx\Client\Resources\QueueResource;
use yousefkadah\FreePbx\Exceptions\ApiException;
use yousefkadah\FreePbx\Exceptions\ConfigurationException;
use yousefkadah\FreePbx\Tenancy\TenantManager;

class FreePbxClient
{
    protected Client $httpClient;
    protected TenantManager $tenantManager;
    protected ?ExtensionResource $extensionResource = null;
    protected ?QueueResource $queueResource = null;

    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
        $this->httpClient = $this->createHttpClient();
    }

    /**
     * Get the extensions resource.
     */
    public function extensions(): ExtensionResource
    {
        if (!$this->extensionResource) {
            $this->extensionResource = new ExtensionResource($this);
        }

        return $this->extensionResource;
    }

    /**
     * Get the queues resource.
     */
    public function queues(): QueueResource
    {
        if (!$this->queueResource) {
            $this->queueResource = new QueueResource($this);
        }

        return $this->queueResource;
    }

    /**
     * Make a GET request.
     */
    public function get(string $endpoint, array $params = []): mixed
    {
        return $this->request('GET', $endpoint, ['query' => $params]);
    }

    /**
     * Make a POST request.
     */
    public function post(string $endpoint, array $data = []): mixed
    {
        return $this->request('POST', $endpoint, ['json' => $data]);
    }

    /**
     * Make a PUT request.
     */
    public function put(string $endpoint, array $data = []): mixed
    {
        return $this->request('PUT', $endpoint, ['json' => $data]);
    }

    /**
     * Make a DELETE request.
     */
    public function delete(string $endpoint): mixed
    {
        return $this->request('DELETE', $endpoint);
    }

    /**
     * Make an HTTP request to FreePBX API.
     */
    protected function request(string $method, string $endpoint, array $options = []): mixed
    {
        $retryTimes = $this->tenantManager->getConfig('api.retry.times', 3);
        $retrySleep = $this->tenantManager->getConfig('api.retry.sleep', 100);
        $attempt = 0;

        while ($attempt < $retryTimes) {
            try {
                $response = $this->httpClient->request($method, $endpoint, $options);
                $body = (string) $response->getBody();

                return json_decode($body, true);
            } catch (GuzzleException $e) {
                $attempt++;

                if ($attempt >= $retryTimes) {
                    $this->handleRequestException($e);
                }

                // Wait before retrying
                usleep($retrySleep * 1000);
            }
        }

        return null;
    }

    /**
     * Handle request exceptions.
     */
    protected function handleRequestException(GuzzleException $e): void
    {
        $statusCode = $e->getCode();
        $message = $e->getMessage();
        $responseData = null;

        if (method_exists($e, 'getResponse') && $e->getResponse()) {
            $statusCode = $e->getResponse()->getStatusCode();
            $body = (string) $e->getResponse()->getBody();
            $responseData = json_decode($body, true);
            $message = $responseData['message'] ?? $message;
        }

        Log::error('FreePBX API Error', [
            'status' => $statusCode,
            'message' => $message,
            'response' => $responseData,
        ]);

        throw new ApiException($message, $statusCode, $responseData, $e);
    }

    /**
     * Create the HTTP client.
     */
    protected function createHttpClient(): Client
    {
        $host = $this->tenantManager->getConfig('api.host');
        $username = $this->tenantManager->getConfig('api.username');
        $password = $this->tenantManager->getConfig('api.password');
        $timeout = $this->tenantManager->getConfig('api.timeout', 30);
        $verifySSL = $this->tenantManager->getConfig('api.verify_ssl', true);

        if (!$host || !$username || !$password) {
            throw new ConfigurationException('FreePBX API credentials are not configured');
        }

        return new Client([
            'base_uri' => rtrim($host, '/') . '/admin/api/',
            'timeout' => $timeout,
            'verify' => $verifySSL,
            'auth' => [$username, $password],
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Get the tenant manager.
     */
    public function getTenantManager(): TenantManager
    {
        return $this->tenantManager;
    }
}
