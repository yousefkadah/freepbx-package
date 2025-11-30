# Changelog

All notable changes to `freepbx-laravel-integrator` will be documented in this file.

## [1.0.0] - 2024-01-01

### Added
- Initial release
- FreePBX API client with extension and queue management
- AMI (Asterisk Manager Interface) integration
- Click-to-call functionality via AMI
- Real-time incoming call popups with contact lookup
- Live agents and queues dashboard
- Webhook dispatcher with retry logic and signing
- CDR (Call Detail Records) database synchronization
- Multi-tenant support with multiple identification strategies
- Encrypted tenant configuration storage
- Broadcasting channels for real-time updates
- Artisan commands for CDR sync and AMI listener
- Comprehensive configuration file
- Database migrations for tenant configs, CDR, and webhook logs
- Frontend assets (JavaScript, CSS) for call popups
- Beautiful dashboard view with Tailwind CSS
- Event-driven architecture with Laravel events
- Middleware for automatic tenant identification
- Test infrastructure with PHPUnit and Orchestra Testbench
- Complete documentation and usage examples
- Support for Laravel 10.x, 11.x, and 12.x
- Support for PHP 8.1, 8.2, and 8.3

### Features
- **API Client**: RESTful wrapper for FreePBX API with automatic retry
- **AMI Integration**: Async socket connections using ReactPHP
- **Call Popups**: WebSocket-based real-time notifications
- **Dashboard**: Live metrics with caching and auto-refresh
- **Webhooks**: Queued delivery with exponential backoff
- **CDR Sync**: Incremental sync with CRM linking
- **Multi-Tenancy**: Complete tenant isolation and configuration

### Dependencies
- guzzlehttp/guzzle: ^7.5
- react/socket: ^1.14
- react/event-loop: ^1.4
- react/promise: ^3.0

[1.0.0]: https://github.com/yousef/freepbx-laravel-integrator/releases/tag/v1.0.0
