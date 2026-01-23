---
description: "Workflow for migrations involving translation tables"
---

# Translation Migration Workflow

## 1. Plan Schema
- Base table plus translation table with entity_id, locale, and localized fields.
- Add unique index on (entity_id, locale) and index locale.

## 2. Generate Migration
- Use php artisan make:migration --no-interaction.
- When modifying a column, include all previous attributes.

## 3. Models and Factories
- **Strict Typing:** Ensure `declare(strict_types=1);` is present.
- **Trait Usage:** Use `App\Models\Traits\HasTranslations` in the base model.
- **Relationships:** Ensure `translations(): HasMany` relationship exists.
- **Factories:** Add or update factories for both base and translation models.

## 4. Tests
- Add tests for translation create, update, and search.

## 5. Verify
```bash
php artisan test --compact --filter=[TestClassName]
```