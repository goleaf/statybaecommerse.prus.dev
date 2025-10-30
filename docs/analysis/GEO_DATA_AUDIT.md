# Geographic Data Audit

## Summary
| Capability | Status | Notes |
| --- | --- | --- |
| Countries catalogue | ⚠️ Partial | Country model offers rich relationships and scopes, but the primary seeder only provisions 56 records, far short of the stated 195+ catalogue.【F:app/Models/Country.php†L34-L249】【F:database/seeders/CountrySeeder.php†L15-L1183】【483c33†L1-L1】 |
| Regions coverage | ❌ Missing pieces | Region seeders enumerate Baltic, Western European, UK, US, and Canadian entries yet the `App\\Models\\Region` class is absent, so nothing can consume or persist those records safely.【F:database/seeders/RegionSeeder.php†L15-L197】【9398a1†L1-L3】 |
| Cities dataset | ⚠️ Partial | Cities include comprehensive relationships and scoped queries, but the “all countries” seeder only defines curated lists for 11 countries, leaving most of the world without city data.【F:app/Models/City.php†L35-L198】【F:database/seeders/AllCountriesComprehensiveCitiesSeeder.php†L14-L147】 |
| Shipping zones | ⚠️ Partial | Zone model exists and multiple seeders create EU/NA/UK/Baltic zones, yet configuration leaves rate matrices largely blank, limiting runtime differentiation.【F:app/Models/Zone.php†L11-L45】【F:database/seeders/ComprehensiveMultilanguageSeeder.php†L98-L116】【F:database/seeders/ComprehensiveOrderSeeder.php†L72-L124】【F:config/shipping.php†L5-L26】 |
| Postal code validation | ⚠️ Partial | Address requests enforce pattern validation and sanitisation, but configuration and normalisation only recognise Lithuanian formats, so other countries fail validation despite seeded data.【F:app/Http/Requests/Frontend/AddressRequest.php†L26-L153】【F:config/addresses.php†L5-L42】【F:app/Support/Address/AddressDataSanitizer.php†L138-L166】 |
| Admin address ownership | ✅ Implemented | Address resource creation now honours the customer selected in the form because the model accepts the `user_id`, avoiding fallback ownership to the logged-in admin and keeping audit trails accurate.【F:app/Models/Address.php†L39-L67】 |

## Detailed Findings

### Countries
- `Country` exposes relationships to addresses, cities, taxation, and currencies, alongside query scopes for regional filtering and VAT requirements, supporting admin analytics and storefront lookups.【F:app/Models/Country.php†L34-L249】
- The primary `CountrySeeder` updates or creates entries using translation payloads, but counting the `'cca2'` entries shows only 56 seeded countries, leaving the catalogue well below the desired 195+ world coverage.【F:database/seeders/CountrySeeder.php†L15-L1183】【483c33†L1-L1】
- Address validation falls back to querying active countries when configuration omits an allow-list, so incomplete seeding directly reduces selectable countries at checkout and in admin forms.【F:app/Http/Requests/Frontend/AddressRequest.php†L102-L123】
- The Filament country infolist now references the correct schema instance, restoring the admin "view" page that previously crashed because it returned an undefined variable.【F:app/Filament/Resources/CountryResource.php†L244-L291】

### Regions
- `RegionSeeder` supplies detailed structures (codes, zone links, localisation) for Baltic states, major EU members, the UK, USA, and Canada, implying expectations for a hierarchical regional catalogue.【F:database/seeders/RegionSeeder.php†L15-L197】
- The project tree lacks `app/Models/Region.php`, confirmed by an `ls` failure, so factories, seeders, and tests referencing the model cannot execute; regional associations therefore remain effectively disabled despite migrations recreating tables.【9398a1†L1-L3】

### Cities
- The `City` model handles slug/code generation, nested hierarchies, and relations to orders, customers, and locations, with scopes to surface active, ordered records for dropdowns and analytics.【F:app/Models/City.php†L35-L205】
- City ordering now leverages the shared `OrdersByName` concern, keeping alphabetical sorting behaviour consistent with other catalogue models that expose customer-facing dropdowns while ensuring the trait emits the canonical quoted `"name"` fragment expected by legacy SQL assertions.【F:app/Models/City.php†L34-L45】【F:app/Models/Concerns/OrdersByName.php†L7-L64】
- `AllCountriesComprehensiveCitiesSeeder` iterates through existing countries but only defines explicit city lists for 11 nations, meaning most seeded countries will still lack city rows after execution.【F:database/seeders/AllCountriesComprehensiveCitiesSeeder.php†L14-L147】

### Zones
- `Zone` supports factory-backed creation and shipping option relations, while multi-stage seeders provision EU, NA, UK, and Baltic codes during demo data setup to align addresses and shipping rates.【F:app/Models/Zone.php†L11-L45】【F:database/seeders/ComprehensiveMultilanguageSeeder.php†L98-L116】【F:database/seeders/ComprehensiveOrderSeeder.php†L72-L124】
- Shipping configuration keeps per-zone rate arrays commented out and only exposes descriptive names for a matrix, so even with seeded zones, cost calculations fall back to a single default rate unless integrators extend the config.【F:config/shipping.php†L5-L26】
- Tax configuration mirrors this gap by leaving zone-specific rates empty, reducing the impact of geographic segmentation for fiscal logic.【F:config/tax.php†L5-L13】

### Postal Codes
- Frontend address requests normalise payloads, restrict states to an allow-list, and apply regex validation when a pattern exists, ensuring sanitised storage and user feedback.【F:app/Http/Requests/Frontend/AddressRequest.php†L26-L153】
- Current configuration declares only Lithuanian regions and postal-code patterns, while the sanitizer exclusively reinstates Lithuanian formats, so addresses for the other 55 seeded countries cannot pass validation without custom overrides.【F:config/addresses.php†L5-L42】【F:app/Support/Address/AddressDataSanitizer.php†L138-L166】

## Recommendations
1. **Expand country dataset**: Import a full ISO-3166 catalogue (195+ entries) and ensure translations exist for required locales to meet global coverage claims.【F:database/seeders/CountrySeeder.php†L15-L1183】
2. **Restore the Region model**: Reintroduce `App\\Models\\Region` with translation support so existing factories, migrations, and seeders can persist and query hierarchical regions.【F:database/seeders/RegionSeeder.php†L15-L197】【9398a1†L1-L3】
3. **Broaden city coverage**: Augment the “all countries” city seeder to ingest larger datasets or external APIs, ensuring every seeded country receives representative metropolitan entries.【F:database/seeders/AllCountriesComprehensiveCitiesSeeder.php†L14-L147】
4. **Wire zone-specific pricing**: Populate `config/shipping.php` and `config/tax.php` with meaningful per-zone rates and ensure shipping options inherit those adjustments to justify the zone scaffolding.【F:config/shipping.php†L5-L26】【F:config/tax.php†L5-L13】
5. **Extend postal validation**: Add patterns and sanitiser rules for each supported country, aligning frontend validation with the expanded country catalogue so customers outside Lithuania can enter addresses successfully.【F:config/addresses.php†L5-L42】【F:app/Support/Address/AddressDataSanitizer.php†L138-L166】
