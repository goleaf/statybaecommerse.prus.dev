# Year-Month-Day DateTime Format Implementation

## Overview

This document describes the implementation of year-month-day (Y-m-d) datetime formatting throughout the Laravel application. The system uses a consistent Y-m-d format for all locales.

## Configuration

### Main Configuration File: `config/datetime.php`

```php
<?php

return [
    'european_locales' => [
        'lt', 'lv', 'et', 'pl', 'sk', 'cs', 'hu', 'ro', 'bg', 'hr', 'sl', 'de'
    ],

    'formats' => [
        'date' => 'Y-m-d',
        'datetime' => 'Y-m-d H:i',
        'datetime_full' => 'Y-m-d H:i:s',
        'time' => 'H:i',
        'date_short' => 'y-m-d',
        'month_year' => 'Y-m',
        'year_month' => 'Y-m',
    ],

    'filament_formats' => [
        'date' => 'Y-m-d',
        'datetime' => 'Y-m-d H:i',
        'datetime_full' => 'Y-m-d H:i:s',
    ],

    'fallback_formats' => [
        'date' => 'Y-m-d',
        'datetime' => 'Y-m-d H:i',
        'datetime_full' => 'Y-m-d H:i:s',
    ],

    'timezone' => 'Europe/Vilnius',
];
```

### Application Configuration: `config/app.php`

- **Timezone**: Set to `Europe/Vilnius` for European time zone
- **Default Locale**: Lithuanian (`lt`)
- **Supported Locales**: `lt`, `en`, `ru`, `de`

## Helper Functions

### Core Date/Time Formatting Functions

Located in `app/helpers.php`:

#### `format_date($date, $locale = null)`
Formats a date using year-month-day format (Y-m-d) for all locales.

```php
format_date(now()); // Returns: "2024-12-15"
format_date(now(), 'en'); // Returns: "2024-12-15"
```

#### `format_datetime($dateTime, $locale = null)`
Formats a datetime using year-month-day format (Y-m-d H:i) for all locales.

```php
format_datetime(now()); // Returns: "2024-12-15 14:30"
format_datetime(now(), 'en'); // Returns: "2024-12-15 14:30"
```

#### `format_datetime_full($dateTime, $locale = null)`
Formats a datetime with seconds using year-month-day format (Y-m-d H:i:s).

```php
format_datetime_full(now()); // Returns: "2024-12-15 14:30:45"
```

#### `format_date_short($date, $locale = null)`
Formats a date using short year-month-day format (y-m-d).

```php
format_date_short(now()); // Returns: "24-12-15"
```

#### `format_time($dateTime, $locale = null)`
Formats time only (H:i).

```php
format_time(now()); // Returns: "14:30"
```

## Implementation Details

### Consistent Format Across All Locales

The system uses year-month-day format for all locales:

```php
// Use year-month-day format for all locales
return $dt->format(config('datetime.formats.date', 'Y-m-d'));
```


## Updated Components

### 1. Helper Functions (`app/helpers.php`)
- Updated all date/time formatting functions
- Added configuration-based formatting
- Maintained backward compatibility

### 2. AppServiceProvider (`app/Providers/AppServiceProvider.php`)
- Updated global date variables to use year-month-day format
- `$CURRENT_DATE` now returns `Y-m-d` format
- `$CURRENT_DATETIME` now returns `Y-m-d H:i:s` format

### 3. GlobalDataCreator (`app/View/Creators/GlobalDataCreator.php`)
- Updated global view data to use year-month-day format
- `currentDate` and `currentDateTime` use configuration-based formatting

### 4. Filament Resources
- Updated `NewsResource.php` DateTimePicker formats
- Updated `CustomerManagementResource.php` date formats
- All Filament forms now use `Y-m-d` format

### 5. Blade Templates
Updated templates to use consistent year-month-day format:
- `resources/views/users/*` - User dashboard, orders, reviews, wishlist
- `resources/views/stock/show.blade.php` - Stock management
- `resources/views/components/order-tracking.blade.php` - Order tracking
- `resources/views/campaigns/partials/campaign-card.blade.php` - Campaign cards

## Usage Examples

### In Blade Templates

```blade
{{-- Year-month-day format for all locales --}}
{{ $order->created_at->format('Y-m-d') }}

{{-- Using helper functions (recommended) --}}
{{ format_date($order->created_at) }}
{{ format_datetime($order->created_at) }}
{{ format_date_short($order->created_at) }}
```

### In Controllers

```php
// Using helper functions
$formattedDate = format_date($order->created_at);
$formattedDateTime = format_datetime($order->created_at);

// Direct formatting
$formattedDate = $order->created_at->format('Y-m-d');
```

### In Filament Resources

```php
DateTimePicker::make('published_at')
    ->label(__('news.published_at'))
    ->default(now())
    ->displayFormat('Y-m-d H:i'),
```

## Benefits

1. **Consistency**: All dates throughout the application use year-month-day format
2. **Standardization**: Y-m-d format is ISO 8601 compliant and internationally recognized
3. **Maintainability**: Centralized configuration makes it easy to update formats
4. **Backward Compatibility**: Existing code continues to work
5. **Flexibility**: Easy to modify formats across the entire application

## Testing

To test the implementation:

1. Set locale to Lithuanian (`lt`) - should show `Y-m-d` format
2. Set locale to English (`en`) - should show `Y-m-d` format
3. Test all helper functions with different locales
4. Verify Filament forms display correct formats
5. Check Blade templates render dates correctly

## Future Enhancements

1. Add locale-specific month/day names while maintaining Y-m-d format
2. Add timezone handling for different countries
3. Consider adding relative date formatting (e.g., "2 days ago")
4. Add more date format variations if needed (e.g., for specific use cases)
