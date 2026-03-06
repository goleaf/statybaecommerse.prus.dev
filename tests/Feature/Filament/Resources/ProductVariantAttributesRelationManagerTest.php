<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ProductVariantResource\Pages\EditProductVariants;
use App\Filament\Resources\ProductVariantResource\RelationManagers\AttributesRelationManager;
use App\Filament\Resources\ProductVariantResource\Schemas\ProductVariantForm;
use App\Models\AdminUser;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductVariantAttributesRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    private ProductVariant $variant;

    private AttributeValue $attributeValue;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolveAdminPanel();

        Currency::factory()->create([
            'code'       => 'EUR',
            'is_default' => true,
            'is_active'  => true,
            'is_enabled' => true,
        ]);

        $admin = AdminUser::factory()->create();

        $product = Product::query()->create([
            'name'           => 'Variant Attribute Product',
            'slug'           => 'variant-attribute-product',
            'sku'            => 'VAR-ATTR-PRD-001',
            'price'          => 19.99,
            'manage_stock'   => false,
            'stock_quantity' => 0,
            'status'         => 'published',
            'is_enabled'     => true,
            'is_featured'    => false,
            'published_at'   => now(),
        ]);

        $this->variant = ProductVariant::query()->create([
            'product_id'     => $product->getKey(),
            'sku'            => 'VAR-ATTR-001',
            'name'           => 'Variant Attribute Entry',
            'price'          => 9.99,
            'cost_price'     => 3.99,
            'stock_quantity' => 0,
            'is_enabled'     => true,
        ]);

        $attribute = Attribute::withoutGlobalScopes()->create([
            'name'          => 'Color',
            'slug'          => 'color',
            'type'          => 'select',
            'is_filterable' => true,
            'is_searchable' => true,
            'is_visible'    => true,
            'is_editable'   => true,
            'is_sortable'   => true,
            'sort_order'    => 1,
            'is_enabled'    => true,
            'is_active'     => true,
        ]);

        $this->attributeValue = AttributeValue::withoutGlobalScopes()->create([
            'attribute_id' => $attribute->getKey(),
            'value'        => 'Red',
            'slug'         => 'red',
            'sort_order'   => 1,
            'is_enabled'   => true,
            'is_active'    => true,
        ]);

        $this->actingAs($admin, 'admin');
    }

    public function test_product_variant_edit_attributes_relation_page_does_not_return_server_error(): void
    {
        $response = $this->get("/admin/product-variants/{$this->variant->getRouteKey()}/edit?relation=3");

        $this->assertLessThan(500, $response->status());
    }

    public function test_can_attach_attribute_value_to_variant_with_attribute_id_pivot_data(): void
    {
        Livewire::test(AttributesRelationManager::class, [
            'ownerRecord' => $this->variant,
            'pageClass'   => EditProductVariants::class,
        ])
            ->mountTableAction('attach')
            ->set('mountedActions.0.data.recordId', $this->attributeValue->getKey())
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('product_variant_attributes', [
            'variant_id'         => $this->variant->getKey(),
            'attribute_id'       => $this->attributeValue->attribute_id,
            'attribute_value_id' => $this->attributeValue->getKey(),
        ]);
    }

    public function test_can_manage_variant_attributes_directly_from_edit_form_repeater(): void
    {
        $sizeAttribute = Attribute::withoutGlobalScopes()->create([
            'name'          => 'Size',
            'slug'          => 'size',
            'type'          => 'select',
            'is_filterable' => true,
            'is_searchable' => true,
            'is_visible'    => true,
            'is_editable'   => true,
            'is_sortable'   => true,
            'sort_order'    => 2,
            'is_enabled'    => true,
            'is_active'     => true,
        ]);

        $sizeValue = AttributeValue::withoutGlobalScopes()->create([
            'attribute_id' => $sizeAttribute->getKey(),
            'value'        => 'XL',
            'slug'         => 'xl',
            'sort_order'   => 1,
            'is_enabled'   => true,
            'is_active'    => true,
        ]);

        Livewire::test(EditProductVariants::class, ['record' => $this->variant->getRouteKey()])
            ->fillForm([
                ProductVariantForm::ATTRIBUTE_SELECTIONS_FIELD => [
                    [
                        'attribute_id'       => $this->attributeValue->attribute_id,
                        'attribute_value_id' => $this->attributeValue->getKey(),
                    ],
                    [
                        'attribute_id'       => $sizeAttribute->getKey(),
                        'attribute_value_id' => $sizeValue->getKey(),
                    ],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_variant_attributes', [
            'variant_id'         => $this->variant->getKey(),
            'attribute_id'       => $this->attributeValue->attribute_id,
            'attribute_value_id' => $this->attributeValue->getKey(),
        ]);

        $this->assertDatabaseHas('product_variant_attributes', [
            'variant_id'         => $this->variant->getKey(),
            'attribute_id'       => $sizeAttribute->getKey(),
            'attribute_value_id' => $sizeValue->getKey(),
        ]);

        $freshVariant = ProductVariant::query()
            ->withoutGlobalScopes()
            ->findOrFail($this->variant->getKey());

        self::assertSame([
            'attribute_' . $this->attributeValue->attribute_id => $this->attributeValue->getKey(),
            'attribute_' . $sizeAttribute->getKey()            => $sizeValue->getKey(),
        ], $freshVariant->variant_attribute_matrix);

        self::assertSame([
            'Color' => 'Red',
            'Size'  => 'XL',
        ], $freshVariant->attributes);
    }
}
