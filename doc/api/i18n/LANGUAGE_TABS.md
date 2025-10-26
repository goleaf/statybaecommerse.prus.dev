# Filament Language Tabs Integration

## Configured Locales
- Default locales: `lt`, `en`, `ru`
- Required locales: `lt`, `en`

## Enabled Resources
- Product (form + create/edit pages)
- Category (form + create/edit pages)
- Brand (form + create/edit pages)

## How to Add New Translatable Fields
1. Wrap the field definitions in `LanguageTabs::make([...])` within the resource form.
2. List the field keys inside the page class’ `getTranslatableFields()` method so the helper trait can synchronise data.
3. When saving, the default locale is persisted into the model’s base columns and translation records are updated for all locales.

## Slug Strategy
- Default locale values are used to populate the legacy string columns.
- Translation tables maintain per-locale slugs; default locale slugs are generated if omitted.
- Uniqueness is enforced at the translation-table level.

## Testing
- Unit coverage lives in `tests/Unit/InteractsWithTranslationTabsTest.php`, ensuring the extraction helper keeps scalar data and builds locale arrays.

## Notes
- Additional models can adopt the `InteractsWithTranslationTabs` trait by implementing `App\Contracts\TranslatableRecord` and returning the appropriate field list.
- Update the config file `config/filament-language-tabs.php` if locales change.
