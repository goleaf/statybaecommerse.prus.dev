# LazyCollections takeUntilTimeout Implementation

## Overview

This document describes the implementation of Laravel's `LazyCollection::takeUntilTimeout()` method throughout the project to prevent long-running operations and improve system reliability.

## What is takeUntilTimeout?

The `takeUntilTimeout` method creates a new lazy collection that enumerates values only until a specified time. After reaching this time, the collection stops processing, making it useful for processing tasks within a time limit.

## Implementation Locations

### 1. CheckLowStockJob
**File:** `app/Jobs/CheckLowStockJob.php`
**Timeout:** 5 minutes
**Purpose:** Prevents low stock checks from running indefinitely when processing large product catalogs.

```php
$timeout = now()->addMinutes(5);
$lowStockProducts = Product::where('is_visible', true)
    ->where('manage_stock', true)
    ->where('stock_quantity', '<=', DB::raw('low_stock_threshold'))
    ->cursor()
    ->takeUntilTimeout($timeout);
```

### 2. ImportProductsChunk
**File:** `app/Jobs/ImportProductsChunk.php`
**Timeout:** 10 minutes
**Purpose:** Ensures import operations complete within reasonable time limits.

```php
$timeout = now()->addMinutes(10);
LazyCollection::make($this->rows)
    ->takeUntilTimeout($timeout)
    ->each(function ($row) {
        // Process import row
    });
```

### 3. RecommendationService
**File:** `app/Services/RecommendationService.php`
**Timeout:** 30 seconds
**Purpose:** Prevents recommendation generation from taking too long and affecting user experience.

```php
$timeout = now()->addSeconds(30);
LazyCollection::make($configs)
    ->takeUntilTimeout($timeout)
    ->each(function ($config) {
        // Generate recommendations
    });
```

### 4. SearchService
**File:** `app/Services/SearchService.php`
**Timeout:** 10 seconds
**Purpose:** Ensures search operations return results quickly for good user experience.

```php
$timeout = now()->addSeconds(10);
Product::query()
    ->cursor()
    ->takeUntilTimeout($timeout)
    ->take($limit)
    ->map(function (Product $product) {
        // Process search results
    });
```

### 5. UltraFastProductImageSeeder
**File:** `database/seeders/UltraFastProductImageSeeder.php`
**Timeout:** 30 minutes
**Purpose:** Prevents image generation from running indefinitely during seeding.

```php
$timeout = now()->addMinutes(30);
Product::query()
    ->cursor()
    ->takeUntilTimeout($timeout)
    ->chunk(self::BATCH_SIZE)
    ->each(function (Collection $products) {
        // Generate images
    });
```

## TimeoutService Utility

**File:** `app/Services/TimeoutService.php`

A utility service that provides convenient methods for different timeout scenarios:

### Methods

- `forScheduledTask()` - For scheduled tasks with 14-minute window
- `forImport()` - For import operations (default 10 minutes)
- `forSearch()` - For search operations (default 10 seconds)
- `forRecommendations()` - For recommendation generation (default 30 seconds)
- `forBackgroundJob()` - For background jobs (default 5 minutes)
- `withTimeout()` - Custom timeout duration
- `isTimeoutReached()` - Check if timeout has been reached
- `getRemainingTime()` - Get remaining time until timeout
- `logTimeoutInfo()` - Log timeout information

### Usage Examples

```php
// For scheduled tasks
$collection = TimeoutService::forScheduledTask($lazyCollection);

// For imports
$collection = TimeoutService::forImport($lazyCollection, 15); // 15 minutes

// For search
$collection = TimeoutService::forSearch($lazyCollection, 5); // 5 seconds

// Custom timeout
$collection = TimeoutService::withTimeout($lazyCollection, 60); // 60 seconds
```

## Demonstration Command

**File:** `app/Console/Commands/DemonstrateTimeoutCommand.php`

A console command to demonstrate the takeUntilTimeout functionality:

```bash
# Demonstrate with numbers (default)
php artisan demo:timeout --timeout=5 --items=1000

# Demonstrate with products
php artisan demo:timeout --operation=products --timeout=10

# Demonstrate with users
php artisan demo:timeout --operation=users --timeout=15
```

## Testing

**File:** `tests/Feature/LazyCollectionTimeoutTest.php`

Comprehensive tests covering:
- Basic timeout functionality
- TimeoutService utility methods
- Database cursor timeout behavior
- Timeout logging functionality

Run tests with:
```bash
php artisan test --filter=LazyCollectionTimeoutTest
```

## Benefits

1. **Prevents Timeouts:** Operations won't run indefinitely
2. **Improves Reliability:** System remains responsive
3. **Better Resource Management:** Prevents memory exhaustion
4. **Enhanced User Experience:** Faster response times
5. **Scheduled Task Safety:** Ensures tasks complete within their time window

## Best Practices

1. **Choose Appropriate Timeouts:**
   - Search operations: 5-10 seconds
   - Background jobs: 5-15 minutes
   - Import operations: 10-30 minutes
   - Scheduled tasks: Leave buffer time

2. **Monitor Performance:**
   - Log timeout information
   - Track processing counts
   - Monitor completion rates

3. **Handle Partial Results:**
   - Process what you can within the timeout
   - Log incomplete operations
   - Consider resuming from where you left off

4. **Use LazyCollections:**
   - Always use `cursor()` for database queries
   - Use `LazyCollection::make()` for arrays
   - Chain `takeUntilTimeout()` early in the pipeline

## Configuration

Timeout values can be configured via environment variables or constants:

```php
// In config files
'timeouts' => [
    'search' => env('SEARCH_TIMEOUT', 10),
    'import' => env('IMPORT_TIMEOUT', 600),
    'recommendations' => env('RECOMMENDATIONS_TIMEOUT', 30),
    'background_jobs' => env('BACKGROUND_JOB_TIMEOUT', 300),
],
```

## Monitoring

The implementation includes logging to help monitor timeout behavior:

```php
TimeoutService::logTimeoutInfo(
    'operation_name',
    $processedCount,
    $timeout,
    $totalCount
);
```

This logs:
- Operation name
- Number of items processed
- Remaining time
- Whether timeout was reached
- Completion percentage

## Future Enhancements

1. **Dynamic Timeouts:** Adjust timeouts based on system load
2. **Resume Capability:** Resume operations from where they left off
3. **Progress Tracking:** Real-time progress updates
4. **Timeout Metrics:** Collect timeout statistics for optimization
5. **Circuit Breaker:** Stop operations if timeouts occur frequently
