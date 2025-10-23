<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class CategoryQueryPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_tree_endpoint_uses_minimal_queries(): void
    {
        Cache::flush();

        $roots = Category::factory()
            ->count(3)
            ->sequence(fn (int $index) => [
                'name' => 'Kategorija '.($index + 1),
                'slug' => 'kategorija-'.($index + 1),
                'sort_order' => $index,
            ])
            ->create();

        $roots->each(function (Category $root, int $index): void {
            Category::factory()
                ->count(2)
                ->sequence(fn (int $childIndex) => [
                    'name' => 'Subkategorija '.($index + 1).'-'.($childIndex + 1),
                    'slug' => 'subkategorija-'.($index + 1).'-'.($childIndex + 1),
                    'parent_id' => $root->id,
                    'sort_order' => $childIndex,
                ])
                ->create();
        });

        DB::enableQueryLog();
        DB::flushQueryLog();

        $response = $this->getJson(route('api.categories.tree'));

        $response->assertOk();

        $queries = DB::getQueryLog();
        $this->assertLessThanOrEqual(5, count($queries), 'Category tree executed too many queries: '.count($queries));
    }
}
