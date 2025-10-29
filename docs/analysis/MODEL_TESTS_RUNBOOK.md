# Model Tests Runbook

The model-focused regression suite is a quick indicator that Eloquent scopes, casts, and relationships remain stable. Use the following checklist when preparing a patch that touches any Laravel model:

1. Install PHP dependencies if the `vendor/` directory is missing:
   ```bash
   composer install
   ```
2. Execute the model test suite. Running the folder keeps execution time reasonable while covering the bulk of behavioural contracts:
   ```bash
   php artisan test tests/Models
   ```
   The PHPUnit configuration also exposes the suite directly, so you can trigger it via:
   ```bash
   ./vendor/bin/phpunit --testsuite Models
   ```
   This mirrors the artisan command without re-running the broader unit suite.
3. When debugging individual failures, target the specific file for faster feedback. For example:
   ```bash
   php artisan test tests/Models/ActivityLogTest.php
   ```
4. After the suite succeeds, review `junit.xml`. The tooling will repoint the file to the latest run; restore it if the diff only reflects timing metadata so Git history stays readable.
5. Capture any non-obvious insights or new invariants in the relevant markdown audits inside `docs/analysis/` so future tasks have the context baked in.
