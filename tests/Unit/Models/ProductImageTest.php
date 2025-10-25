<?php declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ProductImageTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::factory()->create([
            'name' => 'Granite Countertop',
            'status' => 'published',
            'published_at' => now(),
            'is_visible' => true,
        ]);

        Storage::fake('public');
    }

    public function test_it_persists_all_refresh_schema_columns(): void
    {
        $image = ProductImage::factory()->create([
            'product_id' => $this->product->getKey(),
            'title' => 'Hero shot',
            'alt' => 'Hero shot',
            'position' => 1,
            'meta' => ['responsive' => ['lg' => 'hero-lg.jpg']],
        ]);

        expect($image)
            ->toBeInstanceOf(ProductImage::class)
            ->and($image->product_id)->toBe($this->product->getKey())
            ->and($image->title)->toBe('Hero shot')
            ->and($image->alt)->toBe('Hero shot')
            ->and($image->alt_text)->toBe('Hero shot')
            ->and($image->position)->toBe(1)
            ->and($image->sort_order)->toBe(1)
            ->and($image->meta)->toBe(['responsive' => ['lg' => 'hero-lg.jpg']]);
    }

    public function test_fillable_and_casts_match_model_contract(): void
    {
        $model = new ProductImage();

        expect($model->getFillable())->toEqual([
            'product_id',
            'product_variant_id',
            'title',
            'alt',
            'path',
            'position',
            'meta',
        ]);

        expect($model->getCasts())->toEqual([
            'product_id' => 'int',
            'product_variant_id' => 'int',
            'position' => 'int',
            'meta' => 'array',
        ]);

        expect($model->getAttribute('meta'))->toBe([]);
    }

    public function test_variant_relation_resolves_associated_variant(): void
    {
        $variant = ProductVariant::factory()->for($this->product)->create();
        $image = ProductImage::factory()->create([
            'product_id' => $this->product->getKey(),
            'product_variant_id' => $variant->getKey(),
            'position' => 0,
        ]);

        expect($image->variant)->not->toBeNull()
            ->and($image->variant->is($variant))->toBeTrue();
    }

    public function test_ordered_scope_uses_position_for_sorting(): void
    {
        $second = ProductImage::factory()->create([
            'product_id' => $this->product->getKey(),
            'position' => 5,
        ]);
        $first = ProductImage::factory()->create([
            'product_id' => $this->product->getKey(),
            'position' => 1,
        ]);

        $ordered = ProductImage::query()
            ->forProduct($this->product->getKey())
            ->ordered()
            ->pluck('id')
            ->all();

        expect($ordered)->toBe([$first->getKey(), $second->getKey()]);
    }

    public function test_ordered_by_name_falls_back_to_alt_text_when_title_missing(): void
    {
        $alpha = ProductImage::factory()->create([
            'product_id' => $this->product->getKey(),
            'title' => null,
            'alt' => 'Alpha photo',
            'position' => 3,
        ]);
        $bravo = ProductImage::factory()->create([
            'product_id' => $this->product->getKey(),
            'title' => 'Bravo photo',
            'alt' => 'Bravo alt',
            'position' => 1,
        ]);

        $ordered = ProductImage::query()
            ->forProduct($this->product->getKey())
            ->orderedByName()
            ->pluck('id')
            ->all();

        expect($ordered)->toBe([$alpha->getKey(), $bravo->getKey()]);
    }

    public function test_alt_text_alias_updates_primary_alt_column(): void
    {
        $image = ProductImage::factory()->create([
            'product_id' => $this->product->getKey(),
            'alt' => 'Original alt',
        ]);

        $image->alt_text = 'Alias update';
        $image->save();

        expect($image->fresh()->alt)->toBe('Alias update');
    }

    public function test_url_accessor_generates_public_link_for_storage_paths(): void
    {
        $path = 'product-images/example.jpg';
        Storage::disk('public')->put($path, 'content');
        config()->set('filesystems.default', 'public');

        $image = ProductImage::factory()->create([
            'product_id' => $this->product->getKey(),
            'path' => $path,
        ]);

        expect($image->fresh()->url)->toBe(Storage::disk('public')->url($path));
    }
}
