# FILAMENT V4 NAVIGATION GROUP TYPE COMPATIBILITY RULE

## CRITICAL: Automatic Filament v4 Navigation Group Type Fix

**TRIGGER**: Whenever editing any file under `app/Filament/**` or when linting errors mention `$navigationGroup` type issues.

**AUTOMATIC ACTIONS**:

1. **Check for Navigation Group Type Issues**
   - Scan for `protected static $navigationGroup` declarations
   - Verify type is `UnitEnum|string|null`
   - Check for proper `use UnitEnum;` import

2. **Auto-Fix Navigation Group Type**
   ```php
   // WRONG (any of these):
   protected static NavigationGroup $navigationGroup = NavigationGroup::Products;
   protected static ?string $navigationGroup = 'Products';
   protected static $navigationGroup = NavigationGroup::Products;
   
   // CORRECT:
   /** @var UnitEnum|string|null */
   protected static $navigationGroup = NavigationGroup::Products;
   ```

3. **Auto-Fix UnitEnum Import**
   ```php
   // Add if missing:
   use UnitEnum;
   
   // Remove duplicates if found
   ```

4. **Apply to ALL Filament Resources**
   - Resources extending `Filament\Resources\Resource`
   - Pages extending `Filament\Pages\Page`
   - Widgets extending `Filament\Widgets\Widget`
   - Any class with `$navigationGroup` property

5. **Validation Rules**
   - Type MUST be `UnitEnum|string|null`
   - Import MUST include `use UnitEnum;`
   - NO duplicate imports
   - NO type declarations on the property itself
   - Use docblock for type hinting

6. **Error Prevention**
   - Run linting check after any Filament file edit
   - Auto-fix any `$navigationGroup` type errors
   - Ensure compatibility with Filament v4 requirements
   - Prevent future type compatibility issues

**IMPLEMENTATION**:
- Apply this rule automatically when editing Filament files
- Check and fix on file save
- Validate during linting
- Ensure zero tolerance for navigation group type errors

**EXAMPLES OF CORRECT IMPLEMENTATION**:

```php
<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Models\Brand;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use UnitEnum; // REQUIRED

final class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;
    
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::Products; // CORRECT
    
    // ... rest of the class
}
```

**NEVER ALLOW**:
- `protected static NavigationGroup $navigationGroup`
- `protected static ?string $navigationGroup`
- Missing `use UnitEnum;` import
- Duplicate `use UnitEnum;` imports
- Type errors on `$navigationGroup` property

This rule ensures 100% compatibility with Filament v4 navigation group requirements and prevents future type errors.
