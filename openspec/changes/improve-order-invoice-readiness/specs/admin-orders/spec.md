## ADDED Requirements
### Requirement: Manual Admin Invoice Generation for Any Payment State
The system SHALL allow admin-triggered manual invoice generation for orders regardless of payment status.

#### Scenario: Admin manually generates invoice for pending order
- **WHEN** an admin uses manual invoice generation for an order in `pending` payment status
- **THEN** the system attempts invoice generation and stores the resulting PDF relation on success

### Requirement: Invoice Payload Data Readiness
The system SHALL normalize and persist invoice-relevant order address/contact fields before creating provider payloads.

#### Scenario: Missing invoice fields are autofilled from order context
- **WHEN** invoice generation starts and billing/shipping data is incomplete
- **THEN** the system fills missing invoice-relevant fields from shipping/billing/user data and persists normalized addresses to the order

### Requirement: Include Services in Invoice Products
The system SHALL include attached services as invoice product lines.

#### Scenario: Order contains only service value
- **WHEN** an order has non-billable product rows but billable attached services
- **THEN** the invoice payload contains service rows and generation continues

### Requirement: Link Existing Order PDFs Into Invoice History
The system SHALL support linking existing order PDF files into `order_invoices`.

#### Scenario: Run PDF link command
- **WHEN** `orders:invoices:link-pdfs` runs
- **THEN** each unlinked order PDF file in `files` is represented as an `order_invoices` row and latest invoice per order is marked current

### Requirement: Report Unresolved Legacy PDFs Safely
The system SHALL report storage legacy PDFs that cannot be safely mapped through DB links.

#### Scenario: Legacy document PDF has no files row
- **WHEN** the PDF link command scans `secure-media/documents`
- **THEN** it outputs unresolved entries into CSV report without creating guessed order links
