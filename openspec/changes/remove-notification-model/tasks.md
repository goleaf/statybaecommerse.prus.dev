## 1. Inventory and decisions
- [ ] 1.1 Confirm target behavior for notification API, UI, and admin widgets after removal (keep via Laravel defaults vs remove/disable).
- [ ] 1.2 Identify all custom `Notification` model features that must be preserved (if any) and define replacements.

## 2. Core model removal
- [ ] 2.1 Remove `app/Models/Notification.php`.
- [ ] 2.2 Replace `App\Models\Notification` imports/usages with framework types or user notification relationships.
- [ ] 2.3 Update route model binding and policies to avoid referencing the removed class.

## 3. Dependent domain logic
- [ ] 3.1 Refactor `NotificationService` to use Laravel's built-in notification APIs or remove it if redundant.
- [ ] 3.2 Refactor jobs/events/mailables that type-hint the removed model.
- [ ] 3.3 Refactor `User` notification relationships to use framework notifications.

## 4. Admin/UI dependencies
- [ ] 4.1 Refactor or remove Filament notification widgets that rely on custom scopes.
- [ ] 4.2 Refactor Livewire notification components that assume custom attributes/scopes.

## 5. Data generation and tests
- [ ] 5.1 Remove or refactor notification factories/seeders to target framework notifications.
- [ ] 5.2 Update or remove tests that rely on custom model scopes/helpers.
- [ ] 5.3 Run `composer run test` and targeted notification-related tests to confirm behavior.

## 6. Cleanup and validation
- [ ] 6.1 Run `rg -n "App\\Models\\Notification" app database routes tests` and eliminate all references.
- [ ] 6.2 Run `openspec validate remove-notification-model --strict`.
