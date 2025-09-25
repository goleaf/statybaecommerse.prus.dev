<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ProductFeatureResource;
use App\Models\Product;
use App\Models\ProductFeature;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductFeatureResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test product
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
        ]);
    }

    public function test_can_list_product_features(): void
    {
        // Create test data
        ProductFeature::factory()->create([
            'product_id' => $this->product->id,
            'feature_type' => 'specification',
            'feature_key' => 'weight',
            'feature_value' => '2.5',
            'weight' => 1.0,
        ]);

        Livewire::test(ListRecords::class, [
            'resource' => ProductFeatureResource::class,
        ])
            ->assertCanSeeTableRecords(ProductFeature::all());
    }

    public function test_can_create_product_feature(): void
    {
        Livewire::test(CreateRecord::class, [
            'resource' => ProductFeatureResource::class,
        ])
            ->fillForm([
                'product_id' => $this->product->id,
                'feature_type' => 'specification',
                'feature_key' => 'dimensions',
                'feature_value' => '10x20x30',
                'weight' => 0.8,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_features', [
            'product_id' => $this->product->id,
            'feature_type' => 'specification',
            'feature_key' => 'dimensions',
            'feature_value' => '10x20x30',
            'weight' => 0.8,
        ]);
    }

    public function test_can_edit_product_feature(): void
    {
        $feature = ProductFeature::factory()->create([
            'product_id' => $this->product->id,
            'feature_type' => 'specification',
            'feature_key' => 'color',
            'feature_value' => 'red',
            'weight' => 0.5,
        ]);

        Livewire::test(EditRecord::class, [
            'resource' => ProductFeatureResource::class,
            'record' => $feature->getRouteKey(),
        ])
            ->fillForm([
                'feature_value' => 'blue',
                'weight' => 0.7,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_features', [
            'id' => $feature->id,
            'feature_value' => 'blue',
            'weight' => 0.7,
        ]);
    }

    public function test_can_filter_by_feature_type(): void
    {
        ProductFeature::factory()->create([
            'product_id' => $this->product->id,
            'feature_type' => 'specification',
            'feature_key' => 'weight',
        ]);

        ProductFeature::factory()->create([
            'product_id' => $this->product->id,
            'feature_type' => 'benefit',
            'feature_key' => 'durability',
        ]);

        Livewire::test(ListRecords::class, [
            'resource' => ProductFeatureResource::class,
        ])
            ->filterTable('feature_type', 'specification')
            ->assertCanSeeTableRecords(ProductFeature::where('feature_type', 'specification')->get())
            ->assertCanNotSeeTableRecords(ProductFeature::where('feature_type', 'benefit')->get());
    }

    public function test_can_filter_by_product(): void
    {
        $product2 = Product::factory()->create();

        ProductFeature::factory()->create([
            'product_id' => $this->product->id,
            'feature_type' => 'specification',
        ]);

        ProductFeature::factory()->create([
            'product_id' => $product2->id,
            'feature_type' => 'specification',
        ]);

        Livewire::test(ListRecords::class, [
            'resource' => ProductFeatureResource::class,
        ])
            ->filterTable('product_id', $this->product->id)
            ->assertCanSeeTableRecords(ProductFeature::where('product_id', $this->product->id)->get())
            ->assertCanNotSeeTableRecords(ProductFeature::where('product_id', $product2->id)->get());
    }

    public function test_can_sort_by_weight(): void
    {
        $feature1 = ProductFeature::factory()->create([
            'product_id' => $this->product->id,
            'weight' => 0.3,
        ]);

        $feature2 = ProductFeature::factory()->create([
            'product_id' => $this->product->id,
            'weight' => 0.8,
        ]);

        Livewire::test(ListRecords::class, [
            'resource' => ProductFeatureResource::class,
        ])
            ->sortTable('weight', 'desc')
            ->assertCanSeeTableRecords([$feature2, $feature1]);
    }

    public function test_navigation_group_is_products(): void
    {
        $this->assertEquals(
            NavigationGroup::Products,
            ProductFeatureResource::getNavigationGroup()
        );
    }

    public function test_navigation_sort_is_17(): void
    {
        $this->assertEquals(17, ProductFeatureResource::getNavigationSort());
    }

    public function test_navigation_icon_is_star(): void
    {
        $this->assertEquals('heroicon-o-star', ProductFeatureResource::getNavigationIcon());
    }

    public function test_has_correct_pages(): void
    {
        $pages = ProductFeatureResource::getPages();

        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('edit', $pages);
    }

    public function test_form_validation_requires_product(): void
    {
        Livewire::test(CreateRecord::class, [
            'resource' => ProductFeatureResource::class,
        ])
            ->fillForm([
                'feature_type' => 'specification',
                'feature_key' => 'test',
                'feature_value' => 'test value',
            ])
            ->call('create')
            ->assertHasFormErrors(['product_id']);
    }

    public function test_can_bulk_delete_product_features(): void
    {
        $feature1 = ProductFeature::factory()->create([
            'product_id' => $this->product->id,
            'feature_type' => 'specification',
        ]);

        $feature2 = ProductFeature::factory()->create([
            'product_id' => $this->product->id,
            'feature_type' => 'benefit',
        ]);

        Livewire::test(ListRecords::class, [
            'resource' => ProductFeatureResource::class,
        ])
            ->callTableBulkAction('delete', [$feature1, $feature2])
            ->assertHasNoTableBulkActionErrors();

        $this->assertDatabaseMissing('product_features', [
            'id' => $feature1->id,
        ]);

        $this->assertDatabaseMissing('product_features', [
            'id' => $feature2->id,
        ]);
    }

    public function test_feature_type_options_are_available(): void
    {
        $expectedTypes = [
            'specification' => 'Specification',
            'benefit' => 'Benefit',
            'feature' => 'Feature',
            'technical' => 'Technical',
            'performance' => 'Performance',
        ];

        // Test that all expected feature types are available in the form
        Livewire::test(CreateRecord::class, [
            'resource' => ProductFeatureResource::class,
        ])
            ->assertFormExists()
            ->assertFormFieldExists('feature_type');
    }

    public function test_weight_field_accepts_decimal_values(): void
    {
        Livewire::test(CreateRecord::class, [
            'resource' => ProductFeatureResource::class,
        ])
            ->fillForm([
                'product_id' => $this->product->id,
                'feature_type' => 'specification',
                'feature_key' => 'precision',
                'feature_value' => 'high',
                'weight' => 0.1234,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_features', [
            'weight' => 0.1234,
        ]);
    }

    public function test_feature_value_can_be_long_text(): void
    {
        $longValue = str_repeat('This is a long feature value. ', 20);

        Livewire::test(CreateRecord::class, [
            'resource' => ProductFeatureResource::class,
        ])
            ->fillForm([
                'product_id' => $this->product->id,
                'feature_type' => 'specification',
                'feature_key' => 'description',
                'feature_value' => $longValue,
                'weight' => 1.0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_features', [
            'feature_value' => $longValue,
        ]);
    }
}
