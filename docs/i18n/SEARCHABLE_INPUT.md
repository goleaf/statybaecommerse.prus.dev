# Filament Searchable Input Integration

This project uses the [`defstudio/filament-searchable-input`](https://filamentphp.com/plugins/defstudio-searchable-input) plugin to improve lookup experiences in the admin panel. The plugin is available across the following resources:

- **Orders** – customer selection now relies on an autocomplete field that searches by name, email, or phone and persists the chosen `user_id`.
- **Order Items** – product lookups support SKU/EAN/name searches and hydrate related fields (name, SKU, unit price) without additional queries.
- **Cart Items** – product selectors autocomplete SKUs and update price/name fields, while resetting variant picks appropriately.
- **Addresses** – customer, address line, city, and city ID fields provide suggestions; selecting a city also synchronises the textual city and country code.
- **Coupon Usages** – coupon and customer selectors use autocomplete for quick lookups.
- **Inventory** – warehouses now attach products via free-text lookup instead of scrolling select menus.
- **Prices** – administrators can find products by SKU or name before setting price amounts.
- **Product Requests** – support agents can connect incoming requests to products using the same search experience.
- **Wishlist Items** – product pickers are searchable and clear dependent variant selectors when the product changes.

### Supporting services

Reusable search helpers live in `app/Support/Search/`:

| Service | Purpose |
| --- | --- |
| `ProductSearch` | Provides free-text and rich product suggestions with normalised payload metadata for SKU/barcode/name lookups. |
| `CustomerSearch` | Returns customer matches keyed by name/email/phone. |
| `AddressSearch` | Supplies formatted address suggestions, city lists, and city metadata. |
| `CouponSearch` | Exposes coupon code/name lookups. |

Each service scopes queries, limits results to 15 entries, and returns either plain strings or `SearchResult` DTOs. The `App\Support\Search\SearchResultPayload` helper now ensures every DTO exposes a predictable `{ id, label, payload }` structure so Filament components and Livewire actions only need to read from the nested `payload` array.

### Searchable component helper

The `App\Support\Filament\SearchableComponentHelper` centralises `afterStateHydrated` and `afterStateUpdated` logic so every Filament form clears payloads and Livewire options consistently. Use `hydrate()` to repopulate labels when editing a record and `syncSelectedRecord()` to update related attributes (such as `product_id`) while clearing stale state when the field is emptied. The helper also exposes a `clear()` utility that resets both the selected value and dropdown options, ensuring the plugin does not keep orphaned payload metadata in the form state. Every method normalises the payload into the canonical `{ id, label, ... }` structure so localisation layers receive the same metadata as search results.【F:app/Support/Filament/SearchableComponentHelper.php†L21-L205】

### Theme requirements

The admin panel registers `resources/css/filament/admin/theme.scss` as its custom theme. This stylesheet sources Filament app files, in-house components, and the plugin blade views so Tailwind can compile all utility classes:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

@import '../../filament-enhancements.scss';

@source '../../../../app/Filament';
@source '../../../../resources/views/filament';
@source '../../../../vendor/defstudio/filament-searchable-input/resources/**/*.blade.php';
```

### Testing

Targeted Pest tests cover every search service and assert that core product-centric resources (orders, carts, pricing, inventory, wishlists, requests) expose the plugin’s `searchUsing` behaviour. Run the focused suite with:

```bash
php artisan test --group=searchable-input
```

The tests seed minimal data and assert formatted labels, metadata payloads, and live component responses.
