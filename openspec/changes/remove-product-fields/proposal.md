# Change: Remove Deprecated Product Fields

## Why
Product records still carry legacy ecommerce fields that are no longer used. They add confusion to imports, tests, and storefront queries. We want a cleaner schema and a single visibility rule based on `is_enabled` and the published scope.

## What Changes
- Remove the following product fields from the schema and all usage:
  - `type`, `summary`, `sale_price`, `track_stock`, `is_visible`, `video_url`, `button_text`,
    `gallery`, `request_count`/`requests_count`, `view_count`, `views_count`, `last_viewed_at`,
    `sort_order`, `tax_class`, `download_limit`, `download_expiry`.
- Remove `summary` and other removed fields from product translations.
- Update storefront/search queries to rely on `is_enabled` plus the published scope
  (`status` + `published_at`).
- Make product import validation fail if any removed columns are present.

## Impact
- Breaking schema change (greenfield/resettable DB); update existing migrations only.
- Affected code: product queries, importer validation, factories/seeders, tests.
- Data model: products and product_translations tables cleaned; related indexes removed/updated.