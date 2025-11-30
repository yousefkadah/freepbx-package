<?php

namespace yousefkadah\FreePbx\Ami;

use React\EventLoop\Loop;
use React\Promise\Deferred;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;
use Illuminate\Support\Facades\Log;
use yousefkadah\FreePbx\Ami\Events\AmiEventHandler;
use yousefkadah\FreePbx\Exceptions\AmiException;

class AmiConnection
{
    protected string $host;
    protected int $port;
    protected string $username;
    protected string $secret;
    protected ?ConnectionInterface $connection = null;
    protected bool $authenticated = false;
    protected string $buffer = '';
    protected array $pendingActions = [];
    protected AmiEventHandler $eventHandler;
    protected ?string $tenantId;

    public function __construct(
        string $host,
        int $port,
        string $username,
        string $secret,
        ?string $tenantId = null
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->secret = $secret;
        $this->tenantId = $tenantId;
        $this->eventHandler = new AmiEventHandler($tenantId);
    }

    /**
     * Connect to AMI.
     */
    public function connect(): void
    {
        $connector = new Connector();
        $uri = "tcp://{$this->host}:{$this->port}";

        $connector->connect($uri)->then(
            function (ConnectionInterface $connection) {
                $this->connection = $connection;
                $this->setupConnection();
                $this->authenticate();
            },
            function (\Exception $e) {
                Log::error('AMI Connection Failed', [
                    'host' => $this->host,
                    'error' => $e->getMessage(),
                    'tenant' => $this->tenantId,
                ]);
                throw new AmiException("Failed to connect to AMI: {$e->getMessage()}");
            }
        );
    }

    /**
     * Setup connection event handlers.
     */
    protected function setupConnection(): void
    {
        $this->connection->on('data', function ($data) {
            $this->buffer .= $data;
            $this->processBuffer();
        });

        $this->connection->on('error', function (\Exception $e) {
            Log::error('AMI Connection Error', [
                'error' => $e->getMessage(),
                'tenant' => $this->tenantId,
            ]);
        });

        $this->connection->on('close', function () {
            Log::info('AMI Connection Closed', ['tenant' => $this->tenantId]);
            $this->authenticated = false;
            $this->connection = null;
        });
    }

    /**
     * Authenticate with AMI.
     */
    protected function authenticate(): void
    {
        $this->sendAction([
            'Action' => 'Login',
            'Username' => $this->username,
            'Secret' => $this->secret,
        ])->then(
            function ($response) {
                if (($response['Response'] ?? '') === 'Success') {
                    $this->authenticated = true;
                    Log::info('AMI Authentication Successful', ['tenant' => $this->tenantId]);
                } else {
                    throw new AmiException('AMI authentication failed');
                }
            },
            function ($error) {
                throw new AmiException("AMI authentication error: {$error}");
            }
        );
    }

    /**
     * Send an action to AMI.
     */
    public function sendAction(array $action): \React\Promise\PromiseInterface
    {
        if (!$this->connection) {
            throw new AmiException('Not connected to AMI');
        }

        $actionId = uniqid('action_', true);
        $action['ActionID'] = $actionId;

        $message = $this->formatMessage($action);
        $this->connection->write($message);

        $deferred = new Deferred();
        $this->pendingActions[$actionId] = $deferred;

        return $deferred->promise();
    }

    /**
     * Process the receive buffer.
     */
    protected function processBuffer(): void
    {
        while (($pos = strpos($this->buffer, "\r\n\r\n")) !== false) {
            $message = substr($this->buffer, 0, $pos);
            $this->buffer = substr($this->buffer, $pos + 4);

            $parsed = $this->parseMessage($message);
            $this->handleMessage($parsed);
        }
    }

    /**
     * Parse an AMI message.
     */
    protected function parseMessage(string $message): array
    {
        $lines = explode("\r\n", $message);
        $parsed = [];

        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                [$key, $value] = explode(':', $line, 2);
                $parsed[trim($key)] = trim($value);
            }
        }

        return $parsed;
    }

    /**
     * Handle a parsed message.
     */
    protected function handleMessage(array $message): void
    {
        // Handle action responses
        if (isset($message['ActionID']) && isset($this->pendingActions[$message['ActionID']])) {
            $this->pendingActions[$message['ActionID']]->resolve($message);
            unset($this->pendingActions[$message['ActionID']]);
            return;
        }

        // Handle events
        if (isset($message['Event'])) {
            $this->eventHandler->handle($message);
        }
    }

    /**
     * Format a message for sending.
     */
    protected function formatMessage(array $data): string
    {
        $message = '';

        foreach ($data as $key => $value) {
            $message .= "{$key}: {$value}\r\n";
        }

        $message .= "\r\n";

        return $message;
    }

    /**
     * Disconnect from AMI.
     */
    public function disconnect(): void
    {
        if ($this->connection) {
            $this->sendAction(['Action' => 'Logoff']);
            $this->connection->close();
        }
    }

    /**
     * Check if connected and authenticated.
     */
    public function isConnected(): bool
    {
        return $this->connection !== null && $this->authenticated;
    }

    /**
     * Get the tenant ID.
     */
    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }
}
