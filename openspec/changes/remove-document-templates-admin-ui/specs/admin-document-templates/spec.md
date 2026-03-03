## MODIFIED Requirements
### Requirement: Document Template Admin Pages Availability
The system MUST NOT expose Document Template CRUD pages in the admin panel.

#### Scenario: Direct access to document-template index
- **WHEN** an authenticated admin visits `/admin/document-templates`
- **THEN** the request does not resolve to a Document Template admin page

#### Scenario: Route registry lookup
- **WHEN** admin routes are listed
- **THEN** no route with path containing `document-templates` is registered

### Requirement: Document Template Translation Keys Retained for Runtime Enums
The system MUST keep only translation keys still used outside the removed admin resource.

#### Scenario: Enum labels
- **WHEN** `DocumentTemplateType::options()` and `DocumentTemplateCategory::options()` are evaluated
- **THEN** their labels resolve through existing `admin.document_templates.types.*` and `admin.document_templates.categories.*` keys

#### Scenario: Organization form section label
- **WHEN** organization form schema is rendered
- **THEN** `admin.document_templates.document_form.sections.organization` remains resolvable
