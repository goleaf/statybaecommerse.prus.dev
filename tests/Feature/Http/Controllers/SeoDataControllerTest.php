<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Http\Controllers\SeoDataController;
use App\Models\Brand;
use App\Models\Product;
use App\Models\SeoData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View as ViewContract;
use Tests\TestCase;

/**
 * Feature coverage for the public SEO data listing endpoints.
 */
final class SeoDataControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_displays_filtered_seo_records(): void
    {
        // Create two SEO records where only the Lithuanian product matches the combined filters.
        $product = Product::factory()->create();
        $brand = Brand::factory()->create();

        $matchingSeo = $this->insertSeoData([
            'seoable_type' => Product::class,
            'seoable_id'   => $product->id,
            'locale'       => 'lt',
            'title'        => ['lt' => 'Lithuanian SEO Title'],
            'description'  => ['lt' => 'Lithuanian SEO Description'],
            'keywords'     => ['seo', 'lithuanian'],
        ]);
        $this->insertSeoData([
            'seoable_type' => Brand::class,
            'seoable_id'   => $brand->id,
            'locale'       => 'en',
            'title'        => ['en' => 'English Brand SEO Title'],
            'description'  => ['en' => 'English Brand SEO Description'],
            'keywords'     => ['brand'],
        ]);

        $controller = app(SeoDataController::class);
        $request = Request::create('/seo-data', 'GET', [
            'locale' => 'lt',
            'search' => 'Lithuanian',
        ]);
        $view = $controller->index($request);

        $this->assertInstanceOf(ViewContract::class, $view);
        $this->assertSame('seo-data.index', $view->name());

        $filters = $view->getData()['filters'] ?? [];
        $this->assertSame([
            'locale' => 'lt',
            'type'   => null,
            'search' => 'Lithuanian',
        ], $filters);

        $renderedHtml = $view->render();
        $this->assertStringContainsString((string) $matchingSeo->title, $renderedHtml);
        $this->assertStringNotContainsString('English Brand SEO Title', $renderedHtml);
    }

    public function test_by_type_route_uses_index_view_and_filters_results(): void
    {
        // Prepare SEO data records for different morph targets.
        $product = Product::factory()->create();
        $brand = Brand::factory()->create();

        $productSeo = $this->insertSeoData([
            'seoable_type' => Product::class,
            'seoable_id'   => $product->id,
            'locale'       => 'en',
            'title'        => ['en' => 'Product SEO Title'],
            'description'  => ['en' => 'Product specific description'],
            'keywords'     => ['product', 'seo'],
        ]);
        $this->insertSeoData([
            'seoable_type' => Brand::class,
            'seoable_id'   => $brand->id,
            'locale'       => 'en',
            'title'        => ['en' => 'Brand SEO Title'],
            'description'  => ['en' => 'Brand description'],
            'keywords'     => ['brand'],
        ]);

        $controller = app(SeoDataController::class);
        $request = Request::create('/seo-data/type/' . urlencode(Product::class), 'GET');
        $view = $controller->byType(Product::class, $request);

        $this->assertInstanceOf(ViewContract::class, $view);
        $this->assertSame('seo-data.index', $view->name());

        $filters = $view->getData()['filters'] ?? [];
        $this->assertSame([
            'locale' => null,
            'type'   => Product::class,
            'search' => null,
        ], $filters);

        $renderedHtml = $view->render();
        $this->assertStringContainsString((string) $productSeo->title, $renderedHtml);
        $this->assertStringNotContainsString('Brand SEO Title', $renderedHtml);
    }

    public function test_by_type_with_invalid_class_returns_not_found(): void
    {
        // Using a non-existent class should result in a 404 because the type cannot be resolved.
        $this->get(route('seo-data.by-type', [
            'type' => 'Acme\\UnknownType',
        ]))->assertNotFound();
    }

    public function test_statistics_view_receives_expected_metrics(): void
    {
        // Seed two SEO data records so the statistics view has predictable numbers to display.
        $product = Product::factory()->create();
        $brand = Brand::factory()->create();

        $this->insertSeoData([
            'seoable_type' => Product::class,
            'seoable_id'   => $product->id,
            'locale'       => 'lt',
            'title'        => ['lt' => 'Indexed Product'],
            'description'  => ['lt' => 'Indexed description'],
            'keywords'     => ['indexed'],
        ]);
        $this->insertSeoData([
            'seoable_type' => Brand::class,
            'seoable_id'   => $brand->id,
            'locale'       => 'en',
            'title'        => ['en' => 'Needs Work'],
            'description'  => null,
            'keywords'     => null,
        ]);

        $controller = app(SeoDataController::class);
        $view = $controller->statistics();

        $this->assertInstanceOf(ViewContract::class, $view);
        $this->assertSame('seo-data.statistics', $view->name());

        $statistics = $view->getData()['statistics'] ?? [];
        $this->assertIsArray($statistics);
        /** @var array{total:int,complete_seo:int,needs_optimization:int} $statistics */
        $statistics = $statistics;
        $this->assertSame(2, $statistics['total']);
        $this->assertSame(1, $statistics['complete_seo']);
        $this->assertSame(1, $statistics['needs_optimization']);
    }

    /**
     * Insert a raw SEO data record to sidestep complex mutators while keeping the tests focused.
     *
     * @param array<string, mixed> $overrides
     */
    private function insertSeoData(array $overrides): SeoData
    {
        $now = now();

        $data = array_merge([
            'seoable_type'    => Product::class,
            'seoable_id'      => Product::factory()->create()->id,
            'locale'          => 'lt',
            'title'           => ['lt' => 'Default Title'],
            'description'     => ['lt' => 'Default description'],
            'keywords'        => ['default'],
            'canonical_url'   => 'https://example.com/default',
            'meta_tags'       => [],
            'structured_data' => [],
            'no_index'        => 0,
            'no_follow'       => 0,
            'created_at'      => $now,
            'updated_at'      => $now,
        ], $overrides);

        foreach (['title', 'description', 'keywords', 'meta_tags', 'structured_data'] as $jsonKey) {
            if (array_key_exists($jsonKey, $data)) {
                $value = $data[$jsonKey];

                if (is_array($value)) {
                    $data[$jsonKey] = json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
            }
        }

        $id = DB::table('seo_data')->insertGetId($data);

        return SeoData::query()->findOrFail($id);
    }
}
