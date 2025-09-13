<?php

declare(strict_types=1);

use App\Livewire\Pages\Brand\Index;
use App\Models\Brand;
use App\Models\Translations\BrandTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

describe('Brand Index Livewire Component', function () {
    it('renders successfully', function () {
        $component = Livewire::test(Index::class);

        $component->assertStatus(200);
    });

    it('displays enabled brands only', function () {
        $enabledBrand = Brand::factory()->create([
            'name' => 'Enabled Brand',
            'is_enabled' => true,
        ]);

        $disabledBrand = Brand::factory()->create([
            'name' => 'Disabled Brand',
            'is_enabled' => false,
        ]);

        $component = Livewire::test(Index::class);

        $component->assertSee('Enabled Brand');
        $component->assertDontSee('Disabled Brand');
    });

    it('displays brands with translated content', function () {
        $brand = Brand::factory()->create([
            'name' => 'Default Brand',
            'is_enabled' => true,
        ]);

        $translation = BrandTranslation::factory()->create([
            'brand_id' => $brand->id,
            'locale' => 'lt',
            'name' => 'Translated Brand',
            'description' => 'Translated description',
        ]);

        app()->setLocale('lt');

        $component = Livewire::test(Index::class);

        $component->assertSee('Translated Brand');
        $component->assertSee('Translated description');
    });

    it('can search brands by name', function () {
        $brand1 = Brand::factory()->create([
            'name' => 'Apple Brand',
            'is_enabled' => true,
        ]);

        $brand2 = Brand::factory()->create([
            'name' => 'Samsung Brand',
            'is_enabled' => true,
        ]);

        $component = Livewire::test(Index::class);

        $component->set('search', 'Apple');
        $component->assertSee('Apple Brand');
        $component->assertDontSee('Samsung Brand');
    });

    it('can search brands by description', function () {
        $brand1 = Brand::factory()->create([
            'name' => 'Brand 1',
            'description' => 'Technology company',
            'is_enabled' => true,
        ]);

        $brand2 = Brand::factory()->create([
            'name' => 'Brand 2',
            'description' => 'Fashion company',
            'is_enabled' => true,
        ]);

        $component = Livewire::test(Index::class);

        $component->set('search', 'Technology');
        $component->assertSee('Brand 1');
        $component->assertDontSee('Brand 2');
    });

    it('can search brands by translated content', function () {
        $brand = Brand::factory()->create([
            'name' => 'Default Brand',
            'is_enabled' => true,
        ]);

        $translation = BrandTranslation::factory()->create([
            'brand_id' => $brand->id,
            'locale' => 'lt',
            'name' => 'Translated Brand',
            'description' => 'Translated description',
        ]);

        app()->setLocale('lt');

        $component = Livewire::test(Index::class);

        $component->set('search', 'Translated');
        $component->assertSee('Translated Brand');
    });

    it('can sort brands by name', function () {
        $brand1 = Brand::factory()->create([
            'name' => 'Zebra Brand',
            'is_enabled' => true,
        ]);

        $brand2 = Brand::factory()->create([
            'name' => 'Apple Brand',
            'is_enabled' => true,
        ]);

        $component = Livewire::test(Index::class);

        $component->set('sortBy', 'name');

        $brands = $component->get('brands');
        expect($brands->first()->name)->toBe('Apple Brand');
        expect($brands->last()->name)->toBe('Zebra Brand');
    });

    it('can sort brands by products count', function () {
        $brand1 = Brand::factory()->create([
            'name' => 'Brand 1',
            'is_enabled' => true,
        ]);

        $brand2 = Brand::factory()->create([
            'name' => 'Brand 2',
            'is_enabled' => true,
        ]);

        // Add products to brand2
        \App\Models\Product::factory()->count(3)->create([
            'brand_id' => $brand2->id,
        ]);

        $component = Livewire::test(Index::class);

        $component->set('sortBy', 'products_count');

        $brands = $component->get('brands');
        expect($brands->first()->name)->toBe('Brand 2');
        expect($brands->first()->products_count)->toBe(3);
    });

    it('can sort brands by creation date', function () {
        $brand1 = Brand::factory()->create([
            'name' => 'Older Brand',
            'is_enabled' => true,
            'created_at' => now()->subDays(2),
        ]);

        $brand2 = Brand::factory()->create([
            'name' => 'Newer Brand',
            'is_enabled' => true,
            'created_at' => now(),
        ]);

        $component = Livewire::test(Index::class);

        $component->set('sortBy', 'created_at');

        $brands = $component->get('brands');
        expect($brands->first()->name)->toBe('Newer Brand');
        expect($brands->last()->name)->toBe('Older Brand');
    });

    it('resets pagination when search changes', function () {
        Brand::factory()->count(15)->create(['is_enabled' => true]);

        $component = Livewire::test(Index::class);

        // Go to page 2
        $component->set('search', '');
        $component->call('$refresh');

        // Change search
        $component->set('search', 'test');

        // Should be on page 1
        expect($component->get('brands')->currentPage())->toBe(1);
    });

    it('resets pagination when sort changes', function () {
        Brand::factory()->count(15)->create(['is_enabled' => true]);

        $component = Livewire::test(Index::class);

        // Go to page 2
        $component->set('sortBy', 'name');
        $component->call('$refresh');

        // Change sort
        $component->set('sortBy', 'created_at');

        // Should be on page 1
        expect($component->get('brands')->currentPage())->toBe(1);
    });

    it('displays correct page title and description', function () {
        $component = Livewire::test(Index::class);

        expect($component->getPageTitle())->toBe(__('shared.brands'));
        expect($component->getPageDescription())->toBe(__('Browse all our trusted brand partners and discover quality products'));
    });

    it('paginates brands correctly', function () {
        Brand::factory()->count(15)->create(['is_enabled' => true]);

        $component = Livewire::test(Index::class);

        $brands = $component->get('brands');
        expect($brands)->toHaveCount(12);  // Default pagination limit
        expect($brands->hasPages())->toBeTrue();
    });

    it('shows empty state when no brands found', function () {
        $component = Livewire::test(Index::class);

        $component->assertSee(__('shared.no_results_found'));
    });

    it('shows empty state when search returns no results', function () {
        Brand::factory()->create([
            'name' => 'Apple Brand',
            'is_enabled' => true,
        ]);

        $component = Livewire::test(Index::class);

        $component->set('search', 'NonExistent');
        $component->assertSee(__('shared.no_results_found'));
    });
});
