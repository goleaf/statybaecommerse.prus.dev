# Combobox field reference

The admin panel now leans on the `novadaemon/filament-combobox` plugin for relationship pickers that benefit from a two-column, searchable layout. The package ships with Filament and is available to every resource through `Filament\Forms\Components\Combobox`.【F:composer.json†L11-L46】

## Where it is used today

| Resource | Field(s) | Notes |
| --- | --- | --- |
| `NewsResource` | `categories`, `tags` | Dual pickers for taxonomy assignment with localized column headers and taller panels for browsing.【F:app/Filament/Resources/NewsResource.php†L134-L155】 |
| `CampaignResource` | `targetCategories`, `targetProducts`, `targetCustomerGroups`, `discounts` | Handles all targeting relationships in one section so marketers can manage large datasets without modal hopping.【F:app/Filament/Resources/CampaignResource.php†L140-L177】 |
| `CollectionResource` | `products` | Lets merchandisers curate featured product lists with quick searching and preloaded options.【F:app/Filament/Resources/CollectionResource.php†L165-L175】 |
| `RecommendationConfigResource` | `products`, `categories` | Keeps algorithm filters sorted and searchable while forcing consistent serialization of the stored arrays.【F:app/Filament/Resources/RecommendationConfigResource.php†L120-L139】 |
| `RecommendationConfigResourceSimple` | `products`, `categories` | Extends the combobox with create-on-the-fly forms and deterministic sorting for simpler preset builders.【F:app/Filament/Resources/RecommendationConfigResourceSimple.php†L124-L162】 |
| `DiscountConditionResource` | `products`, `categories` | Drives campaign eligibility targeting with custom column labels for “available” versus “selected” lists.【F:app/Filament/Resources/DiscountConditionResource.php†L142-L166】 |

## When to choose the combobox

Use the combobox instead of `Select`/`MultiSelect` when you need:

- **Side-by-side available/selected lists** so operators can see everything they have chosen without scrolling a single column.【F:app/Filament/Resources/NewsResource.php†L134-L155】【F:app/Filament/Resources/CampaignResource.php†L140-L177】
- **Fast filtering across large, preloaded relationships**—each combobox can load hundreds of records and still remain usable because the search inputs live directly in the picker UI.【F:app/Filament/Resources/CollectionResource.php†L165-L175】【F:app/Filament/Resources/RecommendationConfigResource.php†L120-L139】
- **Extra behaviours like inline create or deterministic ordering** that would otherwise require custom components (see the simple recommendation config’s `createOptionForm` and state sort logic).【F:app/Filament/Resources/RecommendationConfigResourceSimple.php†L124-L162】

Stick with Filament’s native `Select` or `RelationManager` flows when you only need a lightweight dropdown, single-selection, or when the relationship exposes complex pivot data that the dual-list UI cannot capture.

## Configuration patterns

Every implementation follows a small set of options:

- `boxSearchs([bool $visible])` toggles the plugin’s search inputs. Call it without arguments to show the inputs on demand, or pass `true` when you want them visible immediately (as in the “simple” recommendation config).【F:app/Filament/Resources/CampaignResource.php†L140-L177】【F:app/Filament/Resources/RecommendationConfigResourceSimple.php†L124-L151】
- `height('###px')` keeps the dual lists tall enough for comfortable browsing (320–360 px in current resources).【F:app/Filament/Resources/NewsResource.php†L134-L153】【F:app/Filament/Resources/CampaignResource.php†L140-L177】
- `optionsLabel()` / `selectedLabel()` override the column headers so translators can localize “available” vs “selected” phrasing per resource.【F:app/Filament/Resources/NewsResource.php†L134-L153】【F:app/Filament/Resources/CampaignResource.php†L140-L177】【F:app/Filament/Resources/DiscountConditionResource.php†L142-L166】
- Combine with standard Filament modifiers such as `relationship()`, `multiple()`, `preload()`, `searchable()`, and `createOptionForm()` to match the data model while keeping the combobox experience consistent.【F:app/Filament/Resources/CollectionResource.php†L165-L175】【F:app/Filament/Resources/RecommendationConfigResourceSimple.php†L124-L162】

## Localization

- News taxonomy headers live in `lang/en/news.php` and `lang/lt/news.php` under the `combobox` key so both panes follow newsroom wording.【F:lang/en/news.php†L177-L185】【F:lang/lt/news.php†L177-L185】
- Discount condition labels (`Available products`, `Selected categories`, etc.) live next to the rest of the discount copy in `lang/en/discount_conditions.php` and should be mirrored in other locales when the feature rolls out beyond English.【F:lang/en/discount_conditions.php†L16-L33】
- Campaign pickers resolve translation keys like `campaigns.combobox.options.target_categories`; if a locale does not provide them yet, the helper falls back to the inline English fallback so nothing breaks during rollout.【F:app/Filament/Resources/CampaignResource.php†L140-L177】【F:app/Filament/Resources/CampaignResource.php†L337-L344】

Add any new combobox translations in the same module-specific language files to keep localisation discoverable for content and operations teams.
