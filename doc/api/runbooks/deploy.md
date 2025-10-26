# Deploy Process

A predictable deployment follows a strict order of operations so that each release can be rolled back quickly if needed. Use this runbook for every production push.

## 1. Build the release artifact
- Freeze the target commit with a git tag or release branch.
- Install PHP and Node dependencies (`composer install --no-dev --optimize-autoloader`, `npm ci`).
- Compile frontend assets with `npm run build` and bundle any server assets (Laravel `php artisan config:cache`, `route:cache`).
- Package the artifact (container image or tarball) and publish it to the registry.

## 2. Run database migrations
- Put the application into maintenance mode when required (`php artisan down --render="errors::503"`).
- Run `php artisan migrate --force` on the primary database.
- Verify schema state with a smoke query (e.g., `php artisan db:monitor`).
- Exit maintenance mode (`php artisan up`).

## 3. Refresh caches
- Clear configuration, route, view, and event caches.
- Rebuild caches: `php artisan config:cache`, `route:cache`, `view:cache`, and `event:cache` as applicable.
- Prime any application-specific caches documented in `docs/runbooks/CachePolicy.md`.

## 4. Warm up queues (if used)
- Scale workers to the new release using zero-downtime rolling updates.
- Drain existing workers to finish in-flight jobs (`php artisan queue:restart` or supervisor drain command).
- Dispatch a lightweight smoke job to confirm queue execution.

## 5. Perform health checks
- Confirm HTTP health probes succeed (`/healthz`, `/readyz`).
- Verify background jobs, scheduled tasks, and third-party integrations.
- Monitor logs and metrics for at least one full queue cycle.

## 6. Rollback procedure
- Trigger automated rollback using your deploy tooling (e.g., `kubectl rollout undo`, `cap production deploy:rollback`).
- Restore the previous artifact (container image tag or release bundle).
- Run `php artisan migrate:rollback --step=1` if the release introduced breaking schema changes.
- Re-run health checks to confirm stability and note the rollback in the incident log.

Following these steps ensures a predictable, reversible deploy process with clear checkpoints and remediation paths.
