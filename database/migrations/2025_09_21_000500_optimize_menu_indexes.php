<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table): void {
            if (! Schema::hasIndex('menus', 'menus_is_active_index')) {
                $table->index('is_active', 'menus_is_active_index');
            }

            if (! Schema::hasIndex('menus', 'menus_location_index')) {
                $table->index('location', 'menus_location_index');
            }
        });

        Schema::table('menu_items', function (Blueprint $table): void {
            if (! Schema::hasIndex('menu_items', 'menu_items_menu_id_visible_index')) {
                $table->index(['menu_id', 'is_visible'], 'menu_items_menu_id_visible_index');
            }

            if (! Schema::hasIndex('menu_items', 'menu_items_parent_sort_index')) {
                $table->index(['parent_id', 'sort_order'], 'menu_items_parent_sort_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table): void {
            if (Schema::hasIndex('menus', 'menus_is_active_index')) {
                $table->dropIndex('menus_is_active_index');
            }

            if (Schema::hasIndex('menus', 'menus_location_index')) {
                $table->dropIndex('menus_location_index');
            }
        });

        Schema::table('menu_items', function (Blueprint $table): void {
            if (Schema::hasIndex('menu_items', 'menu_items_menu_id_visible_index')) {
                $table->dropIndex('menu_items_menu_id_visible_index');
            }

            if (Schema::hasIndex('menu_items', 'menu_items_parent_sort_index')) {
                $table->dropIndex('menu_items_parent_sort_index');
            }
        });
    }
};
