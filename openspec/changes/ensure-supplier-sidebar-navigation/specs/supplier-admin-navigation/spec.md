## MODIFIED Requirements
### Requirement: Supplier Admin CRUD
The system MUST expose Supplier CRUD in admin left navigation only to users authorized to view suppliers.

#### Scenario: Authorized admin sees Supplier in sidebar
- **WHEN** an authenticated admin user has `view_suppliers`
- **THEN** Supplier appears in the left navigation under the Products group
- **AND** `/admin/suppliers` is accessible.

#### Scenario: Unauthorized admin does not see Supplier in sidebar
- **WHEN** an authenticated admin user does not have `view_suppliers`
- **THEN** Supplier is omitted from left navigation
- **AND** direct access to `/admin/suppliers` is forbidden.
