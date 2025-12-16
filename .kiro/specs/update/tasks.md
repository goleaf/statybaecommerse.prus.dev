# Implementation Plan

## Phase 1: Foundation (Boot Reliability)

- [x] 1 Fix TranslatableRecord interface implementation in Product model


  - Implement missing `translations(): HasMany` method in `App\Models\Product`
  - Verify interface compliance for `Brand`, `Collection`, `ProductVariant` models
  - Add unit tests for interface method implementations
  - _Requirements: 1.1, 1.2_

- [x] 1.2 Implement comprehensive boot error handling
  - ✅ Enhanced exception handler with boot error detection
  - ✅ Actionable error messages for common boot failures
  - ✅ Structured logging with debugging context
  - ✅ Configuration-based error pattern detection
  - _Requirements: 1.3_

- [x] 1.3 Establish performance measurement baseline system
  - Create `PerformanceMetrics` model and migration
  - Implement performance measurement middleware for key storefront pages
  - Add baseline measurement for home, categories, products, and search pages
  - Create performance measurement utilities and helpers
  - _Requirements: 2.1, 2.2_

- [x] 1.4 Write property test for application boot reliability
  - **Property 1: Application Boot Reliability**
  - **Validates: Requirements 1.1, 1.2**

- [x] 1.5 Write property test for interface implementation completeness

  - **Property 2: Interface Implementation Completeness**
  - **Validates: Requirements 1.2**

## Phase 2: Locale Optimization

- [x] 2 Centralize locale resolution in middleware



  - Enhance `App\Http\Middleware\SetLocale` to handle all locale resolution
  - Create locale service for consistent locale management
  - Remove duplicate locale resolution from Livewire components
  - _Requirements: 3.1_

- [x] 2.1 Optimize locale persistence logic
  - Implement logic to skip session writes when locale unchanged
  - Prevent unnecessary cookie updates for same locale
  - Add locale consistency preservation for Livewire AJAX requests
  - _Requirements: 3.2, 3.3_

- [x] 2.2 Write property test for centralized locale resolution
  - **Property 3: Centralized Locale Resolution**
  - **Validates: Requirements 3.1**

- [x] 2.3 Write property test for locale persistence optimization
  - **Property 4: Locale Persistence Optimization**
  - **Validates: Requirements 3.2**

- [x] 2.4 Implement aggregated facet counting


  - ✅ Refactored `app/Livewire/Pages/Category/Index.php` to use aggregated queries
  - ✅ Replaced N+1 facet counting with single grouped queries
  - ✅ Implemented `FacetCountingService` with query budget enforcement
  - ✅ Added SCOPE_COLUMN_HINTS to Brand and Collection models to prevent schema introspection
  - _Requirements: 4.1, 4.2_

- [x] 2.5 Optimize product list queries with selective field loading





  - Modify product list queries to select only required fields for DTOs
  - Remove unnecessary eager loading of large text blobs
  - Update `ProductListItemData` to specify required fields explicitly
  - _Requirements: 6.1_


- [x] 2.6 Eliminate N+1 patterns through proper eager loading



  - ✅ Enhanced `withListRelations()` scope to include translations for products, brands, and categories
  - ✅ Added `trans()` method to Product, Brand, and Category models to use eager-loaded translations
  - ✅ Removed redundant translation loading from ComponentShowcase
  - ✅ Ensured no lazy-loading queries during product list iteration in all storefront components
  - _Requirements: 6.2_


- [x] 2.7 Write property test for facet query efficiency




  - ✅ **Property 5: Facet Query Efficiency**
  - ✅ **Validates: Requirements 4.1, 4.2**


- [x] 2.8 Write property test for selective field loading



  - **Property 10: Selective Field Loading**
  - **Validates: Requirements 6.1**



- [x] 2.10 Write property test for eager loading completeness


  - **Property 11: Eager Loading Completeness**
  - **Validates: Requirements 6.2**


- [x] 2.11 Route all search operations through SearchService
  - Refactor `app/Livewire/Pages/Search.php` to use `App\Services\SearchService`
  - Remove direct database queries from search components
  - Ensure all search operations go through unified search layer
  - _Requirements: 5.1_


- [x] 2.12 Implement Scout/database fallback logic
  - Add configuration-based search backend selection
  - Implement optimized database fallback when Scout disabled
  - Ensure search performance optimization for production database engine
  - _Requirements: 5.2_

- [x] 2.13 Add search result caching with proper tagging
  - Implement search result caching in `SearchService`
  - Add cache tagging by locale and catalog tags
  - Configure appropriate TTL for search result cache
  - _Requirements: 5.3_


- [x] 2.14 Write property test for search service integration
  - **Property 7: Search Service Integration**
  - **Validates: Requirements 5.1**


- [x] 2.15 Write property test for search backend selection

  - **Property 8: Search Backend Selection**
  - **Validates: Requirements 5.2**

- [x] 2.16 Write property test for search result caching




  - **Property 9: Search Result Caching**
  - **Validates: Requirements 5.3**

## Phase 5: Caching Strategy Implementation


- [x] 2.17 Implement tag-aware caching system

  - Create `CacheTags` class with consistent tag constants
  - Implement tag-aware caching throughout storefront components
  - Add cache tagging for products, brands, categories, collections, locale, home, navigation
  - _Requirements: 7.1_

- [x] 2.18 Create cache invalidation handlers
  - ✅ Model observers already implemented for catalog data changes
  - ✅ Cache tag invalidation working when products/brands/categories/collections update
  - ✅ Cache updates reflect within expected window via CacheInvalidationService
  - _Requirements: 7.2_

- [x] 2.19 Optimize cache serialization formats
  - ✅ Created SerializationOptimizer for converting Eloquent models to DTOs/arrays
  - ✅ Enhanced CacheService with optimized caching methods
  - ✅ Implemented cache format optimization for product lists and collections
  - _Requirements: 6.3_

- [x] 2.20 Implement cache hit optimization
  - ✅ Created CacheWarmer for ensuring zero queries when caches are warm
  - ✅ Added cache warming strategies for critical storefront data
  - ✅ Implemented redundant cache write prevention
  - _Requirements: 4.3, 7.3_

- [x] 2.21 Write property test for cache hit optimization
  - ✅ **Property 6: Cache Hit Optimization**
  - ✅ **Validates: Requirements 4.3**

- [x] 2.22 Write property test for cache serialization efficiency
  - ✅ **Property 12: Cache Serialization Efficiency**
  - ✅ **Validates: Requirements 6.3**

- [x] 2.23 Write property test for tag-aware cache implementation
  - ✅ **Property 13: Tag-Aware Cache Implementation**
  - ✅ **Validates: Requirements 7.1**

- [x] 2.24 Write property test for cache invalidation correctness
  - ✅ **Property 14: Cache Invalidation Correctness**
  - ✅ **Validates: Requirements 7.2**

## Phase 6: Database Optimization

- [x] 6 Analyze and optimize database indexes
  - ✅ Reviewed storefront query patterns and created comprehensive index migration
  - ✅ Created indexes for filtering/sorting (visibility + published date, brand/category pivots, translation lookups)
  - ✅ Included rollback strategy to remove indexes safely
  - _Requirements: 8.1, 8.2_

- [x] 6.2 Create database migration strategy
  - ✅ Implemented migration with new performance indexes
  - ✅ Created rollback plans for all schema changes
  - ✅ Migration designed to avoid long table locks (uses individual index creation)
  - _Requirements: 8.3_

- [x] 6.3 Write property test for database index optimization
  - ✅ **Property 15: Database Index Optimization**
  - ✅ **Validates: Requirements 8.1**

## Phase 7: Production Configuration

- [x] 7 Implement framework optimization in deployment
  - ✅ Created OptimizeForProduction command with all framework optimizations
  - ✅ Configured `config:cache`, `route:cache`, `view:cache`, `event:cache` commands
  - ✅ Added verification and error handling for production deployment
  - _Requirements: 9.1_

- [x] 7.2 Configure Redis for cache and session storage
  - ✅ Added Redis configuration in performance config
  - ✅ Implemented fallback configuration when Redis not available
  - ✅ Configured session write contention avoidance settings
  - _Requirements: 9.2_

- [x] 7.3 Ensure queue configuration for long-running tasks
  - ✅ Verified existing queue configuration in AppServiceProvider
  - ✅ Long-running work already properly queued (media, emails, analytics)
  - ✅ Queue monitoring already configured via existing job classes
  - _Requirements: 9.3_

## Phase 8: Performance Monitoring and Testing

- [x] 8 Implement performance test suite
  - ✅ Created comprehensive performance test suite with budget enforcement
  - ✅ Added query count and cache usage assertions for all key pages
  - ✅ Implemented automated performance budget enforcement with configurable limits
  - _Requirements: 10.1_

- [x] 8.1 Integrate performance budgets into CI
  - ✅ Created performance configuration with configurable budgets
  - ✅ Implemented automated budget enforcement in test suite
  - ✅ Added clear error messages for budget violations
  - _Requirements: 10.2_

- [x] 8.2 Create performance regression detection system
  - ✅ Implemented comprehensive performance test suite
  - ✅ Added performance monitoring via PerformanceBudgetTest
  - ✅ Created configurable performance budgets system
  - _Requirements: 10.2_

- [x] 8.3 Write property test for performance test coverage
  - ✅ **Property 16: Performance Test Coverage**
  - ✅ **Validates: Requirements 10.1**

- [x] 8.4 Write property test for regression detection
  - ✅ **Property 17: Regression Detection**
  - ✅ **Validates: Requirements 10.2**

## Final Verification

- [x] 9 Comprehensive performance validation





  - Run complete performance test suite
  - Verify all query budgets are met
  - Confirm cache hit ratios meet targets
  - Validate TTFB improvements on key pages

- [x] 9.1 End-to-end functionality verification


  - Ensure all user-visible behavior remains unchanged
  - Verify SEO semantics are preserved
  - Confirm business rules continue to function correctly
  - Test locale switching and search functionality

- [x] 9.2 Production readiness checklist


  - Verify framework optimizations are configured
  - Confirm Redis configuration is production-ready
  - Ensure queue processing is properly configured
  - Validate monitoring and alerting systems