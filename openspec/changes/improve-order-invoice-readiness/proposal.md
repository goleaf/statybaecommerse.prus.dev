# Change: Improve Order Invoice Readiness and PDF Relation History

## Why
Invoice generation currently fails when order payload data is incomplete or when manual generation is requested for unpaid orders. We also need a safe way to relate existing order PDF files into `order_invoices` history.

## What Changes
- Allow manual invoice generation for admin actions regardless of payment status.
- Auto-prepare and persist normalized invoice-related billing/shipping fields before API calls.
- Build invoice products from both order items and attached services, with a fallback line when total is positive.
- Add `orders:invoices:link-pdfs` command to link existing order PDF files to invoice history.
- Add unresolved legacy PDF report generation for storage files without reliable DB mapping.
- Replace hardcoded invoice strings in account order detail with translation keys.

## Impact
- Affected specs: admin-orders
- Affected code: invoice service/client flow, admin order actions, console commands, account order detail UI, translations, tests
