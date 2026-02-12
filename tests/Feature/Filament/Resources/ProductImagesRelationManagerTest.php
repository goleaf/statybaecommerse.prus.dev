<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\RelationManagers\ImagesRelationManager;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductImagesRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $product;

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

        $this->admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->product = Product::query()->create([
            'name'           => 'Relation Manager Product',
            'slug'           => 'relation-manager-product',
            'sku'            => 'REL-IMG-001',
            'price'          => 99.99,
            'manage_stock'   => false,
            'stock_quantity' => 0,
            'status'         => 'published',
            'is_enabled'     => true,
            'is_featured'    => false,
            'published_at'   => now(),
        ]);

        $this->actingAs($this->admin);
    }

    public function test_can_create_image_from_product_relation_manager(): void
    {
        Storage::disk('public')->put('product-images/relation-image.jpg', 'binary-content');

        Livewire::test(ImagesRelationManager::class, [
            'ownerRecord' => $this->product,
            'pageClass'   => EditProduct::class,
        ])
            ->mountTableAction('create')
            ->set('mountedActions.0.data.path', ['product-images/relation-image.jpg'])
            ->set('mountedActions.0.data.alt_text', 'Relation manager image')
            ->set('mountedActions.0.data.sort_order', 1)
            ->set('mountedActions.0.data.is_default', true)
            ->set('mountedActions.0.data.is_active', true)
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $image = ProductImage::withoutGlobalScopes()
            ->where('product_id', $this->product->getKey())
            ->latest('id')
            ->first();

        $this->assertNotNull($image);
        $this->assertSame('Relation manager image', $image->alt_text);

        Storage::disk('public')->assertExists($image->path);
    }

    public function test_product_edit_images_relation_page_does_not_return_server_error(): void
    {
        ProductImage::withoutGlobalScopes()->create([
            'product_id' => $this->product->getKey(),
            'path'       => 'product-images/existing-image.jpg',
            'alt_text'   => 'Existing image',
            'sort_order' => 1,
            'is_default' => true,
            'is_active'  => true,
        ]);

        $response = $this->get("/admin/products/{$this->product->getKey()}/edit?relation=3");

        $this->assertLessThan(500, $response->status());
    }

    public function test_can_associate_existing_image_to_product_from_relation_manager(): void
    {
        $otherProduct = Product::query()->create([
            'name'           => 'Other Product',
            'slug'           => 'other-product',
            'sku'            => 'REL-IMG-OTHER-001',
            'price'          => 49.99,
            'manage_stock'   => false,
            'stock_quantity' => 0,
            'status'         => 'published',
            'is_enabled'     => true,
            'is_featured'    => false,
            'published_at'   => now(),
        ]);

        $existingImage = ProductImage::withoutGlobalScopes()->create([
            'product_id' => $otherProduct->getKey(),
            'path'       => 'product-images/associated-image.jpg',
            'alt_text'   => 'Associatable image',
            'sort_order' => 0,
            'is_default' => true,
            'is_active'  => true,
        ]);

        Livewire::test(ImagesRelationManager::class, [
            'ownerRecord' => $this->product,
            'pageClass'   => EditProduct::class,
        ])
            ->mountTableAction('associate')
            ->set('mountedActions.0.data.recordId', $existingImage->getKey())
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('product_images', [
            'id'         => $existingImage->getKey(),
            'product_id' => $this->product->getKey(),
        ]);
    }
}
