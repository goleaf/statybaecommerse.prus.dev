# Autocomplete Integration Guide

This document explains how to use the enhanced autocomplete system integrated into the Laravel e-commerce platform, based on the Laravel News article approach.

## Overview

The autocomplete system provides real-time search suggestions across multiple entity types (products, categories, brands, collections, attributes) with advanced features like:

- **Real-time search** with debounced input
- **Keyboard navigation** (arrow keys, Enter, Escape)
- **Recent searches** tracking
- **Popular suggestions** based on views
- **Multi-language support** (English, Lithuanian, German)
- **Caching** for performance optimization
- **Accessibility** features (ARIA attributes)
- **Mobile-friendly** interface

## Components

### 1. AutocompleteService

The core service that handles all autocomplete logic:

```php
use App\Services\AutocompleteService;

$autocompleteService = app(AutocompleteService::class);

// General search across all entities
$results = $autocompleteService->search('query', 10, ['products', 'categories']);

// Specific entity searches
$products = $autocompleteService->searchProducts('query', 10);
$categories = $autocompleteService->searchCategories('query', 10);
$brands = $autocompleteService->searchBrands('query', 10);

// Suggestions
$popular = $autocompleteService->getPopularSuggestions(10);
$recent = $autocompleteService->getRecentSuggestions(5);
```

### 2. API Endpoints

The system provides RESTful API endpoints for autocomplete:

```
GET /api/autocomplete/search?q=query&limit=10&types[]=products&types[]=categories
GET /api/autocomplete/products?q=query&limit=10
GET /api/autocomplete/categories?q=query&limit=10
GET /api/autocomplete/brands?q=query&limit=10
GET /api/autocomplete/collections?q=query&limit=10
GET /api/autocomplete/attributes?q=query&limit=10
GET /api/autocomplete/popular?limit=10
GET /api/autocomplete/recent?limit=5
GET /api/autocomplete/suggestions?limit=10
DELETE /api/autocomplete/recent
```

### 3. Livewire Components

#### LiveSearch Component

Enhanced search component with suggestions and keyboard navigation:

```blade
<livewire:components.live-search 
    :max-results="10"
    :min-query-length="2"
    :search-types="['products', 'categories', 'brands']"
    :enable-suggestions="true"
    :enable-recent-searches="true"
    :enable-popular-searches="true"
/>
```

#### ProductAutocomplete Component

For admin forms and product selection:

```blade
<livewire:components.product-autocomplete 
    :selected-product-id="null"
    :selected-product-name="''"
    :required="false"
    :placeholder="'Select a product...'"
    :name="'product_id'"
/>
```

#### CategoryAutocomplete Component

For category selection with multiple selection support:

```blade
<livewire:components.category-autocomplete 
    :selected-category-id="null"
    :selected-category-name="''"
    :required="false"
    :placeholder="'Select a category...'"
    :name="'category_id'"
    :allow-multiple="false"
    :selected-categories="[]"
/>
```

### 4. JavaScript Component

Standalone JavaScript component for custom implementations:

```javascript
// Initialize autocomplete
const autocomplete = new AutocompleteComponent({
    input: '#search-input',
    apiUrl: '/api/autocomplete/search',
    minLength: 2,
    debounceDelay: 300,
    maxResults: 10,
    showSuggestions: true,
    onSelect: function(result) {
        console.log('Selected:', result);
        // Handle selection
    },
    onClear: function() {
        console.log('Cleared');
        // Handle clear
    }
});

// Destroy when done
autocomplete.destroy();
```

## Usage Examples

### 1. Frontend Search

Add to your main layout or search page:

```blade
<!-- resources/views/layouts/app.blade.php -->
<header class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div class="flex-1 max-w-lg">
                <livewire:components.live-search />
            </div>
        </div>
    </div>
</header>
```

### 2. Admin Forms

Use in Filament forms or custom admin forms:

```php
// In a Filament form
Forms\Components\Livewire::make('product-autocomplete')
    ->label('Select Product')
    ->required(),

// In a custom form
<livewire:components.product-autocomplete 
    :selected-product-id="$productId"
    :selected-product-name="$productName"
    :required="true"
/>
```

### 3. Custom JavaScript Implementation

For custom search interfaces:

```html
<input type="text" id="custom-search" placeholder="Search...">
<div id="search-results"></div>

<script src="/js/autocomplete.js"></script>
<script>
const searchAutocomplete = new AutocompleteComponent({
    input: '#custom-search',
    container: '#search-results',
    apiUrl: '/api/autocomplete/search',
    onSelect: function(result) {
        window.location.href = result.url;
    }
});
</script>
```

## Configuration

### Environment Variables

```env
# Cache TTL for autocomplete results (seconds)
AUTOCOMPLETE_CACHE_TTL=300

# Default max results per search
AUTOCOMPLETE_MAX_RESULTS=10

# Minimum query length
AUTOCOMPLETE_MIN_QUERY_LENGTH=2

# Enable/disable features
AUTOCOMPLETE_ENABLE_SUGGESTIONS=true
AUTOCOMPLETE_ENABLE_RECENT=true
AUTOCOMPLETE_ENABLE_POPULAR=true
```

### Service Configuration

```php
// config/autocomplete.php
return [
    'cache_ttl' => env('AUTOCOMPLETE_CACHE_TTL', 300),
    'max_results' => env('AUTOCOMPLETE_MAX_RESULTS', 10),
    'min_query_length' => env('AUTOCOMPLETE_MIN_QUERY_LENGTH', 2),
    'enable_suggestions' => env('AUTOCOMPLETE_ENABLE_SUGGESTIONS', true),
    'enable_recent' => env('AUTOCOMPLETE_ENABLE_RECENT', true),
    'enable_popular' => env('AUTOCOMPLETE_ENABLE_POPULAR', true),
    'search_types' => [
        'products' => 60, // 60% of results
        'categories' => 20, // 20% of results
        'brands' => 20, // 20% of results
    ],
];
```

## Customization

### 1. Custom Search Types

Add new search types by extending the AutocompleteService:

```php
// In AutocompleteService
public function searchCustom(string $query, int $limit = 10): array
{
    // Your custom search logic
    return $results;
}

// In AutocompleteController
public function custom(Request $request): JsonResponse
{
    $results = $this->autocompleteService->searchCustom($request->q, $request->limit);
    return response()->json(['success' => true, 'data' => $results]);
}
```

### 2. Custom Styling

Override the default styles:

```css
/* Custom autocomplete styles */
.autocomplete-container {
    @apply bg-white border border-gray-200 rounded-lg shadow-lg;
}

.autocomplete-item {
    @apply hover:bg-gray-50 cursor-pointer;
}

.autocomplete-item.selected {
    @apply bg-blue-50 border-blue-200;
}
```

### 3. Custom Events

Listen to autocomplete events:

```javascript
// Livewire events
document.addEventListener('product-selected', function(event) {
    console.log('Product selected:', event.detail);
});

document.addEventListener('category-selected', function(event) {
    console.log('Category selected:', event.detail);
});
```

## Performance Optimization

### 1. Caching

The system uses Laravel's cache for performance:

```php
// Cache keys are generated based on query, limit, types, and locale
$cacheKey = "autocomplete_{$typesKey}_{$query}_{$limit}_" . app()->getLocale();

// Cache TTL is configurable
Cache::remember($cacheKey, self::CACHE_TTL, function() {
    // Search logic
});
```

### 2. Database Optimization

Ensure proper indexes on searchable columns:

```sql
-- Products
CREATE INDEX idx_products_name ON products(name);
CREATE INDEX idx_products_sku ON products(sku);
CREATE INDEX idx_products_visible ON products(is_visible, published_at);

-- Categories
CREATE INDEX idx_categories_name ON categories(name);
CREATE INDEX idx_categories_visible ON categories(is_visible);

-- Brands
CREATE INDEX idx_brands_name ON brands(name);
CREATE INDEX idx_brands_visible ON brands(is_visible);
```

### 3. Query Optimization

The service uses optimized queries with proper eager loading:

```php
$products = Product::query()
    ->with(['media', 'brand', 'categories']) // Eager load relationships
    ->where('is_visible', true)
    ->whereNotNull('published_at')
    ->where('published_at', '<=', now())
    ->limit($limit)
    ->get();
```

## Testing

### 1. Unit Tests

```php
// tests/Unit/Services/AutocompleteServiceTest.php
public function test_search_returns_results()
{
    $service = app(AutocompleteService::class);
    $results = $service->search('test', 10);
    
    $this->assertIsArray($results);
    $this->assertLessThanOrEqual(10, count($results));
}

public function test_search_products_returns_products()
{
    $service = app(AutocompleteService::class);
    $results = $service->searchProducts('test', 5);
    
    $this->assertIsArray($results);
    foreach ($results as $result) {
        $this->assertEquals('product', $result['type']);
    }
}
```

### 2. Feature Tests

```php
// tests/Feature/Api/AutocompleteControllerTest.php
public function test_autocomplete_search_endpoint()
{
    $response = $this->getJson('/api/autocomplete/search?q=test&limit=5');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'type', 'title', 'url']
            ],
            'meta'
        ]);
}
```

## Troubleshooting

### Common Issues

1. **No results returned**
   - Check if products/categories are visible and published
   - Verify database indexes are created
   - Check cache configuration

2. **Slow performance**
   - Enable query caching
   - Optimize database indexes
   - Reduce max results limit

3. **JavaScript errors**
   - Ensure Alpine.js is loaded
   - Check browser console for errors
   - Verify API endpoints are accessible

### Debug Mode

Enable debug mode for development:

```php
// In AutocompleteService
if (config('app.debug')) {
    Log::info('Autocomplete search', [
        'query' => $query,
        'results_count' => count($results),
        'execution_time' => microtime(true) - $startTime
    ]);
}
```

## Migration from Existing Search

If you have an existing search system, you can migrate gradually:

1. **Phase 1**: Add autocomplete alongside existing search
2. **Phase 2**: Update frontend components to use autocomplete
3. **Phase 3**: Replace existing search with autocomplete
4. **Phase 4**: Remove old search code

## Support

For issues or questions:

1. Check the Laravel News article: https://laravel-news.com/adding-autocomplete-to-your-laravel-applications
2. Review the code in `app/Services/AutocompleteService.php`
3. Check the API endpoints in `app/Http/Controllers/Api/AutocompleteController.php`
4. Examine the Livewire components in `app/Livewire/Components/`

## Changelog

- **v1.0.0**: Initial implementation with basic autocomplete
- **v1.1.0**: Added suggestions and recent searches
- **v1.2.0**: Added keyboard navigation and accessibility
- **v1.3.0**: Added multi-language support
- **v1.4.0**: Added caching and performance optimizations
- **v1.5.0**: Added specialized components for admin forms
