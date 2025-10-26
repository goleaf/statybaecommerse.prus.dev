# Shipping & Fulfilment Audit

## Summary
- Shipping options store carrier names, service types, geographic scoping, and eligibility constraints so multiple delivery partners can coexist across countries, cities, and fulfilment zones.【F:app/Models/ShippingOption.php†L41-L201】【F:database/migrations/2025_09_19_145445_create_shipping_options_table.php†L16-L43】
- Pricing logic layers weight and order-amount guardrails with configurable free-shipping thresholds and fallback flat rates to calculate final shipping charges.【F:app/Models/ShippingOption.php†L244-L284】【F:app/Services/Pricing/PriceCalculator.php†L13-L45】【F:app/Services/Pricing/PriceConfiguration.php†L59-L77】
- Order-shipping records capture tracking numbers, carrier metadata, and lifecycle timestamps while Filament tooling exposes one-click actions to update shipment state or open provider tracking URLs.【F:app/Models/OrderShipping.php†L41-L183】【F:app/Filament/Resources/OrderResource/RelationManagers/OrderShippingRelationManager.php†L246-L415】
- Storefront checkout surfaces enabled options, applies discount-engine adjustments, and persists the shopper’s selection for downstream totals.【F:app/Livewire/Components/Checkout/Delivery.php†L21-L76】
- A configurable zone/method matrix governs where methods appear, with Livewire tests verifying state normalisation for complex enablement grids.【F:config/shipping.php†L5-L26】【F:app/Filament/Resources/ShippingOptionResource.php†L160-L206】【F:tests/Feature/ShippingOptionResourceTest.php†L60-L160】

## Multiple Carrier Support
- `ShippingOption` exposes `carrier_name`, `service_type`, and relations to `zones`, countries, and cities so administrators can register each carrier’s availability footprint while still reusing shared UI sorting and filters.【F:app/Models/ShippingOption.php†L41-L201】
- Factory seeds random carrier/service combinations to ensure test fixtures represent the mix of DHL, FedEx, UPS, and regional couriers expected in production.【F:database/factories/ShippingOptionFactory.php†L22-L53】
- Order-level shipment records duplicate carrier identifiers and shipping-method labels so fulfilment updates stay linked to the originating partner even after price promotions or manual edits.【F:app/Models/OrderShipping.php†L41-L183】

## Weight & Zone-based Rate Calculation
- Weight and subtotal guardrails (`min_weight`, `max_weight`, `min_order_amount`, `max_order_amount`) block ineligible options before pricing, while `calculatePriceForOrder()` returns zero when a package falls outside the declared bounds.【F:app/Models/ShippingOption.php†L55-L284】
- The migration enforces `zone_id` foreign keys, letting storefront filters or APIs limit results to a shopper’s zone before the checkout step.【F:database/migrations/2025_09_19_145445_create_shipping_options_table.php†L16-L43】
- Price configuration injects a free-shipping threshold and flat-rate fallback so total calculation remains deterministic even when no specific option is selected, mirroring admin-configured defaults.【F:app/Services/Pricing/PriceCalculator.php†L13-L45】【F:app/Services/Pricing/PriceConfiguration.php†L59-L77】

## Tracking & Shipment Lifecycle
- `OrderShipping` stores tracking URLs, numbers, timestamps, and delivered flags while derived helpers expose `isShipped`, `isDelivered`, and computed `status` strings for dashboards and notifications.【F:app/Models/OrderShipping.php†L41-L183】
- The Filament relation manager offers create/edit/delete actions alongside “Mark shipped”, “Mark delivered”, and “Track package” buttons, invoking inline updates and opening the stored tracking URL in a new tab when available.【F:app/Filament/Resources/OrderResource/RelationManagers/OrderShippingRelationManager.php†L246-L415】

## Estimated Delivery Windows
- Shipping options capture `estimated_days_min`/`max` and expose a helper that renders either a single-day commitment or a ranged window for storefront use.【F:app/Models/ShippingOption.php†L59-L242】
- Admin forms require these values alongside pricing, ensuring each carrier record surfaces a projected delivery window in management tables and Livewire components that read the appended `estimated_delivery_text` attribute.【F:app/Filament/Resources/ShippingOptionResource.php†L120-L206】

## Shipping Matrix & Availability Rules
- The shipping configuration lists canonical zones and fulfilment methods, feeding the Filament matrix widget that lets admins toggle carrier availability per zone without custom coding.【F:config/shipping.php†L5-L26】【F:app/Filament/Resources/ShippingOptionResource.php†L160-L206】
- Feature tests assert matrix data is normalised on create/edit and that partially blank rows collapse to empty arrays, preventing stale toggles from leaking into storefront eligibility checks.【F:tests/Feature/ShippingOptionResourceTest.php†L60-L164】
