<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createTranslation('sh_brand_translations', 'brand_id', function (Blueprint $table) {
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
        }, hasSlug: true);

        $this->createTranslation('sh_category_translations', 'category_id', function (Blueprint $table) {
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
        }, hasSlug: true);

        $this->createTranslation('sh_collection_translations', 'collection_id', function (Blueprint $table) {
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
        }, hasSlug: true);

        $this->createTranslation('sh_attribute_translations', 'attribute_id', function (Blueprint $table) {
            $table->string('name');
        });

        $this->createTranslation('sh_attribute_value_translations', 'attribute_value_id', function (Blueprint $table) {
            $table->string('value');
            $table->string('key')->nullable();
        });

        $this->createTranslation('sh_product_translations', 'product_id', function (Blueprint $table) {
            $table->string('name');
            $table->string('slug');
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
        }, hasSlug: true);

        $this->createTranslation('sh_legal_translations', 'legal_id', function (Blueprint $table) {
            $table->string('title');
            $table->string('slug');
            $table->longText('content');
        }, hasSlug: true);
    }

    public function down(): void
    {
        Schema::dropIfExists('sh_legal_translations');
        Schema::dropIfExists('sh_product_translations');
        Schema::dropIfExists('sh_attribute_value_translations');
        Schema::dropIfExists('sh_attribute_translations');
        Schema::dropIfExists('sh_collection_translations');
        Schema::dropIfExists('sh_category_translations');
        Schema::dropIfExists('sh_brand_translations');
    }

    private function createTranslation(string $tableName, string $parentKey, callable $columns, bool $hasSlug = false): void
    {
        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($parentKey, $columns, $hasSlug) {
            $table->id();
            $table->unsignedBigInteger($parentKey);
            $table->string('locale', 10);
            $columns($table);
            $table->timestamps();

            $table->index('locale');
            $table->unique([$parentKey, 'locale']);
            if ($hasSlug) {
                $table->unique(['locale', 'slug']);
            }
        });
    }
};
