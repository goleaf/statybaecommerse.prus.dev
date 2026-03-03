## 1. Implementation
- [x] 1.1 Add `ProductImageWriteService` to centralize product image writes and storage cleanup rules.
- [x] 1.2 Refactor `ProductResource` images relation manager create/edit/associate/delete flows to use the shared service.
- [x] 1.3 Refactor `ProductImageResource` create/edit/delete paths to use the shared service.
- [x] 1.4 Refactor `ProductImporter` image handling (`image_url` replace and `image` append) to route through the shared service.
- [x] 1.5 Add compatibility mirror updates for legacy product media collection writes.

## 2. Verification
- [x] 2.1 Add/update tests for relation manager clone attach, create/edit image upload, and file cleanup behavior.
- [x] 2.2 Add/update importer tests for replace-only, append-only, and combined replace+append image behavior.
- [x] 2.3 Run targeted test files for product image relation/resource/importer and service-level coverage.
- [x] 2.4 Validate OpenSpec change.
