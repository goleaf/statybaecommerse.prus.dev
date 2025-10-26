<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Models\AnalyticsEvent;
use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class IndexPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_analytics_event_time_range_query_uses_indexes(): void
    {
        AnalyticsEvent::factory()->count(5)->create([
            'event_type' => 'product_view',
            'created_at' => Carbon::now()->subDay(),
            'updated_at' => Carbon::now()->subDay(),
        ]);

        $start = Carbon::now()->subDays(7)->toDateTimeString();
        $end = Carbon::now()->toDateTimeString();

        $plan = DB::select(
            $this->explain('SELECT event_type, COUNT(*) AS aggregate FROM analytics_events WHERE event_type = ? AND created_at BETWEEN ? AND ? GROUP BY event_type'),
            ['product_view', $start, $end]
        );

        $this->assertPlanUsesIndex($plan);
    }

    public function test_category_and_product_lookup_queries_use_indexes(): void
    {
        $category = Category::factory()->create([
            'slug' => 'power-tools',
        ]);

        $product = Product::factory()->create([
            'slug'         => 'hammer-drill',
            'sku'          => 'HAMMER-001',
            'is_visible'   => true,
            'published_at' => Carbon::now()->subDay(),
        ]);

        $category->products()->attach($product);

        $categoryPlan = DB::select(
            $this->explain('SELECT id FROM categories WHERE slug = ? LIMIT 1'),
            ['power-tools']
        );

        $this->assertPlanUsesIndex($categoryPlan);

        $productPlan = DB::select(
            $this->explain('SELECT id FROM products WHERE slug = ? AND is_visible = 1 LIMIT 1'),
            ['hammer-drill']
        );

        $this->assertPlanUsesIndex($productPlan);

        $listingPlan = DB::select(
            $this->explain('SELECT p.id FROM products AS p INNER JOIN product_categories AS pc ON pc.product_id = p.id INNER JOIN categories AS c ON c.id = pc.category_id WHERE c.slug = ? AND p.is_visible = 1 ORDER BY p.published_at DESC LIMIT 10'),
            ['power-tools']
        );

        $this->assertPlanUsesIndex($listingPlan);
    }

    public function test_menu_queries_use_indexes(): void
    {
        $menu = Menu::factory()->active()->create([
            'key'      => 'main_header',
            'location' => 'header',
        ]);

        MenuItem::factory()->count(3)->visible()->create([
            'menu_id'   => $menu->id,
            'parent_id' => null,
        ]);

        $menuPlan = DB::select(
            $this->explain('SELECT id FROM menus WHERE key = ? AND is_active = 1 LIMIT 1'),
            ['main_header']
        );

        $this->assertPlanUsesIndex($menuPlan);

        $itemsPlan = DB::select(
            $this->explain('SELECT id FROM menu_items WHERE menu_id = ? AND is_visible = 1 ORDER BY sort_order'),
            [$menu->id]
        );

        $this->assertPlanUsesIndex($itemsPlan);
    }

    /**
     * @param array<int, object> $plan
     */
    private function assertPlanUsesIndex(array $plan): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $details = collect($plan)->map(static fn (object $row): string => (string) ($row->detail ?? ''));

            $hasIndexedSearch = $details->contains(static function (string $detail): bool {
                return str_contains($detail, 'USING INDEX')
                    || str_contains($detail, 'USING COVERING INDEX')
                    || str_contains($detail, 'USING PRIMARY KEY')
                    || str_contains($detail, 'SEARCH TABLE');
            });

            $noFullScan = $details->every(static fn (string $detail): bool => ! str_contains($detail, 'SCAN TABLE'));

            $this->assertTrue($hasIndexedSearch, 'Expected query plan to reference an index: ' . implode(' | ', $details->all()));
            $this->assertTrue($noFullScan, 'Expected query plan to avoid full table scans: ' . implode(' | ', $details->all()));

            return;
        }

        foreach ($plan as $row) {
            $key = $row->key ?? $row->Key ?? null;
            $this->assertNotEmpty($key, 'Expected query plan to report a key for the index.');
        }
    }

    private function explain(string $sql): string
    {
        return match (DB::getDriverName()) {
            'sqlite' => 'EXPLAIN QUERY PLAN ' . $sql,
            default  => 'EXPLAIN ' . $sql,
        };
    }
}
