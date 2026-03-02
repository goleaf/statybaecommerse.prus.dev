## 1. Implementation
- [ ] 1.1 Update invoice generation mode rules so manual generation bypasses paid-status checks.
- [ ] 1.2 Add invoice context preparation that normalizes and persists billing/shipping invoice fields.
- [ ] 1.3 Include attached services in invoice product payload and add positive-total fallback line support.
- [ ] 1.4 Add `orders:invoices:link-pdfs` command with `--dry-run`, `--order-id`, and unresolved report output.
- [ ] 1.5 Localize account order invoice section strings and new command messages in lang files.
- [ ] 1.6 Add/adjust feature tests for manual pending generation, service payload support, and PDF link command behavior.
