# Matrix choice permission grids

The Filament admin uses a centralised permission matrix to hide or show navigation entries, CRUD actions, and panel access across resources. Each cell in the matrix maps a resource-specific ability (column) to a concrete Spatie permission string, and the resources, seeders, and enums all rely on that structure staying consistent.【F:config/authorization.php†L11-L49】【F:app/Support/Authorization/AuthorizationMatrix.php†L19-L118】

## Matrix-enabled resources

| Resource or surface | Why it reads the matrix |
| --- | --- |
| Admin panel entry (Filament guard) | `AdminUser::canAccessPanel()` checks the `panel.access` cell before letting a user into Filament at all.【F:app/Models/AdminUser.php†L32-L59】 |
| ProductResource | Every CRUD gate (`shouldRegisterNavigation`, `canCreate`, etc.) delegates to `AuthorizationMatrix::check('products', …)` so catalogue management follows the matrix.【F:app/Filament/Resources/ProductResource.php†L76-L113】 |
| CategoryResource | Uses the same matrix-driven checks to guard taxonomy CRUD and navigation registration.【F:app/Filament/Resources/CategoryResource.php†L54-L92】 |
| BrandResource | Locks list, create, edit, delete, and navigation visibility behind the `brands` row of the matrix.【F:app/Filament/Resources/BrandResource.php†L48-L91】 |
| OrderResource | Wraps order browsing and mutation in the `orders` row, ensuring support teams get only the actions their role grants.【F:app/Filament/Resources/OrderResource.php†L84-L121】 |
| UserResource | Defers to the `users` row to decide which operators can manage customers and fellow admins.【F:app/Filament/Resources/UserResource.php†L57-L95】 |

Downstream policies (for example `ProductPolicy`) mirror the same checks so API or background access aligns with the UI, keeping the matrix authoritative across the stack.【F:app/Policies/ProductPolicy.php†L14-L54】

## Row & column semantics

Rows in `config/authorization.php` represent Filament-facing domains (`panel`, `products`, `categories`, `brands`, `orders`, `users`). Columns inside each row enumerate the CRUD-style abilities that a resource exposes, and the value of each cell is the canonical permission string saved to the database (`products.create`, `orders.update`, etc.).【F:config/authorization.php†L11-L49】 The `AuthorizationMatrix::ability()` helper simply looks up those row/column pairs and throws if a resource/ability combination is missing, which prevents typos from leaking into production.【F:app/Support/Authorization/AuthorizationMatrix.php†L19-L47】

Guards listed under the same config key (`admin`, `web`) control which authentication guards receive seeded permissions, allowing both the Filament guard and the storefront/user guard to share the matrix definitions when necessary.【F:config/authorization.php†L6-L9】【F:app/Support/Authorization/AuthorizationMatrix.php†L163-L172】

## Stored shape & transport

`AuthorizationMatrix::roles()` normalises the config into arrays shaped like:

```json
[
  {
    "role": "admin",
    "permissions": [
      "panel.access.admin",
      "products.viewAny",
      "products.view",
      "products.create",
      "products.update",
      "products.delete",
      "…"
    ]
  }
]
```

The actual helper returns `AuthorizationRole` enums for the `role` key, but anything serialising the structure (seeders, tests, API transformers) receives plain arrays that mirror the JSON above. That keeps downstream consumers deterministic and lets test fixtures assert against a stable contract.【F:app/Support/Authorization/AuthorizationMatrix.php†L96-L155】 Unit coverage in `AuthorizationMatrixTest` exercises that shape for admin/support roles and confirms both guards are exposed.【F:tests/Unit/AuthorizationMatrixTest.php†L13-L35】

## Extending the matrix

1. **Add the resource row or new ability** – extend the `abilities` array in `config/authorization.php`, keeping the permission string naming convention (`resource.ability`).【F:config/authorization.php†L11-L49】
2. **Grant it to roles** – update the appropriate entries under `roles` in the same config so the matrix keeps role definitions in sync.【F:config/authorization.php†L52-L98】
3. **Expose the enum** – if you introduce a brand-new role, add it to `App\Enums\AuthorizationRole` so helper methods and seeders stay type-safe.【F:app/Enums/AuthorizationRole.php†L7-L33】
4. **Refresh coverage** – extend `AuthorizationMatrixTest` (or add a new assertion) so CI verifies the new permissions remain discoverable.【F:tests/Unit/AuthorizationMatrixTest.php†L13-L35】

After editing the config or enum, rerun whichever seeder provisions your environment (see below) so the Spatie permission tables pick up the new mappings.

## Pivot sync & seeding responsibilities

- `AdminAuthorizationSeeder` is the canonical sync—looping every configured guard, creating each permission string, and calling `syncPermissions()` for each role so the pivot tables match the matrix exactly.【F:database/seeders/AdminAuthorizationSeeder.php†L15-L56】
- `EnhancedFilamentSeeder` seeds an initial admin user and ensures the admin role pulls the full permission set before layering extra bespoke permissions needed for demo data.【F:database/seeders/EnhancedFilamentSeeder.php†L30-L75】
- `LithuanianBuilderShopSeeder` mirrors the pattern for the storefront demo, preloading admin/manager roles with matrix-driven permissions and then handing out supplemental abilities like `view_reports`.【F:database/seeders/LithuanianBuilderShopSeeder.php†L52-L105】

Whenever you adjust the matrix, rerun the relevant seeder (or `php artisan db:seed --class=AdminAuthorizationSeeder`) so Spatie’s pivot tables reflect the new state before QA or production smoke tests.
