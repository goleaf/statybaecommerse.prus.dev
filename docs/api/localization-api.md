# Localization API Documentation

## Overview

The localization API provides access to translation strings and locale management functionality for the e-commerce platform. This document covers the translation file structure, helper functions, and integration patterns.

## Translation File Structure

### Categories Translation Schema

**File**: `resources/lang/{locale}/categories.php`

```php
<?php

declare(strict_types=1);

/**
 * Categories translation file structure.
 * 
 * Provides comprehensive localization support for category management
 * including field labels, UI elements, validation messages, and user guidance.
 * 
 * @return array<string, mixed> Translation key-value pairs organized by context
 */
return [
    // Basic model identification
    'single' => string,           // Singular form: "Category"
    'plural' => string,           // Plural form: "Categories" (EN only)
    'title' => string,            // Page title: "Categories" (EN only)
    
    // Model field labels
    'fields' => [
        'name' => string,              // Category name field
        'slug' => string,              // URL slug field
        'description' => string,       // Full description field
        'short_description' => string, // Brief description field
        'parent' => string,            // Parent category field
        'sort_order' => string,        // Display order field
        'is_enabled' => string,        // Active status field
        'is_visible' => string,        // Visibility status field
        'is_featured' => string,       // Featured status field
        'show_in_menu' => string,      // Menu display field
        'product_limit' => string,     // Product count limit field
        'seo_title' => string,         // SEO title field
        'seo_description' => string,   // SEO description field
        'seo_keywords' => string,      // SEO keywords field
        'image' => string,             // Category image field
        'banner' => string,            // Category banner field
        'gallery' => string,           // Image gallery field
        'children' => string,          // Subcategories field
        'children_count' => string,    // Subcategory count field
        'products_count' => string,    // Product count field
        'created_at' => string,        // Creation timestamp field
        'updated_at' => string,        // Update timestamp field
    ],
    
    // UI section groupings
    'sections' => [
        'basic_information' => string, // Basic info section
        'settings' => string,          // Settings section
        'media' => string,             // Media section
        'hierarchy' => string,         // Category hierarchy section
    ],
    
    // Interface navigation tabs
    'tabs' => [
        'translations' => string,      // Translations tab
        'lithuanian' => string,        // Lithuanian language tab
        'english' => string,           // English language tab
    ],
    
    // Filter and search options
    'filters' => [
        'is_enabled' => string,           // Enabled filter
        'is_featured' => string,          // Featured filter
        'is_visible' => string,           // Visible filter
        'show_in_menu' => string,         // Menu display filter
        'parent' => string,               // Parent category filter
        'has_children' => string,         // Has subcategories filter
        'with_children' => string,        // With subcategories option
        'without_children' => string,     // Without subcategories option
        'has_products' => string,         // Has products filter
        'with_products' => string,        // With products option
        'without_products' => string,     // Without products option
        'products_count_range' => string, // Product count range filter
        'no_products' => string,          // No products option
        '1_to_10_products' => string,     // 1-10 products range
        '11_to_50_products' => string,    // 11-50 products range
        '51_to_100_products' => string,   // 51-100 products range
        '100_plus_products' => string,    // 100+ products range
        'created_from' => string,         // Creation date from filter
        'created_until' => string,        // Creation date until filter
        'has_seo' => string,              // Has SEO data filter
        'root_categories' => string,      // Root categories filter
    ],
    
    // User action labels
    'actions' => [
        'translate' => string,        // Translate action
        'view_products' => string,    // View products action
        'duplicate' => string,        // Duplicate action
        'enable_selected' => string,  // Enable selected action
        'disable_selected' => string, // Disable selected action
        'feature_selected' => string, // Feature selected action
    ],
    
    // Bulk operation labels
    'bulk_actions' => [
        'enable_selected' => string,  // Bulk enable action
        'disable_selected' => string, // Bulk disable action
        'feature_selected' => string, // Bulk feature action
    ],
    
    // System feedback messages
    'messages' => [
        'created' => string,               // Success creation message
        'updated' => string,               // Success update message
        'deleted' => string,               // Success deletion message
        'status_changed' => string,        // Status change message (EN only)
        'featured_toggled' => string,      // Featured toggle message (EN only)
        'no_categories_found' => string,   // No results message
        'create_first_category' => string, // Empty state message
    ],
    
    // UI accessibility labels
    'index_close' => string,           // Close button ARIA label
    'show_adjust_filters' => string,   // Filter guidance text
    
    // User guidance and help
    'help' => [
        'create_first_category' => string, // Category creation help
    ],
    
    // Validation error messages
    'validation' => [
        'name_required' => string,         // Name required error (EN only)
        'name_max' => string,              // Name max length error (EN only)
        'slug_required' => string,         // Slug required error (EN only)
        'slug_unique' => string,           // Slug uniqueness error (EN only)
        'slug_alpha_dash' => string,       // Slug format error (EN only)
        'description_max' => string,       // Description max length error (EN only)
        'short_description_max' => string, // Short description max error (EN only)
        'seo_title_max' => string,         // SEO title max length error
        'seo_description_max' => string,   // SEO description max length error
        'seo_keywords_max' => string,      // SEO keywords max length error
        'sort_order_numeric' => string,    // Sort order numeric error
        'product_limit_numeric' => string, // Product limit numeric error
    ],
];
```

## Helper Functions

### Laravel Translation Helpers

#### `__()`
**Purpose**: Retrieve translation string with optional parameters
**Signature**: `__(string $key, array $replace = [], string $locale = null): string`

```php
// Basic usage
__('categories.fields.name')

// With parameters
__('categories.messages.created', ['name' => $category->name])

// With specific locale
__('categories.index_close', [], 'lt')
```

#### `trans()`
**Purpose**: Retrieve translation with pluralization support
**Signature**: `trans(string $key, array $replace = [], string $locale = null): string|array`

```php
// Get entire translation file
$categories = trans('categories', [], 'lt');

// Get specific section
$fields = trans('categories.fields', [], 'en');
```

#### `trans_choice()`
**Purpose**: Handle pluralization based on count
**Signature**: `trans_choice(string $key, int $number, array $replace = [], string $locale = null): string`

```php
// Pluralization example
trans_choice('categories.products_count', $count, ['count' => $count])
```

### Custom Translation Methods

#### Category Model Translation Support
```php
/**
 * Get translated category name.
 * 
 * @param string|null $locale Target locale (defaults to app locale)
 * @return string Translated category name
 */
public function getTranslatedName(?string $locale = null): string
{
    return $this->getTranslation('name', $locale ?? app()->getLocale());
}

/**
 * Get translated category description.
 * 
 * @param string|null $locale Target locale (defaults to app locale)
 * @return string|null Translated category description
 */
public function getTranslatedDescription(?string $locale = null): ?string
{
    return $this->getTranslation('description', $locale ?? app()->getLocale());
}
```

## API Endpoints

### Locale Management

#### GET `/api/locales`
**Purpose**: Retrieve available locales
**Authentication**: Not required
**Response**:
```json
{
    "data": [
        {
            "code": "lt",
            "name": "Lietuvių",
            "native_name": "Lietuvių",
            "is_default": true
        },
        {
            "code": "en", 
            "name": "English",
            "native_name": "English",
            "is_default": false
        }
    ]
}
```

#### POST `/api/locale`
**Purpose**: Set user locale preference
**Authentication**: Optional (persists in session if authenticated)
**Request Body**:
```json
{
    "locale": "lt"
}
```
**Response**:
```json
{
    "message": "Locale updated successfully",
    "locale": "lt"
}
```

### Translation Endpoints

#### GET `/api/translations/{locale}`
**Purpose**: Retrieve all translations for a locale
**Authentication**: Not required
**Parameters**:
- `locale` (string): Target locale code (lt, en)
**Response**:
```json
{
    "locale": "lt",
    "translations": {
        "categories": {
            "fields": {
                "name": "Pavadinimas"
            }
        }
    }
}
```

#### GET `/api/translations/{locale}/{namespace}`
**Purpose**: Retrieve specific translation namespace
**Authentication**: Not required
**Parameters**:
- `locale` (string): Target locale code
- `namespace` (string): Translation namespace (e.g., 'categories')
**Response**:
```json
{
    "locale": "lt",
    "namespace": "categories",
    "translations": {
        "index_close": "Uždaryti",
        "show_adjust_filters": "Koreguokite filtrus, kad rastumėte tobulus produktus"
    }
}
```

## Integration Patterns

### Blade Template Integration

#### Basic Translation
```php
<!-- Field label -->
<label for="name">{{ __('categories.fields.name') }}</label>

<!-- Accessibility label -->
<button aria-label="{{ __('categories.index_close') }}">
    <svg>...</svg>
</button>

<!-- Help text -->
<p class="help-text">{{ __('categories.show_adjust_filters') }}</p>
```

#### Conditional Translation
```php
@if(app()->getLocale() === 'lt')
    <p>{{ __('categories.help.create_first_category') }}</p>
@endif
```

### Livewire Component Integration

```php
<?php

namespace App\Livewire\Pages\Category;

use Livewire\Component;

class Show extends Component
{
    /**
     * Get close button label for current locale.
     */
    public function getCloseButtonLabelProperty(): string
    {
        return __('categories.index_close');
    }
    
    /**
     * Get filter help text for current locale.
     */
    public function getFilterHelpTextProperty(): string
    {
        return __('categories.show_adjust_filters');
    }
    
    public function render()
    {
        return view('livewire.pages.category.show');
    }
}
```

### Filament Resource Integration

```php
<?php

namespace App\Filament\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;

class CategoryResource extends Resource
{
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')
                ->label(__('categories.fields.name'))
                ->helperText(__('categories.help.create_first_category'))
                ->required()
                ->maxLength(255),
        ]);
    }
}
```

## Error Handling

### Missing Translation Keys
When a translation key is missing, Laravel returns the key itself:

```php
// Missing key returns: "categories.missing_key"
$missing = __('categories.missing_key');

// Check for missing translations
if ($missing === 'categories.missing_key') {
    // Handle missing translation
    Log::warning("Missing translation key: categories.missing_key");
}
```

### Locale Fallback
Laravel automatically falls back to the default locale when translations are missing:

```php
// If 'categories.new_key' doesn't exist in 'en', falls back to 'lt'
app()->setLocale('en');
$translation = __('categories.new_key'); // Returns Lithuanian version if English missing
```

## Performance Considerations

### Translation Caching
```php
// Enable translation caching in production
'cache_translations' => env('CACHE_TRANSLATIONS', true),

// Clear translation cache
php artisan config:cache
php artisan route:cache
```

### Lazy Loading
```php
// Load translations on demand
$translations = app('translator')->get('categories', [], 'lt');
```

## Validation Rules

### Translation Key Validation
```php
// Validate translation key exists
'translation_key' => [
    'required',
    'string',
    function ($attribute, $value, $fail) {
        if (__($value) === $value) {
            $fail("Translation key {$value} does not exist.");
        }
    },
],
```

## Related Documentation

- [Laravel Localization](https://laravel.com/docs/12.x/localization)
- [Filament Localization](https://filamentphp.com/docs/4.x/panels/configuration#localization)
- [Component Documentation](../components/category-ui.md)
- [Localization Architecture](../architecture/localization-architecture.md)