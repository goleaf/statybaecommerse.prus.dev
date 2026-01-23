---
inclusion: always
---

# Localization Guidelines

## Translation File Management

### File Structure
All translation files follow Laravel's standard structure:
- `resources/lang/lt/` - Lithuanian translations (default)
- `resources/lang/en/` - English translations
- `resources/lang/{locale}.json` - JSON format for simple key-value pairs

### Key Naming Conventions
- Use `snake_case` for all translation keys
- Group related keys under logical sections
- Prefix with context when needed (e.g., `index_`, `show_`, `admin_`)

### Required Translations
Every user-facing string must have both Lithuanian (`lt`) and English (`en`) translations:

```php
// ✅ Good - Both languages supported
'index_close' => 'Uždaryti',  // lt
'index_close' => 'Close',     // en

// ❌ Bad - Missing translation
'new_feature' => 'Nauja funkcija', // Only Lithuanian
```

### Section Organization
Organize translations into logical sections:

```php
return [
    // Core model attributes
    'fields' => [...],
    
    // UI sections and panels
    'sections' => [...],
    
    // User actions and buttons
    'actions' => [...],
    
    // System messages and feedback
    'messages' => [...],
    
    // UI labels and accessibility
    'index_close' => 'Close',
    'show_adjust_filters' => 'Adjust filters',
    
    // User guidance and help
    'help' => [...],
    
    // Validation and errors
    'validation' => [...],
];
```

## Accessibility Requirements

### ARIA Labels
Always provide translation keys for ARIA labels:

```php
// Template usage
aria-label="{{ __('categories.index_close') }}"

// Translation file
'index_close' => 'Uždaryti',
```

### Screen Reader Support
- Use descriptive text for interactive elements
- Provide context in translation strings
- Include action descriptions where needed

## Usage Patterns

### Blade Templates
```php
<!-- Simple translation -->
{{ __('categories.fields.name') }}

<!-- With parameters -->
{{ __('categories.messages.created', ['name' => $category->name]) }}

<!-- Pluralization -->
{{ trans_choice('categories.products_count', $count) }}
```

### Livewire Components
```php
class CategoryComponent extends Component
{
    public function getCloseButtonLabelProperty(): string
    {
        return __('categories.index_close');
    }
}
```

### Filament Resources
```php
TextInput::make('name')
    ->label(__('categories.fields.name'))
    ->helperText(__('categories.help.name_field'))
```

## Quality Assurance

### Translation Completeness
- Verify all keys exist in both languages
- Test with different locales active
- Check for missing translations (keys displayed as-is)

### Context Validation
- Test translations in actual UI context
- Verify text length doesn't break layouts
- Ensure cultural appropriateness

### Automated Testing
```php
test('all category translations exist', function () {
    $keys = ['index_close', 'show_adjust_filters'];
    
    foreach ($keys as $key) {
        expect(__("categories.{$key}", [], 'lt'))->not->toBe("categories.{$key}");
        expect(__("categories.{$key}", [], 'en'))->not->toBe("categories.{$key}");
    }
});
```

## Performance Considerations

### Caching
- Enable translation caching in production
- Use `php artisan config:cache` to cache translations
- Monitor cache invalidation on translation updates

### File Size
- Keep translation files focused and organized
- Avoid duplicate keys across files
- Use JSON format for simple key-value pairs

## Best Practices

### Consistency
- Maintain consistent terminology across the application
- Use the same translation keys for similar concepts
- Follow established naming patterns

### Maintainability
- Document translation context and usage
- Keep related translations grouped together
- Use meaningful key names that describe the content

### User Experience
- Provide clear, actionable text
- Consider cultural differences in phrasing
- Test with native speakers when possible

## Common Patterns

### Modal and Dialog Labels
```php
'modal_close' => 'Uždaryti',
'modal_cancel' => 'Atšaukti',
'modal_confirm' => 'Patvirtinti',
```

### Filter and Search
```php
'filter_apply' => 'Pritaikyti filtrus',
'filter_clear' => 'Išvalyti filtrus',
'search_placeholder' => 'Ieškoti...',
```

### Status Messages
```php
'success_created' => 'Sėkmingai sukurta',
'error_validation' => 'Patikrinkite įvestus duomenis',
'info_loading' => 'Kraunama...',
```

## Integration with Design System

### Text Length Considerations
- Account for text expansion in different languages
- Lithuanian text is typically 20-30% longer than English
- Test layouts with longest possible translations

### Typography
- Ensure font support for Lithuanian characters (ą, č, ę, ė, į, š, ų, ū, ž)
- Test readability across different screen sizes
- Maintain consistent text hierarchy

## Deployment Checklist

Before deploying translation changes:

- [ ] Both Lithuanian and English translations added
- [ ] Keys follow naming conventions
- [ ] Translations tested in UI context
- [ ] Accessibility labels included
- [ ] No hardcoded strings remain
- [ ] Translation cache cleared if needed
- [ ] Automated tests updated

## Related Documentation

- [Laravel Localization](https://laravel.com/docs/12.x/localization)
- [Accessibility Guidelines](../docs/accessibility/guidelines.md)
- [UI Component Standards](../docs/components/standards.md)