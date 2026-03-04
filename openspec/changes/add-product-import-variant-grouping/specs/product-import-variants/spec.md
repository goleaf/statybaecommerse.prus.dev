## ADDED Requirements

### Requirement: Group Product Import Rows By Product Name
The product CSV importer SHALL resolve rows with the same normalized product name to a single parent product record.

#### Scenario: Multiple rows share the same product name
- **GIVEN** a CSV contains multiple rows where `name` differs only by casing or surrounding whitespace
- **WHEN** the product importer processes those rows
- **THEN** all matching rows are assigned to one product record instead of creating duplicate products

### Requirement: Upsert Product Variants Per Imported Row
The product CSV importer SHALL create or update a product variant for each imported row that contains variant-identifying data.

#### Scenario: Row contains a variant SKU
- **GIVEN** a CSV row contains `sku` for a product
- **WHEN** the importer processes the row
- **THEN** the importer updates an existing variant for that product+SKU or creates one if missing

#### Scenario: Row has no SKU but variant attributes
- **GIVEN** a CSV row has variant attributes but no SKU
- **WHEN** the importer processes the row
- **THEN** the importer updates or creates a variant using a deterministic fallback key within the same product

### Requirement: Keep Parent Product Snapshot Fields In Sync
The importer SHALL keep parent product snapshot fields aligned with imported variants.

#### Scenario: Variant pricing and stock are imported
- **WHEN** variant rows are created or updated for a product
- **THEN** the product stock quantity is updated from variant stock totals
- **AND** the product price reflects the lowest variant price
- **AND** the product SKU reflects the preferred/default variant SKU when available
