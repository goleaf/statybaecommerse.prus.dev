# Badgeable Table Columns

> Example render (simplified)
>
> | Primary label | Prefix badges | Suffix badges |
> | ------------- | ------------- | ------------- |
> | `EcoMix Timber Deck` | `SKU • EMX-4412` · `Brand • Zephyr` | `Published` · `Visible` · `Featured` · `Stock • 120` · `Reviews • 6` · `Rating • 4.7`

Filament now renders several high-signal columns with the `awcodes/badgeable-column` plugin so that adjacent metadata (statuses, counts, flags, and relations) lives beside the primary attribute instead of across multiple columns.【F:app/Filament/Resources/ProductResource.php†L363-L421】【F:app/Filament/Resources/OrderResource.php†L504-L573】【F:app/Filament/Resources/CustomerResource.php†L210-L279】【F:app/Filament/Resources/CouponResource.php†L207-L292】【F:app/Filament/Resources/PostResource.php†L314-L356】

## Resource highlights

- **Products** – SKU and brand details appear as prefix badges, while publishing state, visibility, feature flag, inventory health, reviews, and rating sit in the suffix stack.【F:app/Filament/Resources/ProductResource.php†L363-L421】【F:lang/en/products.php†L39-L46】
- **Orders** – The status column binds payment state, method, channel, fulfillment progress, order total, and item counts into a single badge stream for quick triage.【F:app/Filament/Resources/OrderResource.php†L504-L573】【F:lang/en/orders.php†L49-L62】
- **Customers** – Geography, account status, verification state, order count, and lifetime value are surfaced inline with the customer name, making segmentation cues visible at a glance.【F:app/Filament/Resources/CustomerResource.php†L210-L279】【F:lang/en/customers.php†L97-L107】
- **Coupons** – Codes expose their type, target scope, lifecycle, usage counters, visibility, automation, and stackability without needing extra columns.【F:app/Filament/Resources/CouponResource.php†L207-L292】【F:lang/en/coupons.php†L1-L20】
- **Posts** – Moderation and publishing badges now also convey featured, pinned, and comment states to streamline editorial review.【F:app/Filament/Resources/PostResource.php†L314-L356】【F:lang/en/posts.php†L88-L96】

## Implementation notes

- Each resource eager loads the relationships/counts required for the badge closures to avoid N+1 queries (e.g., `brand`, `channel`, `country`, `customerGroup`, and `user`).【F:app/Filament/Resources/ProductResource.php†L724-L807】【F:app/Filament/Resources/OrderResource.php†L581-L588】【F:app/Filament/Resources/CustomerResource.php†L368-L375】【F:app/Filament/Resources/CouponResource.php†L311-L317】【F:app/Filament/Resources/PostResource.php†L500-L507】
- Translation packs include dedicated badge labels so admin copy stays localized alongside the new UI affordances.【F:lang/en/products.php†L39-L46】【F:lang/en/orders.php†L49-L62】【F:lang/en/customers.php†L97-L107】【F:lang/en/coupons.php†L1-L20】【F:lang/en/posts.php†L88-L95】

