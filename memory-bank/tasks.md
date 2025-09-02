# Task: Build Laravel + Filament E-commerce (Admin + Storefront)

## Description
Implement a production-ready e-commerce system using Laravel 12 (PHP ^8.2) and Filament v4 on the TALL stack. Configure admin at `/admin` (keep legacy `/cpanel` redirects), seed core data, integrate ACL with roles/permissions, and provide a minimal storefront (Livewire) capable of creating carts and placing orders that appear in the admin. Implement all modules described in docs: Settings, ACL, Catalog (Brands, Categories, Attributes, Products, Variants, Media), Merchandising (Collections), Commerce (Orders, Pricing, Discounts), Customers, Reviews, and Two-Factor Auth. Ensure Livewire components/resources are registered, media conversions exist, feature toggles work, and add tests for critical flows.

## Complexity
Level: 4
Type: Complex System

## Technology Stack
- Framework: Laravel 12 (confirmed Composer shows ^12.0)
- Language: PHP 8.2+
- Admin: Filament v4 with Livewire components
- Frontend: Livewire + Blade (Breeze-like starter already present: `app/Livewire/Pages`)
- Auth: Laravel auth + policies/permissions
- Media: Spatie Media Library
- Permissions: spatie/laravel-permission
- Queue: Horizon (present), Redis (predis)
- DB: MySQL/MariaDB
- CI: Composer scripts `app:install`, `test`, plus artisan commands

## Technology Validation Checkpoints
- [x] Project initialization verified (composer.json present; Filament already required; panel provider exists)
- [x] Required dependencies identified (filament/filament, spatie/permission, livewire, spatie/media-library)
- [x] Build configuration validated (Filament panel path `admin`; legacy `/cpanel` redirects configured)
- [ ] Hello world verification (access `/admin` and storefront basics after routes finalized)
- [ ] Test build passes successfully (`php artisan test`)

## Status
- [x] Initialization complete
- [ ] Planning complete
- [ ] Technology validation complete
- [ ] Implementation steps

## Implementation Plan
1. Verify Filament and assets
   - Ensure `composer require filament/filament` is present (already in composer). Run `php artisan filament:upgrade`.
   - Ensure storage symlink and migrations are run. Run `php artisan migrate --force` and `php artisan storage:link`.
   - Confirm admin path `/admin` via `App\Providers\Filament\AdminPanelProvider`.
2. User model & ACL
   - Confirm `App\Models\User` and policies/permissions.
   - Seed roles: Administrator, Manager, User.
   - Ensure Filament auth guards/policies configured.
3. Settings: Store setup flow
   - Use project settings model/helpers as implemented.
   - Implement General, Address, Social fields; default Channel `Web Store` using APP_URL; set `is_default=true`.
   - Register pages: `Pages\Settings\General` and related components in `config/shopper/components/setting.php`.
4. Locations
   - Ensure inventories with limit from `config/shopper/admin.php` (`inventory_limit` = 4). Pages Index/Create/Edit.
   - Set default location.
5. Currencies
   - Seed ~150 currencies from core data. Admin to enable/disable, set default currency & store currencies list.
6. Zones
   - Implement zones with currency association and shipping options components.
7. Legal
   - CRUD for legal pages (Privacy, Refund, Shipping, Terms). Expose to storefront footer by slug.
8. Media
   - Confirm `config/shopper/media.php` storage, mime types, sizes; conversions large/medium and default thumb `thumb200x200` on models.
9. Catalog
   - Brands: CRUD with enable/disable, slide-over form, toggle in `config/shopper/features.php`.
   - Categories: hierarchical CRUD with SEO fields and enable/disable.
   - Attributes: types via FieldType enum, values, product assignment; searchable/filterable flags.
   - Products & Variants: full admin with pricing, inventory, media, SEO, shipping, attributes, variants, scheduling (`published_at`).
10. Collections
   - Manual & Auto with rules, `match_conditions` (all/any), sorting options. Components and slide-overs registered.
11. Pricing
   - Model `Price` with morphs and currency; register components (`products.pricing`, `slide-overs.manage-pricing`).
12. Discounts
   - CRUD with code/type/conditions/limits/dates; permissioned; hooks during cart/checkout.
13. Customers
   - Pages index/create/show; components addresses/orders/profile; marketing opt-in and send credentials.
14. Orders
   - Ensure models and admin pages; status updates, addresses, refunds, shipping info UI.
   - Storefront checkout creates Order, OrderItems, Addresses, links zone/currency/channel.
15. Reviews
   - Moderation workflow with `review-index` and `review-detail` slide-over.
16. Two-Factor Auth
   - Enable enrollment, confirmation, recovery codes, and middleware enforcement for admin routes.
17. Filament resources/pages/widgets
   - Implement/verify Filament Resources & Pages for modules.
18. Feature toggles
   - Validate toggles and ensure routes/policies/menus respect them.
19. Storefront
   - Add product list/detail, cart, checkout flow; order confirmation page; legal links in footer.
20. Seeders
   - Create seeders for currencies, default currency, location, zone, brands/categories/attributes/products/variants with media, legal page, super admin.
21. Policies & Middleware
   - Enforce roles/permissions on admin pages with gates and middleware.
22. Tests
   - Settings retrieval via `shopper_setting`, ACL gates, CRUD for products/variants, auto collections logic, media upload & conversions, storefront order placement, feature toggle visibility.

## Creative Phases Required
- [ ] Minimal storefront UX (cart/checkout steps)
- [ ] Collections auto-rule builder UX
- [ ] Discounts condition builder UX

## Dependencies
- Shopper 2.1, Livewire, Filament (via Shopper), Spatie Permission, Spatie Media Library, Redis/Horizon

## Challenges & Mitigations
- Data model scope and alignment with Shopper: follow `config/shopper/models.php` and extend core models only where needed.
- Media performance: use conversions and public disk; allow S3 via `filesystems.php` overrides.
- Permissions coverage: seed baseline roles/permissions and wrap menus/routes with `can()` checks.
- Multi-currency and zones: ensure currency per zone and reflect in pricing components.
