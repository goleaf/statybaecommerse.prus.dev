# Design: Remove custom Notification model

## Summary
This change removes the custom `App\Models\Notification` model (a subclass of `Illuminate\Notifications\DatabaseNotification`) and replaces all direct usages with Laravel defaults. The primary technical risk is that many parts of the codebase rely on custom scopes and computed attributes defined on the custom model.

## Key Decisions

### 1. Use framework `DatabaseNotification`
All type hints and imports will move from `App\Models\Notification` to `Illuminate\Notifications\DatabaseNotification` unless the behavior should be removed entirely.

### 2. Replace custom scopes with query fragments
Custom scopes like `unread()`, `urgent()`, `forUser()` and aggregation helpers like `getStats()` will be replaced with explicit query conditions on the notifications table or removed where not essential.

### 3. Route model binding and policy adjustments
Laravel will no longer resolve `App\Models\Notification` for route model binding. Controllers and policies must use `DatabaseNotification` (or manual resolution) and enforce ownership using `notifiable_type` and `notifiable_id`.

## Migration Plan
1. Replace imports/usages and refactor dependent logic.
2. Remove the custom model and associated policy bindings.
3. Update tests to cover the new behavior and ensure there are no lingering references.

## Risks and Mitigations
- Risk: Notification widgets/services rely on custom scopes or computed attributes.
  Mitigation: Replace scopes with explicit queries and/or reduce surface area by removing non-essential UI.
- Risk: Route model binding breaks for notification endpoints.
  Mitigation: Type-hint `DatabaseNotification` and add ownership guards in controller/service methods.
- Risk: Factories/seeders/tests depend on the removed model.
  Mitigation: Update them to use `DatabaseNotification` factories or create notifications through Laravel's notifiable APIs.
