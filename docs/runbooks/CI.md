# CI & Branch Protection Runbook

This runbook documents the checks that must stay green before code can be merged into `main` and how to keep the CI pipeline healthy.

## Required status checks

Protect the `main` branch with the following status checks so pull requests cannot merge unless every job completes successfully:

- `PHP Tests (PHP 8.2)`
- `PHP Tests (PHP 8.3)`
- `Static Analysis`
- `Frontend Build`
- `Security Audit`

To enforce these checks:

1. Navigate to **Settings → Branches** in GitHub.
2. Create or edit the protection rule for `main`.
3. Enable **Require status checks to pass before merging**.
4. Add each status listed above.
5. (Recommended) Also enable **Require branches to be up to date before merging** so the checks run on the latest `main`.
6. Save the rule.

With the rule in place, GitHub will prevent merges until the CI workflow finishes without failures. The workflow produces coverage reports for each PHP version and uploads frontend build artifacts to aid debugging.

## Troubleshooting tips

- **PHP failures**: Review the failing job logs for migration errors or missing seed data. Re-run locally with `php artisan migrate --no-interaction --force` followed by `php artisan test --without-tty --coverage-clover=coverage.xml`.
- **Static analysis**: Fix formatting violations with `vendor/bin/pint` and resolve PHPStan findings using `vendor/bin/phpstan analyse --memory-limit=1G --no-progress`.
- **Frontend build**: Ensure `npm ci` works from a clean checkout and verify the Vite build via `npm run build`.
- **Security audit**: Address Composer advisories via `composer update` or upstream patches. `npm audit --audit-level=high` is informational; investigate but it will not fail the job.

Keep this document updated when jobs are added, renamed, or removed so branch protection remains accurate.
