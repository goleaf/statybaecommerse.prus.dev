<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Translations\BrandTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('BrandTranslation Model', function () {
    it('can be created with valid data', function () {
        $brand = Brand::factory()->create();
        $translation = BrandTranslation::factory()->create([
            'brand_id' => $brand->id,
            'locale' => 'lt',
            'name' => 'Test Brand LT',
            'slug' => 'test-brand-lt',
            'description' => 'Test description in Lithuanian',
            'seo_title' => 'SEO Title LT',
            'seo_description' => 'SEO Description LT',
        ]);

        expect($translation->brand_id)->toBe($brand->id);
        expect($translation->locale)->toBe('lt');
        expect($translation->name)->toBe('Test Brand LT');
        expect($translation->slug)->toBe('test-brand-lt');
        expect($translation->description)->toBe('Test description in Lithuanian');
        expect($translation->seo_title)->toBe('SEO Title LT');
        expect($translation->seo_description)->toBe('SEO Description LT');
    });

    it('has correct fillable attributes', function () {
        $translation = new BrandTranslation;
        $fillable = $translation->getFillable();

        expect($fillable)->toContain(
            'brand_id',
            'locale',
            'name',
            'slug',
            'description',
            'seo_title',
            'seo_description'
        );
    });

    it('casts attributes correctly', function () {
        $brand = Brand::factory()->create();
        $translation = BrandTranslation::factory()->create([
            'brand_id' => $brand->id,
        ]);

        expect($translation->brand_id)->toBeInt();
    });

    it('belongs to brand', function () {
        $brand = Brand::factory()->create();
        $translation = BrandTranslation::factory()->create([
            'brand_id' => $brand->id,
        ]);

        expect($translation->brand)->toBeInstanceOf(Brand::class);
        expect($translation->brand->id)->toBe($brand->id);
    });

    it('can be created via brand relationship', function () {
        $brand = Brand::factory()->create();

        $translation = $brand->translations()->create([
            'locale' => 'lt',
            'name' => 'Test Brand LT',
            'slug' => 'test-brand-lt',
            'description' => 'Test description',
        ]);

        expect($translation)->toBeInstanceOf(BrandTranslation::class);
        expect($translation->brand_id)->toBe($brand->id);
        expect($brand->translations)->toHaveCount(1);
    });

    it('validates unique slug per locale', function () {
        $brand1 = Brand::factory()->create();
        $brand2 = Brand::factory()->create();

        BrandTranslation::factory()->create([
            'brand_id' => $brand1->id,
            'locale' => 'lt',
            'slug' => 'unique-slug',
        ]);

        expect(function () use ($brand2) {
            BrandTranslation::factory()->create([
                'brand_id' => $brand2->id,
                'locale' => 'lt',
                'slug' => 'unique-slug',
            ]);
        })->toThrow(Exception::class);
    });

    it('allows same slug in different locales', function () {
        $brand1 = Brand::factory()->create();
        $brand2 = Brand::factory()->create();

        BrandTranslation::factory()->create([
            'brand_id' => $brand1->id,
            'locale' => 'lt',
            'slug' => 'same-slug',
        ]);

        $translation2 = BrandTranslation::factory()->create([
            'brand_id' => $brand2->id,
            'locale' => 'en',
            'slug' => 'same-slug',
        ]);

        expect($translation2)->toBeInstanceOf(BrandTranslation::class);
    });

    it('validates unique brand_id and locale combination', function () {
        $brand = Brand::factory()->create();

        BrandTranslation::factory()->create([
            'brand_id' => $brand->id,
            'locale' => 'lt',
        ]);

        expect(function () use ($brand) {
            BrandTranslation::factory()->create([
                'brand_id' => $brand->id,
                'locale' => 'lt',
            ]);
        })->toThrow(Exception::class);
    });
});
