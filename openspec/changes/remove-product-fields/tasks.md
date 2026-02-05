## 1. Implementation
- [ ] 1.1 Remove deprecated product columns and indexes from existing migrations.
- [ ] 1.2 Remove deprecated fields from product translations migrations.
- [ ] 1.3 Update product queries to use `is_enabled` + published scope.
- [ ] 1.4 Reject removed columns during product import validation.
- [ ] 1.5 Update factories, seeders, and tests to match new schema.

## 2. Verification
- [ ] 2.1 Run targeted tests (product, importer, storefront/search).
- [ ] 2.2 Validate OpenSpec change.