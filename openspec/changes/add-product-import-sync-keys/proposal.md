## Why
Product imports currently do not re-check existing products during sync mode, so duplicates are created instead of updating. Admins need a reliable upsert workflow that matches products by identifiers present in the CSV.

## What Changes
- Add a product import sync key selector (repeatable list of product fields, with priority order).
- Show which CSV columns are mapped to candidate sync keys, so admins know what can be used.
- Use the selected sync keys to resolve existing products when sync mode is enabled (update if match, create if not).
- If multiple products match the same key value, fail that row to prevent accidental updates.

## Impact
- Admin import behavior changes only when sync mode is enabled.
- No deletion of records not present in the CSV; sync mode is upsert-only.
