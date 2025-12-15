# Performance Update Design Document

## Overview

This design document outlines a comprehensive performance optimization initiative for the egistatyba Laravel 12 + Livewire 3 storefront application. The primary goal is to improve perceived and measured performance (TTFB, query count, memory usage) while maintaining existing user-visible behavior, SEO semantics, and business rules.

The optimization focuses on eliminating performance bottlenecks identified in the current system, including application boot failures, redundant locale resolution, N+1 query patterns, inefficient search implementations, and suboptimal caching strategies.

## Architecture

### Current Architecture Issues

The current storefront architecture suffers from several performance-critical issues:

1. **Boot Reliability**: Fatal errors prevent baseline measurement due to incomplete interface implementations
2. **Locale Resolution Duplication**: Multiple Livewire components independently resolve locale, causing redundant work
3. **Query Inefficiency**: N+1 patterns in facet counting and eager loading of unnecessary data
4. **Search Fragmentation**: Direct database queries bypass existing optimized search services
5. **Cache Strategy Gaps**: Inconsistent caching and lack of tag-aware invalidation

### Target Architecture

The optimized architecture will implement:

1. **Centralized Locale Management**: Single-point locale resolution through middleware
2. **Efficient Query Patterns**: Aggregated queries for facets and selective field loading
3. **Unified Search Layer**: All search operations routed through `SearchService`
4. **Tag-Aware Caching**: Consistent cache tagging for targeted invalidation
5. **Performance Monitoring**: Built-in performance measurement and regression detection

## Components and Interfaces

### Core Components

#### 1. Application Bootstrap Layer
- **Purpose**: Ensure reliable application boot and interface compliance
- **Key Classes**: `App\Models\Product`, `App\Contracts\TranslatableRecord`
- **Interfaces**: `TranslatableRecord::translations(): HasMany`

#### 2. Locale Management System
- **Purpose**: Centralized locale resolution and persistence
- **Key Classes**: `App\Http\Middleware\SetLocale`, Livewire components
- **Interfaces**: Locale resolution service interface

#### 3. Query Optimization Layer
- **Purpose**: Eliminate N+1 patterns and optimize data loading
- **Key Classes**: `app/Livewire/Pages/Category/Index.php`, product list components
- **Interfaces**: Facet counting service, selective field loading

#### 4. Search Integration Layer
- **Purpose**: Unified search through `SearchService`
- **Key Classes**: `app/Livewire/Pages/Search.php`, `App\Services\SearchService`
- **Interfaces**: Search service interface, Scout integration

#### 5. Caching Strategy Layer
- **Purpose**: Tag-aware caching with targeted invalidation
- **Key Classes**: Cache tag management, invalidation handlers
- **Interfaces**: Cache tagging interface, invalidation service

#### 6. Performance Monitoring System
- **Purpose**: Baseline measurement and regression detection
- **Key Classes**: Performance test suite, CI integration
- **Interfaces**: Performance measurement interface, assertion framework

## Data Models

### Performance Metrics Model
```php
class PerformanceMetrics
{
    public string $page_route;
    public float $ttfb_p50;
    public float $ttfb_p95;
    public int $query_count;
    public int $peak_memory_mb;
    public Carbon $measured_at;
}
```

### Cache Tag Structure
```php
class CacheTags
{
    const PRODUCTS = 'products';
    const BRANDS = 'brands';
    const CATEGORIES = 'categories';
    const COLLECTIONS = 'collections';
    const LOCALE = 'locale';
    const HOME = 'home';
    const NAVIGATION = 'navigation';
}
```

### Query Budget Configuration
```php
class QueryBudgets
{
    const HOME_PAGE = 5;
    const CATEGORY_INDEX = 8;
    const CATEGORY_SHOW = 12;
    const PRODUCT_SHOW = 6;
    const SEARCH_RESULTS = 10;
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Application Boot Reliability
*For any* storefront request to locale routes, the application should boot successfully and return a valid HTTP response without fatal errors.
**Validates: Requirements 1.1, 1.2**

### Property 2: Interface Implementation Completeness
*For any* model implementing `TranslatableRecord`, calling the `translations()` method should return a valid `HasMany` relationship without throwing exceptions.
**Validates: Requirements 1.2**

### Property 3: Centralized Locale Resolution
*For any* web request, locale resolution should occur exactly once in the middleware layer, and Livewire components should not perform additional locale resolution.
**Validates: Requirements 3.1**

### Property 4: Locale Persistence Optimization
*For any* request where the resolved locale matches the current locale, no session writes or cookie updates should be triggered.
**Validates: Requirements 3.2**

### Property 5: Facet Query Efficiency
*For any* category page rendering, facet counts should be computed using aggregated queries, and the total number of queries should not exceed the defined budget.
**Validates: Requirements 4.1, 4.2**

### Property 6: Cache Hit Optimization
*For any* facet data request when caches are warm, zero database queries should be executed.
**Validates: Requirements 4.3**

### Property 7: Search Service Integration
*For any* search request, the execution should be routed through `SearchService` and not perform direct database queries.
**Validates: Requirements 5.1**

### Property 8: Search Backend Selection
*For any* search configuration, when Scout is enabled, Scout should be used; when disabled, an optimized database fallback should be used.
**Validates: Requirements 5.2**

### Property 9: Search Result Caching
*For any* repeated search query within TTL, results should be served from cache with appropriate locale and catalog tags.
**Validates: Requirements 5.3**

### Property 10: Selective Field Loading
*For any* product list query, only fields required by the view/DTO should be selected, avoiding unnecessary large text blobs.
**Validates: Requirements 6.1**

### Property 11: Eager Loading Completeness
*For any* product list iteration, no lazy-loading queries should be triggered for relations (brand, media, categories, translations).
**Validates: Requirements 6.2**

### Property 12: Cache Serialization Efficiency
*For any* cached list payload, the cached values should be serializable DTOs/arrays rather than full Eloquent models where practical.
**Validates: Requirements 6.3**

### Property 13: Tag-Aware Cache Implementation
*For any* storefront cache entry, it should include appropriate tags from the `CacheTags` constants for targeted invalidation.
**Validates: Requirements 7.1**

### Property 14: Cache Invalidation Correctness
*For any* catalog data change (product/brand/category/collection update), relevant cache tags should be invalidated within the expected window.
**Validates: Requirements 7.2**

### Property 15: Database Index Optimization
*For any* storefront query pattern, appropriate database indexes should exist and be utilized for filtering and sorting operations.
**Validates: Requirements 8.1**

### Property 16: Performance Test Coverage
*For any* optimized page or component, performance-oriented tests should exist that assert query count and cache usage budgets.
**Validates: Requirements 10.1**

### Property 17: Regression Detection
*For any* performance regression that exceeds agreed budgets, CI should fail with a clear message identifying the offending component.
**Validates: Requirements 10.2**

## Error Handling

### Boot Failure Handling
- Implement comprehensive exception handling during application bootstrap
- Ensure single, actionable error messages rather than cascading failures
- Log boot failures with sufficient context for debugging

### Performance Regression Handling
- Implement automated performance budget enforcement in CI
- Provide clear error messages when budgets are exceeded
- Include component-specific guidance for resolution

### Cache Failure Handling
- Implement graceful degradation when cache services are unavailable
- Ensure application continues to function without caching
- Log cache failures for monitoring and alerting

## Testing Strategy

### Unit Testing Approach
The testing strategy employs both unit tests and property-based tests to ensure comprehensive coverage:

**Unit Tests** will cover:
- Specific interface implementations (e.g., `TranslatableRecord::translations()`)
- Individual component optimizations
- Cache tag assignment and invalidation logic
- Search service integration points

**Property-Based Tests** will verify:
- Query count budgets across different data sets
- Cache behavior under various conditions
- Locale resolution consistency
- Performance characteristics across page types

### Property-Based Testing Framework
We will use **Pest** with **Laravel's testing utilities** for property-based testing, configured to run a minimum of 100 iterations per property test.

Each property-based test will be tagged with comments explicitly referencing the correctness property from this design document using the format: **Feature: performance-update, Property X: [property_text]**

### Performance Testing Requirements
- Implement query count assertions for each optimized page/component
- Create baseline performance measurement utilities
- Establish CI integration for automated performance regression detection
- Include cache hit/miss ratio testing
- Verify memory usage optimization

### Test Environment Configuration
- Configure separate test database for performance testing
- Implement cache driver switching for testing different scenarios
- Create test data factories that simulate realistic catalog sizes
- Establish performance test isolation to prevent interference

## Implementation Guidelines

### Phase 1: Foundation (Boot Reliability)
1. Fix `TranslatableRecord` interface implementations
2. Implement comprehensive boot error handling
3. Establish performance measurement baseline

### Phase 2: Locale Optimization
1. Centralize locale resolution in middleware
2. Eliminate redundant locale resolution in Livewire components
3. Optimize locale persistence logic

### Phase 3: Query Optimization
1. Implement aggregated facet counting
2. Optimize product list queries with selective field loading
3. Eliminate N+1 patterns through proper eager loading

### Phase 4: Search Integration
1. Route all search operations through `SearchService`
2. Implement Scout/database fallback logic
3. Add search result caching with proper tagging

### Phase 5: Caching Strategy
1. Implement tag-aware caching throughout storefront
2. Create cache invalidation handlers for catalog updates
3. Optimize cache serialization formats

### Phase 6: Performance Monitoring
1. Implement performance test suite
2. Integrate performance budgets into CI
3. Create performance regression detection

### Database Migration Strategy
- Create migrations for new performance-related indexes
- Remove redundant indexes safely with rollback plans
- Ensure migrations are compatible with production database engine
- Define acceptable table lock durations for each database engine

### Production Deployment Considerations
- Implement framework optimization commands in deployment pipeline
- Configure Redis for cache and session storage where available
- Ensure queue configuration for long-running tasks
- Establish monitoring for performance metrics and cache hit rates