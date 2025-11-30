# FreePBX Laravel Integrator

[![Latest Version](https://img.shields.io/packagist/v/yousef/freepbx-laravel-integrator.svg)](https://packagist.org/packages/yousef/freepbx-laravel-integrator)
[![License](https://img.shields.io/packagist/l/yousef/freepbx-laravel-integrator.svg)](https://packagist.org/packages/yousef/freepbx-laravel-integrator)

A comprehensive Laravel package for integrating FreePBX telephony features into SaaS CRM applications with full multi-tenant support.

## Features

✅ **FreePBX API Client** - Manage extensions, queues, and agents  
✅ **Click-to-Call** - Initiate calls via AMI (Asterisk Manager Interface)  
✅ **Incoming Call Popups** - Real-time notifications with contact lookup  
✅ **Real-Time Dashboard** - Monitor agents and queues with live updates  
✅ **Webhook Dispatcher** - Send events to external systems with retry logic  
✅ **CDR Database Sync** - Automatic call detail record synchronization  
✅ **Multi-Tenant Support** - Full tenant isolation for SaaS applications  

## Requirements

- PHP 8.1 or higher
- Laravel 10.x, 11.x, or 12.x
- FreePBX 15+ with API enabled
- AMI access to Asterisk
- Laravel Broadcasting configured (Pusher, Redis, etc.)

## Installation

Install the package via Composer:

```bash
composer require yousef/freepbx-laravel-integrator
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=freepbx-config
```

Publish and run migrations:

```bash
php artisan vendor:publish --tag=freepbx-migrations
php artisan migrate
```

Publish assets (optional):

```bash
php artisan vendor:publish --tag=freepbx-assets
php artisan vendor:publish --tag=freepbx-views
```

## Configuration

Configure your FreePBX connection in `.env`:

```env
# FreePBX API
FREEPBX_HOST=https://your-freepbx.com
FREEPBX_USERNAME=admin
FREEPBX_PASSWORD=your-password

# AMI Configuration
FREEPBX_AMI_HOST=your-freepbx.com
FREEPBX_AMI_PORT=5038
FREEPBX_AMI_USERNAME=admin
FREEPBX_AMI_SECRET=your-ami-secret

# Multi-Tenant (optional)
FREEPBX_MULTI_TENANT=true
FREEPBX_TENANT_IDENTIFIER=header
FREEPBX_TENANT_HEADER=X-Tenant-ID

# CDR Sync
FREEPBX_CDR_ENABLED=true
FREEPBX_CDR_DATABASE=asteriskcdrdb
FREEPBX_CDR_USERNAME=cdr_user
FREEPBX_CDR_PASSWORD=cdr_password

# Webhooks
FREEPBX_WEBHOOKS_ENABLED=true
FREEPBX_WEBHOOK_SECRET=your-webhook-secret
```

## Quick Start

### 1. Managing Extensions

```php
use Yousef\FreePbx\Facades\FreePbx;

// List all extensions
$extensions = FreePbx::extensions()->list();

// Get a specific extension
$extension = FreePbx::extensions()->get('1001');

// Create a new extension
$newExtension = FreePbx::extensions()->create([
    'extension' => '1005',
    'name' => 'John Doe',
    'secret' => 'password123',
]);

// Update an extension
FreePbx::extensions()->update('1005', [
    'name' => 'Jane Doe',
]);

// Delete an extension
FreePbx::extensions()->delete('1005');
```

### 2. Click-to-Call

```php
use Yousef\FreePbx\Ami\Actions\OriginateAction;
use Yousef\FreePbx\Ami\AmiManager;

$amiManager = app(AmiManager::class);
$connection = $amiManager->getConnection();
$originate = new OriginateAction($connection);

// Initiate a call
$originate->call(
    fromExtension: '1001',
    toNumber: '+1234567890'
);
```

Or via API endpoint:

```javascript
axios.post('/api/freepbx/call/click-to-call', {
    from_extension: '1001',
    to_number: '+1234567890'
});
```

### 3. Incoming Call Popups

Include the JavaScript in your layout:

```html
<link href="{{ asset('vendor/freepbx/css/call-popup.css') }}" rel="stylesheet">
<script src="{{ asset('vendor/freepbx/js/call-popup.js') }}"></script>

<script>
    const callPopup = new CallPopup({
        extension: '{{ auth()->user()->extension }}',
        tenantId: '{{ auth()->user()->tenant_id }}',
        soundEnabled: true,
        autoDismiss: 0, // 0 = manual dismiss
        onIncomingCall: (event) => {
            console.log('Incoming call from:', event.caller_id);
        }
    });
</script>
```

### 4. Real-Time Dashboard

Access the dashboard at `/freepbx/dashboard` or fetch data via API:

```javascript
// Get all dashboard metrics
axios.get('/api/freepbx/dashboard')
    .then(response => {
        console.log(response.data);
    });

// Get queue metrics only
axios.get('/api/freepbx/dashboard/queues');

// Get agent metrics only
axios.get('/api/freepbx/dashboard/agents');
```

### 5. CDR Synchronization

Run manual sync:

```bash
php artisan freepbx:sync-cdr
```

Sync for specific tenant:

```bash
php artisan freepbx:sync-cdr --tenant=tenant-123
```

Sync since specific date:

```bash
php artisan freepbx:sync-cdr --since="2024-01-01 00:00:00"
```

Schedule automatic sync in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('freepbx:sync-cdr')->everyFiveMinutes();
}
```

### 6. Webhooks

Dispatch webhook events:

```php
use Yousef\FreePbx\Webhooks\WebhookDispatcher;
use Yousef\FreePbx\Webhooks\WebhookEvent;

$dispatcher = app(WebhookDispatcher::class);

$event = new class('call.completed', [
    'call_id' => '12345',
    'duration' => 120,
    'from' => '1001',
    'to' => '+1234567890',
]) extends WebhookEvent {};

$dispatcher->dispatch('https://your-app.com/webhook', $event);
```

## Multi-Tenant Usage

### Setting Tenant Context

```php
use Yousef\FreePbx\Tenancy\TenantManager;

$tenantManager = app(TenantManager::class);
$tenantManager->setTenant('tenant-123');

// Now all FreePBX operations will use tenant-123's configuration
```

### Storing Tenant Configuration

```php
$tenantManager->setConfig('tenant-123', [
    'api' => [
        'host' => 'https://tenant123-freepbx.com',
        'username' => 'admin',
        'password' => 'password',
    ],
    'ami' => [
        'host' => 'tenant123-freepbx.com',
        'username' => 'admin',
        'secret' => 'secret',
    ],
]);
```

## AMI Event Listener

Start the AMI listener to receive real-time events:

```bash
php artisan freepbx:ami-listen
```

For specific tenant:

```bash
php artisan freepbx:ami-listen --tenant=tenant-123
```

Run as a daemon with Supervisor:

```ini
[program:freepbx-ami-listener]
command=php /path/to/artisan freepbx:ami-listen
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/freepbx-ami.log
```

## Events

The package dispatches several Laravel events:

- `Yousef\FreePbx\Events\IncomingCallEvent` - When an incoming call is detected
- `Yousef\FreePbx\Events\Ami\NewchannelEvent` - When a new channel is created
- `Yousef\FreePbx\Events\Ami\QueueMemberStatusEvent` - When queue member status changes
- `Yousef\FreePbx\Events\Ami\GenericAmiEvent` - For all other AMI events

Listen to events in your `EventServiceProvider`:

```php
protected $listen = [
    \Yousef\FreePbx\Events\IncomingCallEvent::class => [
        \App\Listeners\LogIncomingCall::class,
    ],
];
```

## Testing

```bash
composer test
```

## Security

If you discover any security-related issues, please email yousef@example.com instead of using the issue tracker.

## Credits

- [Yousef Kadah](https://github.com/yousefkadah)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
