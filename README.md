# Project Setup and common commands

## Prerequisites

-   **PHP** >= 8.x
-   **Composer**
-   **Node / NPM** (only if front-end assets are used)
-   **Database** configured in `.env` (for tests use SQLite in-memory or a test database)

## Install dependencies

```bash
composer install
cp .env.example .env
php artisan key:generate
# configure DB in .env
```

## Run migrations

```bash
php artisan migrate
```

## Import CSV (production)

```bash
php artisan products:import storage/app/stock.csv
```

## Import CSV (test mode — no DB writes)

```bash
php artisan products:import storage/app/stock.csv --test
```

## Run tests

-   Run the full test suite (Laravel test runner):

```bash
php artisan test
```

-   Run a single test file:

```bash
php artisan test tests/Unit/ProductImporterServiceTest.php
```

-   Direct PHPUnit (alternative):

```bash
vendor/bin/phpunit
# or on Windows PowerShell
vendor\bin\phpunit
```

If you want an in-memory DB for tests, set this in `phpunit.xml`:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

## Test data fixtures

-   Feature tests create CSV fixtures at `storage/app/imports` during execution and clean them up automatically.
-   To run the importer manually, place your CSV at `storage/app/stock.csv` or pass another path to the command.

## Useful notes

-   Command signature: `products:import {path} {--test}`. `path` accepts absolute paths or `storage_path` relative paths.
-   The importer applies business rules (min stock, cost range, skip duplicates) and logs a summary to the application log.
-   For CI, ensure migrations run before tests and that the test DB is writable.

## License

This project is open-sourced under the **MIT License**.
