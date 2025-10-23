# Migration Freeze Policy

All migration files committed before the 2025-09-26 migration window are considered **frozen**. Do not edit, reorder, or delete any of the existing files in `database/migrations/`. Any schema change must be implemented via a brand-new, forward-only migration that preserves the historical record.

## Authoring New Migrations
- Create new migrations with Laravel's generators (`php artisan make:migration`) and ensure they only move the schema forward.
- Always include full foreign key definitions, explicit `onDelete`/`onUpdate` behaviour, and any supporting indexes that queries rely on.
- Never modify or rollback previously run production migrations to "clean up" history. Add a new migration instead.
- Keep new migrations idempotent by checking for existing tables, columns, constraints, and indexes when appropriate.

## Enforcement
- CI runs `php scripts/check-frozen-migrations.php` to fail the pipeline if an existing migration is modified.
- Schema integrity tests verify that critical constraints and indexes remain in place for core order tables.

For exceptions, coordinate with the platform team and document the rationale in a new forward-only migration.
