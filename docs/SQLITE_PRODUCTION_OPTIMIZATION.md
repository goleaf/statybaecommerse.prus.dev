# SQLite Production Optimization Guide

This document outlines the SQLite optimizations implemented in this Laravel project based on the [Laravel News article on using SQLite in production](https://laravel-news.com/using-sqlite-in-production-with-laravel).

## Overview

SQLite can be a suitable choice for certain Laravel applications, particularly when configured appropriately. The optimizations implemented here enhance SQLite's performance for production use while maintaining data integrity.

## Implemented Optimizations

### 1. Write-Ahead Logging (WAL) Mode
- **Setting**: `PRAGMA journal_mode = WAL`
- **Benefit**: Improves concurrency by allowing readers and writers to access the database simultaneously
- **Impact**: Better performance for applications with multiple concurrent users

### 2. Busy Timeout
- **Setting**: `PRAGMA busy_timeout = 10000` (10 seconds)
- **Benefit**: Ensures that if the database is locked, new transactions will wait for a specified time before failing
- **Impact**: Reduces database lock errors and improves reliability

### 3. Cache Size Optimization
- **Setting**: `PRAGMA cache_size = -64000` (64MB)
- **Benefit**: Increases the amount of memory used for caching database pages
- **Impact**: Faster query performance due to reduced disk I/O

### 4. Temporary Storage in Memory
- **Setting**: `PRAGMA temp_store = memory`
- **Benefit**: Stores temporary tables and indices in memory instead of disk
- **Impact**: Faster temporary operations and reduced disk usage

### 5. Memory Mapping
- **Setting**: `PRAGMA mmap_size = 268435456` (256MB)
- **Benefit**: Enables memory mapping for better I/O performance
- **Impact**: Improved performance for large database operations

### 6. Page Size Optimization
- **Setting**: `PRAGMA page_size = 4096` (4KB)
- **Benefit**: Optimal page size for most modern systems
- **Impact**: Better performance and storage efficiency

### 7. Incremental Auto-Vacuum
- **Setting**: `PRAGMA auto_vacuum = incremental` (only on empty databases)
- **Benefit**: Automatically reclaims space from deleted records
- **Impact**: Maintains database performance over time
- **Note**: Cannot be changed on existing databases with data. Use `PRAGMA incremental_vacuum` manually when needed.

### 8. Synchronous Mode
- **Setting**: `PRAGMA synchronous = normal`
- **Benefit**: Balance between safety and performance
- **Impact**: Good performance while maintaining data integrity

### 9. Foreign Key Constraints
- **Setting**: `PRAGMA foreign_keys = on`
- **Benefit**: Ensures referential integrity
- **Impact**: Data consistency and reliability

## Configuration Files

### Database Configuration (`config/database.php`)
The SQLite connection has been enhanced with environment-configurable optimization settings:

```php
'sqlite' => [
    'driver' => 'sqlite',
    'url' => env('DB_URL'),
    'database' => env('DB_DATABASE', database_path('database.sqlite')),
    'prefix' => '',
    'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
    'busy_timeout' => env('DB_BUSY_TIMEOUT', 10000),
    'journal_mode' => env('DB_JOURNAL_MODE', 'wal'),
    'synchronous' => env('DB_SYNCHRONOUS', 'normal'),
    'cache_size' => env('DB_CACHE_SIZE', -64000),
    'temp_store' => env('DB_TEMP_STORE', 'memory'),
    'mmap_size' => env('DB_MMAP_SIZE', 268435456),
    'page_size' => env('DB_PAGE_SIZE', 4096),
    'auto_vacuum' => env('DB_AUTO_VACUUM', 'incremental'),
],
```

### Service Provider (`app/Providers/SqliteOptimizationServiceProvider.php`)
Automatically applies optimizations when the application boots:

- Runs optimization PRAGMA statements
- Logs success/failure of optimization application
- Only runs for SQLite connections

### Artisan Command (`app/Console/Commands/OptimizeSqliteCommand.php`)
Provides manual control over SQLite optimizations:

```bash
# Apply optimizations
php artisan sqlite:optimize

# Check current settings
php artisan sqlite:optimize --check

# Force apply optimizations
php artisan sqlite:optimize --force
```

## Environment Variables

You can customize the optimization settings using these environment variables:

```env
# SQLite Optimization Settings
DB_BUSY_TIMEOUT=10000
DB_JOURNAL_MODE=wal
DB_SYNCHRONOUS=normal
DB_CACHE_SIZE=-64000
DB_TEMP_STORE=memory
DB_MMAP_SIZE=268435456
DB_PAGE_SIZE=4096
DB_AUTO_VACUUM=incremental
```

## Usage

### Automatic Optimization
Optimizations are automatically applied when the application boots via the `SqliteOptimizationServiceProvider`.

### Manual Optimization
Use the Artisan command to manually apply or check optimizations:

```bash
# Check current settings
composer sqlite:check

# Apply optimizations
composer sqlite:optimize
```

### Monitoring
The service provider logs optimization results to the Laravel log:

```
[INFO] SQLite optimizations applied successfully
```

## Performance Benefits

These optimizations provide:

1. **Better Concurrency**: WAL mode allows simultaneous read/write operations
2. **Reduced Lock Contention**: Busy timeout prevents immediate failures
3. **Faster Queries**: Increased cache size reduces disk I/O
4. **Memory Efficiency**: Temporary storage in memory for faster operations
5. **I/O Optimization**: Memory mapping improves large operation performance
6. **Space Management**: Auto-vacuum maintains optimal database size
7. **Data Integrity**: Foreign key constraints ensure referential integrity

## When to Use SQLite in Production

SQLite is suitable for applications that:

- Are internal tools with limited concurrent users
- Do not require scaling across multiple servers
- Benefit from a lightweight, file-based database system
- Have moderate data volume requirements
- Need simple deployment and maintenance

## Limitations

SQLite is not suitable for:

- High-concurrency applications (hundreds of concurrent writes)
- Applications requiring horizontal scaling
- Complex multi-user scenarios with heavy write operations
- Applications requiring advanced database features (stored procedures, etc.)

## Monitoring and Maintenance

### Regular Checks
Run the optimization check command regularly:

```bash
php artisan sqlite:optimize --check
```

### Database Size Monitoring
Monitor database file size and consider running VACUUM if needed:

```sql
-- Check database size
SELECT page_count * page_size as size FROM pragma_page_count(), pragma_page_size();

-- Manual vacuum (if needed)
VACUUM;
```

### Performance Monitoring
Monitor query performance and adjust cache settings if needed based on your application's usage patterns.

## Troubleshooting

### Common Issues

1. **Permission Errors**: Ensure the database file and directory have proper write permissions
2. **Lock Issues**: Check if other processes are accessing the database
3. **Memory Issues**: Adjust cache_size and mmap_size based on available system memory

### Debug Commands

```bash
# Check current SQLite settings
php artisan sqlite:optimize --check

# View Laravel logs for optimization messages
tail -f storage/logs/laravel.log | grep -i sqlite
```

## References

- [Laravel News: Using SQLite in Production with Laravel](https://laravel-news.com/using-sqlite-in-production-with-laravel)
- [SQLite Documentation](https://www.sqlite.org/docs.html)
- [SQLite PRAGMA Statements](https://www.sqlite.org/pragma.html)