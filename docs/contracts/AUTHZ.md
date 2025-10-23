# Authorization Contract

This document captures the canonical role/ability matrix enforced by our
policies and Filament resources. The matrix mirrors the grants applied by
`Database\Seeders\BasicFilamentSeeder` and related seeders.

## Roles

- **super_admin** – unrestricted access (also short-circuited by `Gate::before`).
- **admin** – full management access except destructive customer/order removal
  and role management.
- **manager** – operational access focused on read/update flows.
- **editor** – catalogue & legal content management (no destructive powers).
- **user** – storefront customer; no admin panel abilities.

> The legacy `administrator` role is treated like a super administrator through
> the global gate override in `AuthServiceProvider`.

## Core resource permissions

| Resource  | Ability | super_admin | admin | manager | editor | user |
|-----------|---------|-------------|-------|---------|--------|------|
| Products  | view    | ✅ | ✅ | ✅ | ✅ | 🚫 |
|           | create  | ✅ | ✅ | 🚫 | ✅ | 🚫 |
|           | update  | ✅ | ✅ | ✅ | ✅ | 🚫 |
|           | delete  | ✅ | ✅ | 🚫 | 🚫 | 🚫 |
| Categories| view    | ✅ | ✅ | ✅ | ✅ | 🚫 |
|           | create  | ✅ | ✅ | 🚫 | ✅ | 🚫 |
|           | update  | ✅ | ✅ | ✅ | ✅ | 🚫 |
|           | delete  | ✅ | ✅ | 🚫 | 🚫 | 🚫 |
| Brands    | view    | ✅ | ✅ | ✅ | ✅ | 🚫 |
|           | create  | ✅ | ✅ | 🚫 | ✅ | 🚫 |
|           | update  | ✅ | ✅ | ✅ | ✅ | 🚫 |
|           | delete  | ✅ | ✅ | 🚫 | 🚫 | 🚫 |
| Orders    | view    | ✅ | ✅ | ✅ | 🚫 | ➖ (own only) |
|           | create  | ✅ | ✅ | 🚫 | 🚫 | ➖ (own only) |
|           | update  | ✅ | ✅ | ✅ | 🚫 | ➖ (own only, while cancellable) |
|           | delete  | ✅ | 🚫 | 🚫 | 🚫 | ➖ (own only, while cancellable) |
| Customers | view    | ✅ | ✅ | ✅ | 🚫 | ➖ (own record) |
|           | create  | ✅ | ✅ | 🚫 | 🚫 | ➖ (self-registration) |
|           | update  | ✅ | ✅ | ✅ | 🚫 | ➖ (own record) |
|           | delete  | ✅ | 🚫 | 🚫 | 🚫 | ➖ (own record) |
| Legals    | view    | ✅ | ✅ | 🚫 | ✅ | ✅ |
|           | create  | ✅ | ✅ | 🚫 | ✅ | 🚫 |
|           | update  | ✅ | ✅ | 🚫 | ✅ | 🚫 |
|           | delete  | ✅ | ✅ | 🚫 | 🚫 | 🚫 |
| Settings  | view    | ✅ | ✅ | 🚫 | 🚫 | 🚫 |
|           | edit    | ✅ | ✅ | 🚫 | 🚫 | 🚫 |

Legend: ✅ allowed, 🚫 forbidden, ➖ allowed only for the authenticated
customer acting on their own data (enforced inside policies).

The policies registered in `AuthServiceProvider` enforce the matrix above and
are consumed across controllers and Filament resources using `Gate::allows`
checks and `$this->authorize()` calls.
