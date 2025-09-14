# Laravel Collection skipWhile Implementation

## Overview

This document describes the implementation of Laravel's `skipWhile` collection method throughout the project. The `skipWhile` method skips items from the collection as long as the provided callback returns true. Once the callback returns false, all subsequent items are returned in a new collection.

## What is skipWhile?

The `skipWhile` method is a Laravel Collection method that:
- Skips items from the beginning of a collection while a condition is true
- Returns all remaining items once the condition becomes false
- If the condition never becomes false, returns an empty collection
- Preserves the original collection (does not modify it)

## Implementation Locations

### 1. RecommendationService.php

**Location**: `app/Services/RecommendationService.php`

**Implementation**:
```php
return $allRecommendations
    ->unique('id')
    ->skipWhile(function ($product) {
        // Skip products with low relevance scores or missing essential data
        return $product->relevance_score < 0.3 || 
               empty($product->name) || 
               !$product->is_visible ||
               $product->price <= 0;
    })
    ->take($block->max_products);
```

**Purpose**: Filters out low-quality recommendations before returning them to users.

### 2. AutocompleteService.php

**Location**: `app/Services/AutocompleteService.php`

**Multiple Implementations**:

#### Product Search
```php
return $products
    ->skipWhile(function (Product $product) {
        // Skip products that are not properly configured or have missing essential data
        return empty($product->name) || 
               !$product->is_visible ||
               $product->price <= 0 ||
               empty($product->slug);
    })
    ->map(function (Product $product) use ($query, $locale) {
        // ... mapping logic
    })->toArray();
```

#### Category Search
```php
return $categories
    ->skipWhile(function (Category $category) {
        // Skip categories that are not properly configured or have missing essential data
        return empty($category->name) || 
               !$category->is_visible ||
               empty($category->slug);
    })
    ->map(function (Category $category) use ($query, $locale) {
        // ... mapping logic
    })->toArray();
```

#### Brand Search
```php
return $brands
    ->skipWhile(function (Brand $brand) {
        // Skip brands that are not properly configured or have missing essential data
        return empty($brand->name) || 
               !$brand->is_visible ||
               empty($brand->slug);
    })
    ->map(function (Brand $brand) use ($query, $locale) {
        // ... mapping logic
    })->toArray();
```

#### Collection Search
```php
return $collections
    ->skipWhile(function (Collection $collection) {
        // Skip collections that are not properly configured or have missing essential data
        return empty($collection->name) || 
               !$collection->is_visible ||
               empty($collection->slug);
    })
    ->map(function (Collection $collection) use ($query, $locale) {
        // ... mapping logic
    })->toArray();
```

#### Popular Products
```php
return $popularProducts
    ->skipWhile(function (Product $product) {
        // Skip popular products that are not properly configured or have missing essential data
        return empty($product->name) || 
               !$product->is_visible ||
               empty($product->slug) ||
               $product->price <= 0;
    })
    ->map(function (Product $product) use ($locale) {
        // ... mapping logic
    })->toArray();
```

**Purpose**: Ensures only valid, properly configured entities are returned in search results.

### 3. CollectionController.php

**Location**: `app/Http/Controllers/CollectionController.php`

**Multiple API Endpoints**:

#### API Endpoint
```php
return response()->json([
    'collections' => $collections
        ->skipWhile(function ($collection) {
            // Skip collections that are not properly configured or have missing essential data
            return empty($collection->name) || 
                   !$collection->is_visible ||
                   empty($collection->slug);
        })
        ->map(function ($collection) {
            // ... mapping logic
        }),
]);
```

#### By Type Endpoint
```php
return response()->json([
    'collections' => $collections
        ->skipWhile(function ($collection) {
            // Skip collections that are not properly configured or have missing essential data
            return empty($collection->name) || 
                   !$collection->is_visible ||
                   empty($collection->slug);
        })
        ->map(function ($collection) {
            // ... mapping logic
        }),
]);
```

#### With Products Endpoint
```php
return response()->json([
    'collections' => $collections
        ->skipWhile(function ($collection) {
            // Skip collections that are not properly configured or have missing essential data
            return empty($collection->name) || 
                   !$collection->is_visible ||
                   empty($collection->slug) ||
                   $collection->products_count <= 0;
        })
        ->map(function ($collection) {
            // ... mapping logic
        }),
]);
```

#### Popular Endpoint
```php
return response()->json([
    'collections' => $collections
        ->skipWhile(function ($collection) {
            // Skip collections that are not properly configured or have missing essential data
            return empty($collection->name) || 
                   !$collection->is_visible ||
                   empty($collection->slug) ||
                   $collection->products_count <= 0;
        })
        ->map(function ($collection) {
            // ... mapping logic
        }),
]);
```

**Purpose**: Ensures API responses only contain valid, properly configured collections.

### 4. DataFilteringService.php

**Location**: `app/Services/DataFilteringService.php`

**New Service Created**: A comprehensive service for data filtering using `skipWhile`.

**Key Methods**:

#### filterQualityProducts
```php
public function filterQualityProducts(Collection $products): Collection
{
    return $products->skipWhile(function ($product) {
        // Skip products that don't meet quality standards
        return empty($product->name) || 
               !$product->is_visible ||
               $product->price <= 0 ||
               empty($product->slug) ||
               $product->stock_quantity <= 0 ||
               !$product->is_published;
    });
}
```

#### filterValidCollections
```php
public function filterValidCollections(Collection $collections): Collection
{
    return $collections->skipWhile(function ($collection) {
        // Skip collections that are not properly configured
        return empty($collection->name) || 
               !$collection->is_visible ||
               empty($collection->slug) ||
               $collection->products_count <= 0;
    });
}
```

#### filterRelevantResults
```php
public function filterRelevantResults(Collection $results, float $minRelevanceScore = 0.5): Collection
{
    return $results->skipWhile(function ($result) use ($minRelevanceScore) {
        // Skip results with low relevance scores
        $relevanceScore = $result['relevance_score'] ?? 0;
        return $relevanceScore < $minRelevanceScore;
    });
}
```

#### filterNewRecommendations
```php
public function filterNewRecommendations(Collection $recommendations, array $userInteractions = []): Collection
{
    return $recommendations->skipWhile(function ($recommendation) use ($userInteractions) {
        // Skip recommendations for products user has already viewed/purchased
        $productId = $recommendation->id ?? $recommendation['id'] ?? null;
        return in_array($productId, $userInteractions);
    });
}
```

#### filterProductsByPriceRange
```php
public function filterProductsByPriceRange(Collection $products, float $minPrice = 0, float $maxPrice = null): Collection
{
    return $products->skipWhile(function ($product) use ($minPrice, $maxPrice) {
        $price = $product->price ?? $product['price'] ?? 0;
        
        // Skip products below minimum price
        if ($price < $minPrice) {
            return true;
        }
        
        // Skip products above maximum price (if specified)
        if ($maxPrice !== null && $price > $maxPrice) {
            return true;
        }
        
        return false;
    });
}
```

#### filterWithMultipleCriteria
```php
public function filterWithMultipleCriteria(Collection $items, array $criteria = []): Collection
{
    return $items->skipWhile(function ($item) use ($criteria) {
        // Apply multiple filtering criteria
        foreach ($criteria as $field => $condition) {
            $value = $item->{$field} ?? $item[$field] ?? null;
            
            if (is_array($condition)) {
                // Array condition: ['min' => 0, 'max' => 100]
                if (isset($condition['min']) && $value < $condition['min']) {
                    return true;
                }
                if (isset($condition['max']) && $value > $condition['max']) {
                    return true;
                }
                if (isset($condition['in']) && !in_array($value, $condition['in'])) {
                    return true;
                }
                if (isset($condition['not_in']) && in_array($value, $condition['not_in'])) {
                    return true;
                }
            } else {
                // Simple condition: exact match or boolean check
                if ($value !== $condition) {
                    return true;
                }
            }
        }
        
        return false;
    });
}
```

**Purpose**: Provides reusable filtering methods for various data types and criteria.

## Benefits of skipWhile Implementation

### 1. Data Quality
- Ensures only valid, properly configured entities are returned
- Filters out incomplete or corrupted data
- Improves user experience by showing only relevant results

### 2. Performance
- Reduces processing of invalid data
- Optimizes API responses
- Improves search result quality

### 3. Maintainability
- Centralized filtering logic
- Reusable filtering methods
- Clear separation of concerns

### 4. User Experience
- Better search results
- More relevant recommendations
- Cleaner API responses

## Usage Examples

### Basic Usage
```php
$collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

$subset = $collection->skipWhile(function (int $item) {
    return $item <= 5;
});

// Result: [6, 7, 8, 9, 10]
```

### With Objects
```php
$products = collect([
    (object) ['name' => '', 'price' => 100],
    (object) ['name' => 'Valid Product', 'price' => 200],
    (object) ['name' => 'Another Valid', 'price' => 300],
]);

$validProducts = $products->skipWhile(function ($product) {
    return empty($product->name);
});

// Result: Only products with non-empty names
```

### With Arrays
```php
$results = collect([
    ['title' => 'Result 1', 'relevance_score' => 0.2],
    ['title' => 'Result 2', 'relevance_score' => 0.6],
    ['title' => 'Result 3', 'relevance_score' => 0.8],
]);

$relevantResults = $results->skipWhile(function ($result) {
    return $result['relevance_score'] < 0.5;
});

// Result: Only results with relevance_score >= 0.5
```

## Testing

The implementation includes comprehensive tests in `tests/Feature/SkipWhileFunctionalityTest.php` that cover:
- Basic skipWhile functionality
- Object filtering
- Array filtering
- Multiple criteria filtering
- Edge cases

## Conclusion

The `skipWhile` implementation provides a robust, efficient way to filter collections throughout the application. It ensures data quality, improves performance, and enhances user experience by filtering out invalid or irrelevant data at the collection level.

The implementation follows Laravel best practices and provides a clean, maintainable approach to data filtering that can be easily extended and modified as needed.
