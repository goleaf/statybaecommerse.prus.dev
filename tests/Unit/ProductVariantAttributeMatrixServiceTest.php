<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductVariantAttributeMatrixService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductVariantAttributeMatrixServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_creates_and_clears_pivot_relations(): void
    {
        $attribute = Attribute::factory()->create(['name' => 'Material', 'slug' => 'material-test']);
        $valueCotton = AttributeValue::factory()->for($attribute)->create(['value' => 'Cotton']);
        $valueSilk = AttributeValue::factory()->for($attribute)->create(['value' => 'Silk']);

        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'variant_attribute_matrix' => null]);

        ProductVariantAttributeMatrixService::sync($variant, ['attribute_'.$attribute->getKey() => $valueCotton->getKey()]);

        $this->assertDatabaseHas('product_variant_attributes', [
            'variant_id' => $variant->getKey(),
            'attribute_id' => $attribute->getKey(),
            'attribute_value_id' => $valueCotton->getKey(),
        ]);

        ProductVariantAttributeMatrixService::sync($variant->fresh(), ['attribute_'.$attribute->getKey() => $valueSilk->getKey()]);

        $this->assertDatabaseHas('product_variant_attributes', [
            'variant_id' => $variant->getKey(),
            'attribute_id' => $attribute->getKey(),
            'attribute_value_id' => $valueSilk->getKey(),
        ]);

        $this->assertDatabaseMissing('product_variant_attributes', [
            'variant_id' => $variant->getKey(),
            'attribute_id' => $attribute->getKey(),
            'attribute_value_id' => $valueCotton->getKey(),
        ]);

        ProductVariantAttributeMatrixService::sync($variant->fresh(), []);

        $this->assertDatabaseMissing('product_variant_attributes', [
            'variant_id' => $variant->getKey(),
            'attribute_id' => $attribute->getKey(),
        ]);
    }
}
