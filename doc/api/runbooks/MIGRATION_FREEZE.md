# Migration Freeze Runbook

This runbook explains how we protect historical database migrations from being edited
and how to add new schema changes safely.

## Policy Overview

- **Never modify or delete existing migration files.** Historical migrations capture
  the exact steps that have already shipped to production. Changing them introduces
  drift between environments and invalidates previously run database changes.
- **All schema updates must be introduced through new forward-only migrations.** Use
  descriptive filenames and document the intent within the migration class so that
  rollback-free deployments remain predictable.
- **Tests and CI enforce the freeze.** A unit test verifies critical constraints, and
  the CI workflow rejects pull requests that attempt to edit legacy migration files.

## Adding a Schema Change

1. Create a new migration via `php artisan make:migration` with a descriptive name.
2. Implement the change using forward-only semantics (throw inside `down()` if the
   change cannot be rolled back).
3. Run `php artisan migrate` locally and ensure the application-specific checks or
   tests that cover the new schema pass.
4. Commit both the migration and any corresponding documentation or tests.

## Handling Mistakes

If a previously merged migration contains a mistake, create a new corrective migration
instead of editing the old file. Examples include:

- Dropping/recreating a column or index with the proper definition.
- Renaming tables/columns using dedicated migrations.
- Writing data-fix migrations that repair inconsistent records.

By following these steps, we keep deployments deterministic and avoid the risk of
corrupting production databases.
