# Enum System Documentation

## Overview

The enum system provides a comprehensive, type-safe way to handle enumerated values throughout the application. It includes enhanced enums with rich metadata, translations, and utility methods.

## Available Enums

### 1. AddressType
- **Purpose**: Defines different types of addresses
- **Values**: `shipping`, `billing`, `home`, `work`, `other`
- **Features**: Priority ordering, primary/required flags, icons, colors

### 2. NavigationGroup
- **Purpose**: Defines navigation groups for admin panel
- **Values**: `Referral System`, `Products`, `Orders`, `Users`, `Settings`, `Analytics`, `Content`, `System`, `Marketing`, `Inventory`, `Reports`
- **Features**: Permission-based access, core/public flags, icons, colors

### 3. OrderStatus
- **Purpose**: Defines order lifecycle statuses
- **Values**: `pending`, `confirmed`, `processing`, `shipped`, `delivered`, `cancelled`, `refunded`, `partially_refunded`, `failed`, `on_hold`
- **Features**: Status transitions, business logic methods, icons, colors

### 4. PaymentType
- **Purpose**: Defines payment methods
- **Values**: `stripe`, `notch-pay`, `cash`
- **Features**: Fee calculation, currency support, online/offline flags, icons, colors

### 5. ProductStatus
- **Purpose**: Defines product lifecycle statuses
- **Values**: `draft`, `active`, `inactive`, `out_of_stock`, `discontinued`, `archived`, `pending_review`, `rejected`
- **Features**: Visibility flags, business logic methods, icons, colors

### 6. UserRole
- **Purpose**: Defines user roles and permissions
- **Values**: `super_admin`, `admin`, `manager`, `editor`, `customer`, `guest`
- **Features**: Permission system, role hierarchy, icons, colors

## Core Features

### 1. Rich Metadata
Each enum case includes:
- **Label**: Human-readable name with translations
- **Description**: Detailed description with translations
- **Icon**: Heroicon class name for UI
- **Color**: Color scheme for UI
- **Priority**: Ordering for display

### 2. Business Logic Methods
Each enum includes methods for:
- Status transitions
- Permission checks
- Business rule validation
- Feature flags

### 3. Translation Support
All enums support:
- Lithuanian (lt) - default language
- English (en)
- Extensible to other languages

### 4. Utility Methods
Each enum provides:
- `options()`: For select dropdowns
- `optionsWithDescriptions()`: For advanced UI components
- `values()`: Array of all values
- `labels()`: Array of all labels
- `ordered()`: Collection sorted by priority
- `fromLabel()`: Find case by label
- `toArray()`: Convert to array representation

## Usage Examples

### Basic Usage

```php
use App\Enums\OrderStatus;

// Get enum case
$status = OrderStatus::PENDING;

// Get label
echo $status->label(); // "Laukiantis"

// Get description
echo $status->description(); // "Užsakymas laukia patvirtinimo"

// Get icon
echo $status->icon(); // "heroicon-o-clock"

// Get color
echo $status->color(); // "yellow"

// Check business logic
if ($status->canBeCancelled()) {
    // Allow cancellation
}
```

### Form Usage

```php
use App\Enums\OrderStatus;

// For select dropdown
$options = OrderStatus::options();
// ['pending' => 'Laukiantis', 'confirmed' => 'Patvirtintas', ...]

// For advanced UI components
$optionsWithMeta = OrderStatus::optionsWithDescriptions();
// ['pending' => ['label' => 'Laukiantis', 'icon' => 'heroicon-o-clock', ...], ...]
```

### Validation Usage

```php
use App\Validators\EnumValidator;

// Validate enum value
if (EnumValidator::validateEnumValue('order_status', 'pending')) {
    // Valid
}

// Validate with business rules
if (EnumValidator::validateEnumWithRules('order_status', 'pending', ['canBeCancelled' => true])) {
    // Valid and can be cancelled
}
```

### Service Usage

```php
use App\Services\EnumService;

$enumService = new EnumService();

// Get all enum data
$allData = $enumService->getAllEnumData();

// Get specific enum data
$orderStatuses = $enumService->getEnumOptionsWithDescriptions('order_statuses');

// Get for API
$apiData = $enumService->getForUseCase('api');
```

### Helper Usage

```php
use App\Helpers\EnumHelper;

// Get enum options
$options = EnumHelper::getOptions('order_status');

// Get enum case
$status = EnumHelper::getCase('order_status', 'pending');

// Get enum label
$label = EnumHelper::getLabel('order_status', 'pending');

// Get enum collection
$collection = EnumHelper::getCollection('order_status');
```

### Factory Usage

```php
use App\Factories\EnumFactory;

// Create enum instance
$status = EnumFactory::create('order_status', 'pending');

// Create with fallback
$status = EnumFactory::createWithFallback('order_status', 'invalid', 'pending');

// Create from request data
$status = EnumFactory::createFromRequest('order_status', $request->input('status'));
```

### Collection Usage

```php
use App\Collections\EnumCollection;

// Create collection from enum
$collection = EnumCollection::fromEnum(OrderStatus::class);

// Filter by property
$activeStatuses = $collection->filterBy('isActive', true);

// Sort by priority
$sortedStatuses = $collection->sortByPriority();

// Get for API
$apiData = $collection->forApi();
```

## Translation Structure

### Lithuanian (lt/translations.php)
```php
'order_status_pending' => 'Laukiantis',
'order_status_pending_description' => 'Užsakymas laukia patvirtinimo',
```

### English (en/translations.php)
```php
'order_status_pending' => 'Pending',
'order_status_pending_description' => 'Order is pending confirmation',
```

## API Integration

### REST API
```php
// Get enum data for API
$enumData = $enumService->getForUseCase('api');

// Response format
{
    "order_statuses": [
        {
            "value": "pending",
            "label": "Pending",
            "description": "Order is pending confirmation",
            "icon": "heroicon-o-clock",
            "color": "yellow"
        }
    ]
}
```

### GraphQL
```php
// Get enum data for GraphQL
$graphqlData = $enumService->getForUseCase('graphql');

// Schema format
enum OrderStatus {
    PENDING
    CONFIRMED
    PROCESSING
    # ...
}
```

## Frontend Integration

### TypeScript
```typescript
// Generated TypeScript enum
export enum OrderStatus {
    PENDING = 'pending',
    CONFIRMED = 'confirmed',
    PROCESSING = 'processing',
    // ...
}
```

### JavaScript
```javascript
// Generated JavaScript object
const OrderStatus = {
    PENDING: 'pending',
    CONFIRMED: 'confirmed',
    PROCESSING: 'processing',
    // ...
};
```

### CSS
```css
/* Generated CSS custom properties */
:root {
    --order-status-pending: 'pending';
    --order-status-confirmed: 'confirmed';
    --order-status-processing: 'processing';
    /* ... */
}
```

## Database Integration

### Migration
```php
// Enum column definition
$table->enum('status', OrderStatus::values())->default('pending');
```

### Validation
```php
// Form request validation
'status' => ['required', 'in:' . implode(',', OrderStatus::values())],

// Or using helper
'status' => ['required', OrderValidator::getLaravelValidationRules('order_status')],
```

## Best Practices

### 1. Use Type Hints
```php
public function updateStatus(OrderStatus $status): void
{
    // Type-safe enum usage
}
```

### 2. Use Business Logic Methods
```php
if ($order->status->canBeCancelled()) {
    // Business logic
}
```

### 3. Use Translations
```php
// Always use translated labels
echo $status->label(); // Not $status->value
```

### 4. Use Collections for Bulk Operations
```php
$activeStatuses = OrderStatus::active();
$cancellableStatuses = OrderStatus::collection()->filter(fn($s) => $s->canBeCancelled());
```

### 5. Use Services for Complex Operations
```php
$enumService = new EnumService();
$data = $enumService->getForUseCaseAndEnums('api', ['order_status', 'payment_type']);
```

## Extending Enums

### Adding New Enum
1. Create enum class in `app/Enums/`
2. Implement `EnumInterface`
3. Add translations to language files
4. Register in `EnumService::getAllEnums()`
5. Add to `EnumFactory::getEnumClass()`
6. Add to `EnumValidator::getEnumClass()`

### Adding New Methods
1. Add method to enum class
2. Add to `EnumInterface` if needed
3. Update documentation
4. Add tests

### Adding New Translations
1. Add to `lang/lt/translations.php`
2. Add to `lang/en/translations.php`
3. Add to other language files as needed

## Testing

### Unit Tests
```php
use App\Enums\OrderStatus;

public function test_order_status_has_correct_label()
{
    $status = OrderStatus::PENDING;
    $this->assertEquals('Laukiantis', $status->label());
}

public function test_order_status_can_be_cancelled()
{
    $status = OrderStatus::PENDING;
    $this->assertTrue($status->canBeCancelled());
}
```

### Integration Tests
```php
use App\Services\EnumService;

public function test_enum_service_returns_correct_data()
{
    $service = new EnumService();
    $data = $service->getEnumOptions('order_status');
    
    $this->assertArrayHasKey('pending', $data);
    $this->assertEquals('Laukiantis', $data['pending']);
}
```

## Performance Considerations

### Caching
- Enum data is static and can be cached
- Use Laravel's cache for frequently accessed enum data
- Consider Redis for high-traffic applications

### Memory Usage
- Enums are lightweight and memory-efficient
- Collections provide lazy loading for large datasets
- Use pagination for large enum collections

### Database Queries
- Use enum values in database queries for performance
- Index enum columns for better query performance
- Use enum validation to prevent invalid data

## Security Considerations

### Input Validation
- Always validate enum values from user input
- Use enum validators for form validation
- Sanitize enum data before database operations

### Permission Checks
- Use enum business logic methods for permission checks
- Validate enum transitions in business logic
- Audit enum changes for security compliance

## Troubleshooting

### Common Issues

1. **Translation not found**
   - Check if translation key exists in language files
   - Verify language is set correctly
   - Check for typos in translation keys

2. **Enum value not found**
   - Verify enum value exists in enum class
   - Check for case sensitivity issues
   - Validate enum value before use

3. **Business logic method not found**
   - Check if method exists in enum class
   - Verify method signature
   - Check for typos in method names

### Debugging

```php
// Debug enum data
dd(OrderStatus::optionsWithDescriptions());

// Debug enum service
$service = new EnumService();
dd($service->getAllEnumData());

// Debug enum collection
$collection = EnumCollection::fromEnum(OrderStatus::class);
dd($collection->toArrays());
```

## Migration Guide

### From Basic Enums
1. Replace basic enum with enhanced enum
2. Update method calls to use new API
3. Add translations for labels and descriptions
4. Update UI components to use new metadata

### From Constants
1. Replace constants with enum cases
2. Update type hints to use enum
3. Add business logic methods
4. Update validation rules

### From Database Enums
1. Create enum class with database values
2. Update migrations to use enum values
3. Add business logic methods
4. Update queries to use enum values

## Conclusion

The enum system provides a comprehensive, type-safe, and feature-rich way to handle enumerated values throughout the application. It includes rich metadata, translations, business logic, and extensive utility methods for maximum flexibility and maintainability.

For more information, see the individual enum class documentation and the test files for usage examples.
