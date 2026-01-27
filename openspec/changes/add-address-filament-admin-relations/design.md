# Design: Address Relations in Filament Admin

## Context
`App\Models\Address` supports:
- `user()` via `user_id`
- `country()` via `country_code -> countries.cca2`
- `countryById()` via `country_id`
- `cityById()` via `city_id`
- `orders()` and `shippingOrders()` via shared `user_id`

There is no `address_id` foreign key on `orders`, and `customers()`-style address relations are not backed by a foreign key on the `addresses` table.

## Decisions
- Treat `user`, `country`, and `city` as the authoritative admin relations for addresses
- Surface orders indirectly via the related user (rather than implying address-specific order linkage)
- Prefer `countryById`/`cityById` when the foreign keys are present, but keep `country_code` available for compatibility
- Ensure admin relations can load without global-scope surprises by selectively removing scopes in relation queries

## Risks and Mitigations
- Risk: Multiple location link strategies (`country_code` vs `country_id`) can confuse admins
- Mitigation: Show both fields clearly and prefer the FK-backed relations when available