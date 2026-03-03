## 1. Implementation
- [x] 1.1 Delete `app/Filament/Resources/DocumentTemplateResource.php`.
- [x] 1.2 Delete all files under `app/Filament/Resources/DocumentTemplateResource/**`.
- [x] 1.3 Prune unused `admin.document_templates` locale keys in `lang/en|lt|de|ru/admin.php`, keeping:
  - `document_templates.types.*`
  - `document_templates.categories.*`
  - `document_templates.document_form.sections.organization`
- [x] 1.4 Add regression coverage for removed admin routes.

## 2. Verification
- [x] 2.1 `php artisan route:list --path=document-templates` shows no routes.
- [x] 2.2 Run targeted tests for route removal and enum translation usage.
- [x] 2.3 `openspec validate remove-document-templates-admin-ui --strict` passes.
