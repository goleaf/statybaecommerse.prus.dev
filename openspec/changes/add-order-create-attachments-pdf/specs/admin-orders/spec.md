## ADDED Requirements
### Requirement: Attach Products and Services on Order Create
The system SHALL allow admins to attach products and services with quantity and price during order creation.

#### Scenario: Admin attaches products and services
- **WHEN** an admin creates an order and selects products/services with quantities/prices
- **THEN** the order is saved with the selected items and services and their pivot data

### Requirement: Generate Order PDFs on Create
The system SHALL generate all required order PDF documents when an order is created in the admin panel and associate them with the order.

#### Scenario: PDFs generated on order creation
- **WHEN** an admin creates an order
- **THEN** the configured order PDF documents are generated and visible in the order documents area
