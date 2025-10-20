# Authorization Contract

This project centralizes panel authorization in `config/authorization.php`. The matrix maps Filament-facing resources to the
permission strings seeded for both the `admin` and `web` guards.

## Ability Vocabulary

| Resource   | Abilities (permission string)                          |
|------------|-------------------------------------------------------|
| Panel      | `panel.access.admin`                                   |
| Products   | `products.viewAny`, `products.view`, `products.create`, `products.update`, `products.delete` |
| Categories | `categories.viewAny`, `categories.view`, `categories.create`, `categories.update`, `categories.delete` |
| Brands     | `brands.viewAny`, `brands.view`, `brands.create`, `brands.update`, `brands.delete` |
| Orders     | `orders.viewAny`, `orders.view`, `orders.create`, `orders.update`, `orders.delete` |
| Users      | `users.viewAny`, `users.view`, `users.create`, `users.update`, `users.delete` |

All Filament resources resolve their visibility and action availability through the `AuthorizationMatrix::check()` helper.
Policies for each model call the same helper, guaranteeing the UI and server responses stay in sync.

## Role Expectations

The matrix seeds explicit role presets that can be assigned to either `App\Models\AdminUser` or the customer-facing
`App\Models\User` model:

- **`AuthorizationRole::SUPER_ADMIN`** – wildcard access to every permission.
- **`AuthorizationRole::ADMIN` / `AuthorizationRole::ADMINISTRATOR`** – full CRUD across products, categories, brands, orders, users, plus panel access.
- **`AuthorizationRole::MANAGER`** – can manage catalog content and update orders but cannot delete users or brands.
- **`AuthorizationRole::EDITOR`** – limited to catalog creation and updates.
- **`AuthorizationRole::SUPPORT`** – maintain orders and update user profiles without modifying catalog data.
- **`AuthorizationRole::VIEWER`** – read-only access across the main dashboards.
- **`AuthorizationRole::USER`** – no administrative permissions seeded.

Assigning one of these roles ensures the associated permissions are synchronized automatically for both guards.
Use the `AdminAuthorizationSeeder` when preparing local or CI environments to populate the baseline roles and permissions.

## Integrating New Abilities

1. Add the ability mapping to `config/authorization.php` under the appropriate resource.
2. Reference the new ability from policies and Filament resources via `AuthorizationMatrix::ability()` or
   `AuthorizationMatrix::check()`.
3. Re-run the `AdminAuthorizationSeeder` (or call `AuthorizationMatrix::permissionsForRole(AuthorizationRole::ADMIN)` in bespoke seeders) so the
   new permission is created for each guard and linked to the relevant roles.
