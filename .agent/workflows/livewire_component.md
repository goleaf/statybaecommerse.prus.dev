---
description: "Workflow for creating or updating Livewire components"
---

# Livewire Component Workflow

## 1. Plan State and Actions
- Define component state, actions, and validation rules.
- Confirm authorization requirements.

## 2. Generate Component
- Use php artisan make:livewire --no-interaction.
- Keep a single root element in the Blade view.

## 3. Implement
- Use wire:model.live for realtime updates.
- Use dispatch for events.
- Add wire:key in loops and wire:loading for UX.

## 4. Tests
- Use Livewire::test for action and validation coverage.

## 5. Verify
```bash
vendor/bin/pint --dirty
php artisan test --compact --filter=[TestClassName]
```
