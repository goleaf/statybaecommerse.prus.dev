# Multilanguage Tabs Implementation Guide

## Overview

This document describes the comprehensive implementation of multilanguage tabs across all admin modules using the Solution Forest Tab Layout Plugin. The implementation provides a consistent, user-friendly interface for managing translations with language-specific tabs.

## Features Implemented

### 1. Tab Layout Plugin Integration
- **Plugin**: `solution-forest/tab-layout-plugin` v3.2.2
- **Registration**: Added to AdminPanelProvider widgets
- **Compatibility**: Filament v4.x

### 2. MultiLanguageTabService

A comprehensive service class that provides:

#### Core Features:
- **Language Management**: Supports EN/LT with easy extensibility
- **Tab Creation**: Simple, advanced, and sectioned tab creation methods
- **Flag Icons**: Language-specific flag emojis for visual identification
- **URL Persistence**: Tab state persistence in query strings
- **Default Language**: Lithuanian-first approach with English fallback

#### Available Methods:
```php
// Get available languages
MultiLanguageTabService::getAvailableLanguages()

// Create simple tabs for basic fields
MultiLanguageTabService::createSimpleTabs($fields)

// Create advanced tabs with custom schemas
MultiLanguageTabService::createAdvancedTabs($schemaBuilder)

// Create sectioned tabs for organized content
MultiLanguageTabService::createSectionedTabs($sections)

// Get default active tab (prioritizes Lithuanian)
MultiLanguageTabService::getDefaultActiveTab()
```

### 3. Updated Resources

#### BrandResource
- **Multilanguage Fields**: name, slug, description, seo_title, seo_description
- **Sections**: Basic Information, SEO Information
- **Features**: Tab persistence, flag icons, Lithuanian default

#### CategoryResource
- **Multilanguage Fields**: name, slug, description, seo_title, seo_description
- **Sections**: Basic Information, SEO Information
- **Rich Editor**: Enhanced description field with toolbar options

#### CollectionResource
- **Multilanguage Fields**: name, slug, description, seo_title, seo_description
- **Sections**: Basic Information, SEO Information
- **Dynamic Features**: Automatic collection rules visibility

#### LegalResource
- **Multilanguage Fields**: title, slug, content
- **Rich Editor**: Full content editing with extended toolbar
- **Content Focus**: Optimized for legal document management

### 4. Custom Tab Widgets

#### ProductTranslationTabsWidget
- **Complex Fields**: name, slug, summary, description, seo fields
- **Rich Content**: Full product description editing
- **SEO Optimization**: Complete meta information management

#### CategoryTranslationTabsWidget
- **Hierarchical Support**: Category-specific translation handling
- **Content Management**: Rich description editing
- **SEO Features**: Category-specific optimization

### 5. Translation Keys

#### English (en/translations.php)
```php
// Tab-specific translations
'content_in_language' => 'Content in :language',
'basic_information' => 'Basic Information',
'seo_information' => 'SEO Information',
'content_information' => 'Content Information',
'slug_auto_generated' => 'Auto-generated from name if left empty',
'seo_title_help' => 'Optimal length: 50-60 characters',
'seo_description_help' => 'Optimal length: 150-160 characters',
```

#### Lithuanian (lt/translations.php)
```php
// Tab-specific translations
'content_in_language' => 'Turinys :language kalba',
'basic_information' => 'Pagrindinė informacija',
'seo_information' => 'SEO informacija',
'content_information' => 'Turinio informacija',
'slug_auto_generated' => 'Automatiškai generuojama iš pavadinimo, jei palikta tuščia',
'seo_title_help' => 'Optimalus ilgis: 50-60 simbolių',
'seo_description_help' => 'Optimalus ilgis: 150-160 simbolių',
```

## Usage Examples

### Simple Implementation
```php
use App\Services\MultiLanguageTabService;
use SolutionForest\TabLayoutPlugin\Components\Tabs;

Tabs::make('translations')
    ->tabs(
        MultiLanguageTabService::createSimpleTabs([
            'name' => ['type' => 'text', 'required' => true],
            'description' => ['type' => 'textarea', 'rows' => 3]
        ])
    )
    ->activeTab(MultiLanguageTabService::getDefaultActiveTab())
    ->persistTabInQueryString('tab')
    ->contained(false)
```

### Advanced Implementation
```php
Tabs::make('translations')
    ->tabs(
        MultiLanguageTabService::createSectionedTabs([
            'basic_information' => [
                'name' => [
                    'type' => 'text',
                    'label' => __('translations.name'),
                    'required' => true,
                    'maxLength' => 255,
                ],
                'description' => [
                    'type' => 'rich_editor',
                    'label' => __('translations.description'),
                    'toolbar' => ['bold', 'italic', 'link', 'bulletList', 'orderedList'],
                ],
            ],
            'seo_information' => [
                'seo_title' => [
                    'type' => 'text',
                    'label' => __('translations.seo_title'),
                    'maxLength' => 255,
                ],
            ],
        ])
    )
```

## Benefits

### User Experience
- **Visual Language Identification**: Flag emojis for instant recognition
- **Consistent Interface**: Uniform tab layout across all modules
- **Lithuanian-First**: Default language matches target market
- **URL Persistence**: Bookmarkable tab states

### Developer Experience
- **Reusable Service**: Single service for all multilanguage needs
- **Type Safety**: Strict typing throughout
- **Extensible**: Easy to add new languages or field types
- **Maintainable**: Centralized translation logic

### Content Management
- **Organized Sections**: Logical grouping of related fields
- **Rich Editing**: Advanced content creation capabilities
- **SEO Optimization**: Built-in SEO field management
- **Validation**: Consistent validation across languages

## Configuration

### Adding New Languages
1. Update `config/app-features.php`:
```php
'supported_locales' => ['en', 'lt', 'de', 'fr'],
```

2. Add language mapping in `MultiLanguageTabService::getLanguageName()`:
```php
'de' => __('Deutsch'),
'fr' => __('Français'),
```

3. Add flag emoji in `MultiLanguageTabService::getLanguageFlag()`:
```php
'de' => '🇩🇪',
'fr' => '🇫🇷',
```

### Adding New Field Types
Extend the service methods to support additional field types:
```php
'rich_text' => RichEditor::make("{$field}_{$language['code']}")
    ->label($config['label'] ?? ucfirst($field))
    ->toolbarButtons($config['toolbar'] ?? ['bold', 'italic']),
```

## File Structure
```
app/
├── Services/
│   └── MultiLanguageTabService.php
├── Filament/
│   ├── Resources/
│   │   ├── BrandResource.php (updated)
│   │   ├── CategoryResource.php (updated)
│   │   ├── CollectionResource.php (updated)
│   │   └── LegalResource.php (updated)
│   └── Widgets/
│       ├── ProductTranslationTabsWidget.php
│       └── CategoryTranslationTabsWidget.php
├── Providers/
│   └── Filament/
│       └── AdminPanelProvider.php (updated)
lang/
├── en/
│   └── translations.php (updated)
└── lt/
    └── translations.php (updated)
```

## Future Enhancements

1. **Bulk Translation**: Mass translation management interface
2. **Translation Status**: Visual indicators for completion status
3. **Auto-Translation**: Integration with translation services
4. **Import/Export**: Translation data management tools
5. **Version Control**: Translation change tracking
6. **Performance**: Lazy loading for large translation sets

## Maintenance

- **Regular Updates**: Keep tab layout plugin updated
- **Translation Review**: Periodic review of translation keys
- **Performance Monitoring**: Monitor tab loading times
- **User Feedback**: Collect feedback on tab usability

This implementation provides a solid foundation for multilanguage content management while maintaining flexibility for future enhancements.
