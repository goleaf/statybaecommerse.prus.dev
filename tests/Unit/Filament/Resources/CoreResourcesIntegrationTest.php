<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources;

use App\Filament\Resources\BrandResource;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\DiscountResource;
use App\Filament\Resources\InventoryResource;
use App\Filament\Resources\PriceResource;
use App\Filament\Resources\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Inventory;
use App\Models\Price;
use App\Models\Product;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Tests\TestCase;

/**
 * Integration tests for core Filament resources to ensure they work correctly
 * after the Filament 3.3 downgrade and restoration process.
 *
 * Requirements: 6.3 - Test resource loading and basic operations
 */
final class CoreResourcesIntegrationTest extends TestCase
{
    /**
     * Test that all core resources have correct model bindings
     */
    public function test_core_resources_have_correct_model_bindings(): void
    {
        $resources = [
            ProductResource::class   => Product::class,
            CategoryResource::class  => Category::class,
            BrandResource::class     => Brand::class,
            InventoryResource::class => Inventory::class,
            PriceResource::class     => Price::class,
            DiscountResource::class  => Discount::class,
        ];

        foreach ($resources as $resourceClass => $expectedModelClass) {
            $this->assertSame(
                $expectedModelClass,
                $resourceClass::getModel(),
                "Resource {$resourceClass} should be bound to model {$expectedModelClass}"
            );
        }
    }

    /**
     * Test that all core resources extend the base Resource class
     */
    public function test_core_resources_extend_base_resource_class(): void
    {
        $resources = [
            ProductResource::class,
            CategoryResource::class,
            BrandResource::class,
            InventoryResource::class,
            PriceResource::class,
            DiscountResource::class,
        ];

        foreach ($resources as $resourceClass) {
            $this->assertTrue(
                is_subclass_of($resourceClass, Resource::class),
                "Resource {$resourceClass} should extend the base Resource class"
            );
        }
    }

    /**
     * Test that all core resources have proper slug configuration
     */
    public function test_core_resources_have_proper_slug_configuration(): void
    {
        $resources = [
            ProductResource::class,
            CategoryResource::class,
            BrandResource::class,
            InventoryResource::class,
            PriceResource::class,
            DiscountResource::class,
        ];

        foreach ($resources as $resourceClass) {
            $slug = $resourceClass::getSlug();

            $this->assertIsString(
                $slug,
                "Resource {$resourceClass} should have a string slug"
            );

            $this->assertNotEmpty(
                $slug,
                "Resource {$resourceClass} should have a non-empty slug"
            );

            // Test that slug follows expected format (lowercase, hyphenated)
            $this->assertMatchesRegularExpression(
                '/^[a-z0-9\-]+$/',
                $slug,
                "Resource {$resourceClass} slug should be lowercase and hyphenated"
            );
        }
    }

    /**
     * Test that core resources have proper page configurations
     */
    public function test_core_resources_have_page_configurations(): void
    {
        $resources = [
            ProductResource::class,
            CategoryResource::class,
            BrandResource::class,
            InventoryResource::class,
            PriceResource::class,
            DiscountResource::class,
        ];

        foreach ($resources as $resourceClass) {
            $pages = $resourceClass::getPages();

            $this->assertIsArray(
                $pages,
                "Resource {$resourceClass} should return an array of pages"
            );

            $this->assertNotEmpty(
                $pages,
                "Resource {$resourceClass} should have at least one page configured"
            );

            // Check that each page configuration is valid
            foreach ($pages as $pageName => $pageClass) {
                $this->assertIsString(
                    $pageName,
                    "Page name should be a string for resource {$resourceClass}"
                );

                if ($pageClass instanceof PageRegistration) {
                    $pageClass = $pageClass->getPage();
                }

                $this->assertIsString(
                    $pageClass,
                    "Page registration should resolve to a page class string for resource {$resourceClass}"
                );

                $this->assertTrue(
                    class_exists($pageClass),
                    "Page class {$pageClass} should exist for resource {$resourceClass}"
                );
            }
        }
    }

    /**
     * Test that resources have required methods for Filament 3.3 compatibility
     */
    public function test_core_resources_have_required_methods(): void
    {
        $resources = [
            ProductResource::class,
            CategoryResource::class,
            BrandResource::class,
            InventoryResource::class,
            PriceResource::class,
            DiscountResource::class,
        ];

        $requiredMethods = [
            'form',
            'table',
            'getPages',
            'getModel',
            'getSlug',
            'getNavigationIcon',
            'getNavigationLabel',
            'getNavigationGroup',
        ];

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
     * Test that resources can provide navigation configuration
     */
    public function test_core_resources_provide_navigation_configuration(): void
    {
        $resources = [
            ProductResource::class,
            CategoryResource::class,
            BrandResource::class,
            InventoryResource::class,
            PriceResource::class,
            DiscountResource::class,
        ];

        foreach ($resources as $resourceClass) {
            // Test navigation icon
            $icon = $resourceClass::getNavigationIcon();
            $this->assertTrue(
                is_string($icon) || $icon === null,
                "Resource {$resourceClass} navigation icon should be string or null"
            );

            // Test navigation label
            $label = $resourceClass::getNavigationLabel();
            $this->assertTrue(
                is_string($label) || $label === null,
                "Resource {$resourceClass} navigation label should be string or null"
            );

            // Test navigation group
            $group = $resourceClass::getNavigationGroup();
            $this->assertTrue(
                is_string($group) || $group === null,
                "Resource {$resourceClass} navigation group should be string or null"
            );
        }
    }

    /**
     * Test that models have proper database table configuration
     */
    public function test_core_models_have_proper_table_configuration(): void
    {
        $models = [
            Product::class   => 'products',
            Category::class  => 'categories',
            Brand::class     => 'brands',
            Inventory::class => 'inventories',
            Price::class     => 'prices',
            Discount::class  => 'discounts',
        ];

        foreach ($models as $modelClass => $expectedTable) {
            $model = new $modelClass;
            $this->assertSame(
                $expectedTable,
                $model->getTable(),
                "Model {$modelClass} should use table {$expectedTable}"
            );
        }
    }

    /**
     * Test that models extend Eloquent Model
     */
    public function test_core_models_extend_eloquent_model(): void
    {
        $models = [
            Product::class,
            Category::class,
            Brand::class,
            Inventory::class,
            Price::class,
            Discount::class,
        ];

        foreach ($models as $modelClass) {
            $this->assertTrue(
                is_subclass_of($modelClass, \Illuminate\Database\Eloquent\Model::class),
                "Model {$modelClass} should extend Illuminate\\Database\\Eloquent\\Model"
            );
        }
    }
}
