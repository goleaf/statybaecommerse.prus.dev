<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ProductVariantResource\Pages\EditProductVariants;
use App\Filament\Resources\ProductVariantResource\RelationManagers\ImagesRelationManager;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\VariantImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductVariantImagesRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolveAdminPanel();

        Storage::fake('public');

        Currency::factory()->create([
            'code'       => 'EUR',
            'is_default' => true,
            'is_active'  => true,
            'is_enabled' => true,
        ]);

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $product = Product::query()->create([
            'name'           => 'Variant Image Product',
            'slug'           => 'variant-image-product',
            'sku'            => 'VAR-IMG-PRD-001',
            'price'          => 39.99,
            'manage_stock'   => false,
            'stock_quantity' => 0,
            'status'         => 'published',
            'is_enabled'     => true,
            'is_featured'    => false,
            'published_at'   => now(),
        ]);

        $this->variant = ProductVariant::query()->create([
            'product_id'     => $product->getKey(),
            'sku'            => 'VAR-IMG-001',
            'name'           => 'Variant Image Entry',
            'price'          => 19.99,
            'cost_price'     => 9.99,
            'stock_quantity' => 0,
            'is_enabled'     => true,
        ]);

        $this->actingAs($admin);
    }

    public function test_can_create_variant_image_from_relation_manager(): void
    {
        Storage::disk('public')->put('variant-images/relation-image.jpg', 'binary-content');

        Livewire::test(ImagesRelationManager::class, [
            'ownerRecord' => $this->variant,
            'pageClass'   => EditProductVariants::class,
        ])
            ->mountTableAction('create')
            ->set('mountedActions.0.data.image_path', ['variant-images/relation-image.jpg'])
            ->set('mountedActions.0.data.alt_text', 'Variant relation image')
            ->set('mountedActions.0.data.sort_order', 1)
            ->set('mountedActions.0.data.is_primary', true)
            ->set('mountedActions.0.data.is_active', true)
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $image = VariantImage::query()
            ->where('variant_id', $this->variant->getKey())
            ->latest('id')
            ->first();

        $this->assertNotNull($image);
        $this->assertSame('Variant relation image', $image->alt_text);
        $this->assertSame('variant-images/relation-image.jpg', $image->image_path);
    }

    public function test_product_variant_edit_images_relation_page_does_not_return_server_error(): void
    {
        VariantImage::query()->create([
            'variant_id' => $this->variant->getKey(),
            'image_path' => 'variant-images/existing-image.jpg',
            'alt_text'   => 'Existing Variant Image',
            'sort_order' => 1,
            'is_primary' => true,
            'is_active'  => true,
        ]);

        $response = $this->get("/admin/product-variants/{$this->variant->getRouteKey()}/edit?relation=4");

        $this->assertLessThan(500, $response->status());
    }
}
