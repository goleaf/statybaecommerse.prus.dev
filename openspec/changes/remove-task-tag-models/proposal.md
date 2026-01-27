# Change: Remove Legacy Task/Tag/Taggable Models

## Why
The `App\Models\Task`, `App\Models\Tag`, and `App\Models\Taggable` models appear to be legacy domain concepts that are no longer part of the current product surface. Keeping them introduces maintenance burden and increases the risk of accidental coupling in new code.

## What Changes
- Remove the `Task`, `Tag`, and `Taggable` Eloquent models.
- Remove or refactor all direct references to these models across relationships, services, factories, providers, and tests.
- Remove task/tag-specific factories and pivot models that are no longer valid without these models.
- Update model concerns and relationship optimizations to eliminate task/tag-specific queries and eager loading.
- Ensure the application boots, routes resolve, and the test suite passes without these models.

## Impact
- Affected specs: `legacy-task-tagging` (new removal delta)
- Affected code:
  - `app/Models/Task.php`
  - `app/Models/Tag.php`
  - `app/Models/Taggable.php`
  - Relationship concerns and services referencing task/tag models
  - Related factories, pivots, providers, and tests
- Breaking change: Any code (internal or external) that relies on these models, their relationships, or the underlying tables will break and must be updated.