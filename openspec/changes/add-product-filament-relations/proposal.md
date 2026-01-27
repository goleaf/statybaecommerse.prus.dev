# Change: Add Product Filament Relations

## Why
Product-related Filament resources and product relation management are missing or incomplete. This breaks existing admin tests and leaves key product relations unmanaged in the admin UI.

## What Changes
- Add missing Filament resources for Product variants, images, features, comparisons, similarities, and requests.
- Add Product relation managers for key product relations (variants, images, features, comparisons, similarities, requests).
- Update Product model with missing has-many relations used by relation managers.
- Update ProductVariant availability calculations to align with admin filter expectations.
- Update ProductResource form and table to support required fields, filters, and bulk publish actions.

## Impact
- Affected specs: filament-product-admin (new capability)
- Affected code: app/Models/Product.php, app/Models/ProductVariant.php, app/Filament/Resources/ProductResource.php, new Filament resources and relation managers under app/Filament/Resources/