# Feature Highlights

## Fulfilment & Logistics
- Shipping option delivery ranges in the Filament admin now display a precise window even when carriers promise same-day (0 day) service or when only one bound is stored, helping staff quickly spot incomplete data.

## Developer Experience
- Husky Git hooks rely on the restored bootstrap shim so the project's local Node toolchain runs automatically during commits and pushes.

## Reference
- Review `app/Filament/Resources/ShippingOptionResource.php` for the table presentation logic and `app/Models/ShippingOption.php` for the accessor reused across storefront components.
- Developer tooling now documents the restored Husky bootstrap shim, keeping cross-platform Git hooks consistent for contributors.
