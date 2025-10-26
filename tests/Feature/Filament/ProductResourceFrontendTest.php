<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Models\User;
use App\Support\Nav;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductResourceFrontendTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolveAdminPanel();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_navigation_configuration(): void
    {
        $this->assertSame(Nav::groupForResource(ProductResource::class), ProductResource::getNavigationGroup());
        $this->assertSame(Nav::iconForResource(ProductResource::class), ProductResource::getNavigationIcon());
        $this->assertSame(__('products.title'), ProductResource::getNavigationLabel());
    }

    public function test_resource_pages_are_registered(): void
    {
        $pages = ProductResource::getPages();

        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('edit', $pages);
    }

    public function test_form_schema_is_buildable(): void
    {
        $schema = ProductResource::form(Schema::make());

        $this->assertNotEmpty($schema->getComponents());
    }

    public function test_can_create_update_and_soft_delete_product(): void
    {
        $product = Product::create([
            'name'         => 'Test Product',
            'slug'         => 'test-product',
            'sku'          => 'TEST-1234',
            'price'        => '19.99',
            'status'       => 'draft',
            'is_visible'   => true,
            'published_at' => now(),
        ]);

        $this->assertDatabaseHas('products', [
            'id'   => $product->id,
            'name' => 'Test Product',
        ]);

        $product->update([
            'name'   => 'Updated Product',
            'status' => 'published',
        ]);

        $this->assertDatabaseHas('products', [
            'id'     => $product->id,
            'name'   => 'Updated Product',
            'status' => 'published',
        ]);

        $product->delete();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
