<?php declare(strict_types=1);

use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Brand Model', function () {
    it('can be created with valid data', function () {
        $brand = Brand::factory()->create([
            'name' => 'Test Brand',
            'slug' => 'test-brand',
            'description' => 'Test brand description',
            'website' => 'https://testbrand.com',
            'is_enabled' => true,
        ]);

        expect($brand->name)->toBe('Test Brand');
        expect($brand->slug)->toBe('test-brand');
        expect($brand->description)->toBe('Test brand description');
        expect($brand->website)->toBe('https://testbrand.com');
        expect($brand->is_enabled)->toBeTrue();
    });

    it('has correct fillable attributes', function () {
        $brand = new Brand();
        $fillable = $brand->getFillable();

        expect($fillable)->toContain('name', 'slug', 'description', 'website', 'is_enabled', 'seo_title', 'seo_description');
    });

    it('casts attributes correctly', function () {
        $brand = Brand::factory()->create([
            'is_enabled' => 1,
        ]);

        expect($brand->is_enabled)->toBeBool();
        expect($brand->is_enabled)->toBeTrue();
    });

    it('uses soft deletes', function () {
        $brand = Brand::factory()->create();
        $brandId = $brand->id;

        $brand->delete();

        expect(Brand::find($brandId))->toBeNull();
        expect(Brand::withTrashed()->find($brandId))->not->toBeNull();
    });

    it('has products relationship', function () {
        $brand = Brand::factory()->create();
        $product = Product::factory()->create(['brand_id' => $brand->id]);

        expect($brand->products)->toHaveCount(1);
        expect($brand->products->first())->toBeInstanceOf(Product::class);
        expect($brand->products->first()->id)->toBe($product->id);
    });

    it('can scope enabled brands', function () {
        Brand::factory()->create(['is_enabled' => true]);
        Brand::factory()->create(['is_enabled' => false]);

        $enabledBrands = Brand::enabled()->get();

        expect($enabledBrands)->toHaveCount(1);
        expect($enabledBrands->first()->is_enabled)->toBeTrue();
    });

    it('can scope brands with products', function () {
        $brandWithProducts = Brand::factory()->create();
        $brandWithoutProducts = Brand::factory()->create();

        Product::factory()->create(['brand_id' => $brandWithProducts->id]);

        $brandsWithProducts = Brand::withProducts()->get();

        expect($brandsWithProducts)->toHaveCount(1);
        expect($brandsWithProducts->first()->id)->toBe($brandWithProducts->id);
    });

    it('calculates products count correctly', function () {
        $brand = Brand::factory()->create();
        Product::factory()->count(3)->create(['brand_id' => $brand->id]);

        expect($brand->products_count)->toBe(3);
    });

    it('uses slug as route key', function () {
        $brand = Brand::factory()->create(['slug' => 'test-brand']);

        expect($brand->getRouteKeyName())->toBe('slug');
        expect($brand->getRouteKey())->toBe('test-brand');
    });

    it('validates unique slug', function () {
        Brand::factory()->create(['slug' => 'existing-brand']);

        expect(function () {
            Brand::factory()->create(['slug' => 'existing-brand']);
        })->toThrow(Exception::class);
    });

    it('can be searched globally', function () {
        $brand = Brand::factory()->create([
            'name' => 'Searchable Brand',
            'description' => 'This brand is searchable',
            'website' => 'https://searchable.com',
        ]);

        $searchResults = Brand::where('name', 'like', '%Searchable%')
            ->orWhere('description', 'like', '%searchable%')
            ->orWhere('website', 'like', '%searchable%')
            ->get();

        expect($searchResults)->toHaveCount(1);
        expect($searchResults->first()->id)->toBe($brand->id);
    });

    it('has media collections', function () {
        $brand = new Brand();
        $brand->registerMediaCollections();

        expect($brand->getMediaCollections())->toHaveCount(2);
        expect($brand->getMediaCollections()->pluck('name'))->toContain('logo', 'banner');
    });

    it('logs activity', function () {
        $brand = Brand::factory()->create(['name' => 'Activity Brand']);

        $brand->update(['name' => 'Updated Brand']);

        $activity = \Spatie\Activitylog\Models\Activity::where('subject_id', $brand->id)
            ->where('subject_type', Brand::class)
            ->where('event', 'updated')
            ->first();

        expect($activity)->not->toBeNull();
        expect($activity->description)->toBe('Brand updated');
    });

    it('flushes caches on save and delete', function () {
        $brand = Brand::factory()->create();

        // Mock cache forget
        \Illuminate\Support\Facades\Cache::shouldReceive('forget')
            ->with('sitemap:urls:en')
            ->once();

        $brand->update(['name' => 'Updated Brand']);
    });

    it('has translations relationship', function () {
        $brand = Brand::factory()->create();
        $translation = \App\Models\Translations\BrandTranslation::factory()->create([
            'brand_id' => $brand->id,
            'locale' => 'lt',
            'name' => 'Test Brand LT',
            'slug' => 'test-brand-lt',
        ]);

        expect($brand->translations)->toHaveCount(1);
        expect($brand->translations->first())->toBeInstanceOf(\App\Models\Translations\BrandTranslation::class);
        expect($brand->translations->first()->id)->toBe($translation->id);
    });

    it('can get translated name', function () {
        $brand = Brand::factory()->create(['name' => 'Default Brand']);
        $translation = \App\Models\Translations\BrandTranslation::factory()->create([
            'brand_id' => $brand->id,
            'locale' => 'lt',
            'name' => 'Translated Brand',
        ]);

        app()->setLocale('lt');
        expect($brand->getTranslatedName())->toBe('Translated Brand');
        expect($brand->getTranslatedName('en'))->toBe('Default Brand');
    });

    it('can get translated slug', function () {
        $brand = Brand::factory()->create(['slug' => 'default-brand']);
        $translation = \App\Models\Translations\BrandTranslation::factory()->create([
            'brand_id' => $brand->id,
            'locale' => 'lt',
            'slug' => 'translated-brand',
        ]);

        app()->setLocale('lt');
        expect($brand->getTranslatedSlug())->toBe('translated-brand');
        expect($brand->getTranslatedSlug('en'))->toBe('default-brand');
    });

    it('can check if has translation for locale', function () {
        $brand = Brand::factory()->create();
        \App\Models\Translations\BrandTranslation::factory()->create([
            'brand_id' => $brand->id,
            'locale' => 'lt',
        ]);

        expect($brand->hasTranslation('lt'))->toBeTrue();
        expect($brand->hasTranslation('en'))->toBeFalse();
    });

    it('can get available locales', function () {
        $brand = Brand::factory()->create();
        \App\Models\Translations\BrandTranslation::factory()->create([
            'brand_id' => $brand->id,
            'locale' => 'lt',
        ]);
        \App\Models\Translations\BrandTranslation::factory()->create([
            'brand_id' => $brand->id,
            'locale' => 'en',
        ]);

        $locales = $brand->getAvailableLocales();
        expect($locales)->toContain('lt', 'en');
    });

    it('can get logo and banner URLs with sizes', function () {
        $brand = Brand::factory()->create();
        
        // Test without media
        expect($brand->getLogoUrl())->toBeNull();
        expect($brand->getBannerUrl())->toBeNull();
        expect($brand->getLogoUrl('sm'))->toBeNull();
        expect($brand->getBannerUrl('md'))->toBeNull();
    });
});