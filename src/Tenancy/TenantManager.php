<?php

namespace yousefkadah\FreePbx\Tenancy;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use yousefkadah\FreePbx\Exceptions\ConfigurationException;
use yousefkadah\FreePbx\Models\TenantConfig;

class TenantManager
{
    protected Application $app;
    protected ?string $currentTenantId = null;
    protected array $configCache = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Set the current tenant context.
     */
    public function setTenant(?string $tenantId): void
    {
        $this->currentTenantId = $tenantId;
        
        // Clear config cache when switching tenants
        $this->configCache = [];
    }

    /**
     * Get the current tenant ID.
     */
    public function getCurrentTenantId(): ?string
    {
        if ($this->currentTenantId) {
            return $this->currentTenantId;
        }

        // Auto-detect tenant if multi-tenant is enabled
        if (config('freepbx.multi_tenant.enabled')) {
            return $this->detectTenant();
        }

        return null;
    }

    /**
     * Get configuration for the current tenant.
     */
    public function getConfig(string $key, mixed $default = null): mixed
    {
        $tenantId = $this->getCurrentTenantId();

        if (!$tenantId) {
            return config("freepbx.{$key}", $default);
        }

        // Check cache first
        $cacheKey = "tenant.{$tenantId}.{$key}";
        if (isset($this->configCache[$cacheKey])) {
            return $this->configCache[$cacheKey];
        }

        // Load from database if enabled
        if (config('freepbx.multi_tenant.database_storage')) {
            $value = $this->getConfigFromDatabase($tenantId, $key);
            if ($value !== null) {
                $this->configCache[$cacheKey] = $value;
                return $value;
            }
        }

        // Fall back to default config
        return config("freepbx.{$key}", $default);
    }

    /**
     * Get all configuration for the current tenant.
     */
    public function getAllConfig(): array
    {
        $tenantId = $this->getCurrentTenantId();

        if (!$tenantId) {
            return config('freepbx');
        }

        if (config('freepbx.multi_tenant.database_storage')) {
            $tenantConfig = TenantConfig::where('tenant_id', $tenantId)->first();
            
            if ($tenantConfig) {
                return array_merge(
                    config('freepbx'),
                    $tenantConfig->config
                );
            }
        }

        return config('freepbx');
    }

    /**
     * Set configuration for a tenant.
     */
    public function setConfig(string $tenantId, array $config): void
    {
        if (!config('freepbx.multi_tenant.database_storage')) {
            throw new ConfigurationException('Database storage is not enabled for tenant configurations');
        }

        TenantConfig::updateOrCreate(
            ['tenant_id' => $tenantId],
            ['config' => $config]
        );

        // Clear cache
        $this->configCache = [];
        Cache::forget("tenant_config.{$tenantId}");
    }

    /**
     * Detect the current tenant from the request.
     */
    protected function detectTenant(): ?string
    {
        $identifier = config('freepbx.multi_tenant.identifier');

        return match ($identifier) {
            'header' => $this->detectFromHeader(),
            'subdomain' => $this->detectFromSubdomain(),
            'session' => $this->detectFromSession(),
            'custom' => $this->detectFromCustomResolver(),
            default => null,
        };
    }

    /**
     * Detect tenant from request header.
     */
    protected function detectFromHeader(): ?string
    {
        if (!$this->app->bound('request')) {
            return null;
        }

        $headerName = config('freepbx.multi_tenant.header_name', 'X-Tenant-ID');
        return $this->app->make('request')->header($headerName);
    }

    /**
     * Detect tenant from subdomain.
     */
    protected function detectFromSubdomain(): ?string
    {
        if (!$this->app->bound('request')) {
            return null;
        }

        $host = $this->app->make('request')->getHost();
        $parts = explode('.', $host);

        return count($parts) > 2 ? $parts[0] : null;
    }

    /**
     * Detect tenant from session.
     */
    protected function detectFromSession(): ?string
    {
        if (!$this->app->bound('session')) {
            return null;
        }

        return $this->app->make('session')->get('tenant_id');
    }

    /**
     * Detect tenant using custom resolver.
     */
    protected function detectFromCustomResolver(): ?string
    {
        $resolver = config('freepbx.multi_tenant.resolver');

        if (!$resolver || !class_exists($resolver)) {
            return null;
        }

        $instance = $this->app->make($resolver);
        return $instance->resolve();
    }

    /**
     * Get configuration value from database.
     */
    protected function getConfigFromDatabase(string $tenantId, string $key): mixed
    {
        $cacheKey = "tenant_config.{$tenantId}";

        $config = Cache::remember($cacheKey, 3600, function () use ($tenantId) {
            $tenantConfig = TenantConfig::where('tenant_id', $tenantId)->first();
            return $tenantConfig?->config ?? [];
        });

        return data_get($config, $key);
    }

    /**
     * Check if multi-tenancy is enabled.
     */
    public function isMultiTenantEnabled(): bool
    {
        return (bool) config('freepbx.multi_tenant.enabled', false);
    }
}
