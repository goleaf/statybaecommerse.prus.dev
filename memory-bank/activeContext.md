# Active Context

- Laravel: 12.x (composer requires `laravel/framework:^12.0`), PHP ^8.2
- Filament: v4 present; panel provider at `App\Providers\Filament\AdminPanelProvider` (path: `admin`)
- User model: `App\Models\User`
- Admin prefix: `/admin` (legacy `/cpanel` supported via redirects)
- Feature toggles: attribute, brand, category, collection, discount, review enabled
- Media config: disk `public`, collections `uploads`/`thumbnail`, mime types jpg/jpeg/png, conversions large/medium
- Admin components via Filament Resources/Pages/Widgets under `app/Filament/*`
- Routes: storefront pages Home, SingleProduct, Checkout; health, brand/location pages; auth routes wired
- Seeders: present under `database/seeders` (e.g., ExtendedDemoSeeder, RolesAndPermissionsSeeder, AdminPresetDiscountsSeeder, TranslationSeeder, CampaignSeeder)

## Next Steps
1. Verify Filament panel registration and `/admin` path; `php artisan filament:upgrade` and `php artisan optimize:clear`.
2. Standardize admin routes to `/admin`; keep `/cpanel` redirects for backward compatibility.
3. Ensure seeders run end-to-end (currencies, zones, brands/categories/attributes/products/variants with media, legal, roles).
4. Implement/verify storefront cart/checkout to create orders.
5. Add tests per acceptance criteria (deferred as needed).
