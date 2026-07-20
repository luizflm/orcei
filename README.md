# Orcei

A personal finance management application built with Laravel and Filament. It lets you track accounts, categorize income and expenses, schedule recurring expenses, and visualize your financial activity through an admin dashboard with charts and stats.

## Features

- **Accounts** — manage multiple accounts with balances.
- **Transactions** — record income and expenses with a type, method, category, and account.
- **Categories** — organize transactions into custom categories.
- **Recurring expenses** — schedule expenses that are generated automatically over time.
- **Dashboard** — overview widgets and charts (monthly expenses, expenses by category, stats overview).
- **Authentication** — self-registration and profile editing via the Filament panel.
- **Internationalization** — English and Brazilian Portuguese (`en`, `pt_BR`).

## Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.4 |
| Framework | Laravel 13 |
| Admin / UI | Filament 5 |
| Queues | Redis 7 |
| Styling | Tailwind CSS 4 |
| Database | PostgreSQL (Docker) |
| Testing | Pest 4 |
| Static analysis | Larastan 3 |
| Code style | Laravel Pint |

## Requirements

- PHP 8.4
- Composer
- Node.js & npm
- A database (SQLite works out of the box; PostgreSQL is available via Docker)
- Redis 7 (used as the queue driver — required to process queued jobs, such as recurring expense generation)

## Quick Start

```bash
# 1. Install dependencies, set up .env, generate key, migrate, and build assets
composer setup

# 2. Start the full dev environment (server, queue, logs, Vite)
composer dev
```

The `composer setup` script runs `composer install`, copies `.env.example` to `.env`, generates the app key, runs migrations, installs npm packages, and builds the front-end assets.

The `composer dev` script runs the PHP server, queue worker, log viewer (Pail), and Vite concurrently.

> **Queue driver:** the app uses Redis 7 for queues. Make sure a Redis server is running and reachable (`REDIS_HOST`, `REDIS_PORT`, etc. in `.env`) before starting the queue worker (`composer dev` or `php artisan queue:work`), otherwise queued jobs — such as recurring expense generation — won't be processed.

The application will be available at `http://localhost:8000`.

### Manual setup

If you prefer to run the steps yourself:

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # only when using SQLite
php artisan migrate
npm install
npm run build
php artisan serve
```

### First access

Registration is enabled on the admin panel, so you can create your account directly at `http://localhost:8000/register`.

If you'd rather try the app with sample data instead of creating a new account, run `php artisan db:seed` and log in with the default admin user:

- **Email:** `admin@admin.com`
- **Password:** `admin`

## Running with Docker

A Docker setup with PHP-FPM, Nginx, PostgreSQL, and Redis is included:

```bash
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed # (optional)
```

With Docker, set the database connection in `.env`:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=orcei
DB_USERNAME=root
DB_PASSWORD=root
```

And the queue connection to use the Redis service:

```dotenv
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

The app is served at `http://localhost:8000`.

## Testing

This project uses [Pest](https://pestphp.com). Tests run against a refreshed database automatically (configured in `tests/Pest.php`).

```bash
# Run the full test suite
composer test

# Or directly
php artisan test --compact

# Filter a single test
php artisan test --compact --filter=testName
```

Tests are organized under `tests/Unit` (models, actions, casts, enums, value objects) and `tests/Feature` (controllers, Filament, jobs, console commands).

## Code Quality

```bash
# Format code with Pint
vendor/bin/pint

# Static analysis with Larastan
vendor/bin/phpstan analyse
```

## Internationalization

Translations live in `lang/en.json` and `lang/pt_BR.json`. The default and fallback locales are configured via `APP_LOCALE` and `APP_FALLBACK_LOCALE` in `.env`.