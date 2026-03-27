# Deployment Schema Notes

## Migration status

The licensing server schema is expected to be bootstrapped entirely from Laravel migrations. No manual table creation or manual index creation is required.

Run order assumptions:

- Core Laravel tables are created first: `users`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`
- Domain tables are then created in dependency order:
  - `user_invitations`
  - `apps`
  - `license_plans`
  - `licenses`
  - `license_entitlements`
  - `license_activations`
  - `license_heartbeats`
  - `admin_audit_logs`

The audit log table is produced by creating `audit_logs` and then renaming it to `admin_audit_logs` in a later migration. A clean deployment runs both migrations in sequence.

## Domain tables

Required business tables:

- `apps`
- `license_plans`
- `licenses`
- `license_entitlements`
- `license_activations`
- `license_heartbeats`
- `admin_audit_logs`
- `user_invitations`

Supporting platform tables:

- `users`
- `password_reset_tokens`
- `sessions`
- `cache`
- `cache_locks`
- `jobs`
- `job_batches`
- `failed_jobs`

## Index and constraint expectations

The schema intentionally includes:

- unique external identifiers:
  - `apps.slug`
  - `apps.app_id`
  - `licenses.public_key`
  - `licenses.license_key`
  - `license_activations.activation_id`
- foreign keys for all core domain relationships
- soft deletes on:
  - `apps`
  - `license_plans`
  - `licenses`
  - `license_activations`
- search and operations indexes for:
  - license lookup by key, status, customer email, expiry window
  - activation lookup by machine ID, installation ID, token hash, status, mismatch state
  - heartbeat pruning and activation activity review
  - audit filtering by actor, action, entity, and created date

## Deployment assumptions

- MySQL is the target database engine for production and local Laragon development.
- Business-critical licensing flows must continue to work in normal request-response mode.
- Database-backed queues and cron-driven scheduling are supported, but the schema does not require Redis, Supervisor, or long-running workers.
- Migrations should be executed with `php artisan migrate --force` on the real server.
- Clean-database verification should use `php artisan migrate:fresh --force` against a non-production database before release.
