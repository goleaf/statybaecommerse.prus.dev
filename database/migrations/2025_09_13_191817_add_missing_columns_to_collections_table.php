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
        if (! Schema::hasTable('collections')) {
            return;
        }

        Schema::table('collections', function (Blueprint $table) {
            if (! Schema::hasColumn('collections', 'meta_title')) {
                $table->string('meta_title')->nullable();
            }

            if (! Schema::hasColumn('collections', 'meta_description')) {
                $table->text('meta_description')->nullable();
            }

            if (! Schema::hasColumn('collections', 'meta_keywords')) {
                $table->string('meta_keywords')->nullable();
            }

            if (! Schema::hasColumn('collections', 'display_type')) {
                $table->string('display_type')->default('grid');
            }

            if (! Schema::hasColumn('collections', 'products_per_page')) {
                $table->integer('products_per_page')->default(12);
            }

            if (! Schema::hasColumn('collections', 'show_filters')) {
                $table->boolean('show_filters')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('collections')) {
            return;
        }

        $columns = [
            'meta_title',
            'meta_description',
            'meta_keywords',
            'display_type',
            'products_per_page',
            'show_filters',
        ];

        $existingColumns = array_values(array_filter($columns, fn (string $column): bool => Schema::hasColumn('collections', $column)));

        if ($existingColumns === []) {
            return;
        }

        Schema::table('collections', function (Blueprint $table) use ($existingColumns): void {
            $table->dropColumn($existingColumns);
        });
    }
};
