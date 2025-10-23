# Feature highlights

## Content safety and compliance
- Established an allow-listed HTML sanitizer that runs on product descriptions, translations, and legal documents to prevent script injection.
- Added a storefront `<x-sanitized-html>` component so any rendered rich text automatically passes through the sanitizer.
- Shipped the `php artisan maintenance:sanitize-html` command to reprocess legacy content in bulk.

## Operational tooling
- Existing maintenance commands remain alongside the new sanitization job, giving operators a single entry point for content hygiene tasks.
