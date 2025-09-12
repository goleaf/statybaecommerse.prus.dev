<?php declare(strict_types=1);

use App\Filament\Resources\BrandResource;
use App\Models\Translations\BrandTranslation;
use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

describe('BrandResource', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    });

    it('can list brands', function () {
        $brands = Brand::factory()->count(3)->create();

        Livewire::test(BrandResource\Pages\ListBrands::class)
            ->assertCanSeeTableRecords($brands);
    });

    it('can create brand', function () {
        $newBrand = Brand::factory()->make();

        Livewire::test(BrandResource\Pages\CreateBrand::class)
            ->fillForm([
                'name' => $newBrand->name,
                'slug' => $newBrand->slug,
                'description' => $newBrand->description,
                'website' => $newBrand->website,
                'is_enabled' => $newBrand->is_enabled,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('brands', [
            'name' => $newBrand->name,
            'slug' => $newBrand->slug,
            'description' => $newBrand->description,
            'website' => $newBrand->website,
            'is_enabled' => $newBrand->is_enabled,
        ]);
    });

    it('can edit brand', function () {
        $brand = Brand::factory()->create();

        Livewire::test(BrandResource\Pages\EditBrand::class, [
            'record' => $brand->getRouteKey(),
        ])
            ->assertFormSet([
                'name' => $brand->name,
                'slug' => $brand->slug,
                'description' => $brand->description,
                'website' => $brand->website,
                'is_enabled' => $brand->is_enabled,
            ]);
    });

    it('can update brand', function () {
        $brand = Brand::factory()->create();
        $newData = Brand::factory()->make();

        Livewire::test(BrandResource\Pages\EditBrand::class, [
            'record' => $brand->getRouteKey(),
        ])
            ->fillForm([
                'name' => $newData->name,
                'slug' => $newData->slug,
                'description' => $newData->description,
                'website' => $newData->website,
                'is_enabled' => $newData->is_enabled,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'name' => $newData->name,
            'slug' => $newData->slug,
            'description' => $newData->description,
            'website' => $newData->website,
            'is_enabled' => $newData->is_enabled,
        ]);
    });

    it('can delete brand', function () {
        $brand = Brand::factory()->create();

        Livewire::test(BrandResource\Pages\EditBrand::class, [
            'record' => $brand->getRouteKey(),
        ])
            ->call('delete');

        $this->assertSoftDeleted('brands', [
            'id' => $brand->id,
        ]);
    });

    it('can view brand', function () {
        $brand = Brand::factory()->create();

        Livewire::test(BrandResource\Pages\ViewBrand::class, [
            'record' => $brand->getRouteKey(),
        ])
            ->assertCanSeeTableRecords([$brand]);
    });

    it('can create brand with translations', function () {
        $newBrand = Brand::factory()->make();

        Livewire::test(BrandResource\Pages\CreateBrand::class)
            ->fillForm([
                'name' => $newBrand->name,
                'slug' => $newBrand->slug,
                'description' => $newBrand->description,
                'website' => $newBrand->website,
                'is_enabled' => $newBrand->is_enabled,
                'translations' => [
                    [
                        'locale' => 'lt',
                        'name' => 'Test Brand LT',
                        'slug' => 'test-brand-lt',
                        'description' => 'Test description LT',
                        'seo_title' => 'SEO Title LT',
                        'seo_description' => 'SEO Description LT',
                    ],
                    [
                        'locale' => 'en',
                        'name' => 'Test Brand EN',
                        'slug' => 'test-brand-en',
                        'description' => 'Test description EN',
                        'seo_title' => 'SEO Title EN',
                        'seo_description' => 'SEO Description EN',
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('brands', [
            'name' => $newBrand->name,
            'slug' => $newBrand->slug,
        ]);

        $this->assertDatabaseHas('brand_translations', [
            'locale' => 'lt',
            'name' => 'Test Brand LT',
            'slug' => 'test-brand-lt',
        ]);

        $this->assertDatabaseHas('brand_translations', [
            'locale' => 'en',
            'name' => 'Test Brand EN',
            'slug' => 'test-brand-en',
        ]);
    });

    it('can filter brands by enabled status', function () {
        $enabledBrand = Brand::factory()->create(['is_enabled' => true]);
        $disabledBrand = Brand::factory()->create(['is_enabled' => false]);

        Livewire::test(BrandResource\Pages\ListBrands::class)
            ->filterTable('enabled')
            ->assertCanSeeTableRecords([$enabledBrand])
            ->assertCanNotSeeTableRecords([$disabledBrand]);
    });

    it('can filter brands by products', function () {
        $brandWithProducts = Brand::factory()->create();
        $brandWithoutProducts = Brand::factory()->create();

        Product::factory()->create(['brand_id' => $brandWithProducts->id]);

        Livewire::test(BrandResource\Pages\ListBrands::class)
            ->filterTable('has_products')
            ->assertCanSeeTableRecords([$brandWithProducts])
            ->assertCanNotSeeTableRecords([$brandWithoutProducts]);
    });

    it('can filter brands by translations', function () {
        $brandWithTranslations = Brand::factory()->create();
        $brandWithoutTranslations = Brand::factory()->create();

        BrandTranslation::factory()->create(['brand_id' => $brandWithTranslations->id]);

        Livewire::test(BrandResource\Pages\ListBrands::class)
            ->filterTable('has_translations')
            ->assertCanSeeTableRecords([$brandWithTranslations])
            ->assertCanNotSeeTableRecords([$brandWithoutTranslations]);
    });

    it('can filter brands by translation locale', function () {
        $brand = Brand::factory()->create();
        BrandTranslation::factory()->create([
            'brand_id' => $brand->id,
            'locale' => 'lt',
        ]);

        Livewire::test(BrandResource\Pages\ListBrands::class)
            ->filterTable('translation_locale', 'lt')
            ->assertCanSeeTableRecords([$brand]);
    });

    it('can search brands', function () {
        $brand1 = Brand::factory()->create(['name' => 'Apple Brand']);
        $brand2 = Brand::factory()->create(['name' => 'Samsung Brand']);

        Livewire::test(BrandResource\Pages\ListBrands::class)
            ->searchTable('Apple')
            ->assertCanSeeTableRecords([$brand1])
            ->assertCanNotSeeTableRecords([$brand2]);
    });

    it('can sort brands by name', function () {
        $brand1 = Brand::factory()->create(['name' => 'Zebra Brand']);
        $brand2 = Brand::factory()->create(['name' => 'Apple Brand']);

        Livewire::test(BrandResource\Pages\ListBrands::class)
            ->sortTable('name')
            ->assertCanSeeTableRecords([$brand2, $brand1], inOrder: true);
    });

    it('can sort brands by products count', function () {
        $brand1 = Brand::factory()->create();
        $brand2 = Brand::factory()->create();

        Product::factory()->count(3)->create(['brand_id' => $brand2->id]);
        Product::factory()->count(1)->create(['brand_id' => $brand1->id]);

        Livewire::test(BrandResource\Pages\ListBrands::class)
            ->sortTable('products_count')
            ->assertCanSeeTableRecords([$brand2, $brand1], inOrder: true);
    });

    it('can bulk enable brands', function () {
        $brands = Brand::factory()->count(3)->create(['is_enabled' => false]);

        Livewire::test(BrandResource\Pages\ListBrands::class)
            ->selectTableRecords($brands)
            ->bulkAction('enable');

        foreach ($brands as $brand) {
            $this->assertDatabaseHas('brands', [
                'id' => $brand->id,
                'is_enabled' => true,
            ]);
        }
    });

    it('can bulk disable brands', function () {
        $brands = Brand::factory()->count(3)->create(['is_enabled' => true]);

        Livewire::test(BrandResource\Pages\ListBrands::class)
            ->selectTableRecords($brands)
            ->bulkAction('disable');

        foreach ($brands as $brand) {
            $this->assertDatabaseHas('brands', [
                'id' => $brand->id,
                'is_enabled' => false,
            ]);
        }
    });

    it('can bulk delete brands', function () {
        $brands = Brand::factory()->count(3)->create();

        Livewire::test(BrandResource\Pages\ListBrands::class)
            ->selectTableRecords($brands)
            ->bulkAction('delete');

        foreach ($brands as $brand) {
            $this->assertSoftDeleted('brands', [
                'id' => $brand->id,
            ]);
        }
    });

    it('can toggle brand status from table', function () {
        $brand = Brand::factory()->create(['is_enabled' => true]);

        Livewire::test(BrandResource\Pages\ListBrands::class)
            ->callTableAction('toggle_status', $brand);

        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'is_enabled' => false,
        ]);
    });

    it('validates required fields', function () {
        Livewire::test(BrandResource\Pages\CreateBrand::class)
            ->fillForm([
                'name' => '',
                'slug' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'slug']);
    });

    it('validates unique slug', function () {
        $existingBrand = Brand::factory()->create(['slug' => 'existing-slug']);

        Livewire::test(BrandResource\Pages\CreateBrand::class)
            ->fillForm([
                'name' => 'New Brand',
                'slug' => 'existing-slug',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    });

    it('validates URL format for website', function () {
        Livewire::test(BrandResource\Pages\CreateBrand::class)
            ->fillForm([
                'name' => 'Test Brand',
                'slug' => 'test-brand',
                'website' => 'invalid-url',
            ])
            ->call('create')
            ->assertHasFormErrors(['website']);
    });

    it('shows correct navigation label', function () {
        expect(BrandResource::getNavigationLabel())->toBe(__('admin.brands.navigation.label'));
    });

    it('shows correct model labels', function () {
        expect(BrandResource::getModelLabel())->toBe(__('admin.brands.model.singular'));
        expect(BrandResource::getPluralModelLabel())->toBe(__('admin.brands.model.plural'));
    });
});
