# Technical Context: Laravel E-commerce Platform

## Technology Stack

### Backend Framework
**Laravel 12.0**
- **PHP Version:** 8.2+ (latest stable)
- **Features:** Latest Laravel features, improved performance
- **LTS Support:** Long-term support for enterprise deployment
- **Ecosystem:** Rich package ecosystem with Composer

### Admin Panel Framework
**Filament v4 (Latest)**
- **Version:** 4.x (latest stable release)
- **Features:** Modern admin interface with advanced components
- **Integration:** Seamless Laravel integration
- **Customization:** Extensive customization capabilities
- **Performance:** Optimized for large datasets

### Frontend Technology
**Livewire 3.x**
- **Reactivity:** Full-stack reactivity without JavaScript complexity
- **Performance:** Server-side rendering with client-side interactivity
- **SEO:** Search engine friendly with proper HTML output
- **Integration:** Native Laravel integration

**TailwindCSS 4.1.12**
- **Utility-First:** Rapid UI development with utility classes
- **Responsive:** Mobile-first responsive design
- **Customization:** Extensive theming and customization
- **Performance:** Purged CSS for production optimization

### Database Technology
**Primary:** MySQL/MariaDB
- **Version:** MySQL 8.0+ or MariaDB 10.6+
- **Features:** JSON columns, window functions, CTE support
- **Performance:** Optimized for e-commerce workloads
- **Scaling:** Read/write splitting ready

**Development:** SQLite
- **Usage:** Local development and testing
- **Benefits:** Zero-configuration setup
- **Migration:** Seamless production database migration

### Caching & Performance
**Redis**
- **Usage:** Session storage, cache, queue backend
- **Performance:** In-memory data structure store
- **Scaling:** Clustering support for high availability
- **Integration:** Laravel native Redis support

**Laravel Horizon**
- **Purpose:** Queue monitoring and management
- **Features:** Real-time queue metrics and monitoring
- **Scaling:** Automatic worker scaling
- **UI:** Web-based dashboard for queue management

### Media & File Management
**Spatie Media Library**
- **Features:** Advanced media handling with conversions
- **Storage:** Multiple storage disk support (local, S3, CDN)
- **Conversions:** Automatic image optimization and resizing
- **Performance:** Queue-based processing for large files

### Authentication & Security
**Laravel Breeze + Extensions**
- **Base:** Laravel Breeze for authentication scaffolding
- **Extensions:** Custom 2FA implementation
- **Integration:** Filament panel authentication
- **Security:** Enhanced security features

**Spatie Laravel Permission**
- **RBAC:** Role-based access control
- **Granular Permissions:** 48 permissions across modules
- **Integration:** Filament resource authorization
- **Caching:** Permission caching for performance

### Testing Framework
**Pest (Preferred) + PHPUnit**
- **Modern Syntax:** Clean, readable test syntax
- **Laravel Integration:** Native Laravel testing features
- **Coverage:** Code coverage reporting
- **Performance:** Parallel test execution

**Laravel Dusk + Playwright**
- **Browser Testing:** End-to-end user flow testing
- **Modern Tools:** Playwright for reliable browser automation
- **Integration:** Laravel Dusk for framework integration
- **Coverage:** Critical e-commerce flow testing

## Development Tools

### Code Quality
**Laravel Pint**
- **Purpose:** PHP code style fixing (PSR-12)
- **Integration:** Composer script integration
- **Automation:** Git hook integration ready

**PHPStan + Larastan**
- **Purpose:** Static analysis and type checking
- **Level:** Maximum strictness (level 8)
- **Laravel Integration:** Laravel-specific rules
- **IDE Integration:** PhpStorm and VS Code support

### Build Tools
**Vite 7.1.3**
- **Purpose:** Modern asset bundling and optimization
- **Features:** Hot module replacement, tree shaking
- **Performance:** Fast development builds
- **Production:** Optimized production bundles

### Development Environment
**Laravel Herd (Local)**
- **URL:** `http://statybaecommerse.test`
- **Features:** Local development environment
- **Integration:** Native Laravel integration
- **Performance:** Optimized for Laravel development

## Architecture Constraints

### Technical Constraints
1. **PHP Version:** Minimum PHP 8.2 for modern features
2. **Database:** MySQL 8.0+ or MariaDB 10.6+ for JSON support
3. **Memory:** Minimum 512MB PHP memory limit
4. **Storage:** File system or S3-compatible storage
5. **Cache:** Redis required for optimal performance

### Business Constraints
1. **Multilingual:** Must support Lithuanian, English, German
2. **Currency:** Multi-currency support with EUR as primary
3. **Geography:** Zone-based pricing and shipping
4. **Compliance:** EU GDPR and Lithuanian regulations
5. **Performance:** Sub-2-second page load times

### Security Constraints
1. **Authentication:** Two-factor authentication required for admin
2. **Authorization:** Role-based access control mandatory
3. **Data Protection:** GDPR-compliant data handling
4. **Input Validation:** Comprehensive validation on all inputs
5. **File Security:** Secure file upload and storage

## Integration Points

### External Services
**Payment Processors (Ready for Integration):**
- Stripe: Credit card processing
- NotchPay: Local payment methods
- Cash: In-store and COD payments

**Shipping Providers (API Ready):**
- Local Lithuanian carriers
- International shipping (DHL, FedEx, UPS)
- Pickup point networks

**Email Services:**
- SMTP configuration for transactional emails
- Marketing email integration ready
- Multi-language email templates

### Internal Integrations
**Admin Panel Integration:**
- Filament resources for all models
- Custom widgets and dashboard components
- Translation management interface
- Document generation actions

**Storefront Integration:**
- Livewire components for interactivity
- Session-based cart management
- Real-time pricing and availability
- Multi-step checkout process

## Performance Optimizations

### Database Optimizations
1. **Indexing Strategy:**
   - Composite indexes for complex queries
   - Translation table indexes for locale-based lookups
   - Pricing indexes for currency and zone queries
   - Performance monitoring with query logging

2. **Query Optimization:**
   - Eager loading for N+1 prevention
   - Query scopes for reusable query logic
   - Database connection optimization
   - Read/write splitting ready

### Application Optimizations
1. **Caching Strategy:**
   - Redis-based caching with intelligent invalidation
   - Tagged cache for selective clearing
   - Translation caching for performance
   - Discount eligibility caching

2. **Asset Optimization:**
   - Vite-based asset compilation
   - Image optimization with WebP support
   - Font optimization with @fontsource
   - CSS purging for production

### Background Processing
1. **Queue Strategy:**
   - Database queues for development
   - Redis queues for production
   - Horizon monitoring and scaling
   - Failed job recovery system

2. **Async Operations:**
   - Media conversion in background
   - Email sending via queues
   - Import operations with chunking
   - Document generation asynchronously

## Security Implementation

### Authentication Security
1. **Multi-Factor Authentication:**
   - TOTP-based 2FA implementation
   - Recovery codes for account recovery
   - Middleware enforcement on admin routes
   - Session security hardening

2. **Password Security:**
   - Bcrypt hashing with configurable rounds
   - Password complexity requirements
   - Account lockout protection
   - Secure password reset flow

### Authorization Security
1. **Role-Based Access Control:**
   - Spatie Permission integration
   - Granular permissions (48 across modules)
   - Resource-level authorization
   - Method-level permission checking

2. **API Security:**
   - CSRF protection for all forms
   - Rate limiting on API endpoints
   - Input validation and sanitization
   - SQL injection prevention via ORM

### Data Security
1. **Data Protection:**
   - GDPR-compliant data handling
   - Personal data encryption at rest
   - Secure data transmission (HTTPS)
   - Data retention policies

2. **File Security:**
   - MIME type validation
   - File size restrictions
   - Secure file storage paths
   - Virus scanning integration ready

## Monitoring & Observability

### Application Monitoring
**Laravel Telescope (Development)**
- **Features:** Request monitoring, query analysis, exception tracking
- **Usage:** Development debugging and performance analysis
- **Integration:** Native Laravel integration

**Laravel Horizon (Production)**
- **Features:** Queue monitoring, worker management, metrics
- **Usage:** Production queue monitoring and scaling
- **Dashboard:** Web-based monitoring interface

### Performance Monitoring
1. **Database Monitoring:**
   - Query performance tracking
   - Slow query identification
   - Connection pool monitoring
   - Index usage analysis

2. **Application Monitoring:**
   - Response time tracking
   - Memory usage monitoring
   - Error rate tracking
   - User experience metrics

## Deployment Architecture

### Environment Configuration
**Development:**
- Local Laravel Herd environment
- SQLite database for simplicity
- File-based sessions and cache
- Telescope enabled for debugging

**Production:**
- MySQL/MariaDB database
- Redis for cache and sessions
- Queue workers with Horizon
- Error monitoring and logging

### Scaling Strategy
1. **Horizontal Scaling:**
   - Stateless application design
   - Load balancer ready
   - Shared session storage (Redis)
   - CDN integration for media

2. **Vertical Scaling:**
   - Optimized for single-server deployment
   - Resource usage monitoring
   - Performance tuning capabilities
   - Memory and CPU optimization

## Technology Decisions Rationale

### Framework Selection
**Laravel 12:** Chosen for latest features, performance improvements, and long-term support
**Filament v4:** Selected for modern admin interface with rapid development capabilities
**Livewire 3.x:** Provides full-stack reactivity without JavaScript complexity

### Database Selection
**MySQL/MariaDB:** Enterprise-grade database with JSON support for flexible data structures
**Redis:** High-performance caching and session storage for scalability

### Frontend Approach
**Server-Side Rendering:** Better SEO and initial page load performance
**Progressive Enhancement:** Works without JavaScript, enhanced with interactivity
**Component-Based:** Reusable components for consistent UI

### Security Approach
**Defense in Depth:** Multiple security layers for comprehensive protection
**Industry Standards:** Following OWASP guidelines and Laravel security best practices
**Compliance Ready:** GDPR and data protection regulation compliance
