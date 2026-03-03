## ADDED Requirements
### Requirement: Product Supplier Discoverability
The products admin list MUST expose supplier assignment information and allow filtering by supplier.

#### Scenario: Filter products by supplier
- **WHEN** an admin applies a supplier filter in products table
- **THEN** only products linked to the selected supplier(s) are listed.

### Requirement: Supplier-Aware Product Creation
The system MUST support assigning suppliers during product create/edit while enforcing configured publish restrictions.

#### Scenario: Draft product saved without supplier
- **WHEN** an admin saves a product as draft without suppliers
- **THEN** save succeeds.

#### Scenario: Published product requires supplier
- **WHEN** an admin saves a product as published without suppliers
- **THEN** save is rejected.
