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
}
