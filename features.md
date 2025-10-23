# Feature Highlights

## Fulfilment & Logistics
- Shipping option delivery ranges in the Filament admin now display a precise window even when carriers promise same-day (0 day) service or when only one bound is stored, helping staff quickly spot incomplete data.

## Developer Experience
- Restored Husky's bootstrap shim so repository Git hooks continue invoking Pint, PHPUnit, and other local tooling without manual setup, while still explaining the upcoming v10 deprecation change to contributors.

## Reference
- Review `app/Filament/Resources/ShippingOptionResource.php` for the table presentation logic and `app/Models/ShippingOption.php` for the accessor reused across storefront components.
- Developer tooling now documents the restored Husky bootstrap shim, keeping cross-platform Git hooks consistent for contributors.
