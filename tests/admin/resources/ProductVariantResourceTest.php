<?php

declare(strict_types=1);

namespace Tests\Admin\Resources;

use App\Filament\Resources\ProductVariantResource;
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
            'name' => 'Variant Admin',
        ]));
    }

    public function test_product_variant_resource_can_create_variant_with_metadata(): void
    {
        $product = Product::factory()->create();

        $metadata = [
            'color' => 'Red',
            'size' => 'M',
        ];

        Livewire::test(ProductVariantResource\Pages\CreateProductVariant::class)
            ->fillForm([
                'product_id' => $product->id,
                'sku' => 'PV-001',
                'name' => 'Variant One',
                'price' => '199.99',
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
            'color' => 'Blue',
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
}
