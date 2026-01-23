---
description: "End-to-end workflow for implementing a feature"
---

# Feature Delivery Workflow

## 1. Clarify Scope
- Restate the user goal and expected behavior.
- Identify affected models, Filament resources, or routes.

## 2. Check Docs and Conventions
- Use search-docs for Laravel, Filament, or Livewire guidance.
- Inspect nearby files for conventions and patterns.

## 3. Implement
- Use php artisan make:* commands with --no-interaction.
- Follow the translation pattern (Entity + EntityTranslation) when needed.
- Keep Filament resources thin; move logic to Actions or Services.

## 4. Tests
- Create or update PHPUnit feature tests.
- Cover happy path, validation failures, and authorization failures.

## 5. Format and Verify
```bash
vendor/bin/pint --dirty
php artisan test --compact tests/Feature/[PathToTest].php
```
