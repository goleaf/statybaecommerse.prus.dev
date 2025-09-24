<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\SEOService;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    URL::shouldReceive('route')->andReturnUsing(function ($name, $params = []) {
        return 'https://example.test/'.$name.'/'.(is_array($params) ? (implode('-', $params)) : $params);
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
    $p->brand = (object) ['name' => 'Acme'];
    $p->setRelation('categories', collect([(object) ['name' => 'Tools']]));
    $p->getFirstMediaUrl = fn ($collection, $conv = null) => 'https://cdn/img.jpg';

    return $p;
}

function makeCategory(): Category
{
    $c = new Category;
    $c->name = 'Tools';
    $c->description = 'All tools';
    $c->slug = 'tools';
    $c->getFirstMediaUrl = fn ($collection, $conv = null) => 'https://cdn/cat.jpg';

    return $c;
}

function makeBrand(): Brand
{
    $b = new Brand;
    $b->name = 'Acme';
    $b->description = 'Quality';
    $b->slug = 'acme';
    $b->getFirstMediaUrl = fn ($collection, $conv = null) => 'https://cdn/logo.jpg';

    return $b;
}

it('builds product SEO array', function () {
    $p = makeProduct();
    $seo = SEOService::getProductSEO($p);

    expect($seo)->toHaveKeys(['title', 'description', 'keywords', 'canonical', 'og_image'])
        ->and($seo['canonical'])->toContain('product.show')
        ->and($seo['product_currency'])->toBe('EUR');
});

it('builds category SEO array', function () {
    $c = makeCategory();
    $seo = SEOService::getCategorySEO($c);

    expect($seo)->toHaveKeys(['title', 'description', 'canonical', 'og_image'])
        ->and($seo['canonical'])->toContain('category.show');
});

it('builds brand SEO array', function () {
    $b = makeBrand();
    $seo = SEOService::getBrandSEO($b);

    expect($seo)->toHaveKeys(['title', 'description', 'canonical', 'og_image'])
        ->and($seo['canonical'])->toContain('brands.show');
});

it('builds product structured data', function () {
    $p = makeProduct();
    $s = SEOService::getStructuredData($p);

    expect($s)->toHaveKeys(['@context', '@type', 'name', 'offers'])
        ->and($s['@type'])->toBe('Product')
        ->and($s['offers']['priceCurrency'])->toBe('EUR');
});
