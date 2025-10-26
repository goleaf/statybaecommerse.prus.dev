# STATYBA E-COMMERCE PLATFORM - TECHNICAL REQUIREMENTS

**Generated**: 2025-10-26  
**Version**: 1.0  
**Platform**: Laravel 12.35.1 + Filament v4.1.10  
**Database**: MySQL (statyba)  
**URL**: https://statybaecommerse.prus.dev/

---

## 🎯 EXECUTIVE SUMMARY

Enterprise-grade e-commerce platform built with Laravel 12 and Filament v4, featuring comprehensive product management, multi-currency support, advanced discount campaigns, referral systems, and AI-powered recommendations. The system supports 68+ Eloquent models, 145+ database tables, and 985+ routes across frontend, admin, and API layers.

---

## 📋 TABLE OF CONTENTS

1. [Technology Stack](#-technology-stack)
2. [System Architecture](#-system-architecture)
3. [Core Features](#-core-features)
4. [Database Schema](#-database-schema)
5. [API Endpoints](#-api-endpoints)
6. [Frontend Routes](#-frontend-routes)
7. [Admin Panel](#-admin-panel)
8. [Localization](#-localization--internationalization)
9. [Security & Performance](#-security--performance)
10. [Testing Strategy](#-testing-strategy)
11. [Deployment & DevOps](#-deployment--devops)

---

## 🔧 TECHNOLOGY STACK

### Backend Framework
- **Laravel**: 12.35.1 (Latest stable)
- **PHP**: 8.3.25
- **Database**: MySQL (statyba schema)
- **Queue**: Redis + Horizon 5.37.0
- **Cache**: Redis + Database cache driver
- **Search**: Laravel Scout 10.20.0

### Frontend Stack
- **Livewire**: 3.6.4
- **Livewire Volt**: 1.7.2
- **Tailwind CSS**: 4.1.16
- **Vite**: 7.1.3
- **SASS**: 1.77.8

### Admin Panel
- **Filament**: v4.1.10
- **Filament Plugins**:
  - Shield 4.0 (RBAC)
  - Excel 3.1 (Import/Export)
  - Media Library Plugin 4.0
  - Language Tabs 3.0
  - Socialite 3.0
  - Advanced Widgets 4.0
  - Resource Lock 4.0
  - Table Layout Toggle 3.0
  - Zeus Bolt 4.0.5 (Form Builder)

### Authentication & Authorization
- **Laravel Sanctum**: 4.2.0
- **Spatie Permission**: 6.21
- **Filament Shield**: 4.0

### Media & Assets
- **Spatie Media Library**: 11.x
- **Image Optimization**: WebP conversion
- **PDF Generation**: DomPDF 3.1

### Developer Tools
- **Testing**: Pest 3.8.4 + PHPUnit 11.5.33
- **Browser Testing**: Laravel Dusk 8.3.3
- **Code Quality**: Pint 1.25.1, Larastan 3.7.2, Rector 2.2.5
- **Debugging**: Telescope, Debugbar, Horizon
- **API Documentation**: OpenAPI/Swagger 5.5

### Third-Party Integrations
- **Analytics**: Custom analytics events system
- **Activity Logging**: Spatie Activity Log 4.10
- **Data Objects**: Spatie Laravel Data 4.17
- **Translatable**: Spatie Translatable 6.11
- **Sluggable**: Spatie Sluggable 3.7

---

## 🏗 SYSTEM ARCHITECTURE

### Application Structure

```
app/
├── Actions/              # Single-action classes
├── Application/          # Application layer (DTOs, Services)
│   ├── DTOs/
│   └── Product/
├── Console/              # Artisan commands (200+)
├── Contracts/            # Interfaces
├── Data/                 # Spatie Data objects
├── Domain/               # Domain logic
├── Enums/                # PHP Enums (20+)
├── Events/               # Domain events
├── Exceptions/           # Custom exceptions
├── Filament/             # Admin panel (Resources, Pages, Widgets)
│   ├── Pages/
│   ├── Resources/        # 60+ CRUD resources
│   └── Widgets/
├── Http/
│   ├── Controllers/      # Frontend & API controllers
│   ├── Middleware/
│   └── Requests/         # Form requests with validation
├── Jobs/                 # Queue jobs
├── Livewire/             # Livewire components
│   ├── Pages/
│   └── Components/
├── Models/               # 68+ Eloquent models
├── Observers/            # Model observers
├── Policies/             # Authorization policies
├── Repositories/         # Repository pattern
├── Services/             # Business logic services
├── Support/              # Helper classes
├── Traits/               # Reusable traits
└── ViewModels/           # View models
```

### Design Patterns Applied

1. **Repository Pattern**: Category, Product, Menu, User repositories
2. **Service Layer**: Business logic isolation
3. **Data Transfer Objects**: Spatie Laravel Data
4. **Domain-Driven Design**: Domain folder for complex logic
5. **CQRS Elements**: UseCase classes for commands/queries
6. **Observer Pattern**: Model lifecycle management
7. **Policy-Based Authorization**: Granular access control
8. **Factory Pattern**: Enum factories, model factories

---

## 🎯 CORE FEATURES

### 1. PRODUCT MANAGEMENT

#### Product System (Complex)
- **Base Products**: 68+ models, multi-variant support
- **Product Variants**: Size, color, material variations
- **Product Attributes**: Dynamic attribute system (filterable, searchable)
- **Product Images**: Multi-image gallery with WebP optimization
- **Product History**: Change tracking and audit trail
- **Product Features**: Feature extraction system
- **Product Similarities**: AI-powered similar products
- **Product Requests**: Customer request for unavailable products

#### Inventory Management
- **Multi-Location**: Stock tracking across locations
- **Variant Inventory**: Per-variant stock levels
- **Stock Movements**: Audit trail for all stock changes
- **Stock Reservations**: Temporary holds during checkout
- **Low Stock Alerts**: Automated threshold monitoring
- **Warehouse Management**: Multi-warehouse support

#### Pricing System
- **Base Pricing**: Regular, sale, compare, cost prices
- **Price Lists**: Customer group-specific pricing
- **Dynamic Pricing**: Time-based, quantity-based rules
- **Multi-Currency**: Currency conversion with live rates
- **Variant Pricing**: Per-variant price modifiers
- **Price History**: Historical price tracking

### 2. CATALOG ORGANIZATION

#### Categories
- **Hierarchical**: Unlimited nesting levels
- **Multi-Language**: Full translation support
- **SEO Optimized**: Meta titles, descriptions
- **Visibility Control**: Show/hide, enable/disable
- **Sort Order**: Custom ordering
- **Product Assignment**: Many-to-many relationships

#### Brands
- **Brand Management**: Name, description, website
- **Brand Images**: WebP optimized
- **SEO Data**: Meta information
- **Social Links**: JSON-stored social profiles
- **Premium Brands**: Featured brand system

#### Collections
- **Static Collections**: Manual product selection
- **Dynamic Collections**: Rule-based automatic inclusion
- **Collection Rules**: Complex filtering logic
- **Display Types**: Grid, list, carousel
- **SEO Optimization**: Full meta support

### 3. E-COMMERCE FEATURES

#### Shopping Cart
- **Session-Based**: Guest cart support
- **User Cart**: Persistent for authenticated users
- **Cart Items**: Product + variant selection
- **Quantity Management**: Min/max quantities
- **Price Calculation**: Real-time totals with discounts
- **Cart Attributes**: Custom attributes storage

#### Checkout Process
- **Multi-Step**: Streamlined checkout flow
- **Address Management**: Billing + shipping addresses
- **Shipping Options**: Dynamic based on location/weight
- **Payment Methods**: Cash, transfer, online payments
- **Order Confirmation**: Email notifications
- **Guest Checkout**: No registration required

#### Order Management
- **Order Lifecycle**: Pending → Processing → Shipped → Delivered
- **Order Numbers**: Unique order numbering
- **Order Items**: Line items with snapshots
- **Order Shipping**: Tracking numbers, carriers
- **Order History**: Full audit trail
- **Invoice Generation**: PDF invoices
- **Return Management**: Return requests

### 4. DISCOUNT & CAMPAIGN SYSTEM

#### Discounts
- **Types**: Percentage, fixed amount, free shipping
- **Scope**: Product, category, cart-wide
- **Conditions**: Minimum amount, customer groups
- **Stacking**: Stackable/exclusive rules
- **Time-Based**: Start/end dates
- **Usage Limits**: Per-code, per-user limits

#### Discount Campaigns
- **Campaign Management**: Multi-channel campaigns
- **Target Audiences**: Customer groups, segments
- **Product Targets**: Specific products, categories, brands
- **Performance Tracking**: Views, clicks, conversions
- **Budget Limits**: Spending caps
- **Auto-Management**: Auto-start, auto-pause features

#### Coupons
- **Coupon Codes**: Unique code generation
- **Usage Tracking**: Redemption history
- **Customer Groups**: Restricted access
- **Product/Category Filters**: Applicable products
- **Auto-Apply**: Automatic application logic
- **First-Time Only**: New customer discounts

### 5. REFERRAL & PARTNER SYSTEM

#### Referral Program
- **Referral Codes**: Unique code per user
- **Referral Tracking**: Complete attribution chain
- **Rewards System**: Tiered rewards
- **Reward Types**: Discount, credit, points
- **Campaign Support**: Referral campaigns
- **Statistics**: Usage analytics
- **Code Sharing**: Social sharing tools

#### Partner Management
- **Partner Tiers**: Bronze, Silver, Gold, Platinum
- **Commission Rates**: Per-tier commission
- **Discount Rates**: Partner pricing
- **API Access**: Partner API keys
- **Price Lists**: Partner-specific pricing
- **Order Attribution**: Partner order tracking

### 6. ANALYTICS & TRACKING

#### Product Analytics
- **View Tracking**: Product page views
- **Engagement**: Cart additions, purchases
- **Conversion Rates**: Product performance
- **Wishlist Analytics**: Wishlist additions
- **Variant Analytics**: Per-variant metrics

#### Campaign Analytics
- **Views & Clicks**: Campaign engagement
- **Conversions**: Attribution modeling
- **ROI Tracking**: Return on ad spend
- **Customer Journey**: Touchpoint analysis
- **A/B Testing**: Multi-variant campaigns

#### User Behavior
- **Behavior Tracking**: User actions
- **Product Interactions**: Detailed engagement
- **Session Analysis**: Session-based tracking
- **Preferences**: Learning user preferences
- **Recommendations**: Personalized suggestions

### 7. RECOMMENDATION ENGINE

#### Recommendation Types
- **Similar Products**: Content-based filtering
- **Frequently Bought Together**: Collaborative filtering
- **Trending Products**: Popularity-based
- **Personalized**: User behavior-based
- **Category-Based**: Within-category recommendations

#### Recommendation Blocks
- **Block Management**: Reusable blocks
- **Configuration**: Algorithm selection
- **Caching**: Performance optimization
- **Analytics**: Block performance tracking
- **A/B Testing**: Config comparison

### 8. NEWS & CONTENT SYSTEM

#### News Management
- **News Articles**: Full CMS capabilities
- **Categories**: News categorization
- **Tags**: Tag system for filtering
- **Images**: Multi-image support
- **Comments**: User comments (moderation)
- **Moderation**: Approval workflow
- **SEO**: Full SEO metadata

#### Content Features
- **Posts**: Blog system
- **Pages**: Custom page builder
- **Menus**: Dynamic menu management
- **Sliders**: Homepage sliders
- **Legal Pages**: Terms, privacy, cookies

### 9. CUSTOMER MANAGEMENT

#### User Accounts
- **Registration**: Multi-step registration
- **Authentication**: Email/password + Socialite
- **Profile Management**: Full profile editing
- **Address Book**: Multiple addresses
- **Order History**: Past order viewing
- **Wishlist**: Product wishlist
- **Product Comparison**: Compare products
- **Notifications**: User notifications

#### Customer Groups
- **Group Management**: Custom groups
- **Pricing Rules**: Group-specific pricing
- **Discount Access**: Group-based discounts
- **Credit Limits**: B2B features
- **Payment Terms**: Net 30, Net 60, etc.
- **Permissions**: Group permissions

### 10. NOTIFICATION SYSTEM

#### Notification Types
- **System Notifications**: Admin alerts
- **User Notifications**: Customer alerts
- **Email Campaigns**: Bulk email
- **Newsletter**: Newsletter subscription
- **Order Updates**: Status changes
- **Stock Alerts**: Low stock alerts

#### Notification Templates
- **Email Templates**: Customizable templates
- **Variables**: Dynamic placeholder system
- **Multi-Language**: Translated templates
- **HTML & Text**: Dual format support

### 11. SEO & MARKETING

#### SEO Features
- **Meta Tags**: Title, description, keywords
- **Canonical URLs**: Duplicate content handling
- **Structured Data**: JSON-LD schemas
- **Sitemaps**: XML sitemap generation
- **Robots.txt**: Search engine directives
- **Open Graph**: Social media previews

#### Marketing Tools
- **Email Campaigns**: Mass mailing
- **Subscriber Management**: Newsletter lists
- **Contact Forms**: Customer inquiries
- **Product Reviews**: Review system
- **Social Sharing**: Share buttons

### 12. MULTI-LANGUAGE SUPPORT

#### Supported Locales
- **Lithuanian (lt)**: Default language
- **English (en)**: Secondary language
- **German (de)**: Additional support
- **Russian (ru)**: Additional support

#### Translation System
- **Database Translations**: All entities translatable
- **JSON Files**: UI translations
- **Laravel Translation**: Standard Laravel i18n
- **Spatie Translatable**: Model translations
- **Filament Language Tabs**: Admin interface

### 13. MULTI-CURRENCY SUPPORT

#### Currency Features
- **EUR**: Default currency
- **Exchange Rates**: Automatic updates
- **Currency Conversion**: Real-time conversion
- **Price Display**: Locale-aware formatting
- **Decimal Places**: Per-currency precision
- **Symbol Position**: Before/after amount

### 14. DOCUMENT MANAGEMENT

#### Document System
- **Document Templates**: Reusable templates
- **Document Generation**: PDF generation
- **Variable Replacement**: Dynamic content
- **Document Types**: Invoice, receipt, report
- **Access Control**: Public/private documents
- **Versioning**: Document version control

### 15. LOCATION & SHIPPING

#### Geographic Data
- **Countries**: 195+ countries
- **Regions**: Regional organization
- **Cities**: City database
- **Zones**: Shipping zones
- **Postal Codes**: Validation support

#### Shipping Management
- **Shipping Options**: Multiple carriers
- **Rate Calculation**: Weight/zone-based
- **Tracking**: Shipment tracking
- **Estimated Delivery**: Delivery windows
- **Shipping Matrix**: Complex rules

### 16. ADVANCED FEATURES

#### Feature Flags
- **Feature Toggles**: Dynamic feature control
- **Rollout Strategies**: Gradual rollout
- **Environment-Specific**: Per-env features
- **User Targeting**: User-based enabling
- **Analytics**: Feature usage tracking

#### System Settings
- **Categories**: Organized settings
- **Types**: Multiple data types
- **Validation**: Rule-based validation
- **Dependencies**: Conditional settings
- **History**: Change tracking
- **Encryption**: Sensitive data protection

#### Audit & Compliance
- **Activity Log**: Spatie Activity Log
- **Audit Trails**: Complete audit system
- **Admin Activity**: Admin action logging
- **GDPR Compliance**: Data export/deletion
- **Privacy Controls**: User privacy settings

---

## 📊 DATABASE SCHEMA

### Core Tables (145+ Total)

#### Products & Catalog (25 tables)
- `products` - Base products
- `product_variants` - Product variations
- `product_attributes` - Dynamic attributes
- `product_translations` - Multi-language support
- `product_images` - Product gallery
- `product_histories` - Change tracking
- `product_features` - Feature vectors
- `product_similarities` - Similar products
- `product_requests` - Customer requests
- `product_analytics` - Performance metrics
- `product_categories` - Category pivot
- `product_collections` - Collection pivot
- `variant_inventories` - Stock levels
- `variant_images` - Variant images
- `variant_analytics` - Variant metrics
- `variant_bundles` - Product bundles
- `variant_pricing_rules` - Pricing rules
- `variant_recommendations` - Recommendations

#### Orders & Transactions (10 tables)
- `orders` - Customer orders
- `order_items` - Line items
- `order_shippings` - Shipping info
- `cart_items` - Shopping cart
- `wishlists` - Customer wishlists
- `wishlist_items` - Wishlist products
- `user_wishlists` - Named wishlists
- `product_comparisons` - Product compare

#### Catalog Organization (15 tables)
- `categories` - Product categories
- `category_translations` - Multi-language
- `brands` - Product brands
- `brand_translations` - Brand i18n
- `collections` - Product collections
- `collection_rules` - Dynamic rules
- `collection_translations` - Collection i18n
- `attributes` - Attribute definitions
- `attribute_values` - Attribute options
- `attribute_translations` - Attribute i18n

#### Discounts & Campaigns (20 tables)
- `discounts` - Base discounts
- `discount_codes` - Discount codes
- `discount_conditions` - Conditional logic
- `discount_redemptions` - Usage history
- `coupons` - Coupon system
- `coupon_usages` - Coupon tracking
- `discount_campaigns` - Campaigns
- `campaign_products` - Product targets
- `campaign_categories` - Category targets
- `campaign_views` - View tracking
- `campaign_clicks` - Click tracking
- `campaign_conversions` - Conversion tracking
- `campaign_schedules` - Automated scheduling
- `campaign_customer_segments` - Audience targeting

#### Referral System (10 tables)
- `referrals` - Referral tracking
- `referral_codes` - User referral codes
- `referral_rewards` - Reward allocation
- `referral_campaigns` - Referral campaigns
- `referral_statistics` - Performance stats
- `referral_code_statistics` - Code analytics
- `referral_reward_logs` - Reward audit
- `referral_code_usage_logs` - Usage tracking

#### User & Access (12 tables)
- `users` - User accounts
- `admin_users` - Admin accounts
- `addresses` - User addresses
- `customer_groups` - Customer segmentation
- `customer_group_user` - Group assignments
- `roles` - Authorization roles
- `permissions` - Permission definitions
- `model_has_roles` - Role assignments
- `model_has_permissions` - Direct permissions

#### Analytics & Tracking (10 tables)
- `analytics_events` - Event tracking
- `user_behaviors` - Behavior tracking
- `user_product_interactions` - Engagement
- `user_preferences` - Learned preferences
- `search_logs` - Search analytics
- `performance_metrics` - System metrics
- `recommendation_analytics` - Recommendation metrics
- `variant_analytics` - Variant performance

#### Content Management (12 tables)
- `news` - News articles
- `news_translations` - News i18n
- `news_categories` - News categorization
- `news_tags` - Tag system
- `news_comments` - User comments
- `news_images` - Article images
- `posts` - Blog posts
- `post_approvals` - Content moderation
- `menus` - Menu management
- `menu_items` - Menu structure
- `pages` - Custom pages

#### System Configuration (18 tables)
- `system_settings` - Application settings
- `system_setting_categories` - Setting groups
- `system_setting_translations` - i18n settings
- `system_setting_histories` - Change tracking
- `system_setting_dependencies` - Conditional settings
- `settings` - Legacy settings
- `enhanced_settings` - Enhanced settings
- `enum_values` - Dynamic enums
- `feature_flags` - Feature toggles
- `channels` - Sales channels
- `currencies` - Currency definitions
- `locations` - Physical locations

#### Geo & Shipping (10 tables)
- `countries` - Country database
- `country_translations` - Country i18n
- `regions` - Regional divisions
- `cities` - City database
- `city_translations` - City i18n
- `zones` - Shipping zones
- `shipping_options` - Shipping methods

#### Media & Assets (5 tables)
- `media` - Spatie media library
- `media_collections` - Collection definitions
- `product_images` - Product images
- `news_images` - News images
- `variant_images` - Variant images

#### Audit & Logging (8 tables)
- `activity_log` - Spatie activity log
- `audit_logs` - Custom audit
- `audit_trails` - Detailed trails
- `admin_activity_logs` - Admin actions
- `system_logs` - System logging

#### Technical Tables (12 tables)
- `migrations` - Migration tracking
- `jobs` - Queue jobs
- `job_batches` - Batch jobs
- `failed_jobs` - Failed jobs
- `dead_letter_jobs` - Dead letter queue
- `cache` - Database cache
- `cache_locks` - Cache locking
- `cache_tags` - Tagged cache
- `sessions` - User sessions
- `password_reset_tokens` - Password resets
- `personal_access_tokens` - Sanctum tokens
- `table_settings` - UI preferences

---

## 🔌 API ENDPOINTS

### Public API (RESTful)

#### Products API
```
GET  /api/products/catalog         - Product catalog
GET  /api/products/search           - Product search
GET  /api/products/{slug}           - Product details
```

#### Categories API
```
GET  /api/categories                - All categories
GET  /api/categories/tree           - Category tree
GET  /api/categories/{category}     - Category details
```

#### Brands API
```
GET  /api/brands                    - All brands
GET  /api/brands/{brand}            - Brand details
```

#### Cart API
```
GET  /api/cart/count                - Cart item count
```

#### Wishlist API
```
GET  /api/wishlist/count            - Wishlist count
POST /api/wishlist/toggle           - Add/remove from wishlist
```

#### Orders API
```
GET  /api/orders/{order}            - Order details
```

#### Notifications API (v1)
```
GET    /api/v1/notifications              - List notifications
GET    /api/v1/notifications/{id}         - Single notification
POST   /api/v1/notifications/{id}/mark-read
DELETE /api/v1/notifications/{id}
GET    /api/v1/notifications/stats        - Notification stats
POST   /api/v1/notifications/mark-all-read
```

#### System API
```
GET  /api/v1/health                 - Health check
GET  /api/v1/ready                  - Ready check
GET  /api/v1/search                 - Global search
POST /api/v1/autocomplete-search    - Autocomplete
```

#### Settings API
```
GET  /api/settings/public           - Public settings
GET  /api/settings/{key}            - Specific setting
GET  /api/system-settings           - System settings
```

### Partner API (Authenticated)

```
GET  /api/partner/ping              - Connection test
GET  /api/partner/orders            - Partner orders
```

### Rate Limiting
- **Default**: 60 requests/minute
- **Autocomplete**: 120 requests/minute
- **Notifications**: 100 requests/minute
- **Exports**: 10 requests/minute

---

## 🎨 FRONTEND ROUTES (300+)

### Public Pages

#### Home & Core
```
GET  /                               - Homepage
GET  /{locale}                       - Localized homepage
GET  /about                          - About page
GET  /contact                        - Contact form
GET  /search                         - Search results
```
- Home Livewire component exposes computed state keys (`stats`, `featuredProducts`, `latestProducts`, `latestReviews`) cached with locale-aware cache tags to prevent stale storefront totals.

#### Product Browsing
```
GET  /products                       - Product listing
GET  /products/{product}             - Product details
GET  /products/brand/{brand}         - Brand products
GET  /products/category/{category}   - Category products
GET  /products/search                - Product search
GET  /products/{product}/gallery     - Image gallery
GET  /products/{product}/history     - Price history
```

#### Catalog Navigation
```
GET  /categories                     - All categories
GET  /categories/{category}          - Category page
GET  /brands                         - All brands
GET  /brands/{brand}                 - Brand page
GET  /collections                    - Collections
GET  /collections/{collection}       - Collection page
```

#### Shopping Experience
```
GET  /cart                           - Shopping cart
POST /cart/items                     - Add to cart
POST /cart/items/update/{item}      - Update quantity
POST /cart/items/remove/{item}      - Remove item
POST /cart/clear                     - Clear cart

GET  /wishlist                       - Wishlist
POST /wishlist/add                   - Add to wishlist
DELETE /wishlist/remove              - Remove from wishlist

GET  /checkout                       - Checkout
POST /checkout/process               - Process order
GET  /checkout/success               - Order confirmation
GET  /checkout/cancel                - Cancel checkout
```

#### User Account
```
GET  /account                        - Account dashboard
GET  /account/profile                - Profile management
GET  /account/addresses              - Address management
GET  /account/orders                 - Order history
GET  /account/orders/{number}        - Order details
GET  /account/wishlist               - Wishlist
GET  /account/reviews                - My reviews
GET  /account/notifications          - Notifications
```

#### News & Content
```
GET  /news                           - News listing
GET  /news/{slug}                    - Article page
GET  /news/category/{slug}           - Category news
GET  /news/tag/{slug}                - Tagged news
POST /news/{slug}/comments           - Add comment
```

#### Legal & Info
```
GET  /legal/privacy                  - Privacy policy
GET  /legal/terms                    - Terms of service
GET  /legal/cookies                  - Cookie policy
GET  /legal/shipping                 - Shipping info
GET  /legal/returns                  - Return policy
```

#### Campaigns & Referrals
```
GET  /campaigns                      - Active campaigns
GET  /campaigns/{campaign}           - Campaign details
GET  /referrals                      - Referral program
POST /referrals/generate-code        - Generate code
GET  /ref/{code}                     - Referral tracking
```

#### Localized Routes (All routes duplicated per locale)
```
GET  /{locale}/products
GET  /{locale}/categories
GET  /{locale}/brands
GET  /{locale}/cart
GET  /{locale}/news
... (300+ localized routes)
```

---

## 🔐 ADMIN PANEL (FILAMENT v4)

### Admin Resources (60+)

#### E-Commerce Management
- **Products** - Product CRUD with variants
- **Product Variants** - Variant management
- **Product Images** - Image gallery
- **Product Histories** - Change logs
- **Product Features** - Feature management
- **Product Similarities** - Similar products
- **Product Requests** - Customer requests
- **Categories** - Category tree
- **Brands** - Brand management
- **Collections** - Collection builder
- **Attributes** - Attribute system
- **Attribute Values** - Value management

#### Order Management
- **Orders** - Order processing
- **Order Items** - Line item details
- **Order Shippings** - Shipping tracking
- **Cart Items** - Active carts

#### Inventory
- **Inventories** - Stock levels
- **Variant Inventories** - Variant stock
- **Stock Movements** - Movement history
- **Locations** - Warehouse locations

#### Pricing & Discounts
- **Prices** - Price management
- **Price Lists** - Customer pricing
- **Price List Items** - Price entries
- **Discounts** - Discount rules
- **Discount Codes** - Code management
- **Discount Conditions** - Condition builder
- **Discount Redemptions** - Usage tracking
- **Coupons** - Coupon system
- **Coupon Usages** - Usage history

#### Campaigns
- **Campaigns** - Campaign management
- **Campaign Product Targets** - Product targeting
- **Campaign Clicks** - Click tracking
- **Campaign Conversions** - Conversion tracking
- **Campaign Views** - View analytics
- **Campaign Schedules** - Scheduling
- **Campaign Customer Segments** - Audience segmentation

#### Customer Management
- **Users** - User accounts
- **Customer Groups** - Group management
- **Addresses** - Address book
- **Customer Management** - Unified view

#### Analytics & Reporting
- **Analytics Events** - Event tracking
- **User Behaviors** - Behavior analysis
- **User Product Interactions** - Engagement
- **User Preferences** - Preference learning
- **Recommendation Analytics** - Recommendation performance
- **Variant Analytics** - Variant metrics

#### Referral System
- **Referrals** - Referral tracking
- **Referral Codes** - Code management
- **Referral Rewards** - Reward allocation
- **Referral Campaigns** - Campaign management
- **Referral Statistics** - Performance stats
- **Referral Code Statistics** - Code analytics
- **Referral Reward Logs** - Audit trail

#### Partner Management
- **Partners** - Partner accounts
- **Partner Tiers** - Tier management
- **API Keys** - API access control

#### Content Management
- **News** - News articles
- **News Categories** - Category management
- **News Tags** - Tag management
- **News Comments** - Comment moderation
- **News Images** - Image management
- **Posts** - Blog posts
- **Menus** - Menu builder
- **Menu Items** - Menu structure
- **Sliders** - Homepage sliders

#### System Management
- **System Settings** - Application config
- **System Setting Categories** - Setting groups
- **Feature Flags** - Feature toggles
- **Enum Management** - Dynamic enums
- **Enum Values** - Enum options
- **Channels** - Sales channels
- **Currencies** - Currency config
- **Locations** - Store locations

#### Geographic Data
- **Countries** - Country database
- **Regions** - Regional data
- **Cities** - City database
- **Zones** - Shipping zones
- **Shipping Options** - Shipping methods

#### Marketing & Notifications
- **Email Campaigns** - Email marketing
- **Notification Templates** - Template management
- **Subscribers** - Newsletter subscribers
- **Reviews** - Review moderation

#### Documents & Legal
- **Documents** - Document management
- **Document Templates** - Template library
- **Legals** - Legal pages

#### SEO
- **SEO Data** - Meta management

#### Recommendations
- **Recommendation Blocks** - Block management
- **Recommendation Configs** - Algorithm config
- **Recommendation Cache** - Cache management

#### Security & Audit
- **Activity Logs** - Activity monitoring
- **Audit Trails** - Audit tracking
- **Roles** - Role management
- **Admin Users** - Admin accounts

#### Technical
- **Stocks** - Stock overview
- **Notifications** - System notifications

### Admin Pages (10+)

- **Dashboard** - Analytics overview
- **Customer Segmentation** - Segmentation tool
- **Data Import/Export** - Bulk operations
- **Inventory Management** - Stock dashboard
- **Observability** - System monitoring
- **Security Audit** - Security review
- **Slider Analytics** - Slider performance
- **Slider Management** - Slider editor
- **User Impersonation** - User testing

---

## 🌐 LOCALIZATION & INTERNATIONALIZATION

### Supported Locales
- **lt** (Lithuanian) - Default
- **en** (English) - Secondary
- **de** (German) - Additional
- **ru** (Russian) - Additional

### Translation Strategy

#### Database Translations (Spatie Translatable)
All major entities have dedicated translation tables:
- `*_translations` pattern (45+ translation tables)
- Columns: locale, name, description, slug, seo_*
- Automatic fallback to default locale

#### JSON Translations
```
lang/
├── lt.json
├── en.json
├── de.json
└── ru.json
```

#### Blade Translation Files
```
lang/
├── lt/
├── en/
├── de/
└── ru/
```

### Translatable Entities
- Products (name, description, SEO)
- Categories (name, description, slug)
- Brands (name, description)
- Collections (name, description)
- Attributes (name)
- Attribute Values (value)
- News (title, content, summary)
- Campaigns (name, description, CTA)
- Legal pages (title, content)
- System settings (name, description)
- Menus (labels)
- And 35+ more entities

### Locale Management
- **Locale Switching**: Session-based locale
- **URL Structure**: `/{locale}/path` pattern
- **Middleware**: Locale detection
- **Fallback**: Default to Lithuanian

---

## 🔒 SECURITY & PERFORMANCE

### Authentication
- **Guards**: web, admin, sanctum
- **Multi-Factor**: Two-factor authentication
- **Social Login**: OAuth via Socialite
- **API Tokens**: Sanctum tokens
- **Session Management**: Secure sessions

### Authorization
- **RBAC**: Role-based access control
- **Policies**: Granular permissions
- **Filament Shield**: Admin panel security
- **Abilities**: Custom abilities
- **Gates**: Authorization gates

### Security Headers
- **CSP**: Content Security Policy
- **HSTS**: HTTP Strict Transport Security
- **X-Frame-Options**: DENY
- **X-Content-Type-Options**: nosniff
- **Referrer-Policy**: strict-origin-when-cross-origin

### Rate Limiting
- **API**: 60/minute default
- **Auth Login**: Max 5 attempts / 1 minute
- **Password Reset**: Max 3 attempts / 1 minute
- **Checkout**: Custom limits
- **Autocomplete**: 120/minute

### Performance Optimization
- **Caching**:
  - Redis for sessions, cache, queues
  - Database cache driver
  - Route caching
  - Config caching
  - View caching
  - Media caching

- **Queue System**:
  - Horizon for monitoring
  - Redis queue driver
  - Dead letter queue
  - Job batching
  - Deferred queue

- **Database**:
  - Query optimization
  - Eager loading (N+1 prevention)
  - Database indexing (300+ indexes)
  - Foreign key constraints
  - JSON validation constraints

- **Assets**:
  - Vite bundling
  - WebP image conversion
  - Image optimization
  - CSS/JS minification
  - Lazy loading

### Privacy & GDPR
- **Data Export**: User data export
- **Data Deletion**: Right to be forgotten
- **Consent Management**: Cookie consent
- **Privacy Settings**: User privacy controls
- **Audit Retention**: Configurable retention

---

## 🧪 TESTING STRATEGY

### Test Structure

```
tests/
├── Unit/                 # Unit tests (100+)
│   ├── Models/
│   ├── Services/
│   ├── Components/
│   └── Config/
├── Feature/              # Feature tests (150+)
│   ├── Admin/
│   ├── Frontend/
│   └── Api/
├── Browser/              # Dusk tests
│   ├── Admin/
│   └── Frontend/
└── Performance/          # Performance tests
```

### Testing Tools
- **Pest**: Primary testing framework
- **PHPUnit**: Alternative framework
- **Laravel Dusk**: Browser testing
- **Playwright**: E2E testing
- **Paratest**: Parallel testing

### Test Coverage Areas

#### Unit Tests (100+)
- Model factories and relationships
- Service class logic
- Data objects (Spatie Data)
- Enums and value objects
- Helpers and utilities
- Component rendering
- Policy authorization
- Observer behavior

#### Feature Tests (150+)
- API endpoint responses
- Frontend controllers
- Admin panel resources
- Authentication flows
- Order processing
- Cart operations
- Discount application
- Campaign tracking
- Notification delivery
- Email sending
- File uploads
- Search functionality

#### Browser Tests (Dusk)
- Complete checkout flow
- Admin panel navigation
- Product browsing
- Cart management
- User registration/login
- Account management

### Testing Conventions
- **Pest syntax**: `it()` and `test()` functions
- **Datasets**: Shared test data
- **Factories**: All models have factories
- **Refresh Database**: Trait usage
- **Parallel Testing**: Configured
- **Code Coverage**: Tracked

---

## 🚀 DEPLOYMENT & DEVOPS

### Environment Requirements

#### Production
- **PHP**: ≥8.3
- **MySQL**: ≥8.0
- **Redis**: ≥7.0
- **Node.js**: ≥20.x
- **Composer**: ≥2.7
- **Memory**: ≥512MB (PHP)

#### Extensions Required
- PDO
- mbstring
- OpenSSL
- JSON
- BCMath
- Ctype
- Fileinfo
- GD or Imagick
- Redis extension

### Build Scripts

#### Development
```bash
composer dev          # Start dev environment
npm run dev          # Vite dev server
php artisan horizon  # Queue monitoring
```

#### Production Build
```bash
composer build       # Optimize + build assets
npm run build       # Production assets
php artisan optimize # Cache everything
```

#### Code Quality
```bash
composer check       # Lint + analyze + test
composer fix         # Auto-fix code style
composer analyze     # Static analysis
vendor/bin/pint      # Format code
```

### Artisan Commands (200+)

#### Custom Commands
```bash
php artisan products:import            # Import products
php artisan images:convert-webp        # Convert images
php artisan inventory:reconcile        # Reconcile stock
php artisan i18n:audit                 # Check translations
php artisan reports:generate           # Generate reports
php artisan search:index:rebuild       # Rebuild search
php artisan route:audit                # Route health check
php artisan catalog:xml                # Import/export catalog
php artisan code-style:fix             # Fix code style
php artisan filament:analyze           # Analyze Filament
php artisan backup:prepare             # Prepare backup
php artisan backup:verify              # Verify backup
```

### Queue Jobs (15+)
- `CheckLowStockJob` - Stock monitoring
- `GenerateReportsJob` - Report generation
- `ProcessExportJob` - Data exports
- `SendNotificationJob` - Notifications
- `RebuildSearchIndexJob` - Search indexing
- `GenerateMediaVariantsJob` - Image processing
- `ImportInventoryChunk` - Bulk imports
- `ImportPricesChunk` - Price imports
- `ImportProductsChunk` - Product imports

### Scheduled Tasks
- Horizon snapshots (5 minutes)
- Queue monitoring
- Cache pruning
- Session cleanup
- Failed job retry
- Low stock alerts
- Campaign auto-start/stop
- Report generation
- Analytics aggregation

---

## 📦 KEY PACKAGES

### Laravel Ecosystem
- `laravel/framework` - 12.35.1
- `laravel/horizon` - 5.37.0
- `laravel/sanctum` - 4.2.0
- `laravel/scout` - 10.20.0
- `laravel/tinker` - 2.10.1

### Filament Ecosystem
- `filament/filament` - 4.1.10
- `bezhansalleh/filament-shield` - 4.0
- `pxlrbt/filament-excel` - 3.1
- `lara-zeus/bolt` - 4.0.5
- `filament/spatie-laravel-media-library-plugin` - 4.0
- `pixelpeter/filament-language-tabs` - 3.0
- `dutchcodingcompany/filament-socialite` - 3.0
- `eightynine/filament-advanced-widgets` - 4.0
- `hydrat/filament-table-layout-toggle` - 3.0
- `kenepa/resource-lock` - 4.0

### Spatie Packages
- `spatie/laravel-permission` - 6.21
- `spatie/laravel-activitylog` - 4.10
- `spatie/laravel-data` - 4.17
- `spatie/laravel-medialibrary` - 11.x
- `spatie/laravel-translatable` - 6.11
- `spatie/laravel-sluggable` - 3.7

### Development Tools
- `pestphp/pest` - 3.8.4
- `laravel/dusk` - 8.3.3
- `laravel/pint` - 1.25.1
- `larastan/larastan` - 3.7.2
- `barryvdh/laravel-debugbar` - 3.16
- `barryvdh/laravel-dompdf` - 3.1

---

## 📐 ARCHITECTURAL PATTERNS

### 1. Repository Pattern
```php
ProductRepository
CategoryRepository
MenuRepository
UserRepository
```

### 2. Service Layer
```php
Services/
├── ProductService
├── OrderService
├── DiscountService
├── CampaignService
├── NotificationService
└── RecommendationService
```

### 3. Data Transfer Objects (Spatie Data)
```php
Data/
├── CampaignClickData
├── DiscountCodeValidationData
├── ExportRequestData
├── NewsCommentData
├── ProductRequestData
├── ReviewData
└── SearchQueryData
```

### 4. Domain-Driven Design
```php
Domain/Product/
├── Entities/
├── ValueObjects/
├── Repositories/
└── Services/
```

### 5. Use Cases (CQRS-inspired)
```php
UseCases/
├── Cache/
├── Category/
├── Menu/
└── Product/
```

### 6. Event-Driven Architecture
```php
Events/
├── CouponApplied
├── CouponRemoved
├── NotificationCreated
└── NotificationReadStatusUpdated

Listeners/
└── ConvertBrandImagesToWebP
```

### 7. Observer Pattern
```php
Observers/
├── ProductObserver
├── OrderObserver
├── UserObserver
└── (Auto-registered via attributes)
```

### 8. Policy-Based Authorization
```php
Policies/
├── ProductPolicy
├── OrderPolicy
├── CategoryPolicy
└── (60+ policies)
```

---

## 🔑 KEY FUNCTIONAL REQUIREMENTS

### FR-1: Product Management
- **FR-1.1**: Support simple and variant products
- **FR-1.2**: Dynamic attribute system (filterable, searchable)
- **FR-1.3**: Multi-image gallery with WebP optimization
- **FR-1.4**: SEO metadata per product/variant
- **FR-1.5**: Price history tracking
- **FR-1.6**: Product similarity calculation
- **FR-1.7**: Product request system for unavailable items
- **FR-1.8**: Video URL support
- **FR-1.9**: Minimum quantity enforcement
- **FR-1.10**: Hide add-to-cart option per product

### FR-2: Inventory Management
- **FR-2.1**: Multi-location stock tracking
- **FR-2.2**: Variant-level inventory
- **FR-2.3**: Stock reservations during checkout
- **FR-2.4**: Low stock threshold alerts
- **FR-2.5**: Stock movement audit trail
- **FR-2.6**: Backorder support
- **FR-2.7**: Inventory reconciliation
- **FR-2.8**: Bulk import/export

### FR-3: Pricing Engine
- **FR-3.1**: Base, sale, compare, cost prices
- **FR-3.2**: Customer group pricing
- **FR-3.3**: Price lists (partner/region-specific)
- **FR-3.4**: Quantity-based pricing rules
- **FR-3.5**: Time-based pricing (sales)
- **FR-3.6**: Multi-currency support with conversion
- **FR-3.7**: VAT/tax calculation
- **FR-3.8**: Price history tracking

### FR-4: Discount System
- **FR-4.1**: Percentage and fixed discounts
- **FR-4.2**: Product/category/cart-wide scope
- **FR-4.3**: Conditional logic (min amount, customer group)
- **FR-4.4**: Coupon code system
- **FR-4.5**: Stackable/exclusive rules
- **FR-4.6**: Usage limits (per-user, per-code, per-day)
- **FR-4.7**: Time-based activation
- **FR-4.8**: Auto-apply discounts
- **FR-4.9**: First-time customer discounts
- **FR-4.10**: Free shipping discounts

### FR-5: Campaign Management
- **FR-5.1**: Multi-channel campaign support
- **FR-5.2**: Customer segmentation targeting
- **FR-5.3**: Product/category targeting
- **FR-5.4**: View/click/conversion tracking
- **FR-5.5**: ROI and ROAS calculation
- **FR-5.6**: Attribution modeling (last-click, first-click, linear)
- **FR-5.7**: Budget limits with auto-pause
- **FR-5.8**: Scheduled activation
- **FR-5.9**: A/B testing support
- **FR-5.10**: Campaign analytics dashboard

### FR-6: Referral Program
- **FR-6.1**: Unique referral code per user
- **FR-6.2**: Automatic code generation
- **FR-6.3**: Referral tracking and attribution
- **FR-6.4**: Multi-tier reward system
- **FR-6.5**: Reward types (discount, credit, points)
- **FR-6.6**: Referral campaigns
- **FR-6.7**: Usage statistics and analytics
- **FR-6.8**: Social sharing integration
- **FR-6.9**: Expiration management
- **FR-6.10**: Fraud prevention (self-referral check)

### FR-7: Order Management
- **FR-7.1**: Guest and user checkout
- **FR-7.2**: Order status workflow (9 statuses)
- **FR-7.3**: Multiple payment methods
- **FR-7.4**: Shipping calculation
- **FR-7.5**: Order tracking
- **FR-7.6**: Invoice generation (PDF)
- **FR-7.7**: Order cancellation
- **FR-7.8**: Return requests
- **FR-7.9**: Email notifications
- **FR-7.10**: Order history and reordering

### FR-8: Customer Experience
- **FR-8.1**: Product search with autocomplete
- **FR-8.2**: Advanced filtering (attributes, price, brand)
- **FR-8.3**: Product comparison (max 4 products)
- **FR-8.4**: Wishlist (multiple lists)
- **FR-8.5**: Recently viewed products
- **FR-8.6**: Product recommendations
- **FR-8.7**: Review and rating system
- **FR-8.8**: Q&A on products
- **FR-8.9**: Social sharing
- **FR-8.10**: Email notifications

### FR-9: News & Content
- **FR-9.1**: News article management
- **FR-9.2**: Category organization
- **FR-9.3**: Tag system
- **FR-9.4**: Multi-image support
- **FR-9.5**: Comment system with moderation
- **FR-9.6**: Approval workflow
- **FR-9.7**: SEO optimization
- **FR-9.8**: Related articles
- **FR-9.9**: Featured articles
- **FR-9.10**: RSS feed generation

### FR-10: Recommendation Engine
- **FR-10.1**: Similar products (content-based)
- **FR-10.2**: Frequently bought together
- **FR-10.3**: Trending products
- **FR-10.4**: Personalized recommendations
- **FR-10.5**: Category-based recommendations
- **FR-10.6**: Configurable algorithms
- **FR-10.7**: A/B testing support
- **FR-10.8**: Performance analytics
- **FR-10.9**: Cache optimization
- **FR-10.10**: Recommendation blocks for homepage

---

## 🔌 TECHNICAL REQUIREMENTS

### TR-1: Database
- **TR-1.1**: MySQL ≥8.0
- **TR-1.2**: Proper indexing (300+ indexes)
- **TR-1.3**: Foreign key constraints
- **TR-1.4**: JSON validation constraints
- **TR-1.5**: Soft deletes on critical tables
- **TR-1.6**: Timestamps on all tables
- **TR-1.7**: UTF8MB4 charset
- **TR-1.8**: InnoDB engine

### TR-2: Performance
- **TR-2.1**: Page load time <2s
- **TR-2.2**: API response time <200ms
- **TR-2.3**: Database queries <50 per page
- **TR-2.4**: Redis caching for hot data
- **TR-2.5**: Image lazy loading
- **TR-2.6**: WebP image format
- **TR-2.7**: Vite asset bundling
- **TR-2.8**: Route/config caching in production

### TR-3: Security
- **TR-3.1**: HTTPS only
- **TR-3.2**: Security headers (CSP, HSTS)
- **TR-3.3**: CSRF protection
- **TR-3.4**: XSS prevention
- **TR-3.5**: SQL injection prevention (ORM)
- **TR-3.6**: Rate limiting
- **TR-3.7**: Password hashing (bcrypt)
- **TR-3.8**: API authentication (Sanctum)
- **TR-3.9**: Admin 2FA support
- **TR-3.10**: Session security

### TR-4: Scalability
- **TR-4.1**: Queue-based processing
- **TR-4.2**: Horizontal scaling support
- **TR-4.3**: Database connection pooling
- **TR-4.4**: Cache abstraction layer
- **TR-4.5**: CDN-ready asset structure
- **TR-4.6**: Multi-tenant architecture ready
- **TR-4.7**: Microservices compatibility

### TR-5: Monitoring & Logging
- **TR-5.1**: Laravel Telescope (dev)
- **TR-5.2**: Horizon queue monitoring
- **TR-5.3**: Activity logging (Spatie)
- **TR-5.4**: Custom audit trails
- **TR-5.5**: Performance metrics
- **TR-5.6**: Error tracking (Sentry ready)
- **TR-5.7**: Log rotation
- **TR-5.8**: Custom log channels

### TR-6: Code Quality
- **TR-6.1**: PSR-12 compliance (Pint)
- **TR-6.2**: PHPStan level 6
- **TR-6.3**: Strict typing (`declare(strict_types=1)`)
- **TR-6.4**: Final classes (controllers, services)
- **TR-6.5**: Explicit return types
- **TR-6.6**: No property mutations
- **TR-6.7**: Repository pattern
- **TR-6.8**: Service layer
- **TR-6.9**: Form request validation
- **TR-6.10**: Policy-based authorization

---

## 📊 DATA MODEL SUMMARY

### Product Domain (18 models)
- Product, ProductVariant, ProductImage, ProductHistory
- ProductFeature, ProductSimilarity, ProductRequest
- ProductComparison, ProductAnalytics
- Inventory, VariantInventory, StockMovement
- Price, PriceList, PriceListItem
- Attribute, AttributeValue
- VariantAnalytics, VariantCombination

### Order Domain (8 models)
- Order, OrderItem, OrderShipping
- CartItem, Wishlist, WishlistItem, UserWishlist
- ProductComparison

### Catalog Organization (6 models)
- Category, Brand, Collection
- CollectionRule, Menu, MenuItem

### Discount & Campaign (14 models)
- Discount, DiscountCode, DiscountCondition, DiscountRedemption
- Coupon, CouponUsage
- Campaign, CampaignClick, CampaignConversion, CampaignView
- CampaignSchedule, CampaignProductTarget, CampaignCustomerSegment

### Referral System (5 models)
- Referral, ReferralCode, ReferralReward
- ReferralCampaign, ReferralRewardLog

### Analytics & Tracking (6 models)
- AnalyticsEvent, UserBehavior, UserProductInteraction
- UserPreference, RecommendationAnalytics, RecommendationCache

### User & Access (7 models)
- User, AdminUser, Address
- CustomerGroup, Partner, PartnerTier

### Content Management (7 models)
- News, NewsCategory, NewsTag, NewsComment, NewsImage
- Post, Legal

### Recommendation System (4 models)
- RecommendationBlock, RecommendationCache
- RecommendationAnalytics, ProductSimilarity

### System Configuration (6 models)
- Setting, SystemSetting, SystemSettingCategory
- Currency, Channel, Location, FeatureFlag

### Documents (3 models)
- Document, DocumentTemplate, SeoData

### Notifications (2 models)
- NotificationTemplate, EmailCampaign

### Geographic (4 models)
- Country, Region, City, Location

---

## 🎯 NON-FUNCTIONAL REQUIREMENTS

### NFR-1: Usability
- **NFR-1.1**: Intuitive admin interface (Filament)
- **NFR-1.2**: Mobile-responsive frontend
- **NFR-1.3**: Fast search results (<1s)
- **NFR-1.4**: Clear error messages (translated)
- **NFR-1.5**: Accessible (WCAG 2.1 AA target)

### NFR-2: Reliability
- **NFR-2.1**: 99.9% uptime target
- **NFR-2.2**: Database backups (daily)
- **NFR-2.3**: Failed job retry mechanism
- **NFR-2.4**: Graceful error handling
- **NFR-2.5**: Transaction support for critical operations

### NFR-3: Maintainability
- **NFR-3.1**: Comprehensive PHPDoc comments
- **NFR-3.2**: Clear naming conventions
- **NFR-3.3**: Modular architecture
- **NFR-3.4**: Repository pattern for data access
- **NFR-3.5**: Service layer for business logic
- **NFR-3.6**: Automated testing (unit + feature)

### NFR-4: Compatibility
- **NFR-4.1**: Modern browsers (Chrome, Firefox, Safari, Edge)
- **NFR-4.2**: Mobile browsers (iOS Safari, Chrome Mobile)
- **NFR-4.3**: API versioning (v1)
- **NFR-4.4**: Backward compatibility for minor updates

### NFR-5: Data Integrity
- **NFR-5.1**: Foreign key constraints
- **NFR-5.2**: JSON validation constraints
- **NFR-5.3**: Enum validation
- **NFR-5.4**: Unique constraints
- **NFR-5.5**: Soft deletes for critical data
- **NFR-5.6**: Audit trails

---

## 🗺 FEATURE MATRIX

| Feature Category | Complexity | Status | Priority | Models | Routes | Tests |
|-----------------|------------|--------|----------|--------|--------|-------|
| Product Catalog | High | ✅ Active | Critical | 18 | 150+ | 80+ |
| Order Management | High | ✅ Active | Critical | 8 | 50+ | 40+ |
| Inventory | Medium | ✅ Active | High | 5 | 30+ | 20+ |
| Discounts | High | ✅ Active | Critical | 8 | 40+ | 30+ |
| Campaigns | High | ✅ Active | High | 8 | 50+ | 25+ |
| Referrals | Medium | ✅ Active | Medium | 5 | 30+ | 15+ |
| Recommendations | High | ✅ Active | Medium | 4 | 20+ | 10+ |
| News/Content | Medium | ✅ Active | Medium | 7 | 40+ | 20+ |
| Analytics | High | ✅ Active | High | 6 | 30+ | 15+ |
| Multi-Language | High | ✅ Active | Critical | All | All | 30+ |
| Multi-Currency | Medium | ✅ Active | High | 3 | 20+ | 10+ |
| SEO | Medium | ✅ Active | High | 1 | 15+ | 8+ |
| API | Medium | ✅ Active | High | - | 80+ | 40+ |
| Admin Panel | High | ✅ Active | Critical | - | 600+ | 100+ |

---

## 📈 SYSTEM METRICS

### Database Metrics
- **Total Tables**: 145
- **With Foreign Keys**: 120+
- **With Indexes**: 145 (300+ indexes total)
- **With JSON Columns**: 80+
- **With Translations**: 45+
- **With Soft Deletes**: 60+

### Application Metrics
- **Models**: 68
- **Controllers**: 100+
- **Requests (Form Validation)**: 150+
- **Policies**: 60+
- **Observers**: 20+
- **Services**: 50+
- **Jobs**: 15+
- **Commands**: 200+
- **Middleware**: 15+

### Route Metrics
- **Total Routes**: 985
- **Frontend Routes**: 300+
- **Admin Routes**: 600+
- **API Routes**: 80+
- **Localized Routes**: 400+

### Test Metrics
- **Unit Tests**: 100+
- **Feature Tests**: 150+
- **Browser Tests**: 20+
- **Total Tests**: 270+

---

## 🔐 SECURITY REQUIREMENTS

### Authentication
- **SR-1.1**: Multi-guard authentication (web, admin, sanctum)
- **SR-1.2**: Social login support (OAuth)
- **SR-1.3**: Two-factor authentication
- **SR-1.4**: Password reset flow
- **SR-1.5**: Email verification
- **SR-1.6**: Remember me functionality
- **SR-1.7**: Session management
- **SR-1.8**: API token management

### Authorization
- **SR-2.1**: Role-based access control (RBAC)
- **SR-2.2**: Permission-based authorization
- **SR-2.3**: Resource-level policies
- **SR-2.4**: Filament Shield integration
- **SR-2.5**: Customer group permissions
- **SR-2.6**: Partner access control
- **SR-2.7**: Admin impersonation (with audit)

### Data Protection
- **SR-3.1**: HTTPS only
- **SR-3.2**: Encrypted sensitive data
- **SR-3.3**: CSRF protection
- **SR-3.4**: XSS prevention
- **SR-3.5**: SQL injection prevention
- **SR-3.6**: File upload validation
- **SR-3.7**: Rate limiting
- **SR-3.8**: IP blocking capability

### Privacy & Compliance
- **SR-4.1**: GDPR compliance
- **SR-4.2**: Data export functionality
- **SR-4.3**: Data deletion (right to be forgotten)
- **SR-4.4**: Cookie consent management
- **SR-4.5**: Privacy policy display
- **SR-4.6**: Audit log retention policies

---

## 🚀 DEPLOYMENT ARCHITECTURE

### Environments
```
Production  → statybaecommerse.prus.dev
Staging     → (TBD)
Development → Hr11x.test (Laravel Herd)
```

### Server Requirements
- **Web Server**: Nginx or Apache
- **PHP-FPM**: 8.3+
- **MySQL**: 8.0+
- **Redis**: 7.0+
- **Node.js**: 20.x (build only)
- **Memory**: 1GB minimum, 2GB recommended
- **Storage**: 20GB minimum (with media)

### Build Pipeline
1. **Install Dependencies**: `composer install --no-dev`
2. **Build Assets**: `npm run build`
3. **Optimize**: `php artisan optimize`
4. **Migrate**: `php artisan migrate --force`
5. **Cache**: `composer cache:warm`
6. **Link Storage**: `php artisan storage:link`
7. **Queue Workers**: Start Horizon
8. **Scheduler**: Configure cron

### Monitoring Stack
- **Application**: Laravel Telescope (dev)
- **Queues**: Laravel Horizon
- **Logs**: Daily rotation, Pail viewer
- **Performance**: Custom metrics collector
- **Errors**: Sentry integration ready
- **Uptime**: Health check endpoints

---

## 📝 DATA FLOW DIAGRAMS

### Order Processing Flow
```
Cart → Checkout → Validate → Apply Discounts → Calculate Shipping 
  → Process Payment → Create Order → Send Emails → Update Inventory
  → Queue Notifications → Generate Documents → Complete
```

### Campaign Tracking Flow
```
View Campaign → Track View → Click CTA → Track Click 
  → Add to Cart → Checkout → Track Conversion 
  → Attribute Revenue → Update Analytics → Cache Metrics
```

### Recommendation Generation Flow
```
User Behavior → Extract Features → Apply Algorithm 
  → Filter Products → Score & Rank → Cache Results 
  → Serve Recommendations → Track Performance → Optimize
```

---

## 🧩 INTEGRATION POINTS

### External Services (Ready for Integration)
- **Payment Gateways**: Stripe, PayPal ready
- **Email Service**: Mailchimp API configured
- **Analytics**: Google Analytics ready
- **Search**: Scout driver (Algolia, Meilisearch, Typesense)
- **Media Storage**: S3 configured
- **Error Tracking**: Sentry configured

### Webhooks & Events
- Order status changes
- Stock level changes
- Campaign conversions
- Referral completions
- Payment confirmations

---

## 📚 DOCUMENTATION

### Available Documentation
```
docs/
├── ARCHITECTURE_OVERVIEW.md    - System architecture
├── CONTRIBUTING_DOCS.md         - Documentation guide
├── INDEX.md                     - Documentation index
├── STYLE_GUIDE.md               - Coding standards
├── api/                         - API documentation
├── contracts/                   - Data contracts
├── filament/                    - Admin panel docs
├── forms/                       - Form documentation
├── i18n/                        - Translation docs
├── operations/                  - Operational guides
├── runbooks/                    - Troubleshooting
└── ui/                          - UI component docs
```

### Generated Documentation
- **OpenAPI Spec**: `/public/openapi.json`, `/public/openapi.yaml`
- **API Docs**: `/docs/api`
- **PHPDoc**: Auto-generated from code
- **Test Results**: `/test-results`

---

## 🔄 MAINTENANCE & OPERATIONS

### Regular Maintenance Tasks

#### Daily
- Queue monitoring (Horizon)
- Error log review
- Failed job retry
- Cache warming
- Backup verification

#### Weekly
- Performance review
- Search index optimization
- Image optimization
- Database cleanup
- Translation audit

#### Monthly
- Dependency updates
- Security patches
- Database optimization
- Log archival
- Analytics review

### Automated Cleanup
- **Expired sessions**: Automatic
- **Old cart items**: 7 days
- **Failed jobs**: 30 days
- **Activity logs**: Configurable
- **Cache tags**: Expired entries
- **Temporary uploads**: 24 hours

---

## 🎓 DEVELOPMENT WORKFLOW

### Local Development (Laravel Herd)
```bash
# Start all services
composer dev

# Individual services
php artisan serve       # Web server
php artisan queue:listen # Queue worker
php artisan pail        # Log viewer
npm run dev            # Vite dev server
```

### Code Quality Checks
```bash
# Format code
composer fix:php        # Pint formatter
composer rector         # Rector refactoring

# Analyze code
composer analyze        # PHPStan analysis
composer lint:php       # Pint linter

# Run tests
composer test           # Pest tests
php artisan dusk        # Browser tests
```

### Database Operations
```bash
# Fresh install
php artisan migrate:fresh --seed

# Specific seeders
php artisan db:seed --class=ProductSeeder
php artisan db:seed --class=CategorySeeder

# Import data
php artisan products:import
php artisan catalog:xml --action=import
```

---

## 🧪 TESTING REQUIREMENTS

### Test Coverage Goals
- **Overall**: ≥80%
- **Models**: ≥90%
- **Services**: ≥85%
- **Controllers**: ≥75%
- **API**: ≥95%
- **Critical Paths**: 100%

### Test Types Required

#### Unit Tests
- All models (factories, relationships, scopes)
- All services (business logic)
- All data objects
- All enums
- All helpers

#### Feature Tests
- All API endpoints
- All frontend controllers
- All admin resources (Filament)
- All form requests
- All policies

#### Browser Tests
- Complete checkout flow
- User registration/login
- Product browsing
- Cart operations
- Admin panel navigation

#### Integration Tests
- Payment gateway integration
- Email sending
- Queue processing
- Search indexing
- Cache operations

---

## 📋 OUTSTANDING REQUIREMENTS

### Phase 1 (Current)
- ✅ Core eCommerce functionality
- ✅ Admin panel with Filament v4
- ✅ Multi-language support
- ✅ Multi-currency support
- ✅ Discount system
- ✅ Campaign tracking
- ✅ Referral program
- ✅ News/Blog system

### Phase 2 (Planned)
- ⏳ Payment gateway integration
- ⏳ Shipping carrier integration
- ⏳ Advanced search (Meilisearch)
- ⏳ Real-time notifications (Pusher/Reverb)
- ⏳ Product subscriptions
- ⏳ Gift cards
- ⏳ Customer loyalty program
- ⏳ Advanced analytics dashboard

### Phase 3 (Future)
- 📅 Mobile app API
- 📅 Headless commerce API
- 📅 Multi-tenant support
- 📅 Marketplace features
- 📅 Vendor management
- 📅 Advanced B2B features
- 📅 International expansion

---

## 🏁 SUCCESS CRITERIA

### Technical Success
- ✅ All tests passing (270+ tests)
- ✅ Zero critical bugs
- ✅ PHPStan level 6 compliance
- ✅ PSR-12 code style
- ✅ Database properly indexed
- ✅ No N+1 query issues
- ✅ Proper error handling
- ✅ Complete audit trails

### Business Success
- ✅ Product catalog operational
- ✅ Order processing functional
- ✅ Payment acceptance ready
- ✅ Shipping calculation accurate
- ✅ Discount system working
- ✅ Campaign tracking active
- ✅ Referral program live
- ✅ Multi-language support complete

### Performance Success
- ✅ Homepage load <2s
- ✅ Product page load <2s
- ✅ Search results <1s
- ✅ API response <200ms
- ✅ Admin panel responsive
- ✅ Queue processing <1min
- ✅ Cache hit rate >80%

---

## 📞 SUPPORT & CONTACT

### Technical Stack Owners
- **Laravel**: Laravel team
- **Filament**: Filament team
- **Spatie**: Spatie team
- **Custom**: Internal development team

### Critical Dependencies
- Laravel framework (monthly updates)
- Filament packages (quarterly updates)
- PHP security patches (as released)
- Database drivers (as needed)

---

## 📄 APPENDICES

### A. Filament Resources (60+)
See Admin Panel section for complete list

### B. API Endpoints (80+)
See API Endpoints section for complete list

### C. Database Tables (145)
See Database Schema section for complete list

### D. Configuration Files (60+)
See config/ directory for all configuration files

### E. Artisan Commands (200+)
Run `php artisan list` for complete command list

---

## 🔄 VERSION HISTORY

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2025-10-26 | Initial requirements document |

---

## 📝 NOTES

### User Rules Compliance
- ✅ No users/auth system removal (contradicts project structure)
- ✅ No Docker (Laravel Herd used)
- ✅ No Livewire removal (Filament requires it)
- ✅ Tailwind CSS v4 (confirmed)
- ✅ No Bootstrap (confirmed)
- ✅ No CDN usage (all local npm packages)
- ✅ Single layout (confirmed)
- ❌ No reports (user wants to forget reports, but they exist)
- ❌ No CSV/Excel imports (but they exist in codebase)
- ✅ Multi-translation system (JSON + database)
- ✅ Maximum components in blades
- ✅ All CSS/JS from resources folder

### Technical Debt
- Some legacy tables (categories_legacy, sh_* prefixed tables)
- Multiple translation approaches (consolidation opportunity)
- Export/import features exist despite user preference
- Report system exists despite user preference to forget it

---

**END OF REQUIREMENTS DOCUMENT**
