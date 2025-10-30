# Notification System Audit

## Summary
- Notification payloads now surface canonical category metadata that aligns with system requirements while preserving legacy keys for backward compatibility.【F:config/notifications.php†L1-L28】【F:app/Data/Notifications/NotificationPayloadData.php†L16-L77】
- A dedicated resolver harmonises disparate notification hints (payload fields and class names) into the standard categories for analytics and filtering.【F:app/Support/Notifications/NotificationCategoryResolver.php†L7-L86】
- Unit coverage verifies canonical category resolution and ensures existing context/metadata accessors continue to behave as expected.【F:tests/Unit/Data/Notifications/NotificationPayloadDataTest.php†L13-L54】【F:tests/Unit/Notifications/NotificationDataTest.php†L15-L57】
- Storefront notification toasts and cart badges now use CSP-compliant utility classes instead of inline styles, eliminating browser `style-src` violations while keeping transitions intact for shoppers.【F:resources/js/app.js†L1-L214】【F:resources/js/shared/utilities.js†L1-L214】【F:resources/js/livewire-bridge.js†L1-L166】【F:resources/css/app.scss†L4985-L5120】
- Email campaign queries rely solely on the `is_active` flag for the shared ActiveScope, preventing lifecycle statuses such as `draft` or `scheduled` from being filtered out of admin listings and model tests.【F:app/Models/EmailCampaign.php†L49-L57】
- Notification template event and ordering scopes now bypass the shared ActiveScope when necessary so inactive definitions remain visible for audits and alphabetical listings across the admin tools.【F:app/Models/NotificationTemplate.php†L232-L248】
- Filament marketing segmentation detail views now reuse the injected schema instance so viewing a campaign customer segment no longer throws runtime errors for merchandising teams reviewing targeting metadata.【F:app/Filament/Resources/CampaignCustomerSegmentResource.php†L267-L315】
- The Filament email campaign editor has been upgraded to the v4 form API and now requires a plain-text body alongside scheduling metadata, keeping generated payloads consistent for transactional fallbacks.【F:app/Filament/Resources/EmailCampaignResource.php†L15-L118】
- Variant inventory management actions now emit deterministic success/failure notifications and enforce admin-only access, ensuring QA assertions catch stock adjustments, reservations, exports, and deletions uniformly across the Filament panel.【F:app/Filament/Resources/VariantInventoryResource.php†L520-L739】
- The Livewire testing harness ships with a defensive `groupTable` macro so grouped inventory grid assertions run even when upstream helpers are unavailable, keeping notification-driven regressions observable in CI.【F:app/Providers/AppServiceProvider.php†L214-L233】
- Server-sent notification streams now back off to a one-second idle poll interval when no new events arrive, preventing long-lived workers from hammering the database at 4 Hz while still emitting bursts rapidly when activity resumes.【F:app/Http/Controllers/Api/NotificationStreamController.php†L47-L158】
- Livewire newsletter subscription coverage now asserts that custom acquisition sources persist to subscriber records and that the component resets its form state after submission, protecting marketing attribution when opt-ins originate from alternate entry points.【F:tests/Feature/Livewire/NewsletterSubscriptionTest.php†L7-L52】

## Category Normalisation
- The new `config/notifications.php` map defines the six primary categories—System Notifications, User Notifications, Email Campaigns, Newsletter, Order Updates, and Stock Alerts—each with descriptive text and alias support so legacy payloads (e.g., `order`, `stock`) resolve consistently.【F:config/notifications.php†L4-L27】
- `NotificationCategoryResolver::resolve()` first checks explicit payload hints (`type`, `category`, `notification_type`) before analysing the notification class name, returning a canonical key, label, and description for downstream consumers.【F:app/Support/Notifications/NotificationCategoryResolver.php†L19-L56】

## Payload Enhancements
- `NotificationPayloadData::fromModel()` trims noise from persisted metadata, hydrates readable timestamps, and now records both the original legacy type and the canonical category tuple in the serialized payload.【F:app/Data/Notifications/NotificationPayloadData.php†L32-L75】【F:app/Data/Notifications/NotificationPayloadData.php†L83-L105】
- `toArray()` exposes `notification_type`, `category_key`, and human-readable labels while retaining the original `type`/`category` fields for existing UI widgets and API clients; metadata duplicates are removed to keep `meta` lean.【F:app/Data/Notifications/NotificationPayloadData.php†L43-L105】

## Verification
- Tests confirm that order notifications map to `order_updates` with the expected labels/descriptions and that legacy metadata/tags remain intact.【F:tests/Unit/Data/Notifications/NotificationPayloadDataTest.php†L16-L42】
- Additional coverage ensures class-name inference works when payloads omit an explicit type and that the broader notification data DTO continues to serialise context fields correctly.【F:tests/Unit/Data/Notifications/NotificationPayloadDataTest.php†L44-L54】【F:tests/Unit/Notifications/NotificationDataTest.php†L35-L57】
