<?php

declare(strict_types=1);

use App\Livewire\Pages\Category\Index;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    // Create test data
    $this->brand = Brand::factory()->create(['name' => 'Test Brand', 'is_enabled' => true]);
    
    $this->categoryWithProducts = Category::factory()->create([
        'name' => 'Electronics',
        'description' => 'Electronic devices and gadgets',
        'is_visible' => true,
    ]);
    
    $this->categoryWithoutProducts = Category::factory()->create([
        'name' => 'Books',
        'description' => 'Books and literature',
        'is_visible' => true,
    ]);
    
    // Create products for the first category
    Product::factory()->count(3)->create([
        'brand_id' => $this->brand->id,
        'is_visible' => true,
        'price' => 100.00,
    ])->each(function ($product) {
        $product->categories()->attach($this->categoryWithProducts->id);
    });
});

it('renders the categories page', function () {
    get(localized_route('categories.index'))
        ->assertOk()
        ->assertSeeLivewire(Index::class);
});

it('has a filter form', function () {
    livewire(Index::class)
        ->assertFormExists();
});

it('displays categories with product counts', function () {
    livewire(Index::class)
        ->assertSee('Electronics')
        ->assertSee('Books')
        ->assertSee('3'); // Product count for Electronics
});

it('filters categories by search term', function () {
    livewire(Index::class)
        ->fillForm(['search' => 'Electronics'])
        ->assertSee('Electronics')
        ->assertDontSee('Books');
});

it('filters categories by brand', function () {
    livewire(Index::class)
        ->fillForm(['brandId' => $this->brand->id])
        ->assertSee('Electronics') // Has products from this brand
        ->assertSee('Books'); // Will show with 0 products
});

it('filters categories by price range', function () {
    livewire(Index::class)
        ->fillForm([
            'priceMin' => 50.00,
            'priceMax' => 150.00,
        ])
        ->assertSee('Electronics'); // Has products in this price range
});

it('filters to show only categories with products', function () {
    livewire(Index::class)
        ->fillForm(['hasProducts' => true])
        ->assertSee('Electronics')
        ->assertDontSee('Books'); // Has no products
});

it('sorts categories by name ascending', function () {
    livewire(Index::class)
        ->fillForm(['sort' => 'name_asc'])
        ->assertSeeInOrder(['Books', 'Electronics']);
});

it('sorts categories by name descending', function () {
    livewire(Index::class)
        ->fillForm(['sort' => 'name_desc'])
        ->assertSeeInOrder(['Electronics', 'Books']);
});

it('sorts categories by product count descending', function () {
    livewire(Index::class)
        ->fillForm(['sort' => 'products_desc'])
        ->assertSeeInOrder(['Electronics', 'Books']); // Electronics has more products
});

it('resets pagination when filters change', function () {
    // Create many categories to trigger pagination
    Category::factory()->count(15)->create(['is_visible' => true]);
    
    $component = livewire(Index::class);
    
    // Go to page 2
    $component->set('page', 2);
    
    // Change filter - should reset to page 1
    $component->fillForm(['search' => 'test']);
    
    expect($component->get('page'))->toBe(1);
});

it('persists filters in URL', function () {
    livewire(Index::class)
        ->fillForm([
            'search' => 'Electronics',
            'brandId' => $this->brand->id,
            'sort' => 'name_desc',
        ])
        ->assertSet('search', 'Electronics')
        ->assertSet('brandId', $this->brand->id)
        ->assertSet('sort', 'name_desc');
});

it('shows empty state when no categories found', function () {
    Category::query()->delete();
    
    livewire(Index::class)
        ->assertSee(__('No categories available'))
        ->assertSee(__('Categories will appear here once they are added'));
});

it('shows proper breadcrumb navigation', function () {
    get(localized_route('categories.index'))
        ->assertSee(__('Home'))
        ->assertSee(__('Categories'));
});

it('displays meta information correctly', function () {
    get(localized_route('categories.index'))
        ->assertSee(__('Categories'))
        ->assertSee(__('Explore our comprehensive range of categories'));
});

it('has proper form field attributes', function () {
    livewire(Index::class)
        ->assertFormFieldExists('search')
        ->assertFormFieldExists('brandId')
        ->assertFormFieldExists('priceMin')
        ->assertFormFieldExists('priceMax')
        ->assertFormFieldExists('hasProducts')
        ->assertFormFieldExists('sort');
});

it('loads brands for filter dropdown', function () {
    $component = livewire(Index::class);
    
    expect($component->get('brands'))->toHaveCount(1);
    expect($component->get('brands')->first()->name)->toBe('Test Brand');
});

it('handles invalid sort parameter gracefully', function () {
    livewire(Index::class)
        ->set('sort', 'invalid_sort')
        ->assertSee('Electronics')
        ->assertSee('Books'); // Should still show categories with default sort
});
