# Project: Egistatyba

## 📋 Overview
**Egistatyba** is a Laravel 12 application utilizing **FilamentPHP v4** for its admin panel and infrastructure. The project appears to be a construction or building management system (inferred from "statyba"). It employs a modern stack with Vite and Tailwind CSS v4 for the frontend and heavily relies on Spatie packages.

The project enforces strict development standards and includes a specialized AI agent workflow configuration (`.agent/`, `openspec/`).

## 🛠 Tech Stack
- **Framework**: Laravel 12.0
- **Admin Panel**: FilamentPHP v4.0
- **Language**: PHP 8.2+
- **Frontend**: Vite ^7.0, Tailwind CSS ^4.0, Alpine.js (via Filament), Blade
- **Testing**: PestPHP
- **Static Analysis**: PHPStan
- **Code Style**: Laravel Pint
- **Key Packages**:
    - `spatie/laravel-medialibrary` (v11)
    - `spatie/laravel-translatable`
    - `spatie/laravel-data`
    - `laravel/sanctum`
    - `laravel/scout`

## 🚀 Key Commands
**Note:** Use `composer run` scripts as they are configured to handle concurrent processes and specific flags.

### Development
- **Start Dev Server**: `composer run dev`
    - *Runs `php artisan serve`, `queue:listen`, `pail`, and `npm run dev` concurrently.*
- **Serve (PHP only)**: `composer run serve`
- **Frontend Watch**: `npm run dev`

### Building
- **Build Production**: `composer run build`
    - *Runs `php artisan optimize` and `npm run build`.*
- **Install App**: `composer run app:install`
    - *Runs migrations and links storage.*

### Testing & Quality
- **Run Tests (Pest)**: `composer run test`
- **Test (CI Mode)**: `composer run test:ci`
- **Lint PHP**: `composer run lint:php`
- **Fix PHP Style**: `composer run fix:php`
- **Static Analysis**: `composer run analyze` (PHPStan)
- **Full Check**: `composer run check` (Lint + Analyze + Test)

### Filament Utilities
- **Diagnose**: `composer run diagnose:filament-pages`
- **Fix Navigation**: `composer run filament:fix-navigation`

### MCP Integration
- **Laravel Loop**: Installed and configured.
    - **Config**: `.cursor/mcp.json`
    - **Command**: `php artisan loop:mcp:start`
    - **Filament**: Toolkit registered in `AppServiceProvider`.

## 📂 Project Structure
- **`app/Filament`**: Filament resources, pages, and widgets.
- **`app/Data`**: Data Transfer Objects (spatie/laravel-data).
- **`openspec/`**: AI agent specifications and change proposals.
- **`.agent/`**: "Antigravity Kit" agent configuration.

## 📝 Development Conventions
*   **Code Style**: Enforced by **Pint**. Run `composer run fix` before committing.
*   **Type Safety**: Use strict types (`declare(strict_types=1);`) in new files.
*   **Testing**: Use **Pest** for all tests (`*Test.php`).
    - Unit: `tests/Unit`
    - Feature: `tests/Feature`
*   **Commits**: Must follow **Conventional Commits** (e.g., `feat(filament): add resource`).
*   **Environment**: Copy `.env.example` to `.env`. Never commit secrets.

## 🤖 AI Assistant Guidelines
This project contains specific instructions for AI agents in `openspec/AGENTS.md`.
- **Planning**: If the user asks for a plan, proposal, or spec, refer to `openspec/AGENTS.md`.
- **Architecture**: Respect the modular structure defined in `.agent/ARCHITECTURE.md`.
- **Laravel Boost**: Follow the guidelines in `AGENTS.md` (root) for Laravel best practices and skills.
