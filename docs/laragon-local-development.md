# Laragon Local Development

## Assumptions

This project is intended to run locally with:

- Laragon on Windows
- PHP 8.3
- MySQL from Laragon
- Node.js and npm available in the shell
- Project located under a Laragon web root such as `C:\laragon\www\xArgo`

The backend is designed to stay compatible with later shared-hosting deployment, so local development should also avoid depending on Redis, Supervisor, WebSockets, or long-running workers.

## 1. Create the local database

Using Laragon MySQL, create a database for local development:

- database name: `xargo`
- username: `xargo`
- password: `xargo`

If you prefer different credentials locally, update `.env` to match.

## 2. Install dependencies

From the project root:

```bash
composer install
npm install
```

## 3. Configure `.env`

Copy `.env.example` if `.env` does not exist:

```bash
copy .env.example .env
php artisan key:generate
```

Set the local database and app URL values in `.env`:

```env
APP_NAME=xArgo
APP_ENV=local
APP_DEBUG=true
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

Notes:

- `MAIL_MAILER=log` is enough for normal local backend work.
- `QUEUE_CONNECTION=database` matches the intended shared-hosting-friendly deployment path.
- If you want the app to be reachable from Laragon as `http://xargo.test`, use Laragon auto virtual hosts and make sure the project folder name or configured host matches.

## 4. Run migrations

Bootstrap the database:

```bash
php artisan migrate --force
```

If you need a clean local reset:

```bash
php artisan migrate:fresh --force
```

## 5. Seeding

There is currently no required seed data for normal local development.

Use factories in tests, or add explicit seed data later if a module introduces a real local bootstrap requirement.

## 6. Run the backend and frontend

Recommended split terminal workflow:

Terminal 1:

```bash
php artisan serve
```

Terminal 2:

```bash
npm run dev
```

Optional log viewer:

```bash
php artisan pail --timeout=0
```

The combined `composer dev` script is also available:

```bash
composer dev
```

## 7. Frontend assets for non-dev mode

To verify production asset output locally:

```bash
npm run build
```

## 8. Running tests

Tests are configured to use a separate MySQL database from `phpunit.xml`:

- database name: `xargo_test`
- username: `xargo`
- password: `xargo`

Create that database in Laragon before running the test suite.

Run all tests:

```bash
php artisan test
```

Run a focused subset:

```bash
php artisan test tests/Feature/Admin
php artisan test tests/Feature/Api
```

## 9. Useful local checks

Backend routes:

```bash
php artisan route:list
```

TypeScript check:

```bash
npx tsc --noEmit
```

Production-style migration reset against the current local database:

```bash
php artisan migrate:fresh --force
```

## 10. Local workflow notes

- Core licensing flows are request-response first and should work without a running queue worker.
- Scheduler tasks are cron-friendly; local testing can call commands directly instead of relying on a daemon.
- If you want to test queued email behavior exactly, use the database queue and process jobs manually. For most normal development, the current flows remain usable without that extra setup.
