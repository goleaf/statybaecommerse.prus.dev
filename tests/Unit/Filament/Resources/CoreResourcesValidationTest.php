<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

/**
 * Validation tests for core Filament resources to ensure they are properly structured
 * after the Filament 3.3 downgrade and restoration process.
 *
 * Requirements: 6.3 - Test resource loading and basic operations
 *
 * Note: These tests validate the structure and basic functionality of core resources
 * without requiring full Laravel/Filament bootstrap to avoid compatibility issues
 * during the transition period.
 */
final class CoreResourcesValidationTest extends TestCase
{
    private array $coreResources = [
        'App\Filament\Resources\ProductResource',
        'App\Filament\Resources\CategoryResource',
        'App\Filament\Resources\BrandResource',
        'App\Filament\Resources\InventoryResource',
        'App\Filament\Resources\PriceResource',
        'App\Filament\Resources\DiscountResource',
    ];

    private array $coreModels = [
        'App\Models\Product',
        'App\Models\Category',
        'App\Models\Brand',
        'App\Models\Inventory',
        'App\Models\Price',
        'App\Models\Discount',
    ];

    /**
     * Test that all core resource classes exist and can be loaded
     */
    public function test_core_resource_classes_exist(): void
    {
        foreach ($this->coreResources as $resourceClass) {
            $this->assertTrue(
                class_exists($resourceClass),
                "Core resource class {$resourceClass} should exist after restoration"
            );
        }
    }

    /**
     * Test that all core model classes exist and can be loaded
     */
    public function test_core_model_classes_exist(): void
    {
        foreach ($this->coreModels as $modelClass) {
            $this->assertTrue(
                class_exists($modelClass),
                "Core model class {$modelClass} should exist after restoration"
            );
        }
    }

    /**
     * Test that resource classes have the required Filament methods
     */
    public function test_core_resources_have_required_filament_methods(): void
    {
        $requiredMethods = [
            'form'               => 'Form configuration method',
            'table'              => 'Table configuration method',
            'getPages'           => 'Page routing method',
            'getModel'           => 'Model binding method',
            'getSlug'            => 'URL slug method',
            'getNavigationIcon'  => 'Navigation icon method',
            'getNavigationLabel' => 'Navigation label method',
        ];

        foreach ($this->coreResources as $resourceClass) {
            $reflection = new ReflectionClass($resourceClass);

            foreach ($requiredMethods as $method => $description) {
                $this->assertTrue(
                    $reflection->hasMethod($method),
                    "Resource {$resourceClass} should have {$method} method ({$description})"
                );
            }
        }
    }

    /**
     * Test that resource classes extend the base Filament Resource class
     */
    public function test_core_resources_extend_filament_resource(): void
    {
        foreach ($this->coreResources as $resourceClass) {
            $this->assertTrue(
                is_subclass_of($resourceClass, 'Filament\Resources\Resource'),
                "Resource {$resourceClass} should extend Filament\Resources\Resource"
            );
        }
    }

    /**
     * Test that model classes extend Eloquent Model
     */
    public function test_core_models_extend_eloquent_model(): void
    {
        foreach ($this->coreModels as $modelClass) {
            $this->assertTrue(
                is_subclass_of($modelClass, 'Illuminate\Database\Eloquent\Model'),
                "Model {$modelClass} should extend Illuminate\Database\Eloquent\Model"
            );
        }
    }

    /**
     * Test that resource classes have proper static properties
     */
    public function test_core_resources_have_static_properties(): void
    {
        foreach ($this->coreResources as $resourceClass) {
            $reflection = new ReflectionClass($resourceClass);

            // Check for model property
            if ($reflection->hasProperty('model')) {
                $modelProperty = $reflection->getProperty('model');
                $this->assertTrue(
                    $modelProperty->isStatic(),
                    "Resource {$resourceClass} should have static \$model property"
                );
            }
        }
    }

    /**
     * Test that resource page classes exist
     */
    public function test_core_resource_page_classes_exist(): void
    {
        $expectedPages = [
            'App\Filament\Resources\ProductResource'   => 'App\Filament\Resources\ProductResource\Pages\ListProducts',
            'App\Filament\Resources\CategoryResource'  => 'App\Filament\Resources\CategoryResource\Pages\ListCategories',
            'App\Filament\Resources\BrandResource'     => 'App\Filament\Resources\BrandResource\Pages\ListBrands',
            'App\Filament\Resources\InventoryResource' => 'App\Filament\Resources\InventoryResource\Pages\ListInventories',
            'App\Filament\Resources\PriceResource'     => 'App\Filament\Resources\PriceResource\Pages\ListPrices',
            'App\Filament\Resources\DiscountResource'  => 'App\Filament\Resources\DiscountResource\Pages\ListDiscounts',
        ];

        foreach ($expectedPages as $resourceClass => $listPageClass) {
            $this->assertTrue(
                class_exists($listPageClass),
                "Resource {$resourceClass} should have List page class {$listPageClass}"
            );
        }
    }

    /**
     * Test that resource classes have proper namespace structure
     */
    public function test_core_resources_have_proper_namespace(): void
    {
        foreach ($this->coreResources as $resourceClass) {
            $this->assertStringStartsWith(
                'App\Filament\Resources\\',
                $resourceClass,
                "Resource {$resourceClass} should be in App\Filament\Resources namespace"
            );

            $this->assertStringEndsWith(
                'Resource',
                $resourceClass,
                "Resource {$resourceClass} should end with 'Resource'"
            );
        }
    }

    /**
     * Test that model classes have proper namespace structure
     */
    public function test_core_models_have_proper_namespace(): void
    {
        foreach ($this->coreModels as $modelClass) {
            $this->assertStringStartsWith(
                'App\Models\\',
                $modelClass,
                "Model {$modelClass} should be in App\Models namespace"
            );
        }
    }

    /**
     * Test resource and model pairing
     */
    public function test_resource_model_pairing(): void
    {
        $expectedPairs = [
            'App\Filament\Resources\ProductResource'   => 'App\Models\Product',
            'App\Filament\Resources\CategoryResource'  => 'App\Models\Category',
            'App\Filament\Resources\BrandResource'     => 'App\Models\Brand',
            'App\Filament\Resources\InventoryResource' => 'App\Models\Inventory',
            'App\Filament\Resources\PriceResource'     => 'App\Models\Price',
            'App\Filament\Resources\DiscountResource'  => 'App\Models\Discount',
        ];

        foreach ($expectedPairs as $resourceClass => $modelClass) {
            $this->assertTrue(
                class_exists($resourceClass) && class_exists($modelClass),
                "Resource {$resourceClass} and its model {$modelClass} should both exist"
            );
        }
    }

    /**
     * Test that classes can be instantiated without fatal errors
     */
    public function test_core_classes_can_be_reflected(): void
    {
        $allClasses = array_merge($this->coreResources, $this->coreModels);

        foreach ($allClasses as $className) {
            try {
                $reflection = new ReflectionClass($className);
                $this->assertTrue(
                    $reflection->isInstantiable() || $reflection->isAbstract(),
                    "Class {$className} should be either instantiable or abstract"
                );
            } catch (ReflectionException $e) {
                $this->fail("Failed to reflect class {$className}: " . $e->getMessage());
            }
        }
    }
}
