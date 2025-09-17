# Blade Syntax Fix - PHP 8.3 Compatibility

## Issue Fixed

**Error**: `ParseError - syntax error, unexpected identifier "type"`

**Location**: `resources/views/components/layouts/base.blade.php:108`

## Root Cause

The issue was caused by an inline `@php()` directive immediately followed by an HTML `<script>` tag with a `type` attribute:

```blade
@php($org = config('app.name'))
<script type="application/ld+json">
```

The Blade compiler in PHP 8.3 was interpreting the `type` attribute as part of the PHP syntax, causing a parse error.

## Solution Applied

Changed the inline `@php()` directive to a block directive:

**Before:**
```blade
@php($org = config('app.name'))
<script type="application/ld+json">
```

**After:**
```blade
@php
    $org = config('app.name');
@endphp
<script type="application/ld+json">
```

## Prevention

To avoid similar issues in the future:

1. **Avoid inline `@php()` directives** immediately before HTML tags with attributes
2. **Use block `@php...@endphp` directives** when the PHP code is followed by HTML
3. **Add whitespace or comments** between PHP directives and HTML if inline directives are necessary

## Files Modified

- `resources/views/components/layouts/base.blade.php` - Fixed inline PHP directive

## Testing

- ✅ View compilation successful
- ✅ Application loads without parse errors
- ✅ All cached views cleared and regenerated

## Related Commands

```bash
# Clear view cache after Blade template changes
php artisan view:clear

# Clear all caches
php artisan config:clear && php artisan route:clear && php artisan view:clear

# Remove compiled views manually
rm -rf storage/framework/views/*
```
