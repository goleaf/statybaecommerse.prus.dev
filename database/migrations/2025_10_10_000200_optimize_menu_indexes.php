<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('menus')) {
            Schema::table('menus', function (Blueprint $table): void {
                if (! $this->indexExists('menus', 'menus_location_index')) {
                    $table->index('location', 'menus_location_index');
                }

                if (! $this->indexExists('menus', 'menus_is_active_location_index')) {
                    $table->index(['is_active', 'location'], 'menus_is_active_location_index');
                }
            });
        }

        if (Schema::hasTable('menu_items')) {
            Schema::table('menu_items', function (Blueprint $table): void {
                if (! $this->indexExists('menu_items', 'menu_items_menu_id_index')) {
                    $table->index('menu_id', 'menu_items_menu_id_index');
                }

                if (! $this->indexExists('menu_items', 'menu_items_parent_id_index')) {
                    $table->index('parent_id', 'menu_items_parent_id_index');
                }

                if (! $this->indexExists('menu_items', 'menu_items_visibility_sort_index')) {
                    $table->index(['is_visible', 'sort_order'], 'menu_items_visibility_sort_index');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('menus')) {
            Schema::table('menus', function (Blueprint $table): void {
                if ($this->indexExists('menus', 'menus_location_index')) {
                    $table->dropIndex('menus_location_index');
                }

                if ($this->indexExists('menus', 'menus_is_active_location_index')) {
                    $table->dropIndex('menus_is_active_location_index');
                }
            });
        }

        if (Schema::hasTable('menu_items')) {
            Schema::table('menu_items', function (Blueprint $table): void {
                if ($this->indexExists('menu_items', 'menu_items_menu_id_index')) {
                    $table->dropIndex('menu_items_menu_id_index');
                }

                if ($this->indexExists('menu_items', 'menu_items_parent_id_index')) {
                    $table->dropIndex('menu_items_parent_id_index');
                }

                if ($this->indexExists('menu_items', 'menu_items_visibility_sort_index')) {
                    $table->dropIndex('menu_items_visibility_sort_index');
                }
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $result = DB::select("PRAGMA index_list('{$table}')");
            foreach ($result as $row) {
                $name = $row->name ?? ($row['name'] ?? null);
                if ($name === $index) {
                    return true;
                }
            }

            return false;
        }

        if ($driver === 'mysql') {
            $result = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);

            return ! empty($result);
        }

        return false;
    }
};
