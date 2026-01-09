# Categories Translation Documentation

## Overview

The categories translation files provide comprehensive localization support for the category management system in the e-commerce platform. This documentation covers the structure, usage, and best practices for managing category-related translations.

## File Structure

### Location
- **Lithuanian**: `resources/lang/lt/categories.php`
- **English**: `resources/lang/en/categories.php`

### Organization

The translation files are organized into logical sections:

```php
return [
    // Basic fields - Core category attributes
    'single' => 'Category',
    'fields' => [...],
    
    // Sections - UI groupings
    'sections' => [...],
    
    // Tabs - Interface navigation
    'tabs' => [...],
    
    // Filters - Search and filtering options
    'filters' => [...],
    
    // Actions - User interactions
    'actions' => [...],
    
    // Bulk Actions - Multi-item operations
    'bulk_actions' => [...],
    
    // Messages - System feedback
    'messages' => [...],
    
    // UI Labels - Interface elements
    'index_close' => 'Close',
    'show_adjust_filters' => 'Adjust your filters to find the perfect products',
    
    // Help - User guidance
    'help' => [...],
    
    // Validation - Error messages
    'validation' => [...],
];
```

## Recent Additions

### UI Labels Section

Two new translation keys were added to improve user experience:

#### `index_close`
- **Purpose**: Provides accessible label for close buttons in category interfaces
- **Usage**: Used in modal dialogs, sidebars, and overlay components
- **Context**: ARIA labels for screen readers and tooltips

**Lithuanian**: `'index_close' => 'Uždaryti'`
**English**: `'index_close' => 'Close'`

#### `show_adjust_filters`
- **Purpose**: Guides users on how to refine their product search
- **Usage**: Displayed in filter panels and help text
- **Context**: Encourages user interaction with filtering system

**Lithuanian**: `'show_adjust_filters' => 'Koreguokite filtrus, kad rastumėte tobulus produktus'`
**English**: `'show_adjust_filters' => 'Adjust your filters to find the perfect products'`

## Usage Examples

### In Blade Templates

```php
<!-- Close button with accessibility -->
<button type="button"
        @click="showFilters = false"
        aria-label="{{ __('categories.index_close') }}">
    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
    </svg>
</button>

<!-- Filter guidance text -->
<p class="text-sm leading-relaxed text-sage/80">
    {{ __('categories.show_adjust_filters') }}
</p>
```

### In Livewire Components

```php
class CategoryShow extends Component
{
    public function render()
    {
        return view('livewire.pages.category.show', [
            'closeLabel' => __('categories.index_close'),
            'filterHelpText' => __('categories.show_adjust_filters'),
        ]);
    }
}
```

### In Filament Resources

```php
use Filament\Forms\Components\TextInput;

TextInput::make('name')
    ->label(__('categories.fields.name'))
    ->helperText(__('categories.help.create_first_category'))
```

## Translation Key Conventions

### Naming Pattern
- Use snake_case for all keys
- Group related keys under logical sections
- Prefix with context when needed (e.g., `index_`, `show_`)

### Structure Guidelines
- **fields**: Model attributes and form fields
- **sections**: UI groupings and panels
- **actions**: User interactions and buttons
- **messages**: System feedback and notifications
- **validation**: Error messages and constraints
- **help**: User guidance and tooltips

## Accessibility Considerations

### ARIA Labels
Translation keys like `index_close` are specifically designed for accessibility:

```php
aria-label="{{ __('categories.index_close') }}"
```

### Screen Reader Support
- Provide descriptive text for interactive elements
- Use semantic HTML with proper labeling
- Include context in translation strings

## Best Practices

### 1. Consistency
- Maintain consistent terminology across languages
- Use the same key structure in both language files
- Follow established naming conventions

### 2. Context Awareness
- Include context in translation strings where needed
- Consider cultural differences in phrasing
- Provide clear, actionable text for user guidance

### 3. Maintenance
- Keep both language files synchronized
- Test translations in actual UI context
- Review translations with native speakers

### 4. Performance
- Use translation caching in production
- Minimize translation file size
- Group related translations logically

## Integration Points

### Frontend Components
- Category listing pages (`resources/views/livewire/pages/category/show.blade.php`)
- Filter panels and modals
- Navigation menus and breadcrumbs

### Backend Resources
- Filament admin panels
- Form validation messages
- Table column headers and filters

### API Responses
- Error messages
- Status notifications
- User feedback

## Testing Translations

### Manual Testing
1. Switch between locales in the application
2. Verify all strings display correctly
3. Check for missing translations (fallback to keys)
4. Test with different text lengths

### Automated Testing
```php
// Test translation key existence
test('categories translations exist for both locales', function () {
    $keys = ['index_close', 'show_adjust_filters'];
    
    foreach ($keys as $key) {
        expect(__("categories.{$key}", [], 'lt'))->not->toBe("categories.{$key}");
        expect(__("categories.{$key}", [], 'en'))->not->toBe("categories.{$key}");
    }
});
```

## Related Documentation

- [Laravel Localization](https://laravel.com/docs/12.x/localization)
- [Filament Localization](https://filamentphp.com/docs/4.x/panels/configuration#localization)
- [Accessibility Guidelines](../accessibility/guidelines.md)
- [UI Component Documentation](../components/categories.md)

## Changelog

### 2024-01-07
- Added `index_close` translation key for accessibility
- Added `show_adjust_filters` for user guidance
- Updated both Lithuanian and English translation files
- Improved UI accessibility in category interfaces