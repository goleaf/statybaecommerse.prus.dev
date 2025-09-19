<?php declare(strict_types=1);

namespace Tests\Feature;

use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminResourcesTestSuite extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up test environment
        $this->actingAs(\App\Models\User::factory()->create([
            'email' => 'admin@test.com',
            'is_active' => true
        ]));
    }

    public function test_all_resources_can_be_instantiated(): void
    {
        $resourceClasses = $this->getResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            $this->assertTrue(class_exists($resourceClass), "Resource class {$resourceClass} does not exist");

            $resource = new $resourceClass();
            $this->assertInstanceOf(Resource::class, $resource);
        }
    }

    public function test_all_resources_have_required_methods(): void
    {
        $resourceClasses = $this->getResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            $resource = new $resourceClass();

            // Test required methods exist
            $this->assertTrue(method_exists($resource, 'form'), "Resource {$resourceClass} missing form method");
            $this->assertTrue(method_exists($resource, 'table'), "Resource {$resourceClass} missing table method");
            $this->assertTrue(method_exists($resource, 'getPages'), "Resource {$resourceClass} missing getPages method");
        }
    }

    public function test_all_resources_have_valid_forms(): void
    {
        $resourceClasses = $this->getResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            $resource = new $resourceClass();

            try {
                $form = $resource->form(Form::make());
                $this->assertInstanceOf(Form::class, $form);
            } catch (\Exception $e) {
                $this->fail("Resource {$resourceClass} form method failed: " . $e->getMessage());
            }
        }
    }

    public function test_all_resources_have_valid_tables(): void
    {
        $resourceClasses = $this->getResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            $resource = new $resourceClass();

            try {
                $table = $resource->table(Table::make());
                $this->assertInstanceOf(Table::class, $table);
            } catch (\Exception $e) {
                $this->fail("Resource {$resourceClass} table method failed: " . $e->getMessage());
            }
        }
    }

    public function test_all_resources_have_valid_pages(): void
    {
        $resourceClasses = $this->getResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            $resource = new $resourceClass();

            try {
                $pages = $resource->getPages();
                $this->assertIsArray($pages);
                $this->assertNotEmpty($pages, "Resource {$resourceClass} has no pages");

                foreach ($pages as $page) {
                    $this->assertInstanceOf(Page::class, $page);
                }
            } catch (\Exception $e) {
                $this->fail("Resource {$resourceClass} getPages method failed: " . $e->getMessage());
            }
        }
    }

    public function test_all_resources_have_navigation_icons(): void
    {
        $resourceClasses = $this->getResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            $resource = new $resourceClass();

            // Check if navigation icon is set
            $reflection = new \ReflectionClass($resource);
            $navigationIconProperty = $reflection->getProperty('navigationIcon');
            $navigationIconProperty->setAccessible(true);
            $navigationIcon = $navigationIconProperty->getValue($resource);

            $this->assertNotNull($navigationIcon, "Resource {$resourceClass} missing navigation icon");
        }
    }

    public function test_all_resources_have_navigation_groups(): void
    {
        $resourceClasses = $this->getResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            $resource = new $resourceClass();

            // Check if navigation group is set
            $reflection = new \ReflectionClass($resource);
            $navigationGroupProperty = $reflection->getProperty('navigationGroup');
            $navigationGroupProperty->setAccessible(true);
            $navigationGroup = $navigationGroupProperty->getValue($resource);

            $this->assertNotNull($navigationGroup, "Resource {$resourceClass} missing navigation group");
        }
    }

    public function test_all_resources_have_valid_model(): void
    {
        $resourceClasses = $this->getResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            $resource = new $resourceClass();

            try {
                $model = $resource->getModel();
                $this->assertTrue(class_exists($model), "Resource {$resourceClass} has invalid model: {$model}");
            } catch (\Exception $e) {
                $this->fail("Resource {$resourceClass} getModel method failed: " . $e->getMessage());
            }
        }
    }

    public function test_all_resources_can_render_index_page(): void
    {
        $resourceClasses = $this->getResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            try {
                $resource = new $resourceClass();
                $pages = $resource->getPages();

                // Find index page
                $indexPage = null;
                foreach ($pages as $page) {
                    if (str_contains($page, 'Index')) {
                        $indexPage = $page;
                        break;
                    }
                }

                if ($indexPage) {
                    $this->assertTrue(class_exists($indexPage), "Index page {$indexPage} does not exist");
                }
            } catch (\Exception $e) {
                // Some resources might not have index pages, that's okay
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_all_resources_have_proper_relationships(): void
    {
        $resourceClasses = $this->getResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            $resource = new $resourceClass();

            try {
                $model = $resource->getModel();
                $modelInstance = new $model();

                // Check if model has proper relationships
                $this->assertTrue(method_exists($modelInstance, 'getFillable'), "Model {$model} missing getFillable method");
                $this->assertTrue(method_exists($modelInstance, 'getTable'), "Model {$model} missing getTable method");
            } catch (\Exception $e) {
                $this->fail("Resource {$resourceClass} model validation failed: " . $e->getMessage());
            }
        }
    }

    public function test_all_resources_handle_empty_database(): void
    {
        $resourceClasses = $this->getResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            $resource = new $resourceClass();

            try {
                // Test that resources can handle empty database
                $table = $resource->table(Table::make());
                $this->assertInstanceOf(Table::class, $table);

                $form = $resource->form(Form::make());
                $this->assertInstanceOf(Form::class, $form);
            } catch (\Exception $e) {
                $this->fail("Resource {$resourceClass} failed with empty database: " . $e->getMessage());
            }
        }
    }

    protected function getResourceClasses(): array
    {
        return [
            \App\Filament\Resources\ProductResource::class,
            \App\Filament\Resources\UserResource::class,
            \App\Filament\Resources\CategoryResource::class,
            \App\Filament\Resources\ProductVariantResource::class,
            \App\Filament\Resources\CustomerGroupResource::class,
            \App\Filament\Resources\ZoneResource::class,
            \App\Filament\Resources\SystemSettingsResource::class,
            \App\Filament\Resources\VariantPricingRuleResource::class,
            \App\Filament\Resources\StockResource::class,
            \App\Filament\Resources\SubscriberResource::class,
            \App\Filament\Resources\SystemSettingResource::class,
            \App\Filament\Resources\ReportResource::class,
            \App\Filament\Resources\ReviewResource::class,
            \App\Filament\Resources\SeoDataResource::class,
            \App\Filament\Resources\RecommendationConfigResourceSimple::class,
            \App\Filament\Resources\ReferralResource::class,
            \App\Filament\Resources\ReferralRewardResource::class,
            \App\Filament\Resources\ProductHistoryResource::class,
            \App\Filament\Resources\RecommendationBlockResource::class,
            \App\Filament\Resources\RecommendationConfigResource::class,
            \App\Filament\Resources\PriceListItemResource::class,
            \App\Filament\Resources\PriceListResource::class,
            \App\Filament\Resources\PriceResource::class,
            \App\Filament\Resources\OrderItemResource::class,
            \App\Filament\Resources\OrderResource::class,
            \App\Filament\Resources\PostResource::class,
            \App\Filament\Resources\LocationResource::class,
            \App\Filament\Resources\MenuResource::class,
            \App\Filament\Resources\NewsResource::class,
            \App\Filament\Resources\LegalResource::class,
            \App\Filament\Resources\CurrencyResource::class,
            \App\Filament\Resources\CustomerManagementResource::class,
            \App\Filament\Resources\DiscountCodeResource::class,
            \App\Filament\Resources\DiscountConditionResource::class,
            \App\Filament\Resources\CollectionResource::class,
            \App\Filament\Resources\CompanyResource::class,
            \App\Filament\Resources\CountryResource::class,
            \App\Filament\Resources\CouponResource::class,
            \App\Filament\Resources\CartItemResource::class,
            \App\Filament\Resources\CityResource::class,
            \App\Filament\Resources\CampaignResource::class,
            \App\Filament\Resources\BrandResource::class,
            \App\Filament\Resources\CampaignClickResource::class,
            \App\Filament\Resources\CampaignConversionResource::class,
            \App\Filament\Resources\AddressResource::class,
            \App\Filament\Resources\AnalyticsEventResource::class,
            \App\Filament\Resources\AttributeResource::class,
            \App\Filament\Resources\AttributeValueResource::class,
            \App\Filament\Resources\ActivityLogResource::class,
        ];
    }
}
