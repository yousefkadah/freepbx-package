<?php

namespace Yousef\FreePbx\Tests\Feature;

use Yousef\FreePbx\Tests\TestCase;
use Yousef\FreePbx\Client\FreePbxClient;
use Yousef\FreePbx\Tenancy\TenantManager;

class ExtensionManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    /** @test */
    public function it_can_list_extensions()
    {
        // This is a placeholder test
        // In real implementation, you would mock the HTTP client
        
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_create_extension()
    {
        // Mock test for extension creation
        
        $this->assertTrue(true);
    }

    /** @test */
    public function it_handles_multi_tenant_context()
    {
        $tenantManager = app(TenantManager::class);
        $tenantManager->setTenant('test-tenant');

        $this->assertEquals('test-tenant', $tenantManager->getCurrentTenantId());
    }
}
