# xArgo Licensing Server

Internal licensing backend and admin panel for multiple Electron applications.

## Purpose

This project provides:

- a public licensing API for Electron apps
- an internal admin panel for support and operations
- license management for permanent, subscription, and trial plans
- device-bound activation tracking with non-destructive anti-clone handling
- shared-hosting-friendly backend behavior from the start

## Stack

- Laravel 13
- MySQL
- Inertia
- React + TypeScript
- Vite
- Tailwind CSS

## Local setup

Primary local development guide:

- [docs/laragon-local-development.md](docs/laragon-local-development.md)
- API contract reference: [docs/api-contract.md](docs/api-contract.md)
- Shared hosting deployment notes: [docs/shared-hosting-deployment.md](docs/shared-hosting-deployment.md)

Schema and deployment notes:

- [docs/deployment-schema.md](docs/deployment-schema.md)

Quick start:

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan serve
npm run dev
```

## Environment variables

Core local variables:

```env
APP_NAME=xArgo
APP_ENV=local
APP_URL=http://xargo.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=xargo
DB_USERNAME=xargo
DB_PASSWORD=xargo

QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database
MAIL_MAILER=log
```

Licensing-specific variables:

```env
AUTH_INVITATION_EXPIRE_HOURS=72

LICENSING_LICENSE_KEY_PREFIX=XARGO
LICENSING_PUBLIC_KEY_PREFIX=lic_
LICENSING_DEFAULT_MAX_DEVICES=1
LICENSING_HEARTBEAT_INTERVAL_SECONDS=3600
LICENSING_STALE_DEVICE_THRESHOLD_SECONDS=3600
LICENSING_HEARTBEAT_RETENTION_DAYS=3
LICENSING_DEVICE_MISMATCH_GRACE_PERIOD_SECONDS=300
LICENSING_DEVICE_MISMATCH_BLOCK_REASON_CODE=device_mismatch
LICENSING_API_RATE_LIMIT_PER_MINUTE=120
LICENSING_EXPIRY_WARNING_DAYS=7
LICENSING_TRIAL_ENDING_WARNING_DAYS=3
LICENSING_DEVICE_MISMATCH_ALERTS_ENABLED=true
LICENSING_REBIND_NOTIFICATIONS_ENABLED=true
```

Infrastructure defaults are intentionally shared-hosting-friendly:

```env
INFRASTRUCTURE_SHARED_HOSTING_COMPATIBLE=true
INFRASTRUCTURE_SCHEDULER_DRIVER=cron
INFRASTRUCTURE_REQUIRES_SUPERVISOR=false
INFRASTRUCTURE_REQUIRES_LONG_RUNNING_PROCESSES=false
INFRASTRUCTURE_QUEUE_FALLBACK_CONNECTION=sync
INFRASTRUCTURE_REQUIRES_WEBSOCKETS=false
INFRASTRUCTURE_REQUIRES_REDIS=false
```

## Migrations

The backend schema is expected to be fully bootstrapped from migrations.

Run standard migration:

```bash
php artisan migrate --force
```

Run a clean rebuild:

```bash
php artisan migrate:fresh --force
```

Current core domain tables include:

- `apps`
- `license_plans`
- `licenses`
- `license_entitlements`
- `license_activations`
- `license_heartbeats`
- `admin_audit_logs`
- `user_invitations`

Supporting Laravel tables include users, sessions, cache, jobs, and password reset tokens.

## Testing and TDD

The backend follows TDD-oriented module work:

- feature tests for business flows through Laravel
- unit tests for service and policy logic
- thin controllers with business logic in services/actions

Run all tests:

```bash
php artisan test
```

Useful checks:

```bash
npx tsc --noEmit
php artisan route:list
```

The test suite is configured for a separate MySQL database (`xargo_test`) in `phpunit.xml`.

## Auth model

Authentication is internal-team only:

- email + password login
- password reset
- no public self-registration
- invitation-based onboarding for internal users

Invited users receive an expiring invitation link and set their password during activation.

## Roles

Current internal roles:

- `super_admin`
- `support`
- `read_only`

High-level access model:

- `super_admin`: full admin write access
- `support`: operational read access plus selected actions such as invitations and CSV export
- `read_only`: read access to internal areas without privileged mutations

## Admin panel overview

The internal admin panel currently covers:

- dashboard operational summaries
- internal user management
- invitations
- app and plan management
- license management
- activation visibility and manual rebind
- heartbeat visibility
- audit log visibility
- internal search by license key, customer email, app ID, machine ID, and installation ID where relevant

## API overview

Public API routes:

- `POST /api/v1/licenses/activate`
- `POST /api/v1/licenses/validate`
- `POST /api/v1/licenses/heartbeat`
- `POST /api/v1/licenses/rebind/request`
- `POST /api/v1/licenses/rebind/confirm`

Common payload fields across licensing flows:

- `licenseKey`
- `activationToken` where applicable
- `appId`
- `appVersion`
- `machineId`
- `installationId`

Behavior notes:

- responses use a standardized success/error envelope
- licensing endpoints are rate-limited
- core licensing logic runs in normal request-response mode

## Heartbeat retention behavior

Heartbeat design:

- expected interval: 1 hour
- activation becomes stale/inactive after 1 hour without heartbeat logic coverage
- heartbeat records are retained for the last 3 days

Cleanup is handled by a cron-friendly scheduled command, not by a long-running worker.

## Non-destructive anti-clone rule

The anti-clone behavior is explicit and non-destructive:

- the bound `machineId + installationId` pair remains the legitimate activation
- a copied install on a different device receives `device_mismatch`
- mismatch receives a short grace window, then blocks
- the original activation is not automatically invalidated
- the system does not auto-rebind to the new device

## Manual rebind rule

Rebinding is intentional and manual:

- support can review activation context
- rebind is confirmed through internal admin flow
- rebind changes the bound device only when explicitly performed by staff
- rebind actions are audited
- public API rebind endpoints do not automatically take over an activation

## Shared-hosting compatibility

The backend is designed to remain deployable on shared hosting:

- no Redis requirement
- no Supervisor requirement
- no WebSockets requirement
- no long-running workers required for core flows
- queue usage is compatible with database queue or sync fallback
- scheduler tasks are cron-friendly
- emails and cleanup are auxiliary behavior, not required for core license validation

Business-critical licensing operations continue to work in standard synchronous request-response mode.
