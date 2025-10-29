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
   The PHPUnit configuration now registers `tests/Models` as its own suite, so invoking the folder (or the `Models` suite) avoids duplicate file discovery warnings during broader runs.
3. When debugging individual failures, target the specific file for faster feedback. For example:
   ```bash
   php artisan test tests/Models/ActivityLogTest.php
   ```
4. After the suite succeeds, review `junit.xml`. The tooling will repoint the file to the latest run; restore it if the diff only reflects timing metadata so Git history stays readable.
5. Capture any non-obvious insights or new invariants in the relevant markdown audits inside `docs/analysis/` so future tasks have the context baked in.
6. When overriding query builders (for example, removing global scopes in a Filament resource to expose soft-deleted or hidden rows), document the intent with an `@return Builder<Model>` annotation so PHPStan retains its generic context and other engineers remember why moderation tools bypass the storefront defaults.
7. SQLite-driven suites now depend on the legacy `sh_attributes`/`sh_attribute_values` tables for cascading deletes. The safety migration `2025_12_15_000100_ensure_legacy_attribute_tables_exist.php` keeps those structures present without impacting MySQL or PostgreSQL, ensuring tests like `ProductImageTest::test_cascade_delete_when_product_is_deleted` can exercise hard deletes without foreign-key noise.
