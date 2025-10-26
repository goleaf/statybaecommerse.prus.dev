<?php

declare(strict_types=1);

namespace Tests\Admin\Resources;

use App\Filament\Resources\ProductVariantResource;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductVariantResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
            'name'  => 'Variant Admin',
        ]));
    }

    public function test_product_variant_resource_can_create_variant_with_metadata(): void
    {
        $product = Product::factory()->create();

        $metadata = [
            'color' => 'Red',
            'size'  => 'M',
        ];

        Livewire::test(ProductVariantResource\Pages\CreateProductVariant::class)
            ->fillForm([
                'product_id'       => $product->id,
                'sku'              => 'PV-001',
                'name'             => 'Variant One',
                'price'            => '199.99',
                'variant_metadata' => $metadata,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $variant = ProductVariant::where('sku', 'PV-001')->first();

        $this->assertNotNull($variant);
        $this->assertSame($metadata, $variant->variant_metadata);
    }

    public function test_product_variant_resource_can_update_variant_metadata(): void
    {
        $variant = ProductVariant::factory()->create([
            'variant_metadata' => ['initial' => 'value'],
        ]);

        $metadata = [
            'material' => 'Cotton',
            'color'    => 'Blue',
        ];

        Livewire::test(ProductVariantResource\Pages\EditProductVariant::class, [
            'record' => $variant->getRouteKey(),
        ])
            ->fillForm([
                'variant_metadata' => $metadata,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $variant->refresh();

        $this->assertSame($metadata, $variant->variant_metadata);
    }

    public function test_product_variant_matrix_persists_and_syncs_on_create(): void
    {
        $attribute = Attribute::factory()->create([
            'name' => 'Size',
            'slug' => 'size-test',
        ]);

        $valueSmall = AttributeValue::factory()->for($attribute)->create(['value' => 'S']);
        $valueMedium = AttributeValue::factory()->for($attribute)->create(['value' => 'M']);

        $product = Product::factory()->create();
        $product->attributes()->attach($attribute->getKey(), ['attribute_value_id' => $valueSmall->getKey()]);

        Livewire::test(ProductVariantResource\Pages\CreateProductVariant::class)
            ->fillForm([
                'product_id'               => $product->id,
                'sku'                      => 'PV-MATRIX-001',
                'name'                     => 'Matrix Variant',
                'price'                    => '49.99',
                'variant_attribute_matrix' => [
                    'attribute_' . $attribute->getKey() => (string) $valueMedium->getKey(),
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $variant = ProductVariant::where('sku', 'PV-MATRIX-001')->firstOrFail();

        $expectedMatrix = ['attribute_' . $attribute->getKey() => (string) $valueMedium->getKey()];

        $this->assertSame($expectedMatrix, $variant->variant_attribute_matrix);
        $this->assertDatabaseHas('product_variant_attributes', [
            'variant_id'         => $variant->getKey(),
            'attribute_id'       => $attribute->getKey(),
            'attribute_value_id' => $valueMedium->getKey(),
        ]);
    }

    public function test_product_variant_matrix_update_resyncs_relations(): void
    {
        $attribute = Attribute::factory()->create([
            'name' => 'Color',
            'slug' => 'color-test',
        ]);

        $valueRed = AttributeValue::factory()->for($attribute)->create(['value' => 'Red']);
        $valueBlue = AttributeValue::factory()->for($attribute)->create(['value' => 'Blue']);

        $product = Product::factory()->create();
        $product->attributes()->attach($attribute->getKey(), ['attribute_value_id' => $valueRed->getKey()]);

        $variant = ProductVariant::factory()->create([
            'product_id'               => $product->id,
            'variant_attribute_matrix' => ['attribute_' . $attribute->getKey() => $valueRed->getKey()],
        ]);

        $this->assertDatabaseHas('product_variant_attributes', [
            'variant_id'         => $variant->getKey(),
            'attribute_id'       => $attribute->getKey(),
            'attribute_value_id' => $valueRed->getKey(),
        ]);

        Livewire::test(ProductVariantResource\Pages\EditProductVariant::class, [
            'record' => $variant->getRouteKey(),
        ])
            ->fillForm([
                'variant_attribute_matrix' => [
                    'attribute_' . $attribute->getKey() => (string) $valueBlue->getKey(),
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $variant->refresh();

        $this->assertSame([
            'attribute_' . $attribute->getKey() => (string) $valueBlue->getKey(),
        ], $variant->variant_attribute_matrix);

        $this->assertDatabaseHas('product_variant_attributes', [
            'variant_id'         => $variant->getKey(),
            'attribute_id'       => $attribute->getKey(),
            'attribute_value_id' => $valueBlue->getKey(),
        ]);

        $this->assertDatabaseMissing('product_variant_attributes', [
            'variant_id'         => $variant->getKey(),
            'attribute_id'       => $attribute->getKey(),
            'attribute_value_id' => $valueRed->getKey(),
        ]);
    }
}
