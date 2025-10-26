<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ProductImageResource\Pages\CreateProductImage;
use App\Filament\Resources\ProductImageResource\Pages\EditProductImage;
use App\Filament\Resources\ProductImageResource\Pages\ListProductImages;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductImageResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolveAdminPanel();

        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->product = Product::factory()->create([
            'status'       => 'published',
            'published_at' => now(),
            'is_visible'   => true,
        ]);

        Storage::fake('public');

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_product_images(): void
    {
        $image = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'alt_text'   => 'Hero image',
            'sort_order' => 1,
        ]);

        Livewire::test(ListProductImages::class)
            ->assertCanSeeTableRecords([$image])
            ->searchTable('Hero image')
            ->assertCanSeeTableRecords([$image]);
    }

    public function test_can_create_product_image(): void
    {
        $file = UploadedFile::fake()->image('product.jpg');

        Livewire::test(CreateProductImage::class)
            ->fillForm([
                'product_id' => $this->product->id,
                'path'       => $file,
                'alt_text'   => 'Gallery image',
                'sort_order' => 2,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $image = ProductImage::latest()->first();
        $this->assertNotNull($image);
        $this->assertSame('Gallery image', $image->alt_text);
        $this->assertSame(2, $image->sort_order);
    }

    public function test_can_edit_product_image(): void
    {
        $image = ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'alt_text'   => 'Original alt',
            'sort_order' => 1,
        ]);

        Livewire::test(EditProductImage::class, ['record' => $image->getRouteKey()])
            ->fillForm([
                'product_id' => $this->product->id,
                'path'       => [$image->path],
                'alt_text'   => 'Updated alt text',
                'sort_order' => 5,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_images', [
            'id'         => $image->id,
            'alt_text'   => 'Updated alt text',
            'sort_order' => 5,
        ]);
    }

    public function test_can_delete_product_image(): void
    {
        $image = ProductImage::factory()->create([
            'product_id' => $this->product->id,
        ]);

        Livewire::test(ListProductImages::class)
            ->callTableAction('delete', $image)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
    }

    public function test_can_bulk_delete_product_images(): void
    {
        $images = ProductImage::factory()->count(3)->create([
            'product_id' => $this->product->id,
        ]);

        Livewire::test(ListProductImages::class)
            ->callTableBulkAction('delete', $images)
            ->assertHasNoTableBulkActionErrors();

        foreach ($images as $image) {
            $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
        }
    }
}
