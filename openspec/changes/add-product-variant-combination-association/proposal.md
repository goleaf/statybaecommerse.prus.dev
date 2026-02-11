# Change: Associate Variant Combinations to Products

## Why
Admins need to reuse existing variant combinations when managing products. Today the Product edit page only allows creating new combinations, so existing combinations cannot be attached through the UI.

## What Changes
- Add an Associate action to the Product → Variant Combinations relation manager.
- Only show unassigned variant combinations in the associate list (product_id is null).
- Enable searching the associate list by combination hash / formatted combinations.

## Impact
- Affected specs: product-variant-combinations (admin UX)
- Affected code: app/Filament/Resources/ProductResource/RelationManagers/VariantCombinationsRelationManager.php
- Data model: no migrations
