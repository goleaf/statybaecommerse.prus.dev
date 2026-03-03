# Change: Add Supplier Admin CRUD and Product Supplier Linking

## Why
The admin panel needs a dedicated Supplier management workflow and products must be assignable to one or more suppliers for operational consistency.

## What Changes
- Add a new `Supplier` domain model and table with soft deletes.
- Add a Filament Supplier resource in left navigation under Products/Catalog.
- Add many-to-many Product↔Supplier relationship using `product_supplier`.
- Add Supplier selection to Product admin form and Supplier column/filter to Products table.
- Migrate inventory supplier foreign keys from `partners` to `suppliers` with backfill from used partner records.
- Add dedicated supplier permissions and policy checks.

## Impact
- Affected specs: supplier-admin, product-suppliers
- Affected code: migrations, models, Filament resources/forms/tables/pages, auth policy registration, authorization matrix, tests.
- Data model: introduces `suppliers` and `product_supplier`; repoints `variant_inventories.supplier_id` foreign key.
