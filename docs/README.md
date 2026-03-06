# Documentation Overview

This documentation covers the comprehensive localization system and related components for the e-commerce platform built with Laravel 12, Filament 4, and Livewire 3.

## Recent Updates

### 2024-01-07: Categories Translation Enhancement

Added new UI accessibility labels to improve user experience and screen reader support:

- **`index_close`**: Accessible close button labels for modals and overlays
- **`show_adjust_filters`**: Contextual guidance text for filter interactions

These additions follow our localization guidelines with full Lithuanian and English support.

## Documentation Structure

### Core Documentation

#### [Localization](./localization/)
- **[Categories Translations](localization/categories-translations.md)** - Complete guide to category translation structure and usage
- **[Localization Guidelines](./.kiro/steering/localization-guidelines.md)** - Project-wide translation standards and best practices

#### [Components](./components/)
- **[Category UI Components](components/category-ui.md)** - UI component documentation with accessibility focus

#### [API Documentation](./api/)
- **[Localization API](api/localization-api.md)** - Translation system API reference and integration patterns

#### [Architecture](./architecture/)
- **[Localization Architecture](architecture/localization-architecture.md)** - System design and technical implementation details

### Testing

#### [Feature Tests](../tests/Feature/Localization/)
- **[Categories Translation Test](../tests/Feature/Localization/CategoriesTranslationTest.php)** - Comprehensive translation validation tests

## Key Features

### Multilingual Support
- **Primary Language**: Lithuanian (`lt`) - Default locale
- **Secondary Language**: English (`en`) - Fallback locale
- **Translation Structure**: Organized by logical sections (fields, actions, messages, validation)
- **Accessibility**: ARIA labels and screen reader support

### Translation Organization
```php
return [
    'fields' => [...],           // Model attributes
    'actions' => [...],          // User interactions
    'messages' => [...],         // System feedback
    'validation' => [...],       // Error messages
    'index_close' => 'Close',    // UI accessibility labels
    'show_adjust_filters' => 'Adjust filters', // User guidance
    'help' => [...],             // User assistance
];
```

- **File Format Policy**: Use PHP translation files only (`lang/<locale>/*.php`).
- **Do Not Use**: JSON translation files (for example, `lang/*.json`).

### Integration Points
- **Blade Templates**: `{{ __('categories.index_close') }}`
- **Livewire Components**: Property-based translation loading
- **Filament Resources**: Form field and table column localization
- **API Responses**: Localized error messages and feedback

## Best Practices

### Translation Keys
- Use `snake_case` for all keys
- Group related translations logically
- Prefix with context when needed (`index_`, `show_`, `admin_`)

### Accessibility
- Provide ARIA labels for interactive elements
- Use descriptive text for screen readers
- Include contextual help where appropriate

### Performance
- Enable translation caching in production
- Use lazy loading for large translation sets
- Optimize file structure for maintainability

## Quality Assurance

### Automated Testing
- Translation completeness validation
- Accessibility compliance checks
- Performance benchmarking
- Cross-locale consistency verification

### Manual Testing
- UI context validation
- Screen reader compatibility
- Cultural appropriateness review
- Text length and layout testing

## Development Workflow

### Adding New Translations
1. Add keys to both `lt` and `en` translation files
2. Follow naming conventions and section organization
3. Include accessibility labels where needed
4. Write tests for new translation keys
5. Validate in UI context
6. Keep all new translations in PHP files only; do not add JSON translation files

### Deployment Checklist
- [ ] Both Lithuanian and English translations added
- [ ] Keys follow naming conventions
- [ ] Translations tested in UI context
- [ ] Accessibility labels included
- [ ] No hardcoded strings remain
- [ ] Translation cache cleared if needed
- [ ] Automated tests updated and passing

## Related Resources

### External Documentation
- [Laravel Localization](https://laravel.com/docs/12.x/localization)
- [Filament Localization](https://filamentphp.com/docs/4.x/panels/configuration#localization)
- [Web Accessibility Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)

### Project Standards
- [Project Rules](../.kiro/steering/projectrules.md)
- [Localization Guidelines](../.kiro/steering/localization-guidelines.md)
- [Filament Admin Setup](../.kiro/specs/filament-admin-backend-setup/)

## Contributing

When contributing to the localization system:

1. **Follow Guidelines**: Adhere to established naming conventions and structure
2. **Test Thoroughly**: Validate translations in actual UI context
3. **Consider Accessibility**: Include appropriate ARIA labels and descriptions
4. **Document Changes**: Update relevant documentation files
5. **Maintain Consistency**: Keep terminology consistent across the application

## Support

For questions about the localization system:

1. Check the documentation in this directory
2. Review existing translation files for patterns
3. Run the test suite to validate changes
4. Consult the project steering guidelines

---

**Last Updated**: January 7, 2024  
**Version**: 1.0.0  
**Maintainer**: Development Team
