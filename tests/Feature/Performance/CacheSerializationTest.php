<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Models\Product;
use App\Support\Cache\SerializationOptimizer;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * **Feature: performance-update, Property 12: Cache Serialization Efficiency**
 * **Validates: Requirements 6.3**
 *
 * Ensures cached list payloads use serializable DTOs/arrays rather than full Eloquent models
 * to reduce Livewire hydration overhead.
 */
final class CacheSerializationTest extends TestCase
{
    use RefreshDatabase;

    public function test_paginator_optimization_reduces_memory_footprint(): void
    {
        $products = Product::factory()->count(10)->create();
        $paginator = new LengthAwarePaginator(
            $products,
            10,
            5,
            1
        );

        $originalSize = strlen(serialize($paginator));
        $optimized = SerializationOptimizer::optimizePaginator($paginator);
        $optimizedSize = strlen(serialize($optimized));

        expect($optimizedSize)->toBeLessThan($originalSize)
            ->and($optimized)->toBeArray()
            ->and($optimized)->toHaveKeys(['data', 'current_page', 'total', 'per_page']);
    }

    public function test_collection_optimization_converts_models_to_arrays(): void
    {
        $products = Product::factory()->count(5)->create();
        $collection = new EloquentCollection($products);

        $optimized = SerializationOptimizer::optimizeCollection($collection);

        expect($optimized)->toBeArray()
            ->and($optimized)->toHaveCount(5);

        foreach ($optimized as $item) {
            expect($item)->toBeArray()
                ->and($item)->toHaveKeys(['attributes', 'model_class'])
                ->and($item['model_class'])->toBe(Product::class);
        }
    }

    public function test_model_optimization_preserves_relations(): void
    {
        $product = Product::factory()->create();
        $product->load('brand', 'categories');

        $optimized = SerializationOptimizer::optimizeModel($product);

        expect($optimized)->toBeArray()
            ->and($optimized)->toHaveKeys(['attributes', 'relations', 'model_class'])
            ->and($optimized['model_class'])->toBe(Product::class)
            ->and($optimized['relations'])->toBeArray();
    }

    public function test_paginator_restoration_maintains_structure(): void
    {
        $products = Product::factory()->count(10)->create();
        $originalPaginator = new LengthAwarePaginator(
            $products,
            20,
            5,
            2
        );

        $optimized = SerializationOptimizer::optimizePaginator($originalPaginator);
        $restored = SerializationOptimizer::restorePaginator($optimized);

        expect($restored)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($restored->total())->toBe(20)
            ->and($restored->perPage())->toBe(5)
            ->and($restored->currentPage())->toBe(2)
            ->and($restored->count())->toBe(10);
    }

    public function test_optimization_detection_works_correctly(): void
    {
        $products = Product::factory()->count(3)->create();

        // Test paginator detection
        $paginator = new LengthAwarePaginator($products, 3, 3, 1);
        $optimizedPaginator = SerializationOptimizer::optimizePaginator($paginator);

        expect(SerializationOptimizer::isOptimized($optimizedPaginator))->toBeTrue()
            ->and(SerializationOptimizer::isOptimized($paginator))->toBeFalse();

        // Test model detection
        $optimizedModel = SerializationOptimizer::optimizeModel($products->first());

        expect(SerializationOptimizer::isOptimized($optimizedModel))->toBeTrue()
            ->and(SerializationOptimizer::isOptimized($products->first()))->toBeFalse();

        // Test collection detection
        $optimizedCollection = SerializationOptimizer::optimizeCollection($products);

        expect(SerializationOptimizer::isOptimized($optimizedCollection))->toBeTrue()
            ->and(SerializationOptimizer::isOptimized($products))->toBeFalse();
    }

    public function test_serialization_reduces_livewire_hydration_overhead(): void
    {
        $products = Product::factory()->count(20)->create();

        // Measure serialization time for Eloquent collection
        $start = microtime(true);
        $serializedEloquent = serialize($products);
        $eloquentTime = microtime(true) - $start;

        // Measure serialization time for optimized array
        $optimized = SerializationOptimizer::optimizeCollection($products);
        $start = microtime(true);
        $serializedOptimized = serialize($optimized);
        $optimizedTime = microtime(true) - $start;

        expect($optimizedTime)->toBeLessThanOrEqual($eloquentTime)
            ->and(strlen($serializedOptimized))->toBeLessThanOrEqual(strlen($serializedEloquent));
    }
}
