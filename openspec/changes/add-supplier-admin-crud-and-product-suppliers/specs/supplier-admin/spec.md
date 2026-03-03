## ADDED Requirements
### Requirement: Supplier Admin CRUD
The system MUST provide a Filament Supplier resource that allows administrators to list, create, edit, and soft-delete suppliers from the admin left navigation.

#### Scenario: Admin creates a supplier
- **WHEN** an admin submits the Supplier create form
- **THEN** a supplier record is persisted with generated or provided code and appears in the suppliers list.

### Requirement: Supplier Product Linking
The system MUST allow admins to attach multiple suppliers to a product using a dedicated pivot table.

#### Scenario: Admin assigns suppliers to product
- **WHEN** an admin saves a product with selected suppliers
- **THEN** the selected supplier links are synchronized in the product-supplier pivot table.

### Requirement: Enabled Supplier Selection Rules
The system MUST allow selecting enabled suppliers for new associations while preserving existing inactive links on already-linked products.

#### Scenario: Product edit retains inactive linked supplier
- **WHEN** a product already linked to an inactive supplier is opened
- **THEN** the existing link remains until explicitly removed.

### Requirement: Publication Guard
The system MUST prevent publishing products without at least one supplier in admin product workflows.

#### Scenario: Publish blocked without suppliers
- **WHEN** an admin attempts to publish a product that has no suppliers
- **THEN** publishing is rejected with a validation error.

### Requirement: Inventory Supplier Migration
The system MUST migrate inventory supplier references to the new suppliers table and enforce referential integrity.

#### Scenario: Inventory references remain valid after migration
- **WHEN** migrations are executed
- **THEN** supplier IDs referenced by variant inventory records resolve to suppliers records via foreign key.
