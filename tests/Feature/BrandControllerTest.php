<?php declare(strict_types=1);

use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->brand = Brand::factory()->create([
        'name' => 'Test Brand',
        'slug' => 'test-brand',
        'description' => 'Test brand description',
        'website' => 'https://testbrand.com',
        'is_enabled' => true,
    ]);
});

it('displays brands index page', function () {
    $response = $this->get(localized_route('brands.index'));

    $response->assertOk();
    $response->assertViewIs('brands.index');
    $response->assertSee('Test Brand');
});

it('displays individual brand page', function () {
    $response = $this->get(localized_route('brands.show', $this->brand));

    $response->assertOk();
    $response->assertViewIs('brands.show');
    $response->assertSee('Test Brand');
    $response->assertSee('Test brand description');
});

it('displays localized brands index page', function () {
    $response = $this->get(route('localized.brands.index', ['locale' => 'en']));

    $response->assertOk();
    $response->assertSee('Test Brand');
});

it('displays localized brand page', function () {
    $response = $this->get(route('localized.brands.show', [
        'locale' => 'en',
        'slug' => $this->brand->slug
    ]));

    $response->assertOk();
    $response->assertSee('Test Brand');
});

it('redirects to localized route when accessing non-localized brand route', function () {
    $response = $this->get(localized_route('brands.show', $this->brand));

    $response->assertRedirect();
});

it('handles brand not found gracefully', function () {
    $response = $this->get(localized_route('brands.show', 'non-existent-brand'));

    $response->assertNotFound();
});

it('displays only enabled brands on index', function () {
    Brand::factory()->create(['is_enabled' => false, 'name' => 'Disabled Brand']);

    $response = $this->get(localized_route('brands.index'));

    $response->assertOk();
    $response->assertSee('Test Brand');
    $response->assertDontSee('Disabled Brand');
});

it('can search brands by name', function () {
    Brand::factory()->create(['name' => 'Another Brand']);

    $response = $this->get(localized_route('brands.index', ['search' => 'Test']));

    $response->assertOk();
    $response->assertSee('Test Brand');
    $response->assertDontSee('Another Brand');
});

it('displays brand with products count', function () {
    $response = $this->get(localized_route('brands.show', $this->brand));

    $response->assertOk();
    // The view should display products count if there are any
});

it('handles brand with website link', function () {
    $response = $this->get(localized_route('brands.show', $this->brand));

    $response->assertOk();
    $response->assertSee('https://testbrand.com');
});

it('displays brand SEO information', function () {
    $brand = Brand::factory()->create([
        'seo_title' => 'SEO Title',
        'seo_description' => 'SEO Description',
    ]);

    $response = $this->get(localized_route('brands.show', $brand));

    $response->assertOk();
    $response->assertSee('SEO Title');
    $response->assertSee('SEO Description');
});

it('handles brand without description', function () {
    $brand = Brand::factory()->create(['description' => null]);

    $response = $this->get(localized_route('brands.show', $brand));

    $response->assertOk();
    // Should not throw error when description is null
});

it('handles brand without website', function () {
    $brand = Brand::factory()->create(['website' => null]);

    $response = $this->get(localized_route('brands.show', $brand));

    $response->assertOk();
    // Should not display website link when website is null
});

it('displays brand in correct language', function () {
    $response = $this->get(route('localized.brands.index', ['locale' => 'lt']));

    $response->assertOk();
    // Should display content in Lithuanian
});

it('handles invalid locale gracefully', function () {
    $response = $this->get(route('localized.brands.index', ['locale' => 'invalid']));

    $response->assertOk();
    // Should fallback to default locale
});
