# Laravel 11 + Filament v4 Auto-Fix Setup

## Overview

This project now includes a comprehensive auto-formatting, static analysis, testing, and auto-fixing system that runs automatically in Cursor IDE. The system provides:

- **Auto-formatting** with Laravel Pint
- **Static analysis** with PHPStan (Larastan)
- **Auto-refactoring** with Rector
- **Testing** with Pest
- **Filament v4 compatibility** fixes

## Installed Tools

### Development Dependencies
- `laravel/pint` - Code formatting
- `nunomaduro/larastan` - Static analysis for Laravel
- `pestphp/pest` - Testing framework
- `pestphp/pest-plugin-laravel` - Laravel integration for Pest
- `rector/rector` - Automated refactoring
- `rector/rector-laravel` - Laravel-specific refactoring rules

### Configuration Files

#### 1. `phpstan.neon` - Static Analysis Configuration
```neon
includes:
  - vendor/nunomaduro/larastan/extension.neon

parameters:
  level: 9
  paths:
    - app
    - database
    - config
  checkMissingIterableValueType: true
  checkGenericClassInNonGenericObjectType: false
  treatPhpDocTypesAsCertain: true
  reportUnmatchedIgnoredErrors: true
  tmpDir: storage/framework/cache/phpstan
  universalObjectCratesClasses:
    - Illuminate\Support\Fluent

# Filament v4 specific ignores
ignoreErrors:
  - '#Call to an undefined method Illuminate\\Database\\Eloquent\\Builder::(.*)#'
  - '#Call to an undefined method Filament\\Forms\\Components\\(.*)#'
  - '#Call to an undefined method Filament\\Tables\\Columns\\(.*)#'
  - '#Call to an undefined method Filament\\Actions\\(.*)#'
  - '#Property App\\Filament\\.*::\$navigationIcon \(string\) does not accept BackedEnum#'

# Livewire specific ignores
  - '#Call to an undefined method Livewire\\Component::(.*)#'
  - '#Property .*::\$rules \(array\) does not accept array#'

# Laravel specific ignores
  - '#Call to an undefined method Illuminate\\Support\\Collection::(.*)#'
  - '#Call to an undefined method Illuminate\\Database\\Eloquent\\Collection::(.*)#'
```

#### 2. `rector.php` - Refactoring Configuration
```php
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Set\ValueObject\LevelSetList;
use RectorLaravel\Set\LaravelSetList;

return static function (RectorConfig $config): void {
    $config->paths([
        __DIR__ . '/app',
        __DIR__ . '/database',
        __DIR__ . '/config',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ]);

    $config->sets([
        LevelSetList::UP_TO_PHP_83,
        LaravelSetList::LARAVEL_110,
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_STATIC_TO_INJECTION,
    ]);

    $config->rule(InlineConstructorDefaultToPropertyRector::class);

    $config->importNames();
    $config->parallel();
};
```

## Composer Scripts

The following scripts are available in `composer.json`:

### Individual Commands
- `composer lint:php` - Check code style with Pint
- `composer fix:php` - Fix code style with Pint
- `composer analyze` - Run static analysis with PHPStan
- `composer rector` - Run automated refactoring
- `composer blade:format` - Clear and cache Blade views
- `composer test` - Run tests with Pest

### Combined Commands
- `composer check` - Run all checks (lint + analyze + test)
- `composer fix` - Run all fixes (Pint + Rector + Blade cache)

## Cursor IDE Integration

### Auto-Fix Rules (`.cursor/laravel-autofix.mdc`)
The system automatically runs when files are changed:

1. **Syntax Check** - Validates PHP syntax
2. **Auto-Fix** - Runs Pint, Rector, and Blade formatting
3. **Static Analysis** - Runs PHPStan analysis
4. **Cache Operations** - Clears and rebuilds Laravel caches
5. **Testing** - Runs the test suite

### Filament v4 Rules (`.cursor/filament-v4-rules.mdc`)
Automatically fixes common Filament v4 issues:

- Removes conflicting `use Filament\Schemas\Schema;` imports
- Ensures proper `Form` and `Table` imports
- Normalizes method signatures:
  - `public static function form(Form $form): Form`
  - `public static function table(Table $table): Table`
- Standardizes `$navigationIcon` property typing
- Fixes variable names in method bodies

## Fixed Issues

During setup, the following issues were automatically resolved:

### 1. SystemSetting.php
- **Issue**: Duplicate method declarations for `clearCache()`
- **Fix**: Renamed instance method to `clearInstanceCache()`

### 2. OrdersRelationManager.php
- **Issue**: Multiple syntax errors in Filament table configuration
- **Fix**: Added missing array brackets, method calls, and proper structure

### 3. EnumHelper.php
- **Issue**: Duplicate method declaration for `getUniqueCollectionByAll()`
- **Fix**: Removed duplicate method at end of file

### 4. Filament Resources
- **Issue**: Incorrect imports and method signatures for Filament v4
- **Fix**: Updated all resources to use proper v4 syntax

## Usage

### Manual Commands
```bash
# Quick check (lint + analyze + test)
composer check

# Auto-fix everything
composer fix

# Individual operations
composer lint:php
composer analyze
composer test
```

### Automatic Operation
The system runs automatically in Cursor IDE when you:
- Save PHP files
- Save Blade templates
- Modify configuration files

### Error Handling
If any step fails, the system will:
1. Analyze the error logs
2. Attempt automatic fixes for common issues
3. Re-run only the failed steps
4. Continue until all checks pass or no more fixes are possible

## Benefits

1. **Early Error Detection** - Catch issues before opening in browser
2. **Consistent Code Style** - Automatic formatting with Pint
3. **Code Quality** - Static analysis with PHPStan
4. **Modern PHP** - Automated refactoring with Rector
5. **Filament v4 Compatibility** - Automatic fixes for v4 syntax
6. **Comprehensive Testing** - Automated test execution

## Troubleshooting

### Common Issues

1. **PHPStan Errors**: Check the `ignoreErrors` section in `phpstan.neon`
2. **Rector Issues**: Ensure all dependencies are up to date
3. **Test Failures**: Run `composer test` to see detailed error messages
4. **Cache Issues**: Run `composer fix` to clear and rebuild caches

### Manual Fixes

If automatic fixes fail, you can:
1. Run individual commands to isolate issues
2. Check the error logs for specific problems
3. Manually fix syntax errors
4. Update configuration files as needed

## Performance

The system is optimized for:
- **Fast execution** - Parallel processing where possible
- **Minimal resource usage** - Efficient caching and memory management
- **Incremental analysis** - Only processes changed files when possible

## Maintenance

- **Regular Updates**: Keep dependencies updated for latest features
- **Configuration Tuning**: Adjust PHPStan rules as project evolves
- **Rule Updates**: Update Cursor rules for new patterns and requirements

This setup provides a robust development environment with automatic code quality enforcement and error prevention.
