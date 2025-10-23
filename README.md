# Statyba E-commerce Platform

A Laravel 11 + Filament v4 application that powers the Statyba B2B commerce experience, including catalog management, marketing automations, and a multilingual admin portal.

## Quickstart
1. Install PHP dependencies:
   ```bash
   composer install
   ```
2. Copy the example environment file and update credentials:
   ```bash
   cp .env.example .env
   ```
3. Generate an application key and run database migrations:
   ```bash
   php artisan key:generate
   php artisan migrate --seed
   ```
4. Compile front-end assets during development:
   ```bash
   npm install
   npm run dev
   ```

## Documentation
All project notes, reports, and operational guides live in [`docs/INDEX.md`](docs/INDEX.md). Start there for context, runbooks, and formal deliverables.
