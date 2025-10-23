# Combobox field reference

The admin panel now leans on the `novadaemon/filament-combobox` plugin for relationship pickers that benefit from a two-column, searchable layout. We expose it through `App\Filament\Components\Combobox`, a thin wrapper around the vendor class that centralises our defaults and translation helpers.【F:composer.json†L11-L46】【F:app/Filament/Components/Combobox.php†L10-L54】

> **Shared defaults**: The wrapper automatically enables the dual search inputs and applies a 360 px panel height so most forms inherit a usable layout without extra method calls. Override `boxSearchs()` when you need to force the search boxes open or hidden (see the “simple” recommendation config) and adjust `height()` whenever a section demands a shorter or taller list, such as the news and discount modules.【F:app/Filament/Components/Combobox.php†L18-L26】【F:app/Filament/Resources/RecommendationConfigResourceSimple.php†L124-L159】【F:app/Filament/Resources/NewsResource.php†L135-L152】【F:app/Filament/Resources/DiscountConditionResource.php†L150-L170】

> **Build note:** The combobox styles and scripts are imported through `resources/css/filament/admin/theme.css` and `resources/js/filament/admin/theme.js` so the Vite build can bundle the vendor assets alongside the rest of the Filament theme.【F:resources/css/filament/admin/theme.css†L5-L8】【F:resources/js/filament/admin/theme.js†L1-L2】【F:vite.config.js†L17-L24】

## Where it is used today

| Resource | Field(s) | Notes |
| --- | --- | --- |
| `NewsResource` | `categories`, `tags` | Dual pickers for taxonomy assignment with localized column headers and taller panels for browsing.【F:app/Filament/Resources/NewsResource.php†L135-L154】 |
| `CampaignResource` | `targetCategories`, `targetProducts`, `targetCustomerGroups`, `discounts` | Handles all targeting relationships in one section so marketers can manage large datasets without modal hopping.【F:app/Filament/Resources/CampaignResource.php†L141-L186】 |
| `CollectionResource` | `products` | Lets merchandisers curate featured product lists with quick searching and preloaded options.【F:app/Filament/Resources/CollectionResource.php†L165-L174】 |
| `RecommendationConfigResource` | `products`, `categories` | Keeps algorithm filters sorted and searchable while forcing consistent serialization of the stored arrays.【F:app/Filament/Resources/RecommendationConfigResource.php†L118-L135】 |
| `RecommendationConfigResourceSimple` | `products`, `categories` | Extends the combobox with create-on-the-fly forms and deterministic sorting for simpler preset builders.【F:app/Filament/Resources/RecommendationConfigResourceSimple.php†L124-L159】 |
| `DiscountConditionResource` | `products`, `categories` | Drives campaign eligibility targeting with custom column labels for “available” versus “selected” lists.【F:app/Filament/Resources/DiscountConditionResource.php†L150-L170】 |

## When to choose the combobox

Use the combobox instead of `Select`/`MultiSelect` when you need:

- **Side-by-side available/selected lists** so operators can see everything they have chosen without scrolling a single column.【F:app/Filament/Resources/NewsResource.php†L135-L154】【F:app/Filament/Resources/CampaignResource.php†L141-L186】
- **Fast filtering across large, preloaded relationships**—each combobox can load hundreds of records and still remain usable because the search inputs live directly in the picker UI.【F:app/Filament/Resources/CollectionResource.php†L165-L174】【F:app/Filament/Resources/RecommendationConfigResource.php†L118-L135】
- **Extra behaviours like inline create or deterministic ordering** that would otherwise require custom components (see the simple recommendation config’s `createOptionForm` and state sort logic).【F:app/Filament/Resources/RecommendationConfigResourceSimple.php†L124-L159】

Stick with Filament’s native `Select` or `RelationManager` flows when you only need a lightweight dropdown, single-selection, or when the relationship exposes complex pivot data that the dual-list UI cannot capture.

## Configuration patterns

Every implementation follows a small set of options:

- `translatedLabels($availableKey, $selectedKey, ?$availableFallback = null, ?$selectedFallback = null)` sets both column headers with one call while safely falling back to inline copy when a locale is missing a string. Use fallbacks for multilingual rollouts (see campaigns) or rely on existing translation keys (news, discounts).【F:app/Filament/Components/Combobox.php†L32-L53】【F:app/Filament/Resources/CampaignResource.php†L143-L186】【F:app/Filament/Resources/NewsResource.php†L135-L152】【F:app/Filament/Resources/DiscountConditionResource.php†L150-L170】
- `boxSearchs([bool $visible])` still toggles the search inputs, but the wrapper calls it for you. Only override it when you want the search UI expanded on load (`true`) or intentionally hidden (`false`).【F:app/Filament/Components/Combobox.php†L18-L26】【F:app/Filament/Resources/RecommendationConfigResourceSimple.php†L124-L159】
- `height('###px')` overrides the shared 360 px default to accommodate tighter layouts or denser datasets (e.g. news at 320 px, discount conditions at 340 px).【F:app/Filament/Components/Combobox.php†L18-L26】【F:app/Filament/Resources/NewsResource.php†L135-L152】【F:app/Filament/Resources/DiscountConditionResource.php†L150-L170】
- Combine with standard Filament modifiers such as `relationship()`, `multiple()`, `preload()`, `searchable()`, and `createOptionForm()` to match the data model while keeping the combobox experience consistent.【F:app/Filament/Resources/CollectionResource.php†L165-L174】【F:app/Filament/Resources/RecommendationConfigResourceSimple.php†L124-L159】

## Localization

- News taxonomy headers live in `lang/en/news.php` and `lang/lt/news.php` under the `combobox` key so both panes follow newsroom wording.【F:lang/en/news.php†L177-L185】【F:lang/lt/news.php†L177-L185】
- Discount condition labels (`Available products`, `Selected categories`, etc.) live next to the rest of the discount copy in `lang/en/discount_conditions.php` and should be mirrored in other locales when the feature rolls out beyond English.【F:lang/en/discount_conditions.php†L16-L33】
- Campaign pickers resolve translation keys like `campaigns.combobox.options.target_categories`; if a locale does not provide them yet, the helper falls back to the inline English fallback so nothing breaks during rollout.【F:app/Filament/Resources/CampaignResource.php†L143-L186】【F:app/Filament/Resources/CampaignResource.php†L337-L344】

Add any new combobox translations in the same module-specific language files to keep localisation discoverable for content and operations teams.
