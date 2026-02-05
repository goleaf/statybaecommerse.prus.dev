# Change: Add Filament Locations CRUD with Translation Tabs

## Why
Locations are currently managed via legacy admin endpoints, so admins need a first-class Filament workflow with multi-language content editing.

## What Changes
- Add a Filament Location resource with list/create/edit/view pages.
- Build a form with a General tab for business fields and locale tabs for translatable name/slug/description.
- Persist translations to the location_translations table using the existing translation-table approach and keep the default locale synced to base columns.
- Add a Locations table view with key columns and status toggles.
- Add tests covering list rendering and translation-aware create flow.

## Impact
- Affected specs: manage-locations
- Affected code: app/Filament/Resources/LocationResource.php, app/Filament/Resources/LocationResource/*, tests/Feature/LocationResourceTest.php
- Data model: no migrations (uses existing locations and location_translations tables)
