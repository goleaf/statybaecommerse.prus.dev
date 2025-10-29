# Document System Audit

## Overview
The document platform combines reusable templates, automated generation, variable substitution, typed classifications, guarded access, and lifecycle tracking to support invoices, receipts, and internal reports across the storefront and admin panels.

## Document Templates
- `DocumentTemplate` stores editable HTML bodies, variable definitions, categorisation, and print settings while auto-generating slugs and exposing historic documents for the template, enabling reusable layouts for downstream generation.【F:app/Models/DocumentTemplate.php†L31-L199】

## Document Generation
- `DocumentService::generateDocument()` sanitises template HTML, applies variables, persists the draft record, and optionally notifies the initiating user; `generatePdf()` renders the processed HTML into a DomPDF file, applies print settings, saves the asset to secure storage, and publishes the document.【F:app/Services/DocumentService.php†L35-L103】
- `Database\\Seeders\\DocumentSeeder` now loops per record to attach freshly created invoices, receipts, and reports to the latest user fixtures and sampled orders, preventing stray factory-generated relations from drifting outside curated test data.【F:database/seeders/DocumentSeeder.php†L20-L142】

## Variable Replacement
- `DocumentService::processTemplate()` normalises arrays, objects, and booleans before performing placeholder substitution, while `DocumentTemplate::render()` supports `{{placeholder}}` tokens—together powering dynamic content injection for generated documents.【F:app/Services/DocumentService.php†L110-L131】【F:app/Models/DocumentTemplate.php†L110-L130】
- `DocumentService::getAvailableVariables()` exposes cached global keys (order details, customer metadata, etc.), and `extractVariablesFromModel()` maps model attributes (including order-specific overrides) for automatic replacement.【F:app/Services/DocumentService.php†L139-L199】【F:app/Services/DocumentService.php†L205-L226】

## Document Types
- `DocumentTemplateType` enumerates supported template types—invoice, receipt, report, and more—providing translated labels and palette metadata for consistent UI presentation.【F:app/Enums/DocumentTemplateType.php†L7-L33】

## Access Control & Distribution
- `Document` records default to private, downloadable assets, exposing convenience checks (e.g., `isPublic`, `isDownloadable`) and generating temporary signed URLs via secure storage for gated delivery.【F:app/Models/Document.php†L66-L210】【F:app/Models/Document.php†L244-L258】
- `DocumentGenerated` notifications respect authorisation gates before surfacing view links and only attach PDFs when secure files exist, ensuring notifications honour per-document permissions.【F:app/Notifications/DocumentGenerated.php†L18-L103】

## Versioning & Lifecycle Management
- Status constants (`draft`, `generated`, `published`, `archived`) and helper scopes support lifecycle queries, while the `version` field and template-level `documents()` relationship (scoped to include archived records) preserve historical revisions.【F:app/Models/Document.php†L43-L317】【F:app/Models/DocumentTemplate.php†L64-L71】
- Morph-many audit logs on `Document` capture chronological changes for traceability across revisions.【F:app/Models/Document.php†L166-L174】
