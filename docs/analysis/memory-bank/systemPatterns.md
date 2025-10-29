# System Patterns: Laravel E-commerce Architecture

## Architectural Patterns

### 1. Model-View-Controller (MVC) Pattern
**Implementation:** Laravel's standard MVC architecture with extensions
- **Models:** Eloquent ORM with custom traits and relationships
- **Views:** Blade templates with Livewire components for interactivity
- **Controllers:** Slim controllers with business logic in service classes

### 2. Repository Pattern (via Eloquent)
**Implementation:** Eloquent models serve as repositories
- **Benefits:** Clean data access layer, testable business logic
- **Usage:** All data access through Eloquent models with proper relationships
- **Extensions:** Custom query scopes and model traits

### 3. Service Layer Pattern
**Implementation:** Business logic encapsulated in service classes
- **DiscountEngine:** Complex discount calculation and application
- **PaymentService:** Multi-provider payment processing abstraction
- **TaxCalculator:** Geographic tax calculation service
- **DocumentService:** Template processing and PDF generation
- **SimpleJsonSchemaValidator:** Schema enforcement utility with `oneOf`/array handling to keep public contracts aligned with published specs.

### 4. Action Pattern
**Implementation:** Complex operations as single-purpose action classes
- **CreateOrder:** Complete order processing from cart to confirmation
- **ZoneSessionManager:** Geographic session management
- **PayWithCash:** Cash payment processing implementation

### 5. Data Transfer Object (DTO) Pattern
**Implementation:** Type-safe data structures for complex operations
- **Framework:** Spatie Laravel Data objects with attribute-based validation enforce schema guarantees across controllers, services, and jobs, keeping payloads consistent.
- **AddressData:** Structured address information
- **CountryByZoneData:** Geographic data relationships
- **PriceData:** Pricing information structure
- **ProductRequestData:** Example storefront payload that demonstrates attribute rules, casting, and transformation hooks before data reaches the application layer.

## Design Patterns

### 1. Factory Pattern
**Implementation:** Model factories for testing and seeding
- **Usage:** All models have corresponding factories
- **Benefits:** Consistent test data generation, realistic demo data
- **Features:** State variations (in-stock, discounted, with variants)

### 2. Observer Pattern
**Implementation:** Model events for cache invalidation and logging
- **Cache Invalidation:** Automatic cache clearing on model updates
- **Activity Logging:** Audit trail for business operations
- **Media Processing:** Background image conversion on upload

### 3. Strategy Pattern
**Implementation:** Multiple payment providers and discount types
- **Payment Strategy:** Stripe, NotchPay, Cash payment implementations
- **Discount Strategy:** Percentage, fixed, BOGO, volume discount types
- **Translation Strategy:** Database-driven with fallback mechanisms

### 4. Template Method Pattern
**Implementation:** Document generation with variable templates
- **DocumentTemplate:** Reusable HTML templates with placeholders
- **Variable Processing:** Dynamic content replacement system
- **PDF Generation:** Template-based document creation

## Architectural Decisions

### 1. Translation System Architecture
**Decision:** Database-driven translations with separate tables
**Rationale:** 
- Better performance than JSON-based translations
- Admin interface for translation management
- SEO-friendly with unique slugs per locale
- Scalable for unlimited languages

**Implementation:**
- `HasTranslations` trait for consistent translation access
- Separate translation tables for each translatable model
- Automatic fallback to default locale
- Translation API endpoints for admin interface

### 2. Discount Engine Architecture
**Decision:** Condition-based rule engine with priority system
**Rationale:**
- Supports complex business rules
- Extensible for new discount types
- Performance optimized with caching
- Audit trail for business analytics

**Implementation:**
- JSON-based condition storage for flexibility
- Priority-based stacking with exclusivity rules
- Cached eligibility evaluation
- Tag-based cache invalidation
- Query scopes on discount redemptions expose per-discount, per-user, order-specific, and date-filtered analytics for reporting surfaces.【F:app/Models/DiscountRedemption.php†L130-L178】

### 3. Media Management Architecture
**Decision:** Spatie Media Library with automatic conversions
**Rationale:**
- Professional media handling
- Automatic image optimization
- Multiple format support
- CDN-ready architecture

**Implementation:**
- Multiple media collections per model
- Automatic conversions (thumb, small, large)
- Queue-based processing for performance
- WebP support for modern browsers

### 4. Authentication & Authorization Architecture
**Decision:** Laravel Breeze + Spatie Permissions + Custom 2FA
**Rationale:**
- Standard Laravel authentication patterns
- Granular permission system
- Enhanced security with 2FA
- Filament panel integration

**Implementation:**
- Role-based access control (Administrator, Manager, User)
- 48 granular permissions across modules
- Two-factor authentication with recovery codes
- Session-based authentication with remember tokens

## Performance Patterns

### 1. Caching Strategy
**Pattern:** Multi-layer caching with intelligent invalidation
- **Query Caching:** Eloquent query result caching
- **View Caching:** Blade template caching for static content
- **Translation Caching:** Cached translation lookups
- **Discount Caching:** Cached discount eligibility evaluation

### 2. Queue Processing Pattern
**Pattern:** Background processing for heavy operations
- **Media Processing:** Image conversion in background
- **Email Sending:** Notification queues
- **Import Operations:** Bulk data import via queues
- **Document Generation:** PDF creation in background

### 3. Database Optimization Pattern
**Pattern:** Strategic indexing and query optimization
- **Composite Indexes:** Multi-column indexes for complex queries
- **Translation Indexes:** Locale-based content retrieval
- **Pricing Indexes:** Currency and zone-based pricing lookups
- **Performance Monitoring:** Query optimization with Laravel Telescope
- **Case-insensitive Search Normalisation:** Query scopes such as the
  SystemSettingDependency filters lower-case both the stored column values and
  the incoming terms so LIKE comparisons behave consistently across MySQL,
  PostgreSQL, and SQLite when administrators search for dependency conditions.

## Security Patterns

### 1. Input Validation Pattern
**Pattern:** Multi-layer validation with Form Requests
- **Frontend Validation:** Real-time validation with Livewire
- **Backend Validation:** Laravel Form Request validation
- **Database Validation:** Model-level validation rules
- **API Validation:** Consistent validation across endpoints

### 2. Authorization Pattern
**Pattern:** Policy-based authorization with middleware
- **Route Protection:** Middleware-based route protection
- **Resource Authorization:** Filament resource policies
- **Method-level Authorization:** Controller method authorization
- **Component Authorization:** Livewire component access control

### 3. Security Headers Pattern
**Pattern:** Comprehensive security header implementation
- **CSRF Protection:** Laravel's built-in CSRF protection
- **XSS Prevention:** Input sanitization and output encoding
- **SQL Injection Prevention:** Eloquent ORM parameterized queries
- **File Upload Security:** MIME type validation and path restrictions

## Integration Patterns

### 1. Third-party Integration Pattern
**Pattern:** Service abstraction for external integrations
- **Payment Providers:** Unified interface for multiple providers
- **Shipping APIs:** Ready for shipping provider integration
- **Email Services:** Configurable email provider support
- **CDN Integration:** Media storage abstraction for CDN deployment

### 2. API Design Pattern
**Pattern:** RESTful APIs with consistent response structure
- **Translation API:** CRUD operations for translation management
- **Admin API:** Administrative operations and data management
- **Public API:** Product catalog and customer operations
- **Webhook Support:** Ready for external system notifications

## Scalability Patterns

### 1. Horizontal Scaling Pattern
**Pattern:** Stateless application design for horizontal scaling
- **Session Storage:** Database/Redis session storage
- **File Storage:** Abstracted storage for distributed systems
- **Cache Distribution:** Redis clustering support
- **Queue Distribution:** Multiple queue workers

### 2. Database Scaling Pattern
**Pattern:** Read/write splitting and connection optimization
- **Connection Pooling:** Optimized database connections
- **Query Optimization:** Efficient queries with proper indexing
- **Data Partitioning:** Ready for large dataset partitioning
- **Backup Strategy:** Automated backup and recovery procedures

## Development Patterns

### 1. Testing Strategy Pattern
**Pattern:** Comprehensive testing with Pest framework
- **Unit Tests:** Model and service class testing
- **Feature Tests:** HTTP endpoint and component testing
- **Integration Tests:** End-to-end workflow testing
- **Browser Tests:** Critical user flow testing with Playwright
- **Filament snapshots:** Leave Filament's resource auto-discovery enabled in tests so navigation snapshots and resource audits operate on the canonical ordering registered in production.
- **Environment Guardrails:** The `make test` target now installs Composer dependencies on demand to keep local and CI runs from failing due to missing vendor packages.

### 2. Code Quality Pattern
**Pattern:** Automated code quality enforcement
- **Style Enforcement:** Laravel Pint for PSR-12 compliance
- **Static Analysis:** PHPStan/Larastan for type safety
- **Dependency Management:** Composer for package management
- **Version Control:** Git with semantic commit messages

### 3. Deployment Pattern
**Pattern:** Automated deployment with zero-downtime strategy
- **Environment Configuration:** Environment-based configuration
- **Asset Compilation:** Vite-based asset compilation
- **Migration Strategy:** Safe database migrations
- **Cache Warming:** Post-deployment cache warming

### 4. Data Hygiene Pattern
**Pattern:** Enum maintenance routines protect integrity while keeping tables lean
- **Duplicate Safety:** EnumValue duplicates generate collision-resistant keys so repeated clones never violate the unique `(type, key)` index.【F:app/Models/EnumValue.php†L148-L191】
- **Usage Reset:** Newly duplicated rows zero out usage counters to ensure reporting starts from a clean slate.【F:app/Models/EnumValue.php†L157-L165】
- **Lifecyle Cleanup:** Stale enum rows are pruned lazily by ID, skipping records with any usage to minimise memory pressure during maintenance tasks.【F:app/Models/EnumValue.php†L248-L271】

### 5. Analytics Resilience Pattern
**Pattern:** Stats queries guard against schema drift so dashboards stay accurate
- **Schema-aware revenue totals:** `ProductSeries::dailySales()` now checks whether the `order_items.total` column exists and falls back to `quantity * unit_price` calculations when older exports omit the denormalised totals, ensuring sparkline revenue values match expectations across migrations and cached datasets.【F:app/Support/Stats/Series/ProductSeries.php†L54-L115】

## Filament Integration Patterns

### 1. Navigation Group Pattern
**Pattern:** Consistent navigation group type declarations
- **Type Declaration:** Always use `protected static \UnitEnum|string|null $navigationGroup = NavigationGroup::EnumCase;`
- **Enum Usage:** Use NavigationGroup enum values instead of string literals
- **Import Requirement:** Always import `use App\Enums\NavigationGroup;` at the top of Filament files
- **Type Safety:** Prevents type compatibility errors with Filament's Page class requirements

**Implementation:**
- All Filament pages and resources must use the NavigationGroup enum
- Navigation group property must match Filament's expected type signature
- Consistent navigation organization across the admin panel
- Type-safe navigation group assignments

### 2. Filament Resource Pattern
**Pattern:** Standardized Filament resource structure
- **Form Schemas:** Separate form schema classes for complex forms
 - **Form Signatures:** Maintain `public static function form(Schema $schema): Schema` and return the provided `$schema` so the
  Filament upgrade tooling remains compatible
- **Table Schemas:** Dedicated table schema classes for data display
- **Page Classes:** Custom page classes for create, edit, and list operations
- **Navigation Integration:** Proper navigation group and icon assignments

## Documentation Patterns

### 1. Code Documentation Pattern
**Pattern:** Self-documenting code with strategic comments
- **PHPDoc Comments:** Type hints and method documentation
- **Inline Comments:** Complex business logic explanation
- **README Files:** Setup and configuration instructions
- **API Documentation:** Endpoint documentation with examples

### 2. Business Documentation Pattern
**Pattern:** Comprehensive business process documentation
- **User Guides:** Admin and customer interface guides
- **Process Documentation:** Business workflow documentation
- **Technical Specifications:** Architecture and integration guides
- **Troubleshooting Guides:** Common issue resolution
- **Catalogue Ordering:** Root category navigation relies on `Category::scopeTopLevelVisible()` to filter visible parents and then delegates to the shared `orderedByName` scope for deterministic alphabetical fallbacks.【F:app/Models/Category.php†L254-L267】
- **Campaign/Channel Listings:** Marketing and system channels normalise ordered views through case-insensitive wrappers around the shared ordering trait so dropdowns stay predictable across database engines.【F:app/Models/Campaign.php†L101-L115】【F:app/Models/Channel.php†L125-L138】
- **Campaign Click Metrics:** Click tracking mutators standardise timestamps to UTC while helper methods return float-safe conversion totals for analytics dashboards.【F:app/Models/CampaignClick.php†L61-L94】【F:app/Models/CampaignClick.php†L292-L297】
