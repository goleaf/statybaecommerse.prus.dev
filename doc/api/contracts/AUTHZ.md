# Authorization Matrix

This document captures the canonical authorization contract for the admin panel and API.
Roles and abilities are defined in `config/permissions.php` and synchronised to policies,
Filament resources, and database seeders.

## Roles

| Role     | Summary |
|----------|---------|
| `admin`  | Full control over catalog, orders, and user management. |
| `manager`| Create/update catalog items, manage orders, and review users. No destructive actions. |
| `editor` | Update existing catalog content without creating or deleting records. |
| `viewer` | Read-only access to catalog and order data. |

Aliases are mapped for backwards compatibility: `administrator` and `super_admin`
inherit `admin` abilities, while `user` inherits `viewer` abilities.

## Abilities by Domain

Each ability is expressed as `<entity>.<action>`. Policies consume the matrix and enforce
least-privilege defaults.

| Entity   | Admin | Manager | Editor | Viewer |
|----------|:-----:|:-------:|:------:|:------:|
| Product  | viewAny, view, create, update, delete, restore | viewAny, view, create, update | viewAny, view, update | viewAny, view |
| Category | viewAny, view, create, update, delete, restore | viewAny, view, create, update | viewAny, view, update | viewAny, view |
| Brand    | viewAny, view, create, update, delete, restore | viewAny, view, create, update | viewAny, view, update | viewAny, view |
| Order    | viewAny, view, create, update, delete, restore | viewAny, view, update | viewAny, view | viewAny, view |
| User     | viewAny, view, create, update, delete, restore | viewAny, view | – | – |

`admin` can perform destructive actions (`delete`, `restore`); other roles cannot. Managers
receive order-update access for operational workflows. Editors are restricted to updates on
catalog records, and viewers have read-only access.

## Seeding

`database/seeders/RolesAndPermissionsSeeder.php` reads the configuration matrix, creates any
missing roles/permissions, and aligns aliases. `database/seeders/AdminUserSeeder.php` provisions the
canonical `admin@example.com` account with comprehensive admin roles for initial access while
removing legacy credentials during reseeds.

## Filament Behaviour

Filament resources call their respective policies for navigation, page access, table actions,
and header actions. Unsupported actions are hidden in the UI and return HTTP 403 when accessed
directly.

## API Alignment

Policies are registered in `App\Providers\AuthServiceProvider` and guard both Filament and API
controllers to ensure consistent enforcement.
