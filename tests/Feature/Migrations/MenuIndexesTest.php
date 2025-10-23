<?php

declare(strict_types=1);

namespace Tests\Feature\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class MenuIndexesTest extends TestCase
{
    use RefreshDatabase;

    public function test_menus_table_has_expected_indexes(): void
    {
        $indexes = $this->listIndexes('menus');

        $this->assertContains('menus_location_index', $indexes);
        $this->assertContains('menus_is_active_location_index', $indexes);
    }

    public function test_menu_items_table_has_expected_indexes(): void
    {
        $indexes = $this->listIndexes('menu_items');

        $this->assertContains('menu_items_menu_id_index', $indexes);
        $this->assertContains('menu_items_parent_id_index', $indexes);
        $this->assertContains('menu_items_visibility_sort_index', $indexes);
    }

    /**
     * @return array<int, string>
     */
    private function listIndexes(string $table): array
    {
        $result = DB::select("PRAGMA index_list('{$table}')");

        return array_values(array_filter(array_map(static function ($row) {
            return $row->name ?? ($row['name'] ?? null);
        }, $result)));
    }
}
