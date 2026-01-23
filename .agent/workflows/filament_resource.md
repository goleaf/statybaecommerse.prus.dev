---
description: "Workflow for creating or updating Filament resources"
---

# Filament Resource Workflow

## 1. Inspect Existing Patterns
- Check app/Filament for the panel and resource structure.
- Follow existing naming and layout conventions.

## 2. Generate
- Use Filament artisan commands with --no-interaction.
- Use list-artisan-commands if options are unclear.

## 3. Configure
- Use Filament\Schemas\Components for layout.
- Use relationship() where appropriate for selects and repeaters.
- Keep logic in Actions or Services.

## 4. Tests
- Use Livewire::test to cover list, create, edit, delete, filters.
- Set the correct panel in tests.

## 5. Verify
```bash
vendor/bin/pint --dirty
php artisan test --compact tests/Feature/Admin/[ResourceName]ResourceTest.php
```
