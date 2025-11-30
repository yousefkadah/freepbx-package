<?php

namespace Yousef\FreePbx\Ami;

use Illuminate\Support\Facades\Log;
use Yousef\FreePbx\Exceptions\AmiException;
use Yousef\FreePbx\Tenancy\TenantManager;

class AmiManager
{
    protected TenantManager $tenantManager;
    protected array $connections = [];

    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
    }

    /**
     * Get or create an AMI connection for the current tenant.
     */
    public function getConnection(?string $tenantId = null): AmiConnection
    {
        $tenantId = $tenantId ?? $this->tenantManager->getCurrentTenantId();
        $connectionKey = $tenantId ?? 'default';

        if (!isset($this->connections[$connectionKey]) || !$this->connections[$connectionKey]->isConnected()) {
            $this->connections[$connectionKey] = $this->createConnection($tenantId);
        }

        return $this->connections[$connectionKey];
    }

    /**
     * Create a new AMI connection.
     */
    protected function createConnection(?string $tenantId): AmiConnection
    {
        // Temporarily set tenant context if provided
        if ($tenantId) {
            $originalTenant = $this->tenantManager->getCurrentTenantId();
            $this->tenantManager->setTenant($tenantId);
        }

        $host = $this->tenantManager->getConfig('ami.host');
        $port = $this->tenantManager->getConfig('ami.port', 5038);
        $username = $this->tenantManager->getConfig('ami.username');
        $secret = $this->tenantManager->getConfig('ami.secret');

        // Restore original tenant context
        if (isset($originalTenant)) {
            $this->tenantManager->setTenant($originalTenant);
        }

        if (!$host || !$username || !$secret) {
            throw new AmiException('AMI credentials are not configured');
        }

        $connection = new AmiConnection($host, $port, $username, $secret, $tenantId);
        $connection->connect();

        Log::info('AMI Connection Created', [
            'host' => $host,
            'tenant' => $tenantId,
        ]);

        return $connection;
    }

    /**
     * Disconnect a specific tenant's connection.
     */
    public function disconnect(?string $tenantId = null): void
    {
        $tenantId = $tenantId ?? $this->tenantManager->getCurrentTenantId();
        $connectionKey = $tenantId ?? 'default';

        if (isset($this->connections[$connectionKey])) {
            $this->connections[$connectionKey]->disconnect();
            unset($this->connections[$connectionKey]);
        }
    }

    /**
     * Disconnect all connections.
     */
    public function disconnectAll(): void
    {
        foreach ($this->connections as $connection) {
            $connection->disconnect();
        }

        $this->connections = [];
    }

    /**
     * Get all active connections.
     */
    public function getActiveConnections(): array
    {
        return array_filter($this->connections, fn($conn) => $conn->isConnected());
    }
}
