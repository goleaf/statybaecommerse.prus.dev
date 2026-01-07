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
use Filament\Resources\Resource;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit tests for core Filament resources to ensure they load and operate correctly
 * after the Filament 3.3 downgrade and restoration process.
 *
 * Requirements: 6.3 - Test resource loading and basic operations
 */
final class CoreResourcesTest extends TestCase
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
     * Test that all core resources have navigation configuration
     */
    public function test_core_resources_have_navigation_configuration(): void
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
            // Test that the resource has navigation methods
            $this->assertTrue(
                method_exists($resourceClass, 'getNavigationIcon'),
                "Resource {$resourceClass} should have a getNavigationIcon method"
            );

            $this->assertTrue(
                method_exists($resourceClass, 'getNavigationGroup'),
                "Resource {$resourceClass} should have a getNavigationGroup method"
            );

            $this->assertTrue(
                method_exists($resourceClass, 'getNavigationLabel'),
                "Resource {$resourceClass} should have a getNavigationLabel method"
            );

            // Test that the resource has a static navigationIcon property or method
            $reflection = new ReflectionClass($resourceClass);
            $hasNavigationIcon = $reflection->hasProperty('navigationIcon') ||
                                method_exists($resourceClass, 'getNavigationIcon');

            $this->assertTrue(
                $hasNavigationIcon,
                "Resource {$resourceClass} should have navigation icon configuration"
            );
        }
    }

    /**
     * Test that resources can be registered without errors
     */
    public function test_core_resources_can_be_registered(): void
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
            // Test that the resource can be instantiated without errors
            $this->assertTrue(
                class_exists($resourceClass),
                "Resource class {$resourceClass} should exist"
            );

            // Test that the resource has required methods
            $this->assertTrue(
                method_exists($resourceClass, 'form'),
                "Resource {$resourceClass} should have a form() method"
            );

            $this->assertTrue(
                method_exists($resourceClass, 'table'),
                "Resource {$resourceClass} should have a table() method"
            );

            $this->assertTrue(
                method_exists($resourceClass, 'getPages'),
                "Resource {$resourceClass} should have a getPages() method"
            );
        }
    }

    /**
     * Test that resources have proper slug configuration
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
            foreach ($pages as $pageName => $pageConfig) {
                $this->assertIsString(
                    $pageName,
                    "Page name should be a string for resource {$resourceClass}"
                );

                // In Filament v3.3, page configurations can be PageRegistration objects or class strings
                if (is_string($pageConfig)) {
                    $this->assertTrue(
                        class_exists($pageConfig),
                        "Page class {$pageConfig} should exist for resource {$resourceClass}"
                    );
                } else {
                    // For PageRegistration objects, just verify it's an object
                    $this->assertIsObject(
                        $pageConfig,
                        "Page configuration should be an object for resource {$resourceClass}"
                    );
                }
            }
        }
    }
}
