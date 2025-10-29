# User Profiles Contract Fixtures

- Keep CSV and JSON fixtures aligned with the expectations in `tests/Feature/DataTransfer/UserProfilesDataTransferTest.php` so round-trip tests remain deterministic.
- Preserve UTF-8 encoding without BOM and avoid spreadsheet artefacts; the import logic assumes simple RFC4180 formatting.
- When extending the contract schema, update both the fixtures and any linked documentation in `docs/contracts/` to describe the new columns.
