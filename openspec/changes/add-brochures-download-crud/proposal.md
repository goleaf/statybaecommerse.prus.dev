# Change: Add Brochure Download CRUD

## Why
Administrators need a dedicated area to manage downloadable brochure PDFs, and storefront visitors need a public localized downloads page.

## What Changes
- Add `Brochure` and `BrochureFile` domain models with migrations.
- Add a Filament Brochure resource under Content with full CRUD.
- Support multiple files per brochure via inline repeater editing.
- Enforce activation rule: active brochure requires at least one active file.
- Add localized storefront route `/{locale}/brochures` and `/brochures` redirect.
- Add Downloads link in the top navigation menu.
- Serve PDF downloads through signed secure-media URLs.

## Impact
- Affected specs: brochure-downloads
- Affected code: migrations, models, Filament resource pages/schemas/tables, frontend controller/view/routes, translations, tests.
