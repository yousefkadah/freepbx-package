<?php

namespace Yousef\FreePbx;

use Illuminate\Support\ServiceProvider;
use Yousef\FreePbx\Ami\AmiManager;
use Yousef\FreePbx\Client\FreePbxClient;
use Yousef\FreePbx\Console\Commands\SyncCdrCommand;
use Yousef\FreePbx\Console\Commands\StartAmiListenerCommand;
use Yousef\FreePbx\Dashboard\DashboardService;
use Yousef\FreePbx\Sync\CdrSyncService;
use Yousef\FreePbx\Tenancy\TenantManager;
use Yousef\FreePbx\Webhooks\WebhookDispatcher;

class FreePbxServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__.'/../config/freepbx.php',
            'freepbx'
        );

        // Register singletons
        $this->app->singleton(TenantManager::class, function ($app) {
            return new TenantManager($app);
        });

        $this->app->singleton(FreePbxClient::class, function ($app) {
            $tenantManager = $app->make(TenantManager::class);
            return new FreePbxClient($tenantManager);
        });

        $this->app->singleton(AmiManager::class, function ($app) {
            $tenantManager = $app->make(TenantManager::class);
            return new AmiManager($tenantManager);
        });

        $this->app->singleton(DashboardService::class, function ($app) {
            return new DashboardService(
                $app->make(FreePbxClient::class),
                $app->make(AmiManager::class)
            );
        });

        $this->app->singleton(WebhookDispatcher::class, function ($app) {
            return new WebhookDispatcher();
        });

        $this->app->singleton(CdrSyncService::class, function ($app) {
            $tenantManager = $app->make(TenantManager::class);
            return new CdrSyncService($tenantManager);
        });

        // Register facade alias
        $this->app->alias(FreePbxClient::class, 'freepbx');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/freepbx.php' => config_path('freepbx.php'),
        ], 'freepbx-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'freepbx-migrations');

        // Publish assets
        $this->publishes([
            __DIR__.'/../resources/js' => public_path('vendor/freepbx/js'),
            __DIR__.'/../resources/css' => public_path('vendor/freepbx/css'),
            __DIR__.'/../resources/sounds' => public_path('vendor/freepbx/sounds'),
        ], 'freepbx-assets');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/freepbx'),
        ], 'freepbx-views');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'freepbx');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncCdrCommand::class,
                StartAmiListenerCommand::class,
            ]);
        }

        // Register event listeners
        $this->registerEventListeners();

        // Register broadcasting channels
        $this->registerBroadcastingChannels();
    }

    /**
     * Register event listeners.
     */
    protected function registerEventListeners(): void
    {
        $events = $this->app->make('events');

        // Register AMI event listeners
        if (config('freepbx.events.dispatch_laravel_events', true)) {
            $events->listen(
                \Yousef\FreePbx\Events\Ami\NewChannelEvent::class,
                \Yousef\FreePbx\Listeners\DetectIncomingCall::class
            );

            $events->listen(
                \Yousef\FreePbx\Events\IncomingCallEvent::class,
                \Yousef\FreePbx\Listeners\BroadcastIncomingCall::class
            );

            $events->listen(
                \Yousef\FreePbx\Events\Ami\QueueMemberStatusEvent::class,
                \Yousef\FreePbx\Listeners\UpdateDashboardMetrics::class
            );
        }
    }

    /**
     * Register broadcasting channels.
     */
    protected function registerBroadcastingChannels(): void
    {
        if (!$this->app->bound('Illuminate\Broadcasting\BroadcastManager')) {
            return;
        }

        require __DIR__.'/../routes/channels.php';
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            TenantManager::class,
            FreePbxClient::class,
            AmiManager::class,
            DashboardService::class,
            WebhookDispatcher::class,
            CdrSyncService::class,
            'freepbx',
        ];
    }
}
