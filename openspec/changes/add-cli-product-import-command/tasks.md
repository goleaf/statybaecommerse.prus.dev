## 1. Implementation
- [x] 1.1 Add OpenSpec requirement for CLI product CSV import behavior.
- [x] 1.2 Implement `import:products` command using `ProductImporter` and `CsvImportProcessor`.
- [x] 1.3 Extend `ProductImporter` with command-only `require_existing_sync_match` option.
- [x] 1.4 Add feature tests for command flow and importer strict-sync behavior.

## 2. Verification
- [x] 2.1 Run targeted Pest tests for new command and importer behavior.
- [x] 2.2 Validate the OpenSpec change with `openspec validate --strict`.
