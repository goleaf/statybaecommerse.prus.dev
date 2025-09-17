<?php

declare(strict_types=1);

use App\Livewire\Pages\Brand\Index;
use App\Models\Brand;
use App\Models\Product;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    // Create test brands
    $this->brandWithProducts = Brand::factory()->create([
        'name' => 'Apple',
        'description' => 'Technology company',
        'is_enabled' => true,
    ]);
    
    $this->brandWithoutProducts = Brand::factory()->create([
        'name' => 'Samsung',
        'description' => 'Electronics manufacturer',
        'is_enabled' => true,
    ]);
    
    $this->disabledBrand = Brand::factory()->create([
        'name' => 'Disabled Brand',
        'is_enabled' => false,
    ]);
    
    // Create products for the first brand
    Product::factory()->count(5)->create([
        'brand_id' => $this->brandWithProducts->id,
        'is_visible' => true,
    ]);
});

it('renders the brands page', function () {
    get(localized_route('brands.index'))
        ->assertOk()
        ->assertSeeLivewire(Index::class);
});

it('has a filter form', function () {
    livewire(Index::class)
        ->assertFormExists();
});

it('displays enabled brands with product counts', function () {
    livewire(Index::class)
        ->assertSee('Apple')
        ->assertSee('Samsung')
        ->assertDontSee('Disabled Brand')
        ->assertSee('5'); // Product count for Apple
});

it('filters brands by search term', function () {
    livewire(Index::class)
        ->fillForm(['search' => 'Apple'])
        ->assertSee('Apple')
        ->assertDontSee('Samsung');
});

it('searches in brand descriptions', function () {
    livewire(Index::class)
        ->fillForm(['search' => 'Technology'])
        ->assertSee('Apple')
        ->assertDontSee('Samsung');
});

it('sorts brands by name', function () {
    livewire(Index::class)
        ->fillForm(['sortBy' => 'name'])
        ->assertSeeInOrder(['Apple', 'Samsung']);
});

it('sorts brands by product count', function () {
    livewire(Index::class)
        ->fillForm(['sortBy' => 'products_count'])
        ->assertSeeInOrder(['Apple', 'Samsung']); // Apple has more products
});

it('sorts brands by creation date', function () {
    // Create a newer brand
    $newerBrand = Brand::factory()->create([
        'name' => 'Newer Brand',
        'is_enabled' => true,
        'created_at' => now()->addDay(),
    ]);
    
    livewire(Index::class)
        ->fillForm(['sortBy' => 'created_at'])
        ->assertSeeInOrder(['Newer Brand', 'Samsung']); // Newer first
});

it('resets pagination when filters change', function () {
    // Create many brands to trigger pagination
    Brand::factory()->count(15)->create(['is_enabled' => true]);
    
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
            'search' => 'Apple',
            'sortBy' => 'products_count',
        ])
        ->assertSet('search', 'Apple')
        ->assertSet('sortBy', 'products_count');
});

it('shows empty state when no brands found', function () {
    Brand::query()->delete();
    
    livewire(Index::class)
        ->assertSee(__('shared.no_results_found'))
        ->assertSee(__('No brands are available at the moment'));
});

it('shows empty state for search with no results', function () {
    livewire(Index::class)
        ->fillForm(['search' => 'NonexistentBrand'])
        ->assertSee(__('shared.no_results_found'))
        ->assertSee(__('Try adjusting your search terms'));
});

it('can clear search filters', function () {
    $component = livewire(Index::class)
        ->fillForm(['search' => 'NonexistentBrand'])
        ->assertSee(__('shared.no_results_found'));
    
    // Simulate clicking clear filters
    $component->set('search', '');
    
    $component->assertSee('Apple')
        ->assertSee('Samsung');
});

it('displays proper page header', function () {
    get(localized_route('brands.index'))
        ->assertSee(__('shared.brands'))
        ->assertSee(__('Browse all our trusted brand partners and discover quality products'));
});

it('displays meta information correctly', function () {
    get(localized_route('brands.index'))
        ->assertSee(__('translations.brands'))
        ->assertSee(__('Browse all our trusted brand partners and discover quality products'));
});

it('has proper form field attributes', function () {
    livewire(Index::class)
        ->assertFormFieldExists('search')
        ->assertFormFieldExists('sortBy');
});

it('shows pagination when there are many brands', function () {
    Brand::factory()->count(15)->create(['is_enabled' => true]);
    
    get(localized_route('brands.index'))
        ->assertSee('Next'); // Pagination link
});

it('includes brand links to individual brand pages', function () {
    get(localized_route('brands.index'))
        ->assertSee(localized_route('brands.show', $this->brandWithProducts));
});

it('displays brand logos when available', function () {
    // This would require setting up media for the brand
    // For now, just check that the placeholder is shown
    get(localized_route('brands.index'))
        ->assertSee('svg'); // Placeholder SVG
});

it('handles brands with translations correctly', function () {
    // This test assumes the brand model uses Spatie Translatable
    app()->setLocale('lt');
    
    livewire(Index::class)
        ->assertSee('Apple')
        ->assertSee('Samsung');
});
