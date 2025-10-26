<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Translations\ProductTranslation;
use App\Services\CatalogIntegrityService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Throwable;

final class CatalogIntegrityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_no_issues_for_empty_catalog(): void
    {
        $service = app(CatalogIntegrityService::class);
        $report = $service->validate();

        $this->assertFalse($report->hasIssues());
        $this->assertSame([], $report->slugConflicts);
        $this->assertSame([], $report->categoryCycles);
        $this->assertSame([], $report->attributeGroupMismatches);
    }

    public function test_detects_duplicate_translation_slugs(): void
    {
        /** @var Product $productA */
        $productA = Product::factory()->create();
        /** @var Product $productB */
        $productB = Product::factory()->create();

        try {
            Schema::table('product_translations', function (Blueprint $table): void {
                $table->dropUnique('product_translations_locale_slug_unique');
            });
        } catch (Throwable $exception) {
            try {
                Schema::table('product_translations', function (Blueprint $table): void {
                    $table->dropUnique(['locale', 'slug']);
                });
            } catch (Throwable) {
                // Ignore: fall back to raw statement below.
            }

            DB::statement('DROP INDEX IF EXISTS product_translations_locale_slug_unique');

            if (Schema::hasTable('product_translations')) {
                Schema::drop('product_translations');
            }

            Schema::create('product_translations', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->string('locale', 10);
                $table->string('name')->nullable();
                $table->string('slug')->nullable();
                $table->text('summary')->nullable();
                $table->longText('description')->nullable();
                $table->longText('short_description')->nullable();
                $table->string('seo_title')->nullable();
                $table->text('seo_description')->nullable();
                $table->text('meta_keywords')->nullable();
                $table->string('alt_text')->nullable();
                $table->timestamps();

                $table->index('product_id');
                $table->index('locale');
                $table->unique(['product_id', 'locale']);
            });
        }

        ProductTranslation::factory()->create([
            'product_id' => $productA->id,
            'locale'     => 'lt',
            'slug'       => 'duplicated-slug',
        ]);

        ProductTranslation::factory()->create([
            'product_id' => $productB->id,
            'locale'     => 'lt',
            'slug'       => 'duplicated-slug',
        ]);

        $service = app(CatalogIntegrityService::class);
        $report = $service->validate();

        $this->assertTrue($report->hasIssues());
        $this->assertNotEmpty($report->slugConflicts);

        $conflict = $report->slugConflicts[0];
        $this->assertSame('product', $conflict['entity']);
        $this->assertSame('lt', $conflict['locale']);
        $this->assertSame('duplicated-slug', $conflict['slug']);
        $this->assertEqualsCanonicalizing([$productA->id, $productB->id], $conflict['entity_ids']);
    }

    public function test_detects_category_cycles(): void
    {
        /** @var Category $root */
        $root = Category::factory()->create(['parent_id' => null, 'slug' => 'root']);
        /** @var Category $child */
        $child = Category::factory()->create(['parent_id' => $root->id, 'slug' => 'child']);
        /** @var Category $grandchild */
        $grandchild = Category::factory()->create(['parent_id' => $child->id, 'slug' => 'grandchild']);

        $root->update(['parent_id' => $grandchild->id]);

        $service = app(CatalogIntegrityService::class);
        $report = $service->validate();

        $this->assertTrue($report->hasIssues());
        $this->assertNotEmpty($report->categoryCycles);

        $cycle = $report->categoryCycles[0];
        $this->assertEqualsCanonicalizing([
            $root->id,
            $child->id,
            $grandchild->id,
        ], $cycle['category_ids']);
        $this->assertEqualsCanonicalizing([
            $root->slug,
            $child->slug,
            $grandchild->slug,
        ], $cycle['slugs']);
    }

    public function test_detects_variant_attribute_group_mismatches(): void
    {
        /** @var Product $product */
        $product = Product::factory()->create();
        /** @var ProductVariant $variantA */
        $variantA = ProductVariant::factory()->create(['product_id' => $product->id]);
        /** @var ProductVariant $variantB */
        $variantB = ProductVariant::factory()->create(['product_id' => $product->id]);

        /** @var Attribute $color */
        $color = Attribute::factory()->create(['group_name' => 'appearance']);
        /** @var Attribute $size */
        $size = Attribute::factory()->create(['group_name' => 'dimensions']);
        /** @var Attribute $material */
        $material = Attribute::factory()->create(['group_name' => 'materials']);

        /** @var AttributeValue $colorValueA */
        $colorValueA = AttributeValue::factory()->create(['attribute_id' => $color->id]);
        /** @var AttributeValue $colorValueB */
        $colorValueB = AttributeValue::factory()->create(['attribute_id' => $color->id]);
        /** @var AttributeValue $sizeValue */
        $sizeValue = AttributeValue::factory()->create(['attribute_id' => $size->id]);
        /** @var AttributeValue $materialValue */
        $materialValue = AttributeValue::factory()->create(['attribute_id' => $material->id]);

        $variantA->attributes()->attach($colorValueA->id, ['attribute_id' => $color->id]);
        $variantA->attributes()->attach($sizeValue->id, ['attribute_id' => $size->id]);

        $variantB->attributes()->attach($colorValueB->id, ['attribute_id' => $color->id]);
        $variantB->attributes()->attach($materialValue->id, ['attribute_id' => $material->id]);

        $service = app(CatalogIntegrityService::class);
        $report = $service->validate();

        $this->assertTrue($report->hasIssues());
        $this->assertNotEmpty($report->attributeGroupMismatches);

        $mismatch = $report->attributeGroupMismatches[0];
        $this->assertSame($product->id, $mismatch['product_id']);
        $this->assertEquals(['appearance', 'dimensions'], $mismatch['expected']);
        $this->assertArrayHasKey($variantB->id, $mismatch['variants']);
        $this->assertSame(['appearance', 'materials'], $mismatch['variants'][$variantB->id]);
    }
}
