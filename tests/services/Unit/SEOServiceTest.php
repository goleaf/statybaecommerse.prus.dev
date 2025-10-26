<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\SEOService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Number;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    URL::shouldReceive('route')->andReturnUsing(function (string $name, $params = [], $absolute = true) {
        $routeName = $name;
        $suffix = '';

        if (is_array($params)) {
            $parts = [];
            foreach ($params as $value) {
                if (is_scalar($value)) {
                    $parts[] = (string) $value;
                }
            }

            $suffix = implode('-', $parts);
        } elseif (is_string($params) || is_int($params) || is_float($params)) {
            $suffix = (string) $params;
        }

        return 'https://example.test/' . $routeName . ($suffix !== '' ? '/' . $suffix : '');
    });
});

function makeProduct(): Product
{
    $p = new Product;
    $p->name = 'Widget';
    $p->description = 'Great product';
    $p->slug = 'widget';
    $p->price = 9.99;
    $p->stock_quantity = 10;
    $p->sku = 'W-1';
    $p->seo_title = 'Custom Widget Title';
    $p->seo_description = 'Custom widget description';
    $p->meta_keywords = 'widget, custom';
    $brand = new Brand;
    $brand->name = 'Acme';
    $p->setRelation('brand', $brand);
    $category = new Category;
    $category->name = 'Tools';
    $p->setRelation('categories', collect([$category]));

    return $p;
}

function makeCategory(): Category
{
    $c = new Category;
    $c->name = 'Tools';
    $c->description = 'All tools';
    $c->slug = 'tools';
    $c->seo_title = 'Category Title';
    $c->seo_description = 'Category description';

    return $c;
}

function makeBrand(): Brand
{
    $b = new Brand;
    $b->name = 'Acme';
    $b->description = 'Quality';
    $b->slug = 'acme';
    $b->seo_title = 'Brand Title';
    $b->seo_description = 'Brand description';

    return $b;
}

it('builds product SEO array', function () {
    $p = makeProduct();
    $seo = SEOService::getProductSEO($p);

    expect($seo)->toHaveKeys(['title', 'description', 'keywords', 'canonical', 'og_image'])
        ->and($seo['title'])->toBe('Custom Widget Title')
        ->and($seo['description'])->toBe('Custom widget description')
        ->and($seo['keywords'])->toBe('widget, custom')
        ->and($seo['canonical'])->toContain('localized.products.show')
        ->and($seo['canonical'])->toContain('widget')
        ->and($seo['product_currency'])->toBe(function_exists('current_currency') ? current_currency() : 'EUR')
        ->and($seo['product_price'])->toBe(Number::currency(9.99, function_exists('current_currency') ? current_currency() : 'EUR', locale: app()->getLocale()))
        ->and($seo['product_availability'])->toBe('https://schema.org/InStock');
});

it('builds category SEO array', function () {
    $c = makeCategory();
    $seo = SEOService::getCategorySEO($c);

    expect($seo)->toHaveKeys(['title', 'description', 'canonical', 'og_image'])
        ->and($seo['title'])->toBe('Category Title')
        ->and($seo['description'])->toBe('Category description')
        ->and($seo['canonical'])->toContain('localized.categories.show');
});

it('builds brand SEO array', function () {
    $b = makeBrand();
    $seo = SEOService::getBrandSEO($b);

    expect($seo)->toHaveKeys(['title', 'description', 'canonical', 'og_image'])
        ->and($seo['title'])->toBe('Brand Title')
        ->and($seo['description'])->toBe('Brand description')
        ->and($seo['canonical'])->toContain('localized.brands.show');
});

it('builds product structured data', function () {
    $p = makeProduct();
    $s = SEOService::getStructuredData($p);

    expect($s)->toHaveKeys(['@context', '@type', 'name', 'offers'])
        ->and($s['@type'])->toBe('Product')
        ->and($s['offers'])->toBeArray();

    /** @var array<string, mixed> $offers */
    $offers = $s['offers'];

    expect($offers['priceCurrency'])->toBe(function_exists('current_currency') ? current_currency() : 'EUR')
        ->and($offers['price'])->toBe('9.99');
});
