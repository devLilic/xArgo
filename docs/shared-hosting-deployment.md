# Shared Hosting Deployment Notes

## Assumptions

This backend is intended to remain deployable on typical PHP shared hosting with:

- PHP 8.3 support
- MySQL access
- normal Laravel web requests
- cron access

The architecture does not require:

- Redis
- Supervisor
- WebSockets
- long-running queue workers

## Scheduler and cron

Laravel scheduler support is expected through a standard cron entry:

```bash
php /path/to/project/artisan schedule:run
```

Recommended cron frequency:

- every minute

The scheduler currently drives auxiliary tasks such as:

- heartbeat cleanup
- scheduled notification sending

Core licensing validation and activation do not depend on scheduler execution.

## Queue strategy

Preferred queue mode on shared hosting:

- `QUEUE_CONNECTION=database`

Safe fallback when database queue processing is not available:

- `INFRASTRUCTURE_QUEUE_FALLBACK_CONNECTION=sync`

Operational rule:

- business-critical licensing flows must still work in normal request-response mode
- queued work is for auxiliary behavior such as email dispatch

If the host cannot support practical database queue processing, `sync` remains an acceptable fallback for non-critical side effects.

## Mail configuration

Expected mail setup depends on the host:

- SMTP is preferred when the host provides stable outbound mail
- `MAIL_MAILER=log` is acceptable for smoke testing or pre-production verification

Operational note:

- email success is not required for core activation, validation, heartbeat, or rebind decisions
- mail is an auxiliary behavior and should not block licensing correctness

## Migration and deploy notes

Recommended deploy sequence:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

Before first live deploy:

- verify the production database exists
- verify `.env` matches the hosting database and mail credentials
- verify the cron entry is active
- verify storage and cache directories are writable

Schema expectations are documented in:

- [deployment-schema.md](deployment-schema.md)
