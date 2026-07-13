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

## Quick Start

```bash
# 1. Install dependencies, set up .env, generate key, migrate, and build assets
composer setup

# 2. Start the full dev environment (server, queue, logs, Vite)
composer dev
```

The `composer setup` script runs `composer install`, copies `.env.example` to `.env`, generates the app key, runs migrations, installs npm packages, and builds the front-end assets.

The `composer dev` script runs the PHP server, queue worker, log viewer (Pail), and Vite concurrently.

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

## Running with Docker

A Docker setup with PHP-FPM, Nginx, and PostgreSQL is included:

```bash
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
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

## Project Structure

The application follows a thin-controller, action-based architecture:

```
app/
  Actions/      # Business logic — one action per use case
  Casts/        # Custom Eloquent casts (e.g. money)
  Enums/        # Backed enums (TransactionType, TransactionMethod)
  Filament/     # Admin panel: Resources, Pages, Widgets
  Http/         # Controllers, FormRequests, Resources, Middleware
  Jobs/         # Queued work (e.g. recurring expense generation)
  Models/       # Account, Category, Transaction, RecurringExpense, User
  ValueObjects/ # Domain value objects (e.g. Money)
```

Monetary values are stored as integer cents and converted via a money cast. Business logic lives in Action classes, not controllers or models.

## Internationalization

Translations live in `lang/en.json` and `lang/pt_BR.json`. The default and fallback locales are configured via `APP_LOCALE` and `APP_FALLBACK_LOCALE` in `.env`.
