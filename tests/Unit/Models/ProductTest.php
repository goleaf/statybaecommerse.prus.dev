<?php declare(strict_types=1);

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Product Model', function () {
    it('can be created with valid data', function () {
        $brand = Brand::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'TEST-001',
            'price' => 29.99,
            'brand_id' => $brand->id,
            'is_published' => true,
        ]);

        expect($product->name)->toBe('Test Product');
        expect($product->slug)->toBe('test-product');
        expect($product->sku)->toBe('TEST-001');
        expect($product->price)->toBe(29.99);
        expect($product->brand_id)->toBe($brand->id);
        expect($product->is_published)->toBeTrue();
    });

    it('has correct fillable attributes', function () {
        $product = new Product();
        $fillable = $product->getFillable();

        expect($fillable)->toContain(
            'name', 'slug', 'sku', 'barcode', 'description', 'short_description',
            'price', 'compare_price', 'cost_price', 'weight', 'stock_quantity',
            'low_stock_threshold', 'track_stock', 'allow_backorder',
            'is_published', 'is_featured', 'is_digital', 'requires_shipping',
            'brand_id', 'seo_title', 'seo_description', 'published_at'
        );
    });

    it('casts attributes correctly', function () {
        $product = Product::factory()->create([
            'price' => '29.99',
            'compare_price' => '39.99',
            'cost_price' => '15.00',
            'weight' => '1.5',
            'stock_quantity' => '10',
            'low_stock_threshold' => '5',
            'track_stock' => 1,
            'allow_backorder' => 0,
            'is_published' => 1,
            'is_featured' => 0,
            'is_digital' => 0,
            'requires_shipping' => 1,
        ]);

        expect($product->price)->toBeFloat();
        expect($product->compare_price)->toBeFloat();
        expect($product->cost_price)->toBeFloat();
        expect($product->weight)->toBeFloat();
        expect($product->stock_quantity)->toBeInt();
        expect($product->low_stock_threshold)->toBeInt();
        expect($product->track_stock)->toBeBool();
        expect($product->allow_backorder)->toBeBool();
        expect($product->is_published)->toBeBool();
        expect($product->is_featured)->toBeBool();
        expect($product->is_digital)->toBeBool();
        expect($product->requires_shipping)->toBeBool();
    });

    it('uses soft deletes', function () {
        $product = Product::factory()->create();
        $productId = $product->id;

        $product->delete();

        expect(Product::find($productId))->toBeNull();
        expect(Product::withTrashed()->find($productId))->not->toBeNull();
    });

    it('belongs to a brand', function () {
        $brand = Brand::factory()->create();
        $product = Product::factory()->create(['brand_id' => $brand->id]);

        expect($product->brand)->toBeInstanceOf(Brand::class);
        expect($product->brand->id)->toBe($brand->id);
    });

    it('belongs to many categories', function () {
        $product = Product::factory()->create();
        $categories = Category::factory()->count(3)->create();

        $product->categories()->attach($categories->pluck('id'));

        expect($product->categories)->toHaveCount(3);
        expect($product->categories->first())->toBeInstanceOf(Category::class);
    });

    it('belongs to many collections', function () {
        $product = Product::factory()->create();
        $collections = Collection::factory()->count(2)->create();

        $product->collections()->attach($collections->pluck('id'));

        expect($product->collections)->toHaveCount(2);
        expect($product->collections->first())->toBeInstanceOf(Collection::class);
    });

    it('can scope published products', function () {
        Product::factory()->create(['is_published' => true]);
        Product::factory()->create(['is_published' => false]);

        $publishedProducts = Product::published()->get();

        expect($publishedProducts)->toHaveCount(1);
        expect($publishedProducts->first()->is_published)->toBeTrue();
    });

    it('can scope featured products', function () {
        Product::factory()->create(['is_featured' => true]);
        Product::factory()->create(['is_featured' => false]);

        $featuredProducts = Product::featured()->get();

        expect($featuredProducts)->toHaveCount(1);
        expect($featuredProducts->first()->is_featured)->toBeTrue();
    });

    it('can scope digital products', function () {
        Product::factory()->create(['is_digital' => true]);
        Product::factory()->create(['is_digital' => false]);

        $digitalProducts = Product::digital()->get();

        expect($digitalProducts)->toHaveCount(1);
        expect($digitalProducts->first()->is_digital)->toBeTrue();
    });

    it('can scope products in stock', function () {
        Product::factory()->create(['stock_quantity' => 10]);
        Product::factory()->create(['stock_quantity' => 0]);

        $inStockProducts = Product::inStock()->get();

        expect($inStockProducts)->toHaveCount(1);
        expect($inStockProducts->first()->stock_quantity)->toBe(10);
    });

    it('can scope low stock products', function () {
        Product::factory()->create(['stock_quantity' => 3]);
        Product::factory()->create(['stock_quantity' => 10]);

        $lowStockProducts = Product::lowStock()->get();

        expect($lowStockProducts)->toHaveCount(1);
        expect($lowStockProducts->first()->stock_quantity)->toBe(3);
    });

    it('can scope out of stock products', function () {
        Product::factory()->create(['stock_quantity' => 0]);
        Product::factory()->create(['stock_quantity' => 5]);

        $outOfStockProducts = Product::outOfStock()->get();

        expect($outOfStockProducts)->toHaveCount(1);
        expect($outOfStockProducts->first()->stock_quantity)->toBe(0);
    });

    it('can check if product is in stock', function () {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        expect($product->isInStock())->toBeTrue();

        $product->update(['stock_quantity' => 0]);
        expect($product->isInStock())->toBeFalse();
    });

    it('can check if product is low stock', function () {
        $product = Product::factory()->create(['stock_quantity' => 3, 'low_stock_threshold' => 5]);

        expect($product->isLowStock())->toBeTrue();

        $product->update(['stock_quantity' => 10]);
        expect($product->isLowStock())->toBeFalse();
    });

    it('can check if product is out of stock', function () {
        $product = Product::factory()->create(['stock_quantity' => 0]);

        expect($product->isOutOfStock())->toBeTrue();

        $product->update(['stock_quantity' => 5]);
        expect($product->isOutOfStock())->toBeFalse();
    });

    it('can decrease stock quantity', function () {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $product->decreaseStock(3);

        expect($product->fresh()->stock_quantity)->toBe(7);
    });

    it('can increase stock quantity', function () {
        $product = Product::factory()->create(['stock_quantity' => 5]);

        $product->increaseStock(3);

        expect($product->fresh()->stock_quantity)->toBe(8);
    });

    it('cannot decrease stock below zero when tracking stock', function () {
        $product = Product::factory()->create(['stock_quantity' => 5, 'track_stock' => true]);

        $product->decreaseStock(10);

        expect($product->fresh()->stock_quantity)->toBe(0);
    });

    it('can decrease stock below zero when allowing backorder', function () {
        $product = Product::factory()->create(['stock_quantity' => 5, 'allow_backorder' => true]);

        $product->decreaseStock(10);

        expect($product->fresh()->stock_quantity)->toBe(-5);
    });

    it('uses slug as route key', function () {
        $product = Product::factory()->create(['slug' => 'test-product']);

        expect($product->getRouteKeyName())->toBe('slug');
        expect($product->getRouteKey())->toBe('test-product');
    });

    it('validates unique slug', function () {
        Product::factory()->create(['slug' => 'existing-product']);

        expect(function () {
            Product::factory()->create(['slug' => 'existing-product']);
        })->toThrow(Exception::class);
    });

    it('validates unique sku', function () {
        Product::factory()->create(['sku' => 'EXISTING-001']);

        expect(function () {
            Product::factory()->create(['sku' => 'EXISTING-001']);
        })->toThrow(Exception::class);
    });

    it('can be searched globally', function () {
        $product = Product::factory()->create([
            'name' => 'Searchable Product',
            'sku' => 'SEARCH-001',
            'description' => 'This product is searchable',
        ]);

        $searchResults = Product::where('name', 'like', '%Searchable%')
            ->orWhere('sku', 'like', '%SEARCH%')
            ->orWhere('description', 'like', '%searchable%')
            ->get();

        expect($searchResults)->toHaveCount(1);
        expect($searchResults->first()->id)->toBe($product->id);
    });

    it('has media collections', function () {
        $product = Product::factory()->create();

        expect($product->getMediaCollections())->toHaveCount(1);
        expect($product->getMediaCollections()->pluck('name'))->toContain('images');
    });

    it('calculates discount percentage correctly', function () {
        $product = Product::factory()->create([
            'price' => 20.00,
            'compare_price' => 25.00,
        ]);

        expect($product->discount_percentage)->toBe(20);
    });

    it('returns null discount percentage when no compare price', function () {
        $product = Product::factory()->create([
            'price' => 20.00,
            'compare_price' => null,
        ]);

        expect($product->discount_percentage)->toBeNull();
    });

    it('returns null discount percentage when compare price is lower', function () {
        $product = Product::factory()->create([
            'price' => 25.00,
            'compare_price' => 20.00,
        ]);

        expect($product->discount_percentage)->toBeNull();
    });

    it('can get formatted price', function () {
        $product = Product::factory()->create(['price' => 29.99]);

        expect($product->formatted_price)->toBe('29,99' . "\u{00A0}" . '€');
    });

    it('can get formatted compare price', function () {
        $product = Product::factory()->create(['compare_price' => 39.99]);

        expect($product->formatted_compare_price)->toBe('39,99' . "\u{00A0}" . '€');
    });

    it('can get stock status', function () {
        $inStockProduct = Product::factory()->create(['stock_quantity' => 10]);
        $lowStockProduct = Product::factory()->create(['stock_quantity' => 3, 'low_stock_threshold' => 5]);
        $outOfStockProduct = Product::factory()->create(['stock_quantity' => 0]);

        expect($inStockProduct->stock_status)->toBe('in_stock');
        expect($lowStockProduct->stock_status)->toBe('low_stock');
        expect($outOfStockProduct->stock_status)->toBe('out_of_stock');
    });

    it('can get stock status for non-tracked products', function () {
        $product = Product::factory()->create(['track_stock' => false]);

        expect($product->stock_status)->toBe('not_tracked');
    });
});