## Why
- Brochure download feature needs reliable seeded data so frontend and backend always have real downloadable fixtures.
- Existing PDF-related tests use ad-hoc byte strings, which are brittle and inconsistent with real PDF flows.

## What Changes
- Add a reusable lorem PDF generator utility that emits valid single-page PDF binaries.
- Add `BrochureSeeder` that creates brochure records, brochure file records, and writes generated PDF files to secure storage.
- Register `BrochureSeeder` in standard/default seed profiles.
- Add configurable brochure seeding volume keys in `config/seeds.php`.
- Add shared PDF fixture helper for tests and refactor existing PDF-related tests to use it.
- Add dedicated brochure seeder tests (counts, idempotency, valid file content, config overrides).

## Impact
- Seed runs now include brochure fixtures by default.
- Frontend `/brochures` and admin brochure management are testable against seeded records.
- PDF tests become more realistic while reducing hardcoded binary literals.
