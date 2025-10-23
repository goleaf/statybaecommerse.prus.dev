<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SearchIndexCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_index_command_skips_when_scout_disabled(): void
    {
        config([
            'search.driver' => 'database',
            'search.scout.enabled' => false,
            'scout.driver' => 'collection',
        ]);

        $this->artisan('search:index')
            ->expectsOutputToContain('Scout search driver is disabled')
            ->assertExitCode(0);
    }

    public function test_search_index_command_indexes_models(): void
    {
        config([
            'search.driver' => 'scout',
            'search.scout.enabled' => true,
            'scout.driver' => 'collection',
        ]);

        $brand = Brand::factory()->create([
            'name' => 'Command Brand',
            'is_enabled' => true,
        ]);

        $category = Category::factory()->create([
            'name' => 'Command Category',
            'slug' => 'command-category',
            'is_visible' => true,
        ]);

        $product = Product::factory()->create([
            'name' => 'Command Drill',
            'brand_id' => $brand->id,
            'is_visible' => true,
            'published_at' => now()->subDay(),
            'price' => 149.00,
        ]);

        $category->products()->attach($product);

        $this->artisan('search:index', ['--fresh' => true])
            ->expectsOutputToContain('Search indexing completed')
            ->assertExitCode(0);

        $this->assertTrue(Product::search('Command')->get()->contains('id', $product->id));
        $this->assertTrue(Category::search('Command')->get()->contains('id', $category->id));
        $this->assertTrue(Brand::search('Command')->get()->contains('id', $brand->id));
    }
}
