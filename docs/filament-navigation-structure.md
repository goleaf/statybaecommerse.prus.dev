# Filament Admin Navigation Structure

## Overview

This document describes the comprehensive navigation structure implemented for the Filament admin panel. The navigation is organized into logical groups based on business functionality, making it easy for administrators to find and manage different aspects of the e-commerce system.

## Navigation Groups

### 1. Products (`NavigationGroup::Products`)
**Priority:** 1 | **Icon:** `heroicon-o-cube` | **Color:** Blue

Contains all product-related management functionality:

- **ProductResource** - Main product management
- **CategoryResource** - Product categories and hierarchy
- **BrandResource** - Product brands
- **CollectionResource** - Product collections
- **AttributeResource** - Product attributes
- **AttributeValueResource** - Attribute values
- **ProductHistoryResource** - Product change history
- **PriceResource** - Product pricing

### 2. Orders (`NavigationGroup::Orders`)
**Priority:** 2 | **Icon:** `heroicon-o-shopping-bag` | **Color:** Green

Contains order processing and customer management:

- **OrderResource** - Order management and processing
- **CartItemResource** - Shopping cart items
- **AddressResource** - Customer addresses

### 3. Users (`NavigationGroup::Users`)
**Priority:** 3 | **Icon:** `heroicon-o-users` | **Color:** Indigo

Contains user and customer management:

- **CustomerGroupResource** - Customer groups and segmentation
- **ReferralResource** - Referral system management
- **ReferralRewardResource** - Referral rewards

### 4. Inventory (`NavigationGroup::Inventory`)
**Priority:** 4 | **Icon:** `heroicon-o-archive-box` | **Color:** Teal

Contains inventory and stock management:

- **StockResource** - Stock management
- **VariantStockResource** - Product variant stock
- **InventoryResource** - Inventory tracking

### 5. Marketing (`NavigationGroup::Marketing`)
**Priority:** 5 | **Icon:** `heroicon-o-megaphone` | **Color:** Orange

Contains marketing and promotional features:

- **CampaignResource** - Marketing campaigns
- **CampaignClickResource** - Campaign click tracking
- **CampaignConversionResource** - Campaign conversions
- **DiscountCodeResource** - Discount codes
- **DiscountConditionResource** - Discount conditions

### 6. Analytics (`NavigationGroup::Analytics`)
**Priority:** 6 | **Icon:** `heroicon-o-chart-bar` | **Color:** Yellow

Contains analytics and monitoring:

- **AnalyticsEventResource** - Analytics events
- **ActivityLogResource** - Activity logs

### 7. Reports (`NavigationGroup::Reports`)
**Priority:** 7 | **Icon:** `heroicon-o-document-chart-bar` | **Color:** Cyan

Contains reporting functionality:

- **ReviewResource** - Product reviews
- **ReportResource** - System reports

### 8. Content (`NavigationGroup::Content`)
**Priority:** 8 | **Icon:** `heroicon-o-document-text` | **Color:** Pink

Contains content management:

- **SeoDataResource** - SEO data management
- **NewsResource** - News articles
- **PostResource** - Blog posts
- **LegalResource** - Legal documents

### 9. Referral System (`NavigationGroup::Referral`)
**Priority:** 9 | **Icon:** `heroicon-o-gift` | **Color:** Purple

Contains referral system features:

- **ReferralCodeResource** - Referral codes

### 10. Settings (`NavigationGroup::Settings`)
**Priority:** 10 | **Icon:** `heroicon-o-cog-6-tooth` | **Color:** Gray

Contains application settings:

- **SystemSettingResource** - System settings

### 11. System (`NavigationGroup::System`)
**Priority:** 11 | **Icon:** `heroicon-o-computer-desktop` | **Color:** Red

Contains system configuration:

- **CurrencyResource** - Currency management
- **ZoneResource** - Shipping zones
- **CountryResource** - Countries
- **RegionResource** - Regions
- **CityResource** - Cities
- **LocationResource** - Store locations
- **RecommendationConfigResourceSimple** - Recommendation system

## Technical Implementation

### Navigation Group Enum

The navigation structure is built using the `App\Enums\NavigationGroup` enum, which provides:

- **Consistent naming** across all resources
- **Translation support** for Lithuanian and English
- **Icon definitions** for each group
- **Color coding** for visual distinction
- **Priority ordering** for logical flow
- **Permission system** for access control

### Filament v4 Compatibility

All resources use the `getNavigationGroup()` method approach for maximum compatibility:

```php
public static function getNavigationGroup(): ?string
{
    return NavigationGroup::Products->label();
}
```

### Translation Support

Navigation groups support multiple languages through the enum's `label()` method:

```php
// Lithuanian
NavigationGroup::Products->label() // Returns translated label

// English  
NavigationGroup::Products->label() // Returns translated label
```

## Benefits

1. **Improved User Experience** - Logical grouping makes it easy to find related functionality
2. **Scalable Structure** - Easy to add new resources to appropriate groups
3. **Consistent Interface** - All resources follow the same navigation pattern
4. **Multi-language Support** - Full translation support for international use
5. **Permission System** - Some groups require specific permissions for access
6. **Visual Organization** - Color coding and icons provide visual distinction

## Adding New Resources

When adding new resources to the admin panel:

1. **Choose the appropriate navigation group** based on functionality
2. **Add the NavigationGroup import** to the resource file
3. **Implement the getNavigationGroup() method**:

```php
use App\Enums\NavigationGroup;

public static function getNavigationGroup(): ?string
{
    return NavigationGroup::YourGroup->label();
}
```

4. **Set appropriate navigation sort order** for positioning within the group
5. **Add translations** for the resource labels in both Lithuanian and English

## Maintenance

- **Navigation groups are defined in** `app/Enums/NavigationGroup.php`
- **Translations are managed in** `lang/lt/translations.php` and `lang/en/translations.php`
- **Resource navigation is implemented in** individual resource files under `app/Filament/Resources/`

This structure provides a clean, professional, and highly organized navigation system that scales with the application's growth while maintaining consistency and usability.
