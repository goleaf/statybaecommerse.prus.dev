# Change: Remove Document Templates Admin UI

## Why
The admin-facing Document Templates management surface (`/admin/document-templates`) should be removed from the project while keeping the broader document domain unchanged.

## What Changes
- Remove the Filament `DocumentTemplateResource` and all nested resource files.
- Remove generated admin routes for document-template management.
- Prune unused `admin.document_templates` translation keys while keeping keys still used by enums and organization form labels.
- Add regression tests to ensure the removed admin routes remain unavailable.

## Impact
- Admin UI breaking change: direct access to document-template management pages is removed.
- No database schema changes in this change set.
- No changes to document generation services/models outside the admin resource surface.