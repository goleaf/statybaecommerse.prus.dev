# Local Setup Guide

This guide walks you from a fresh clone to a running Statybos e-commerce instance in under ten minutes.

## 1. Supported operating systems

The project is routinely developed on:

- **macOS 13+** with Homebrew-managed PHP/Node toolchains.
- **Ubuntu 22.04+** or other modern Linux distributions (WSL2 works great on Windows).

> ℹ️ Windows users should install [Windows Subsystem for Linux](https://learn.microsoft.com/windows/wsl/install) and follow the Linux instructions.

## 2. Required tooling

| Tool            | Version      | Notes                                                           |
| --------------- | ------------ | --------------------------------------------------------------- |
| PHP             | 8.2 or newer | Ensure `ext-sqlite3`, `ext-fileinfo`, and `ext-gd` are enabled. |
| Composer        | 2.6 or newer | Ships with the PHP installer on most platforms.                 |
| Node.js         | 20.x LTS     | Installs with npm 10+, required for Vite and frontend builds.   |
| npm             | 10.x         | Bundled with Node 20.                                           |
| SQLite          | 3.x          | Default development database backend.                           |
| Make (optional) | Latest       | Simplifies the bootstrap commands via provided targets.         |

Optional but helpful: Redis (for queue experiments) and pnpm (mirrors npm commands).

## 3. Clone & install dependencies

```bash
# 1. Clone the repository
git clone https://github.com/prus-dev/statybaecommerse.prus.dev.git
cd statybaecommerse.prus.dev

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install
```

> ✅ Running `npm install` also refreshes the Husky hook shim so older clones keep sourcing the modern `_/h` helper. Re-run `npm run prepare` if hooks stop firing—it reruns the install step and regenerates `.husky/_/husky.sh` via `scripts/ensure-husky-shim.mjs`.

If you prefer the automated route, run `make setup` after cloning—it performs all three steps, copies the `.env`, and prepares the SQLite database.

## 4. Configure the environment

Copy the provided template and set the app key:

```bash
cp .env.example .env
php artisan key:generate
```

Key environment toggles from `.env.example`:

| Variable                             | Purpose                                                             | Default                    |
| ------------------------------------ | ------------------------------------------------------------------- | -------------------------- |
| `APP_ENV` / `APP_DEBUG`              | Toggle debugging locally.                                           | `local` / `true`           |
| `APP_LOCALE` / `APP_FALLBACK_LOCALE` | Default and fallback languages.                                     | `lt` / `en`                |
| `DB_CONNECTION`                      | Database driver. Use `sqlite` to stay zero-config.                  | `sqlite`                   |
| `DB_DATABASE`                        | Path to the SQLite file.                                            | `database/database.sqlite` |
| `QUEUE_CONNECTION`                   | `database` gives async queues; swap to `sync` if you lack a worker. | `database`                 |
| `MAIL_MAILER`                        | Logs emails locally via `log`.                                      | `log`                      |
| `SCOUT_DRIVER`                       | Search driver (`null` disables).                                    | `null`                     |

> 🗒️ Keep `APP_URL=http://127.0.0.1:8000` if you intend to use the built-in PHP server.

## 5. Prepare the database

SQLite is the default and requires no services:

```bash
# Ensure the database directory exists
mkdir -p database

# Create the SQLite file if missing
touch database/database.sqlite

# Run migrations and seeders
php artisan migrate
php artisan db:seed
```

The seeders create an admin account (`admin@statybaecommerse.prus.dev` / `admin123`).

Prefer MySQL/PostgreSQL? Update the `DB_*` values in `.env` and rerun the migration/seed steps.

## 6. Serve the application

You have two options:

1. **Simple PHP server + Vite build**

    ```bash
    php artisan serve &
    npm run dev
    ```

    Visit the storefront at <http://127.0.0.1:8000> and the admin at <http://127.0.0.1:8000/admin>.

2. **All-in-one Make target**

    ```bash
    make dev
    ```

    This spins up the PHP server, queue worker, Vite, and Pail log viewer together.

Stop background commands with `Ctrl+C` when you are done.

## 7. Smoke test the stack

Run the basic application checks to confirm everything is wired correctly:

```bash
composer test
php artisan test
npm run lint
composer test
```

`composer test` now proxies to the Pest test runner (`vendor/bin/pest`) so contributors can rely on a single command that works
across environments without installing a global PHPUnit binary.

You can explore more helper commands in the `Makefile` (`make test`, `make analyse`, `make build`).

> 💡 The `composer test` script proxies to `vendor/bin/pest`, so you do not need a global PHPUnit or Pest binary installed.

## 8. Common issues & quick fixes (FAQ)

| Symptom | Resolution |
| --- | --- |
| `Class "PDO" not found` or `could not find driver` | Ensure PHP’s SQLite extension is enabled (`php -m \| grep sqlite`). On macOS with Homebrew: `brew install php` and restart your shell. |
| `APP_KEY` related errors | Run `php artisan key:generate` after creating `.env`. |
| Migrations fail because the SQLite file is read-only | Verify the `database/` folder and `database.sqlite` file are writable (`chmod 664 database/database.sqlite`). |
| `npm run dev` fails with OpenSSL or incompatible Node errors | Confirm `node -v` reports ≥20. Reinstall via `nvm install 20` or `brew install node@20`. |
| Admin panel styles are missing | Ensure Vite is running (`npm run dev`) or run `npm run build` for a static build. |
| Queue jobs pile up | Switch to synchronous processing locally by setting `QUEUE_CONNECTION=sync` in `.env` and rerun `php artisan queue:restart`. |
| Storage symlink missing 404s for media | Execute `php artisan storage:link` to recreate `public/storage`. |

---

You now have a fully functional development environment ready for iteration. Happy building!
