<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            // Add new fields for enhanced attribute functionality
            if (!Schema::hasColumn('attributes', 'description')) {
                $table->text('description')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('attributes', 'validation_rules')) {
                $table->json('validation_rules')->nullable()->after('description');
            }
            if (!Schema::hasColumn('attributes', 'default_value')) {
                $table->text('default_value')->nullable()->after('validation_rules');
            }
            if (!Schema::hasColumn('attributes', 'is_visible')) {
                $table->boolean('is_visible')->default(true)->after('is_searchable');
            }
            if (!Schema::hasColumn('attributes', 'is_editable')) {
                $table->boolean('is_editable')->default(true)->after('is_visible');
            }
            if (!Schema::hasColumn('attributes', 'is_sortable')) {
                $table->boolean('is_sortable')->default(true)->after('is_editable');
            }
            if (!Schema::hasColumn('attributes', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->after('is_enabled');
            }
            if (!Schema::hasColumn('attributes', 'group_name')) {
                $table->string('group_name')->nullable()->after('category_id');
            }
            if (!Schema::hasColumn('attributes', 'icon')) {
                $table->string('icon')->nullable()->after('group_name');
            }
            if (!Schema::hasColumn('attributes', 'color')) {
                $table->string('color', 7)->nullable()->after('icon');
            }
            if (!Schema::hasColumn('attributes', 'min_value')) {
                $table->decimal('min_value', 10, 2)->nullable()->after('color');
            }
            if (!Schema::hasColumn('attributes', 'max_value')) {
                $table->decimal('max_value', 10, 2)->nullable()->after('min_value');
            }
            if (!Schema::hasColumn('attributes', 'step_value')) {
                $table->decimal('step_value', 10, 2)->nullable()->after('max_value');
            }
            if (!Schema::hasColumn('attributes', 'placeholder')) {
                $table->string('placeholder')->nullable()->after('step_value');
            }
            if (!Schema::hasColumn('attributes', 'help_text')) {
                $table->text('help_text')->nullable()->after('placeholder');
            }
            if (!Schema::hasColumn('attributes', 'meta_data')) {
                $table->json('meta_data')->nullable()->after('help_text');
            }

            // Add soft deletes if not exists
            if (!Schema::hasColumn('attributes', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Add indexes separately to avoid issues
        Schema::table('attributes', function (Blueprint $table) {
            if (!Schema::hasIndex('attributes', 'attributes_category_id_index')) {
                $table->index(['category_id']);
            }
            if (!Schema::hasIndex('attributes', 'attributes_group_name_index')) {
                $table->index(['group_name']);
            }
            if (!Schema::hasIndex('attributes', 'attributes_type_index')) {
                $table->index(['type']);
            }
            if (!Schema::hasIndex('attributes', 'attributes_is_enabled_is_visible_index')) {
                $table->index(['is_enabled', 'is_visible']);
            }
            if (!Schema::hasIndex('attributes', 'attributes_is_filterable_is_searchable_index')) {
                $table->index(['is_filterable', 'is_searchable']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['group_name']);
            $table->dropIndex(['type']);
            $table->dropIndex(['is_enabled', 'is_visible']);
            $table->dropIndex(['is_filterable', 'is_searchable']);

            $table->dropSoftDeletes();
            $table->dropColumn([
                'description',
                'validation_rules',
                'default_value',
                'is_visible',
                'is_editable',
                'is_sortable',
                'category_id',
                'group_name',
                'icon',
                'color',
                'min_value',
                'max_value',
                'step_value',
                'placeholder',
                'help_text',
                'meta_data',
            ]);
        });
    }
};
