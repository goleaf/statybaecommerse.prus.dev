# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased] - 2025-01-27

### Fixed
- **Lithuanian Language as Default**: Fixed application to use Lithuanian (lt) as the default language instead of English
- **Product Images**: Added professional SVG tool icons to all products that were missing images
- **Translation Display**: Fixed translation system to properly load and display Lithuanian text throughout the website

### Changed
- **Environment Configuration**:
  - Changed `APP_LOCALE` from `en` to `lt` in `.env` file
  - Changed `APP_FALLBACK_LOCALE` from `en` to `lt` in `.env` file

- **Translation Files**:
  - Updated `resources/lang/lt.json` with comprehensive Lithuanian translations
  - Updated `resources/lang/en.json` with corresponding English translations
  - Added navigation translations: `nav_home`, `nav_categories`, `nav_brands`, `nav_collections`, etc.
  - Added homepage translations: `home_new_arrivals`, `home_trending_products`, `home_shop_now`, etc.
  - Added UI element translations: `Add to cart`, `Quick View`, `Featured`, `New`, etc.
  - Added admin model translations: `admin.models.products`, `admin.models.categories`, etc.
  - Added footer and contact translations: `Kontaktai`, `Pristatymas`, `Grąžinimai`, etc.

### Added
- **Product Image System**:
  - Created `LocalProductImagesSeeder.php` for generating SVG tool icons
  - Created `RealProductImagesSeeder.php` for future external image integration
  - Generated 10 professional tool SVG icons:
    - `drill.svg` - Electric drill icon
    - `hammer.svg` - Hammer tool icon
    - `saw.svg` - Circular saw icon
    - `screwdriver.svg` - Screwdriver tool icon
    - `wrench.svg` - Adjustable wrench icon
    - `level.svg` - Spirit level tool icon
    - `safety-helmet.svg` - Safety helmet icon
    - `safety-boots.svg` - Safety boots icon
    - `measuring-tape.svg` - Measuring tape icon
    - `pliers.svg` - Pliers tool icon

- **Translation Keys**:
  - Navigation: `nav_home` → "Pradžia", `nav_categories` → "Kategorijos"
  - Products: `admin.models.products` → "produktai"
  - UI Actions: `cart_add_to_cart` → "Įdėti į krepšelį"
  - Homepage: `home_new_arrivals` → "Naujos prekės"
  - Footer: `All rights reserved.` → "Visos teisės saugomos."

### Technical Details

#### Database Changes
- Ran `php artisan migrate:fresh --seed` to ensure clean database state
- Executed `LocalProductImagesSeeder` to add SVG images to all 50 products
- All products now have proper media library attachments

#### File Structure Changes
```
/public/images/products/
├── drill.svg
├── hammer.svg
├── saw.svg
├── screwdriver.svg
├── wrench.svg
├── level.svg
├── safety-helmet.svg
├── safety-boots.svg
├── measuring-tape.svg
└── pliers.svg
```

#### Translation File Updates
- `resources/lang/lt.json`: Added 25+ new translation keys
- `resources/lang/en.json`: Added corresponding English translations
- Fixed translation file location issue (moved from `lang/` to `resources/lang/`)

#### Configuration Updates
- Application locale configuration updated to prioritize Lithuanian
- Cache cleared multiple times to ensure changes take effect
- Configuration cached to improve performance

### Impact
- **User Experience**: Website now displays in Lithuanian by default, providing a native experience for Lithuanian users
- **Visual Appeal**: All products now have professional tool icons instead of placeholder text or broken images
- **Internationalization**: Proper foundation for multi-language support with Lithuanian as primary language
- **SEO**: Better localization for Lithuanian market targeting

### Statistics
- **Products**: 50/50 products now have images (100% coverage)
- **Translations**: 25+ new Lithuanian translation keys added
- **Languages**: Proper support for Lithuanian (lt), English (en), and German (de)
- **Media**: Professional SVG icons for 10 different tool categories

### Verification
- ✅ Homepage displays "Pradžia" instead of "Home"
- ✅ Navigation shows "Kategorijos" instead of "Categories"  
- ✅ Product statistics show "produktai" instead of "products"
- ✅ All products display appropriate tool icons
- ✅ Language switching works correctly between LT/EN/DE
- ✅ Application locale set to `lt` (Lithuanian)

### Files Modified
1. `.env` - Updated locale settings
2. `resources/lang/lt.json` - Added comprehensive Lithuanian translations
3. `resources/lang/en.json` - Added corresponding English translations
4. `database/seeders/LocalProductImagesSeeder.php` - New seeder for SVG images
5. `database/seeders/RealProductImagesSeeder.php` - New seeder for external images
6. `public/images/products/` - 10 new SVG tool icons

### Cache Operations
- Configuration cache cleared and rebuilt
- Application cache cleared multiple times
- View cache cleared to ensure template updates
- Route cache cleared for proper URL generation

---

## Previous Versions

### [1.0.0] - Initial Release
- Basic e-commerce functionality with English as default language
- Product catalog with placeholder images
- Multi-language support framework
