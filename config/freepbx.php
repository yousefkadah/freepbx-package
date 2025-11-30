<?php

return [
    /*
    |--------------------------------------------------------------------------
    | FreePBX API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your FreePBX API connection. For multi-tenant applications,
    | these serve as default values. Tenant-specific configurations will
    | override these defaults.
    |
    */

    'api' => [
        'host' => env('FREEPBX_HOST', 'https://freepbx.example.com'),
        'username' => env('FREEPBX_USERNAME'),
        'password' => env('FREEPBX_PASSWORD'),
        'timeout' => env('FREEPBX_TIMEOUT', 30),
        'verify_ssl' => env('FREEPBX_VERIFY_SSL', true),
        'retry' => [
            'times' => env('FREEPBX_RETRY_TIMES', 3),
            'sleep' => env('FREEPBX_RETRY_SLEEP', 100), // milliseconds
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AMI (Asterisk Manager Interface) Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the AMI connection for real-time events and click-to-call.
    |
    */

    'ami' => [
        'host' => env('FREEPBX_AMI_HOST', 'freepbx.example.com'),
        'port' => env('FREEPBX_AMI_PORT', 5038),
        'username' => env('FREEPBX_AMI_USERNAME'),
        'secret' => env('FREEPBX_AMI_SECRET'),
        'connect_timeout' => env('FREEPBX_AMI_CONNECT_TIMEOUT', 10),
        'read_timeout' => env('FREEPBX_AMI_READ_TIMEOUT', 10),
        'ping_interval' => env('FREEPBX_AMI_PING_INTERVAL', 60), // seconds
        'reconnect' => [
            'enabled' => env('FREEPBX_AMI_RECONNECT', true),
            'max_attempts' => env('FREEPBX_AMI_RECONNECT_ATTEMPTS', 5),
            'delay' => env('FREEPBX_AMI_RECONNECT_DELAY', 5), // seconds
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenant Configuration
    |--------------------------------------------------------------------------
    |
    | Enable multi-tenant support and configure how tenant context is resolved.
    |
    */

    'multi_tenant' => [
        'enabled' => env('FREEPBX_MULTI_TENANT', false),
        
        // How to identify the current tenant
        // Options: 'header', 'subdomain', 'session', 'custom'
        'identifier' => env('FREEPBX_TENANT_IDENTIFIER', 'header'),
        
        // Header name when using 'header' identifier
        'header_name' => env('FREEPBX_TENANT_HEADER', 'X-Tenant-ID'),
        
        // Custom resolver class (must implement TenantResolverInterface)
        'resolver' => null,
        
        // Store tenant configs in database
        'database_storage' => env('FREEPBX_TENANT_DB_STORAGE', true),
        
        // Encrypt sensitive tenant data
        'encrypt_credentials' => env('FREEPBX_TENANT_ENCRYPT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Call Popup Configuration
    |--------------------------------------------------------------------------
    |
    | Configure incoming call popup behavior and contact matching.
    |
    */

    'call_popup' => [
        'enabled' => env('FREEPBX_CALL_POPUP_ENABLED', true),
        
        // Broadcasting channel for call events
        'channel' => 'freepbx.calls',
        
        // Contact lookup configuration
        'contact_lookup' => [
            'enabled' => true,
            'model' => env('FREEPBX_CONTACT_MODEL', 'App\\Models\\Contact'),
            'phone_field' => env('FREEPBX_CONTACT_PHONE_FIELD', 'phone'),
            'search_fields' => ['phone', 'mobile', 'work_phone'],
        ],
        
        // Play notification sound
        'notification_sound' => env('FREEPBX_NOTIFICATION_SOUND', true),
        
        // Auto-dismiss popup after seconds (0 = manual dismiss)
        'auto_dismiss' => env('FREEPBX_AUTO_DISMISS', 0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the real-time agents and queues dashboard.
    |
    */

    'dashboard' => [
        'enabled' => env('FREEPBX_DASHBOARD_ENABLED', true),
        
        // Refresh interval in seconds
        'refresh_interval' => env('FREEPBX_DASHBOARD_REFRESH', 5),
        
        // Cache metrics for performance
        'cache' => [
            'enabled' => env('FREEPBX_DASHBOARD_CACHE', true),
            'ttl' => env('FREEPBX_DASHBOARD_CACHE_TTL', 30), // seconds
        ],
        
        // Queues to monitor (empty = all queues)
        'monitored_queues' => env('FREEPBX_MONITORED_QUEUES', ''),
        
        // Broadcasting channel for live updates
        'channel' => 'freepbx.dashboard',
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configure webhook dispatching for FreePBX events.
    |
    */

    'webhooks' => [
        'enabled' => env('FREEPBX_WEBHOOKS_ENABLED', true),
        
        // Queue webhooks for async delivery
        'queue' => env('FREEPBX_WEBHOOK_QUEUE', 'default'),
        
        // Retry configuration
        'retry' => [
            'times' => env('FREEPBX_WEBHOOK_RETRY_TIMES', 3),
            'backoff' => env('FREEPBX_WEBHOOK_RETRY_BACKOFF', 'exponential'), // linear or exponential
        ],
        
        // Timeout for webhook requests
        'timeout' => env('FREEPBX_WEBHOOK_TIMEOUT', 10),
        
        // Sign webhook payloads
        'signing' => [
            'enabled' => env('FREEPBX_WEBHOOK_SIGNING', true),
            'secret' => env('FREEPBX_WEBHOOK_SECRET'),
            'header' => 'X-FreePBX-Signature',
        ],
        
        // Log all webhook attempts
        'logging' => [
            'enabled' => env('FREEPBX_WEBHOOK_LOGGING', true),
            'retention_days' => env('FREEPBX_WEBHOOK_LOG_RETENTION', 30),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CDR Sync Configuration
    |--------------------------------------------------------------------------
    |
    | Configure Call Detail Record synchronization from FreePBX.
    |
    */

    'cdr' => [
        'enabled' => env('FREEPBX_CDR_ENABLED', true),
        
        // CDR database connection (separate from main FreePBX)
        'connection' => [
            'host' => env('FREEPBX_CDR_HOST', env('FREEPBX_HOST')),
            'port' => env('FREEPBX_CDR_PORT', 3306),
            'database' => env('FREEPBX_CDR_DATABASE', 'asteriskcdrdb'),
            'username' => env('FREEPBX_CDR_USERNAME'),
            'password' => env('FREEPBX_CDR_PASSWORD'),
        ],
        
        // Sync configuration
        'sync' => [
            'enabled' => env('FREEPBX_CDR_SYNC_ENABLED', true),
            'schedule' => env('FREEPBX_CDR_SYNC_SCHEDULE', '*/5 * * * *'), // every 5 minutes
            'batch_size' => env('FREEPBX_CDR_SYNC_BATCH', 1000),
            'incremental' => true, // Only sync new records
        ],
        
        // Link CDR to CRM records
        'crm_linking' => [
            'enabled' => true,
            'contact_model' => env('FREEPBX_CONTACT_MODEL', 'App\\Models\\Contact'),
            'user_model' => env('FREEPBX_USER_MODEL', 'App\\Models\\User'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Events Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which FreePBX/AMI events to listen for and process.
    |
    */

    'events' => [
        // AMI events to listen for
        'ami_events' => [
            'Newchannel',
            'Newstate',
            'Hangup',
            'QueueMemberStatus',
            'QueueMemberAdded',
            'QueueMemberRemoved',
            'QueueMemberPause',
            'Join',
            'Leave',
            'AgentCalled',
            'AgentConnect',
            'AgentComplete',
        ],
        
        // Dispatch Laravel events for AMI events
        'dispatch_laravel_events' => env('FREEPBX_DISPATCH_EVENTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Click-to-Call Configuration
    |--------------------------------------------------------------------------
    |
    | Configure click-to-call functionality.
    |
    */

    'click_to_call' => [
        'enabled' => env('FREEPBX_CLICK_TO_CALL_ENABLED', true),
        
        // Default context for outgoing calls
        'context' => env('FREEPBX_CALL_CONTEXT', 'from-internal'),
        
        // Default priority
        'priority' => env('FREEPBX_CALL_PRIORITY', 1),
        
        // Timeout for call origination
        'timeout' => env('FREEPBX_CALL_TIMEOUT', 30000), // milliseconds
        
        // Caller ID for originated calls
        'caller_id' => env('FREEPBX_CALLER_ID'),
    ],
];
