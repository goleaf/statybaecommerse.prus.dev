# Change: Remove custom Notification model

## Why
The project defines `App\Models\Notification` as a subclass of Laravel's `DatabaseNotification`. This duplicates framework behavior, creates a large surface area of custom scopes and helpers, and couples many parts of the codebase to an optional customization. Fully removing the custom model simplifies the notification system and aligns the app with Laravel defaults.

## What Changes
- Remove the `App\Models\Notification` class and its custom scopes/helpers.
- Replace all usages of `App\Models\Notification` with framework-supported alternatives (primarily `Illuminate\Notifications\DatabaseNotification` and `User` notification relationships).
- Remove or refactor dependent services, policies, factories, seeders, Filament widgets, Livewire components, and tests that rely on the custom model's API.
- Update route model binding, authorization, and job/event payloads to use framework notifications.
- Ensure notification-related UI and API routes either:
  - continue working using Laravel defaults, or
  - are explicitly removed/disabled with clear behavior.

## Impact
- Affected specs: `notifications`
- Affected code:
  - `app/Models/Notification.php`
  - `app/Services/NotificationService.php`
  - `app/Http/Controllers/Api/NotificationController.php`
  - `app/Providers/AuthServiceProvider.php`
  - `app/Policies/NotificationPolicy.php`
  - `app/Events/NotificationCreated.php`
  - `app/Events/NotificationReadStatusUpdated.php`
  - `app/Jobs/SendNotificationJob.php`
  - `app/Mail/NotificationMail.php`
  - `app/Filament/Widgets/Notification*.php`
  - `database/factories/*Notification*.php`
  - `database/seeders/NotificationSeeder.php`
  - `tests/**/*Notification*`
