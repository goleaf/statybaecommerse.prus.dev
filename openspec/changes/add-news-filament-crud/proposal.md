## Why
The admin needs a Filament CRUD for managing news items so content can be created, edited, and published without manual database changes.

## What Changes
- Add a Filament resource for `App\Models\News` with list, create, edit, and view pages.
- Include standard fields used by the News model (title, slug, excerpt/summary, content/body, status, published_at, category/tags if present, and media if present).
- Add appropriate table columns, filters, and default sorting.

## Impact
- Admin UI change only; no public behavior change unless published news is edited.
- Requires tests for resource access and basic CRUD.
