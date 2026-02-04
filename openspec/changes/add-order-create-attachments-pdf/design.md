## Context
Admin order creation currently lacks inline attachment for products/services and does not auto-generate order PDFs. We need a streamlined workflow that attaches line items and services and produces PDFs at creation time.

## Goals / Non-Goals
- Goals:
  - Attach products and services during order creation.
  - Generate all required order PDFs on creation and link them to the order.
- Non-Goals:
  - Changing existing storefront checkout behavior.
  - Redesigning the documents system beyond order creation needs.

## Decisions
- Decision: Implement attachments directly on the create form using existing Filament patterns for relations.
- Decision: Use existing document generation services/templates to produce PDFs on creation.

## Risks / Trade-offs
- Risk: Increased create time due to PDF generation.
  - Mitigation: Keep generation synchronous for now; revisit queueing if needed.

## Migration Plan
- No data migration required.

## Open Questions
- Confirm which document templates count as "all" for order creation.
