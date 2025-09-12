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
            $table->text('description')->nullable()->after('slug');
            $table->json('validation_rules')->nullable()->after('description');
            $table->text('default_value')->nullable()->after('validation_rules');
            $table->boolean('is_visible')->default(true)->after('is_searchable');
            $table->boolean('is_editable')->default(true)->after('is_visible');
            $table->boolean('is_sortable')->default(true)->after('is_editable');
            $table->unsignedBigInteger('category_id')->nullable()->after('is_enabled');
            $table->string('group_name')->nullable()->after('category_id');
            $table->string('icon')->nullable()->after('group_name');
            $table->string('color', 7)->nullable()->after('icon');
            $table->decimal('min_value', 10, 2)->nullable()->after('color');
            $table->decimal('max_value', 10, 2)->nullable()->after('min_value');
            $table->decimal('step_value', 10, 2)->nullable()->after('max_value');
            $table->string('placeholder')->nullable()->after('step_value');
            $table->text('help_text')->nullable()->after('placeholder');
            $table->json('meta_data')->nullable()->after('help_text');
            
            // Add soft deletes
            $table->softDeletes();
            
            // Add indexes
            $table->index(['category_id']);
            $table->index(['group_name']);
            $table->index(['type']);
            $table->index(['is_enabled', 'is_visible']);
            $table->index(['is_filterable', 'is_searchable']);
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
