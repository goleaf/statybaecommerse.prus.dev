<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attribute_values')) {
            return;
        }

        Schema::table('attribute_values', function (Blueprint $table) {
            if (! Schema::hasColumn('attribute_values', 'attribute_value_type')) {
                $table->string('attribute_value_type', 50)
                    ->default('text')
                    ->after('value');
                $table->index('attribute_value_type', 'attribute_values_attribute_value_type_index');
            }

            if (! Schema::hasColumn('attribute_values', 'valueable_type')) {
                $table->string('valueable_type')->nullable()->after('attribute_value_type');
                $table->index('valueable_type', 'attribute_values_valueable_type_index');
            }

            if (! Schema::hasColumn('attribute_values', 'valueable_id')) {
                $table->unsignedBigInteger('valueable_id')->nullable()->after('valueable_type');
                $table->index(['valueable_type', 'valueable_id'], 'attribute_values_valueable_type_valueable_id_index');
            }

            if (! Schema::hasColumn('attribute_values', 'is_searchable')) {
                $table->boolean('is_searchable')->default(false)->after('is_default');
                $table->index('is_searchable', 'attribute_values_is_searchable_index');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('attribute_values')) {
            return;
        }

        Schema::table('attribute_values', function (Blueprint $table) {
            if (Schema::hasColumn('attribute_values', 'is_searchable')) {
                $table->dropIndex('attribute_values_is_searchable_index');
                $table->dropColumn('is_searchable');
            }

            if (Schema::hasColumn('attribute_values', 'valueable_id')) {
                $table->dropIndex('attribute_values_valueable_type_valueable_id_index');
                $table->dropColumn('valueable_id');
            }

            if (Schema::hasColumn('attribute_values', 'valueable_type')) {
                $table->dropIndex('attribute_values_valueable_type_index');
                $table->dropColumn('valueable_type');
            }

            if (Schema::hasColumn('attribute_values', 'attribute_value_type')) {
                $table->dropIndex('attribute_values_attribute_value_type_index');
                $table->dropColumn('attribute_value_type');
            }
        });
    }
};
