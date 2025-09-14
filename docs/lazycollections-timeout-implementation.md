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
    ->map(function (Product $product) use ($query) {
        // Process search results
    });
```


## ReportGenerationService

**File:** `app/Services/ReportGenerationService.php`

Comprehensive report generation with timeout protection for different report types:

### Report Types

- **Sales Reports** (5 minutes timeout)
- **Product Analytics** (10 minutes timeout)
- **User Activity** (8 minutes timeout)
- **System Reports** (15 minutes timeout)

### Usage

```php
$reportService = new ReportGenerationService();

// Generate sales report
$salesReport = $reportService->generateSalesReport();

// Generate product analytics
$productReport = $reportService->generateProductAnalyticsReport();

// Generate user activity report
$userReport = $reportService->generateUserActivityReport();

// Generate system report
$systemReport = $reportService->generateSystemReport();
```

## Scheduled Tasks with Timeout Protection

### Activity Log Cleanup
**File:** `routes/console.php`
**Timeout:** 5 minutes
**Schedule:** Daily

```php
Schedule::call(function () {
    $timeout = now()->addMinutes(5); // 5 minute timeout for log cleanup
    
    \Spatie\Activitylog\Models\Activity::where('created_at', '<', now()->subDays(90))
        ->cursor()
        ->takeUntilTimeout($timeout)
        ->each(function ($activity) {
            $activity->delete();
        });
})->daily();
```

### Export Rotation
**File:** `app/Providers/AppServiceProvider.php`
**Timeout:** 3 minutes
**Schedule:** Daily at 02:40

```php
$schedule->call(function (): void {
    // Rotate exports older than 7 days with timeout protection
    $timeout = now()->addMinutes(3); // 3 minute timeout for export rotation
    $disk = \Storage::disk('public');
    $dir = 'exports';
    if ($disk->exists($dir)) {
        $files = collect($disk->files($dir))
            ->takeUntilTimeout($timeout);
        
        foreach ($files as $path) {
            // Process file rotation
        }
    }
})->dailyAt('02:40')->name('exports:rotate')->withoutOverlapping();
```

## Data Export Operations

### StockController Export
**File:** `app/Http/Controllers/StockController.php`
**Timeout:** 15 minutes
**Purpose:** Prevents large stock data exports from running indefinitely.

```php
$timeout = now()->addMinutes(15); // 15 minute timeout for stock exports

$query->cursor()
    ->takeUntilTimeout($timeout)
    ->each(function ($item) use ($handle) {
        // Process export data
    });
```

### CampaignClickController Export
**File:** `app/Http/Controllers/Api/CampaignClickController.php`
**Timeout:** 10 minutes
**Purpose:** Prevents campaign click exports from running too long.

```php
$timeout = now()->addMinutes(10); // 10 minute timeout for campaign click exports

LazyCollection::make($clicks)
    ->takeUntilTimeout($timeout)
    ->each(function ($click) use ($handle) {
        // Process click data
    });
```

## Notification Processing

### LiveNotificationService
**File:** `app/Services/LiveNotificationService.php`
**Timeout:** 30 seconds for admin notifications, 2 minutes for bulk user notifications

```php
// Admin notifications
$timeout = now()->addSeconds(30); // 30 second timeout for admin notifications

User::whereHas('roles', function ($query) {
    $query->whereIn('name', ['administrator', 'manager']);
})
->cursor()
->takeUntilTimeout($timeout)
->each(function ($user) use ($title, $message, $type) {
    $this->sendToUser($user, $title, $message, $type);
});

// Bulk user notifications
$timeout = now()->addMinutes(2); // 2 minute timeout for bulk user notifications

LazyCollection::make($users)
    ->takeUntilTimeout($timeout)
    ->each(function ($user) use ($title, $message, $type) {
        $this->sendToUser($user, $title, $message, $type);
    });
```

## Console Commands

### DemonstrateTimeoutCommand
**File:** `app/Console/Commands/DemonstrateTimeoutCommand.php`
**Purpose:** Demonstrates takeUntilTimeout functionality with different scenarios.

### GenerateReportsCommand
**File:** `app/Console/Commands/GenerateReportsCommand.php`
**Purpose:** Generates various reports with timeout protection.

## Testing

### TimeoutImplementationTest
**File:** `tests/Feature/TimeoutImplementationTest.php`

Comprehensive test suite covering all timeout implementations:

- TimeoutService utility methods
- ReportGenerationService methods
- LazyCollection timeout scenarios
- Console command execution
- Timeout validation and logging

## Benefits

The `takeUntilTimeout` implementation provides several key benefits:

1. **Prevents Long-Running Operations**: All data processing operations now have timeout limits
2. **Memory Efficiency**: Uses LazyCollections to process data one item at a time
3. **System Reliability**: Prevents jobs from running indefinitely and consuming resources
4. **User Experience**: Ensures search and recommendation operations return results quickly
5. **Resource Management**: Prevents database timeouts and memory exhaustion
6. **Monitoring**: Comprehensive logging of timeout information and processing statistics

## Usage Guidelines

1. **Choose Appropriate Timeouts**: Consider the operation type and expected data size
2. **Monitor Timeout Logs**: Check logs for timeout events and adjust limits if needed
3. **Test with Large Datasets**: Ensure timeouts work correctly with production data volumes
4. **Use TimeoutService**: Leverage the utility service for consistent timeout handling
5. **Handle Timeout Gracefully**: Implement proper error handling for timeout scenarios

- `forRecommendations()` - For recommendation generation (default 30 seconds)
- `forBackgroundJob()` - For background jobs (default 5 minutes)
- `withTimeout()` - Generic timeout wrapper
- `isTimeoutReached()` - Check if timeout has been reached
- `getRemainingTime()` - Get remaining time until timeout
- `logTimeoutInfo()` - Log timeout information

### Usage Examples

```php
// Database operations
TimeoutService::withTimeout($collection, 30);

// Scheduled tasks
TimeoutService::forScheduledTask($collection, 60);

// Import operations
TimeoutService::forImport($collection, 10);

// Search operations
TimeoutService::forSearch($collection, 10);

// Recommendation generation
TimeoutService::forRecommendations($collection, 30);

// Background jobs
TimeoutService::forBackgroundJob($collection, 5);
```

## ReportGenerationService

**File:** `app/Services/ReportGenerationService.php`

Comprehensive report generation with timeout protection for different report types:

### Report Types

- **Sales Reports** (5 minutes timeout)
- **Product Analytics** (10 minutes timeout)
- **User Activity** (8 minutes timeout)
- **System Reports** (15 minutes timeout)

### Usage

```php
$reportService = new ReportGenerationService();

// Generate sales report
$salesReport = $reportService->generateSalesReport();

// Generate product analytics
$productReport = $reportService->generateProductAnalyticsReport();

// Generate user activity report
$userReport = $reportService->generateUserActivityReport();

// Generate system report
$systemReport = $reportService->generateSystemReport();
```

## Scheduled Tasks with Timeout Protection

### Activity Log Cleanup
**File:** `routes/console.php`
**Timeout:** 5 minutes
**Schedule:** Daily

```php
Schedule::call(function () {
    $timeout = now()->addMinutes(5);
    
    \Spatie\Activitylog\Models\Activity::where('created_at', '<', now()->subDays(90))
        ->cursor()
        ->takeUntilTimeout($timeout)
        ->each(function ($activity) {
            $activity->delete();
        });
})->daily();
```

### Export Rotation
**File:** `app/Providers/AppServiceProvider.php`
**Timeout:** 3 minutes
**Schedule:** Daily at 02:40

```php
$schedule->call(function (): void {
    $timeout = now()->addMinutes(3);
    $disk = \Storage::disk('public');
    $dir = 'exports';
    if ($disk->exists($dir)) {
        $files = collect($disk->files($dir))
            ->takeUntilTimeout($timeout);
        
        foreach ($files as $path) {
            // Process file rotation
        }
    }
})->dailyAt('02:40');
```

## Data Export Operations

### Stock Export
**File:** `app/Http/Controllers/StockController.php`
**Timeout:** 15 minutes

```php
$timeout = now()->addMinutes(15);
$query->cursor()
    ->takeUntilTimeout($timeout)
    ->each(function ($item) use ($handle) {
        // Export stock data
    });
```

### Campaign Click Export
**File:** `app/Http/Controllers/Api/CampaignClickController.php`
**Timeout:** 10 minutes

```php
$timeout = now()->addMinutes(10);
LazyCollection::make($clicks)
    ->takeUntilTimeout($timeout)
    ->each(function ($click) use ($handle) {
        // Export click data
    });
```

## Notification Processing

### LiveNotificationService
**File:** `app/Services/LiveNotificationService.php`

- **Admin Notifications:** 30 seconds timeout
- **Bulk User Notifications:** 2 minutes timeout

```php
// Admin notifications
$timeout = now()->addSeconds(30);
User::whereHas('roles', function ($query) {
    $query->whereIn('name', ['administrator', 'manager']);
})
->cursor()
->takeUntilTimeout($timeout)
->each(function ($user) use ($title, $message, $type) {
    $this->sendToUser($user, $title, $message, $type);
});

// Bulk user notifications
$timeout = now()->addMinutes(2);
LazyCollection::make($users)
    ->takeUntilTimeout($timeout)
    ->each(function ($user) use ($title, $message, $type) {
        $this->sendToUser($user, $title, $message, $type);
    });
```

## Console Commands

### GenerateReportsCommand
**File:** `app/Console/Commands/GenerateReportsCommand.php`

Generates various reports with timeout protection:

```bash
# Generate all reports
php artisan reports:generate --type=all

# Generate specific report type
php artisan reports:generate --type=sales --format=json

# Generate with custom timeout
php artisan reports:generate --type=system --timeout=20
```

### DemonstrateTimeoutCommand
**File:** `app/Console/Commands/DemonstrateTimeoutCommand.php`

Demonstrates timeout functionality:

```bash
# Demonstrate with default settings
php artisan demo:timeout

# Custom timeout and items
php artisan demo:timeout --timeout=5 --items=100

# Different operation types
php artisan demo:timeout --operation=products --timeout=10
```

## Testing

Comprehensive test suite in `tests/Feature/TimeoutImplementationTest.php` covers:

- TimeoutService functionality
- LazyCollection timeout behavior
- Report generation with timeouts
- Database cursor operations
- Different timeout contexts
- Timeout logging and monitoring

Run tests with:
```bash
php artisan test --filter=TimeoutImplementationTest
```

### 5. UltraFastProductImageSeeder
**File:** `database/seeders/UltraFastProductImageSeeder.php`
**Timeout:** 30 minutes
**Purpose:** Prevents image generation from running excessively long, especially with many products.

```php
$timeout = now()->addMinutes(30); // 30 minute timeout for image generation

Product::query()
    ->cursor()
    ->takeUntilTimeout($timeout)
    ->chunk(self::BATCH_SIZE)
    ->each(function (Collection $products): void {
        // Process product images
    });
```

### 6. TimeoutService
**File:** `app/Services/TimeoutService.php`
**Purpose:** Centralized utility service for timeout management across different contexts.

```php
// Database operations
TimeoutService::withTimeout($collection, 30);

// Scheduled tasks
TimeoutService::forScheduledTask($collection, 60);

// Import operations
TimeoutService::forImport($collection, 10);

// Search operations
TimeoutService::forSearch($collection, 10);

// Recommendation generation
TimeoutService::forRecommendations($collection, 30);

// Background jobs
TimeoutService::forBackgroundJob($collection, 5);
```

### 7. ReportGenerationService
**File:** `app/Services/ReportGenerationService.php`
**Purpose:** Comprehensive report generation with timeout protection for different report types.

```php
// Sales reports (5 minutes)
$timeout = now()->addMinutes(5);
$query->cursor()->takeUntilTimeout($timeout)->each(function ($event) {
    // Process sales data
});

// Product analytics (10 minutes)
$timeout = now()->addMinutes(10);
$query->cursor()->takeUntilTimeout($timeout)->each(function ($product) {
    // Process product data
});

// User activity reports (8 minutes)
$timeout = now()->addMinutes(8);
$query->cursor()->takeUntilTimeout($timeout)->each(function ($event) {
    // Process user activity
});

// System reports (15 minutes)
$timeout = now()->addMinutes(15);
// Process system data with timeout protection
```

### 8. Data Export Operations
**Files:** `app/Http/Controllers/StockController.php`, `app/Http/Controllers/Api/CampaignClickController.php`
**Purpose:** Prevent long-running export operations from consuming excessive resources.

```php
// Stock exports (15 minutes)
$timeout = now()->addMinutes(15);
$query->cursor()->takeUntilTimeout($timeout)->each(function ($item) {
    // Export stock data
});

// Campaign click exports (10 minutes)
$timeout = now()->addMinutes(10);
LazyCollection::make($clicks)->takeUntilTimeout($timeout)->each(function ($click) {
    // Export campaign data
});
```

### 9. LiveNotificationService
**File:** `app/Services/LiveNotificationService.php`
**Purpose:** Prevent notification dispatch operations from taking too long.

```php
// Admin notifications (30 seconds)
$timeout = now()->addSeconds(30);
User::whereHas('roles', function ($query) {
    $query->whereIn('name', ['administrator', 'manager']);
})->cursor()->takeUntilTimeout($timeout)->each(function ($user) {
    // Send notification
});

// Bulk user notifications (2 minutes)
$timeout = now()->addMinutes(2);
LazyCollection::make($users)->takeUntilTimeout($timeout)->each(function ($user) {
    // Send notification
});
```

### 10. Scheduled Tasks
**Files:** `routes/console.php`, `app/Providers/AppServiceProvider.php`
**Purpose:** Ensure scheduled tasks complete within their allocated time windows.

```php
// Activity log cleanup (5 minutes)
$timeout = now()->addMinutes(5);
\Spatie\Activitylog\Models\Activity::where('created_at', '<', now()->subDays(90))
    ->cursor()
    ->takeUntilTimeout($timeout)
    ->each(function ($activity) {
        $activity->delete();
    });

// Export rotation (3 minutes)
$timeout = now()->addMinutes(3);
$files = collect($disk->files($dir))->takeUntilTimeout($timeout);
foreach ($files as $path) {
    // Process file rotation
}
```

## Benefits

The `takeUntilTimeout` implementation provides several key benefits:

1. **Prevents Long-Running Operations**: All data processing operations now have timeout limits
2. **Memory Efficiency**: Uses LazyCollections to process data one item at a time
3. **System Reliability**: Prevents jobs from running indefinitely and consuming resources
4. **User Experience**: Ensures search and recommendation operations return results quickly
5. **Resource Management**: Prevents database timeouts and memory exhaustion
6. **Monitoring**: Comprehensive logging of timeout information and processing statistics

## Usage Examples

```bash
# Generate system report with timeout protection
php artisan reports:generate --type=system --format=json

# Demonstrate timeout functionality
php artisan demo:timeout --timeout=5 --items=1000 --operation=numbers

# Run comprehensive tests
php artisan test --filter=TimeoutImplementationTest
```

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
