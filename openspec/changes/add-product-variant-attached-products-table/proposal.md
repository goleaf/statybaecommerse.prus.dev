# Change: Show Attached Products on Product Variant View

## Why
Admins need to quickly verify which product a variant belongs to while reviewing variant details. Today, the variant view does not surface the attached product in a clear table block, forcing extra navigation.

## What Changes
- Add a read-only “Attached Products” table block to the Product Variant view page, rendered below the variant details.
- Source rows from the variant’s `product` relation and display key fields (image, name, SKU, price).
- Ensure the block uses existing translation keys for headings and column labels; add any missing keys if needed.

## Impact
- Affected specs: product-variant-admin (UI enhancement)
- Affected code: app/Filament/Resources/ProductVariantResource/Schemas/ProductVariantInfolist.php, app/Filament/Resources/ProductVariantResource/Pages/ViewProductVariants.php (or new widget), lang/*/admin.php, lang/*/messages.php
- Data model: no migrations