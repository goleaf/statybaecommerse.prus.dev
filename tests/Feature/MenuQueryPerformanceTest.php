<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Frontend\MenuController;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class MenuQueryPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_index_uses_cached_relations(): void
    {
        Cache::flush();

        $menu = Menu::factory()->active()->create(['location' => 'header']);

        MenuItem::factory()
            ->count(3)
            ->visible()
            ->sequence(fn (int $index) => [
                'menu_id'    => $menu->id,
                'label'      => 'Punktas ' . ($index + 1),
                'sort_order' => $index,
            ])
            ->create()
            ->each(function (MenuItem $item, int $index) use ($menu): void {
                MenuItem::factory()
                    ->visible()
                    ->create([
                        'menu_id'   => $menu->id,
                        'parent_id' => $item->id,
                        'label'     => 'Vaikas ' . ($index + 1),
                    ]);
            });

        $controller = app(MenuController::class);
        $request = Request::create('/menus', 'GET', ['location' => 'header']);

        DB::enableQueryLog();
        DB::flushQueryLog();

        $response = $controller->index($request);

        $this->assertSame(200, $response->status());

        $queries = DB::getQueryLog();
        $this->assertLessThanOrEqual(4, count($queries), 'Menu index executed too many queries: ' . count($queries));
    }
}
