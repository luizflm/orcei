# Project: Orcei - System Prompt

You are working on a Laravel 13 application. This is a production
backend/frontend service handling a personal financial management based on transactions. The application runs
inside Docker containers.

## Critical Rules
All comments, commit messages, and documentation must be in English only.
Do not generate code with comments in any other language.
Always run the health-check skill after completing any implementation.
Never commit code that fails Pint, Pest, or Larastan checks.
If you are unsure about an architectural decision, stop and ask.
Do not guess. Do not invent new patterns that are not already in
the codebase.

## Imports
@rules/coding-style.md
@rules/architecture.md
@rules/testing.md
@rules/git-workflow.md

## Environment
The application runs in Docker. All artisan, composer, and pest
commands must be executed inside the app container:
```bash
docker exec app php artisan [command]
docker exec app ./vendor/bin/pint [args]
docker exec app php artisan test [args]
docker exec app ./vendor/bin/phpstan analyse
```

## Domain Overview
This application helps individual users track their personal finances by recording and categorizing financial transactions. Each user manages their own isolated data — there is no shared or multi-tenant data model.

The central entity is the Transaction, which represents a single financial movement: money coming in (income) or going out (expense). Every transaction belongs to a User, has an amount, a date, a type (income or expense), and is assigned to a Category.

Categories organize transactions into groups (e.g., Food, Salary, Rent, Transport). They belong to a User, allowing personalized categorization. A Category has a type that matches the transaction type it groups.

Users authenticate via FilamentPHP's built-in authentication and can only access their own data. Authorization is enforced at the policy layer for every resource.

The primary use cases are: recording new transactions, listing and filtering transactions by date range or category, and summarizing totals per category or period to give the user an overview of their financial health.

## Key Conventions
- Single-action controllers for non-CRUD endpoints
- Resource controllers for standard CRUD
- FormRequests for all validation, no inline rules in controllers
- Actions for business logic, injected via parameter
- Enums for all status and type fields
- Events + Listeners for side effects (notifications, logging)
- Jobs for anything that takes more than 200ms
- Policies for all authorization checks

## Database
Boost MCP has access to the schema. Use it to inspect tables. Migrations are the source of truth for schema changes. Always create a migration for any database change, never edit existing migrations that have been run. Use Eloquent factories with explicit states for test data.
