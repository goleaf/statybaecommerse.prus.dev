---
description: "Workflow for deployments and releases"
---

# Deployment and Release Workflow

## 1. Pre-flight Checks
- Confirm target environment and deployment window.
- Review pending migrations and risky changes.
- Ensure secrets and env vars are set (do not commit .env).

## 2. Build and Dependencies
- composer install --no-dev --optimize-autoloader
- npm run build (if frontend assets changed)

## 3. Migrations and Caches
- php artisan migrate --force
- php artisan config:cache
- php artisan route:cache
- php artisan view:cache

## 4. Verification
- Run smoke checks on key routes.
- Verify logs for new errors.

## 5. Rollback Plan
- Note last known good release.
- Ensure database rollback strategy is documented.
