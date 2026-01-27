# Change: Fix storefront add-to-cart flow and cart page

## Why
The storefront add-to-cart action is currently missing, and the cart page behavior needs to be reliable for products with variants.

## What Changes
- Add a working ddToCart action on the storefront home page with variant support.
- Ensure the storefront cart page renders correctly with added items.
- Add test coverage for add-to-cart scenarios (guest and authenticated), including variant selection.

## Impact
- Affected specs: storefront-cart (new)
- Affected code: Livewire storefront page(s), cart service/controller, cart view, and tests
