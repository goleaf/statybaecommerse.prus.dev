# Matrix Choice Field Usage

This project integrates the [lara-zeus/matrix-choice](https://github.com/lara-zeus/matrix-choice) Filament component to capture grid-style selections. The component renders an accessible table of radio buttons or checkboxes and stores its state as JSON on the model. Tailwind is configured to scan the package views so that the bundled styling ships with the admin panel build.

## Where matrices are used

| Resource | Field | Rows | Columns | Mode |
| --- | --- | --- | --- | --- |
| **Users** (`App\Filament\Resources\UserResource`) | `permissions_matrix` | Core modules (Products, Categories, Brands, Orders, Users) | Actions (List, View, Create, Update, Delete) | Checkbox |
| **Shipping options** (`App\Filament\Resources\ShippingOptionResource`) | `shipping_matrix` | Fulfilment zones (LT, LV, EE, PL, EU) | Service types (Courier, Pickup, Locker, Express, Free over threshold) | Checkbox |
| **Channels** (`App\Filament\Resources\ChannelResource`) | `payment_matrix` | Customer regions (LT, LV, EE, PL, EU) | Sales touchpoints (Web, POS, Marketplace) | Checkbox |
| **Products** (`App\Filament\Resources\ProductResource`) | `variant_attribute_matrix` | Variant attributes (Size, Color, Material) | SKU groupings (Primary, Bundle, Limited) | Checkbox |

Each matrix is constructed through `App\Support\Forms\MatrixFactory`, which exposes helpers for checkbox and radio grids. Should you need a bespoke grid, call `MatrixFactory::checkboxGrid()` or `::radioGrid()` with your row/column labels and optional custom label text.

## Data shape

All matrix columns are persisted as JSON columns and cast to `array` on their models:

```php
// Example structure for permissions_matrix
[
    "products" => ["viewAny", "view", "update"],
    "orders" => ["viewAny", "update"],
    // ...
]

// Example structure for shipping_matrix
[
    "lt" => ["courier", "pickup", "locker"],
    "eu" => ["courier", "express"],
]
```

For radio-mode matrices the stored value per row is a single string instead of an array.

## Extending the grids

* Update the translation files (`lang/en/*.php`, `lang/lt/*.php`) to add new row or column labels so the UI remains multilingual.
* Adjust the arrays passed to `MatrixFactory` inside the resource form definition to add or remove rows/columns.
* When persisting additional behaviour (for example syncing Spatie permissions), iterate the decoded array and map row/column pairs to your downstream data.

## Styling & build pipeline

* Tailwind scans `./vendor/lara-zeus/matrix-choice/resources/views/**/*.blade.php` to compile the plugin styles.
* Any additional matrices should reuse the existing Filament theme build (`npm run build`) to refresh compiled assets.

## Testing hints

* The unit test suite includes `MatrixFieldCastTest` verifying that models cast the JSON columns to arrays.
* When adding new matrices, extend that test or create a dedicated feature test to cover round-tripping the form data.

