## ADDED Requirements
### Requirement: Attached Products Block on Variant View
The system MUST display a read-only Attached Products block on the Product Variant view page that lists the product associated with the variant.

#### Scenario: Admin views a product variant
- **WHEN** an admin opens a Product Variant view page
- **THEN** they see an Attached Products table with the product image, name, SKU, and price

### Requirement: Empty State When No Product
The system MUST handle variants without an associated product by showing an empty-state message in the Attached Products block.

#### Scenario: Variant without product relation
- **WHEN** an admin opens a Product Variant view page for a variant without an associated product
- **THEN** the Attached Products block shows a clear empty state