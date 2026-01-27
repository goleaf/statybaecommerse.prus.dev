# Change: Add Address Admin Relations in Filament

## Why
The Address model defines several important relations (user, country, city, and user-scoped orders), but the Filament admin panel does not currently expose Address CRUD or relation managers for these links. This makes address data hard to inspect and manage from the admin UI.

## What Changes
- Add a dedicated Address Filament resource for admin CRUD
- Expose core Address relations in Filament (user, country, city)
- Add relation managers on related resources so admins can manage addresses from those records
- Ensure relation queries are robust to global scopes and optional foreign keys

## Impact
- Affected specs: filament-address-admin
- Affected code: `app/Filament/Resources/*`, Address-related relation managers, and focused Filament tests