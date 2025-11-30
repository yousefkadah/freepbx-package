<?php

namespace yousefkadah\FreePbx\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use yousefkadah\FreePbx\FreePbxServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Additional setup
    }

    protected function getPackageProviders($app)
    {
        return [
            FreePbxServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Setup FreePBX test configuration
        $app['config']->set('freepbx.api.host', 'https://test-freepbx.com');
        $app['config']->set('freepbx.api.username', 'test');
        $app['config']->set('freepbx.api.password', 'test');
    }
}
