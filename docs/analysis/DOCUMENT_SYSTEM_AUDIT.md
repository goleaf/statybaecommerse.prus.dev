# Document System Audit

## Overview
The document platform combines reusable templates, automated generation, variable substitution, typed classifications, guarded access, and lifecycle tracking to support invoices, receipts, and internal reports across the storefront and admin panels.

## Document Templates
- `DocumentTemplate` stores editable HTML bodies, variable definitions, categorisation, and print settings while auto-generating slugs and exposing historic documents for the template, enabling reusable layouts for downstream generation.【F:app/Models/DocumentTemplate.php†L31-L199】
- Alphabetical pickers stay predictable thanks to the hardened `DocumentTemplate::orderedByName()` scope, which normalises direction input before applying qualified ordering clauses for complex joins.【F:app/Models/DocumentTemplate.php†L234-L240】
- Filament regression tests now exercise the document template table actions and relation manager, covering duplicate handling, preview rendering, deactivate bulk workflows, and status filtering to ensure the v4 UI remains stable.【F:tests/Feature/Filament/Resources/DocumentTemplateResourceTableTest.php†L16-L159】
- Template duplication and bulk activation/deactivation guard against derived attributes and mixed selection payloads, unsetting the `documents_count` aggregate before cloning and resolving record IDs prior to set-based updates so the actions succeed regardless of how records are provided.【F:app/Filament/Resources/DocumentTemplateResource.php†L315-L338】【F:app/Filament/Resources/DocumentTemplateResource.php†L350-L358】

## Document Generation
- `DocumentService::generateDocument()` sanitises template HTML, applies variables, persists the draft record, and optionally notifies the initiating user; `generatePdf()` renders the processed HTML into a DomPDF file, applies print settings, saves the asset to secure storage, and publishes the document.【F:app/Services/DocumentService.php†L35-L103】
- `Database\\Seeders\\DocumentSeeder` now loops per record to attach freshly created invoices, receipts, and reports to the latest user fixtures and sampled orders, preventing stray factory-generated relations from drifting outside curated test data.【F:database/seeders/DocumentSeeder.php†L20-L142】

## Variable Replacement
- `DocumentService::processTemplate()` normalises arrays, objects, and booleans before performing placeholder substitution, while `DocumentTemplate::render()` supports `{{placeholder}}` tokens—together powering dynamic content injection for generated documents.【F:app/Services/DocumentService.php†L110-L131】【F:app/Models/DocumentTemplate.php†L114-L130】
- `DocumentService::getAvailableVariables()` exposes cached global keys (order details, customer metadata, etc.), and `extractVariablesFromModel()` maps model attributes (including order-specific overrides) for automatic replacement.【F:app/Services/DocumentService.php†L139-L199】【F:app/Services/DocumentService.php†L205-L226】

## Document Types
- `DocumentTemplateType` enumerates supported template types—invoice, receipt, report, and more—providing translated labels and palette metadata for consistent UI presentation.【F:app/Enums/DocumentTemplateType.php†L7-L33】

## Access Control & Distribution
- `Document` records default to private, downloadable assets, exposing convenience checks (e.g., `isPublic`, `isDownloadable`) and generating temporary signed URLs via secure storage for gated delivery.【F:app/Models/Document.php†L66-L210】【F:app/Models/Document.php†L244-L258】
- Ordering scopes trim whitespace-only names before deferring to the title, keeping admin dropdowns stable even when legacy records contain padded values.【F:app/Models/Document.php†L304-L320】
- Documents now denormalise creator and updater names via dedicated columns so admin listings and audit trails surface attribution data without eager loading, with the seeder backfilling names when the schema supports it.【F:app/Models/Document.php†L110-L148】【F:database/migrations/2025_11_12_000001_add_attribution_names_to_documents_table.php†L1-L41】【F:database/seeders/DocumentSeeder.php†L27-L114】
- `DocumentGenerated` notifications respect authorisation gates before surfacing view links and only attach PDFs when secure files exist, ensuring notifications honour per-document permissions.【F:app/Notifications/DocumentGenerated.php†L18-L103】

## Versioning & Lifecycle Management
- Status constants (`draft`, `generated`, `published`, `archived`) and helper scopes support lifecycle queries, while the `version` field and template-level `documents()` relationship (scoped to include archived records) preserve historical revisions.【F:app/Models/Document.php†L43-L317】【F:app/Models/DocumentTemplate.php†L64-L71】
- Morph-many audit logs on `Document` capture chronological changes for traceability across revisions.【F:app/Models/Document.php†L166-L174】
