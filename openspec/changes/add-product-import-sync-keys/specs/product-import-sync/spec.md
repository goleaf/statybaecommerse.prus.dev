## ADDED Requirements
### Requirement: Configure Sync Keys for Product Imports
The system MUST allow admins to configure product import sync keys as an ordered list when sync mode is enabled.

#### Scenario: Admin selects sync keys
- **WHEN** an admin enables sync mode on the product import page
- **THEN** they can add, remove, and reorder sync key fields to define match priority

### Requirement: Show CSV Mapping for Sync Keys
The system MUST show how candidate sync key fields map to CSV columns (or indicate when a key is unmapped).

#### Scenario: Admin reviews CSV mapping
- **WHEN** a CSV is uploaded for product import
- **THEN** the UI displays which CSV columns map to each candidate sync key field

### Requirement: Upsert Products Using Sync Keys
The system MUST update an existing product when a CSV row matches a selected sync key, and create a new product when no match exists.

#### Scenario: Matching SKU updates an existing product
- **GIVEN** a product exists with a matching SKU
- **WHEN** a CSV row is imported with that SKU in sync mode
- **THEN** the existing product is updated instead of creating a duplicate

### Requirement: Fail Ambiguous Sync Key Matches
The system MUST fail the row when more than one product matches the same sync key value.

#### Scenario: Duplicate key values trigger an error
- **GIVEN** multiple products share the same sync key value
- **WHEN** a CSV row is imported using that sync key
- **THEN** the row is marked as failed with an ambiguity error
