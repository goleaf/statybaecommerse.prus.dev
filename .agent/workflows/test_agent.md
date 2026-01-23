---
description: "Subagent workflow for creating and updating PHPUnit tests"
---

# Test Generation and Update Workflow

## 1. Analyze Requirements
Identify the feature or resource that needs testing.
- Check app/Models for properties and relationships.
- Check app/Filament/Admin/Resources for form fields and table columns.
- Use search-docs for Laravel, Livewire, or Filament testing guidance.

## 2. Locate Existing Tests
Check tests/Feature/Admin for existing tests.
- If upgrading, read the existing test file.
- If new, find a similar resource test as a template.

## 3. Generate or Update Test File
Create or modify the test file in tests/Feature/Admin.
- Naming: [ResourceName]ResourceTest.php
- Base Class: Tests\TestCase
- Traits: use RefreshDatabase
- Setup: Ensure an admin user in setUp()

### Standard Test Cases
- test_can_render_index_page
- test_can_render_create_page
- test_can_render_edit_page
- test_can_create_record (include translations and relationships)
- test_can_edit_record
- test_can_delete_record

### Coverage Checklist
- Happy path
- Validation failures
- Authorization failures
- Translation save and search

## 4. Run Minimal Tests
Run the specific test file:
```bash
php artisan test --compact tests/Feature/Admin/[ResourceName]ResourceTest.php
```

## 5. Fix and Re-run
If tests fail:
- Read the error output.
- Check component namespaces and validation rules.
- Re-run the minimal test scope.

## 6. Format and Final Check
Run Pint and re-run the minimal test scope:
```bash
vendor/bin/pint --dirty
php artisan test --compact --filter=[TestClassName]
```
