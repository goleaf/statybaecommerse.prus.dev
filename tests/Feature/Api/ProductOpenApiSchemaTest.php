<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\Contracts\SimpleJsonSchemaValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

final class ProductOpenApiSchemaTest extends TestCase
{
    use RefreshDatabase;

    private SimpleJsonSchemaValidator $validator;

    /**
     * @var array<string, mixed>
     */
    private array $openApi;

    protected function setUp(): void
    {
        parent::setUp();

        $specPath = base_path('public/openapi.yaml');
        if (! File::exists($specPath)) {
            $this->artisan('api:spec');
        }

        /** @var array<string, mixed> $spec */
        $spec = Yaml::parseFile($specPath);

        $this->openApi = $spec;
        $this->validator = app(SimpleJsonSchemaValidator::class);

        config(['app.currency' => 'EUR']);

        Product::resolveRelationUsing('category', static fn (Product $product) => $product->categories()->limit(1));
    }

    public function test_product_search_matches_documented_schema(): void
    {
        $brand = Brand::factory()->create(['is_enabled' => true]);
        $category = Category::factory()->create(['is_visible' => true]);

        Product::factory()
            ->count(2)
            ->published()
            ->for($brand)
            ->create()
            ->each(static fn (Product $product) => $product->categories()->attach($category->getKey()));

        $response = $this->getJson(route('api.products.search', ['q' => $category->name]));
        $response->assertOk();

        $schema = $this->schemaForPath('/products/search');
        $errors = $this->validator->validateInline($response->json(), $schema, $this->openApi);

        $this->assertSame([], $errors, 'OpenAPI schema validation failed: '.implode('; ', $errors));
    }

    public function test_product_catalog_matches_documented_schema(): void
    {
        $brand = Brand::factory()->create(['is_enabled' => true]);
        $category = Category::factory()->create(['is_visible' => true]);

        Product::factory()
            ->count(3)
            ->published()
            ->for($brand)
            ->create()
            ->each(static fn (Product $product) => $product->categories()->attach($category->getKey()));

        $response = $this->getJson(route('api.products.index', ['per_page' => 2]));
        $response->assertOk();

        $payload = $response->json();
        $this->assertSame('product-resource', $payload['contract']);
        $this->assertSame('v2', $payload['version']);
        $this->assertArrayHasKey('data', $payload);
        $this->assertArrayHasKey('meta', $payload);
        $this->assertSame(2, $payload['meta']['pagination']['per_page']);
    }

    /**
     * @return array<string, mixed>
     */
    private function schemaForPath(string $path): array
    {
        $resource = $this->openApi['paths'][$path]['get']['responses']['200']['content']['application/json']['schema'] ?? null;
        $this->assertIsArray($resource, sprintf('Expected schema for [%s] GET 200 response to be defined.', $path));

        /** @var array<string, mixed> $schema */
        $schema = $resource;

        return $schema;
    }
}
