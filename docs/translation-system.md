# Translation System Documentation

## Overview

This Laravel application implements a comprehensive multi-language translation system supporting Lithuanian (lt) as the primary language and English (en) as the fallback language. The system is designed to provide complete localization for both frontend and admin interfaces.

## Supported Languages

- **Lithuanian (lt)** - Primary language
- **English (en)** - Fallback language
- **German (de)** - Partial support

## Translation File Structure

### Core Translation Files

#### Admin Panel Translations
- `resources/lang/lt/admin.php` - Main admin panel translations
- `resources/lang/en/admin.php` - English admin translations

#### Module-Specific Translations
- `resources/lang/lt/campaigns.php` - Campaign management translations
- `resources/lang/lt/orders.php` - Order management translations
- `resources/lang/lt/translations.php` - Common translations used across modules
- `resources/lang/lt/shared.php` - Shared UI components

#### Filament Shield Translations
- `resources/lang/vendor/filament-shield/lt/filament-shield.php` - Filament Shield Lithuanian translations

### Translation Key Structure

The translation system uses a hierarchical key structure:

```php
// Navigation
'admin.navigation.dashboard' => 'Valdymo skydas'
'admin.navigation.catalog' => 'Katalogas'
'admin.navigation.content' => 'Turinys'

// Models
'admin.models.product' => 'Produktas'
'admin.models.category' => 'Kategorija'
'admin.models.seo_data' => 'SEO duomenys'

// Fields
'admin.seo_data.fields.title' => 'Pavadinimas'
'admin.seo_data.fields.description' => 'Aprašymas'

// Common translations
'translations.create' => 'Sukurti'
'translations.edit' => 'Redaguoti'
'translations.delete' => 'Ištrinti'
```

## Usage in Code

### Basic Translation Usage

```php
// Simple translation
__('admin.navigation.dashboard')

// With parameters
__('admin.messages.welcome', ['name' => $user->name])

// Pluralization
trans_choice('admin.messages.items', $count)
```

### Filament Resources

All Filament resources use proper translation methods:

```php
public static function getNavigationGroup(): ?string
{
    return __('admin.navigation.content');
}

public static function getNavigationLabel(): string
{
    return __('admin.navigation.news');
}

public static function getModelLabel(): string
{
    return __('admin.models.news');
}
```

### Translation Service Usage

The application includes a custom TranslationService for advanced functionality:

```php
use App\Services\Shared\TranslationService;

$translationService = new TranslationService();

// Get translation with caching
$text = $translationService->getTranslation('admin.navigation.dashboard');

// Get translation with parameters
$text = $translationService->getTranslation(
    'admin.messages.welcome', 
    ['name' => 'John']
);
```

## Key Translation Categories

### 1. Navigation Groups
- `admin.navigation.dashboard` - Valdymo skydas
- `admin.navigation.catalog` - Katalogas
- `admin.navigation.content` - Turinys
- `admin.navigation.marketing` - Rinkodara
- `admin.navigation.users` - Naudotojai
- `admin.navigation.system` - Sistema

### 2. Common Actions
- `translations.create` - Sukurti
- `translations.edit` - Redaguoti
- `translations.delete` - Ištrinti
- `translations.save` - Išsaugoti
- `translations.cancel` - Atšaukti
- `translations.search` - Paieška

### 3. UI Elements
- `admin.filament.search` - Paieška
- `admin.filament.global_search` - Globali paieška
- `admin.filament.table.list` - Sąrašas
- `admin.filament.pagination.showing` - Rodomi nuo
- `admin.filament.pagination.per_page` - puslapyje

### 4. Business Models
- `admin.models.product` - Produktas
- `admin.models.category` - Kategorija
- `admin.models.order` - Užsakymas
- `admin.models.campaign` - Kampanija
- `admin.models.seo_data` - SEO duomenys

### 5. Status Values
- `translations.active` - Aktyvus
- `translations.inactive` - Neaktyvus
- `translations.pending` - Laukiantis
- `translations.completed` - Užbaigtas
- `translations.cancelled` - Atšauktas

## Filament Shield Integration

The Filament Shield package is fully localized with Lithuanian translations:

```php
// Navigation
'nav.group' => 'Turinio valdymas'
'nav.role.label' => 'Vaidmenys'

// Resources
'resource.label.role' => 'Vaidmuo'
'resource.label.roles' => 'Vaidmenys'

// Table columns
'column.name' => 'Pavadinimas'
'column.guard_name' => 'Apsaugos pavadinimas'
'column.permissions' => 'Leidimai'

// Permissions
'view' => 'Peržiūrėti'
'create' => 'Sukurti'
'update' => 'Atnaujinti'
'delete' => 'Ištrinti'
```

## Performance Optimization

### Caching Strategy
- Configuration cache enabled for faster loading
- Translation files are cached by Laravel's built-in system
- Custom TranslationService includes caching for frequently used translations

### Best Practices
1. **Use consistent key naming**: Follow the `module.section.key` pattern
2. **Group related translations**: Keep related translations in the same file
3. **Use descriptive keys**: Make translation keys self-documenting
4. **Avoid hardcoded strings**: Always use translation functions
5. **Test translations**: Verify all translations display correctly

## Adding New Translations

### 1. Add to Translation File
```php
// In resources/lang/lt/admin.php
return [
    'new_section' => [
        'new_key' => 'Naujas vertimas',
    ],
];
```

### 2. Use in Code
```php
__('admin.new_section.new_key')
```

### 3. Add to Filament Resource
```php
public static function getNavigationLabel(): string
{
    return __('admin.new_section.navigation_label');
}
```

## Testing Translations

The application includes comprehensive tests for the translation system:

- `tests/Feature/TranslationSystemComprehensiveTest.php`
- `tests/Unit/AdminNavigationTest.php`
- `tests/Unit/AnalyticsTranslationsTest.php`

Run translation tests:
```bash
php artisan test --filter=Translation
```

## Troubleshooting

### Common Issues

1. **Missing Translation Keys**
   - Check if the key exists in the correct language file
   - Verify the key path is correct
   - Ensure the translation file is properly formatted

2. **Translation Not Loading**
   - Clear application cache: `php artisan cache:clear`
   - Clear configuration cache: `php artisan config:clear`
   - Clear view cache: `php artisan view:clear`

3. **Filament Shield Translations**
   - Ensure Shield service provider is registered
   - Check if Shield translations are published
   - Verify navigation group translations exist

### Debugging Tools

```php
// Check if translation exists
app('translator')->has('admin.navigation.dashboard', 'lt')

// Get all translations for a namespace
app('translator')->get('admin', [], 'lt')

// Check current locale
app()->getLocale()
```

## Maintenance

### Regular Tasks
1. **Review missing translations**: Check logs for missing translation warnings
2. **Update translations**: Keep translations synchronized with code changes
3. **Test new features**: Ensure new features have proper translations
4. **Performance monitoring**: Monitor translation loading performance

### File Organization
- Keep related translations together
- Use consistent naming conventions
- Document complex translation logic
- Maintain translation file structure

## Future Enhancements

1. **Translation Management Interface**: Admin panel for managing translations
2. **Automatic Translation Detection**: Tools to find untranslated strings
3. **Translation Validation**: Automated testing for translation completeness
4. **Performance Metrics**: Monitoring translation loading times
5. **Dynamic Translation Updates**: Hot-reload translations without cache clearing

---

*Last updated: $(date)*
*Version: 1.0*

