<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources;

use PHPUnit\Framework\TestCase;

/**
 * Basic unit tests for core Filament resources to ensure they can be loaded
 * after the Filament 3.3 downgrade and restoration process.
 *
 * Requirements: 6.3 - Test resource loading and basic operations
 */
final class CoreResourcesBasicTest extends TestCase
{
    /**
     * Test that core resource classes exist and can be loaded
     */
    public function test_core_resource_classes_exist(): void
    {
        $resources = [
            'App\Filament\Resources\ProductResource',
            'App\Filament\Resources\CategoryResource',
            'App\Filament\Resources\BrandResource',
            'App\Filament\Resources\InventoryResource',
            'App\Filament\Resources\PriceResource',
            'App\Filament\Resources\DiscountResource',
        ];

        foreach ($resources as $resourceClass) {
            $this->assertTrue(
                class_exists($resourceClass),
                "Resource class {$resourceClass} should exist"
            );
        }
    }

    /**
     * Test that core model classes exist and can be loaded
     */
    public function test_core_model_classes_exist(): void
    {
        $models = [
            'App\Models\Product',
            'App\Models\Category',
            'App\Models\Brand',
            'App\Models\Inventory',
            'App\Models\Price',
            'App\Models\Discount',
        ];

        foreach ($models as $modelClass) {
            $this->assertTrue(
                class_exists($modelClass),
                "Model class {$modelClass} should exist"
            );
        }
    }

    /**
     * Test that resource classes have required methods
     */
    public function test_core_resources_have_required_methods(): void
    {
        $resources = [
            'App\Filament\Resources\ProductResource',
            'App\Filament\Resources\CategoryResource',
            'App\Filament\Resources\BrandResource',
            'App\Filament\Resources\InventoryResource',
            'App\Filament\Resources\PriceResource',
            'App\Filament\Resources\DiscountResource',
        ];

        $requiredMethods = ['form', 'table', 'getPages', 'getModel'];

        foreach ($resources as $resourceClass) {
            foreach ($requiredMethods as $method) {
                $this->assertTrue(
                    method_exists($resourceClass, $method),
                    "Resource {$resourceClass} should have method {$method}"
                );
            }
        }
    }

    /**
     * Test that resources extend the base Resource class
     */
    public function test_core_resources_extend_base_resource(): void
    {
        $resources = [
            'App\Filament\Resources\ProductResource',
            'App\Filament\Resources\CategoryResource',
            'App\Filament\Resources\BrandResource',
            'App\Filament\Resources\InventoryResource',
            'App\Filament\Resources\PriceResource',
            'App\Filament\Resources\DiscountResource',
        ];

        foreach ($resources as $resourceClass) {
            $this->assertTrue(
                is_subclass_of($resourceClass, 'Filament\Resources\Resource'),
                "Resource {$resourceClass} should extend Filament\Resources\Resource"
            );
        }
    }

    /**
     * Test that models extend the base Model class
     */
    public function test_core_models_extend_base_model(): void
    {
        $models = [
            'App\Models\Product',
            'App\Models\Category',
            'App\Models\Brand',
            'App\Models\Inventory',
            'App\Models\Price',
            'App\Models\Discount',
        ];

        foreach ($models as $modelClass) {
            $this->assertTrue(
                is_subclass_of($modelClass, 'Illuminate\Database\Eloquent\Model'),
                "Model {$modelClass} should extend Illuminate\Database\Eloquent\Model"
            );
        }
    }
}
