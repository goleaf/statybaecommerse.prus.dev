# System Requirements

To reduce environment-specific surprises and ensure the platform runs consistently across contributors, provision the following toolchain and services.

## PHP Runtime
- **Version:** PHP 8.2 or newer (Laravel 12 baseline).
- **Required extensions:** `intl`, `mbstring`, `fileinfo`, and one of `gd` or `imagick` for image processing.
- **Recommended extensions:** `sqlite3` for the default local database, plus `openssl`, `pdo`, and `curl` (included with most PHP distributions).

## Node.js Tooling
- **Node.js:** 20.11.0 or newer (ESM-compatible runtime).
- **npm:** 10.0.0 or newer (bundled with Node.js 20.11+).

## Databases
- **SQLite:** Version 3.39 or newer for local development out of the box.
- **MySQL / MariaDB:** MySQL 8.0 (or MariaDB 10.6) when running the application against a MySQL-compatible server.
- **PostgreSQL:** Version 15 or newer for production parity with managed environments.

Keeping these versions aligned across machines helps drive fewer “works on my machine” issues.
