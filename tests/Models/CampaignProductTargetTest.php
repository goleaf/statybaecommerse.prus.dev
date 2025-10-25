<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Brand;
use App\Models\Campaign;
use App\Models\CampaignProductTarget;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection as SupportCollection;
use Tests\TestCase;

/**
 * @internal
 */
final class CampaignProductTargetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Disabling related seeding keeps the factory output deterministic for targeted
        // model assertions and prevents unrelated campaign targets from polluting tests.
        config(['factory.seed_campaign_relations' => false]);
    }

    public function test_casts_configuration_is_explicit(): void
    {
        // Using a bound closure keeps the test focused on the developer-defined casts
        // without being affected by Laravel's internal normalization of the cast map.
        $model = new CampaignProductTarget;

        $casts = (function (): array {
            // Binding a closure to the model instance grants temporary access to the
            // protected casts definition without weakening the production visibility.
            /** @var array<string, string> $casts */
            $casts = $this->casts();

            return $casts;
        })->call($model);

        $this->assertSame([
            'campaign_id'   => 'integer',
            'product_id'    => 'integer',
            'category_id'   => 'integer',
            'brand_id'      => 'integer',
            'collection_id' => 'integer',
            'priority'      => 'integer',
            'weight'        => 'integer',
            'sort_order'    => 'integer',
            'is_active'     => 'boolean',
            'is_featured'   => 'boolean',
            'conditions'    => 'array',
        ], $casts);
    }

    public function test_it_exposes_expected_relationships(): void
    {
        // Creating dependent models ensures that each relationship returns a concrete
        // instance and guards against accidental morph-map changes.
        $campaign = Campaign::factory()->create();
        $product = Product::factory()->create();
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $collection = Collection::factory()->create();

        $target = CampaignProductTarget::factory()->create([
            'campaign_id'   => $campaign->id,
            'product_id'    => $product->id,
            'category_id'   => $category->id,
            'brand_id'      => $brand->id,
            'collection_id' => $collection->id,
            'target_type'   => 'product',
        ]);

        $campaignRelation = $target->campaign;
        self::assertInstanceOf(Campaign::class, $campaignRelation);
        $this->assertTrue($campaignRelation->is($campaign));

        $productRelation = $target->product;
        self::assertInstanceOf(Product::class, $productRelation);
        $this->assertTrue($productRelation->is($product));

        $categoryRelation = $target->category;
        self::assertInstanceOf(Category::class, $categoryRelation);
        $this->assertTrue($categoryRelation->is($category));

        $brandRelation = $target->brand;
        self::assertInstanceOf(Brand::class, $brandRelation);
        $this->assertTrue($brandRelation->is($brand));

        $collectionRelation = $target->collection;
        self::assertInstanceOf(Collection::class, $collectionRelation);
        $this->assertTrue($collectionRelation->is($collection));
    }

    public function test_dynamic_target_name_and_identifier_accessors(): void
    {
        // Explicit strings make it obvious which attribute is being asserted within each
        // accessor branch and doubles as regression coverage for translations.
        $campaign = Campaign::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Featured Drill',
            'sku'  => 'DRILL-001',
        ]);
        $category = Category::factory()->create([
            'name' => 'Power Tools',
            'slug' => 'power-tools',
        ]);
        $brand = Brand::factory()->create([
            'name' => 'Acme Tools',
            'slug' => 'acme-tools',
        ]);
        $collection = Collection::factory()->create([
            'name' => 'Holiday Specials',
            'slug' => 'holiday-specials',
        ]);

        $productTarget = CampaignProductTarget::factory()
            ->product()
            ->create([
                'campaign_id' => $campaign->id,
                'product_id'  => $product->id,
            ]);
        $categoryTarget = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'target_type' => 'category',
            'category_id' => $category->id,
        ]);
        $brandTarget = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'target_type' => 'brand',
            'brand_id'    => $brand->id,
        ]);
        $collectionTarget = CampaignProductTarget::factory()->create([
            'campaign_id'   => $campaign->id,
            'target_type'   => 'collection',
            'collection_id' => $collection->id,
        ]);

        $this->assertSame('Featured Drill', $productTarget->target_name);
        $this->assertSame('DRILL-001', $productTarget->target_identifier);
        $this->assertSame('Power Tools', $categoryTarget->target_name);
        $this->assertSame('power-tools', $categoryTarget->target_identifier);
        $this->assertSame('Acme Tools', $brandTarget->target_name);
        $this->assertSame('acme-tools', $brandTarget->target_identifier);
        $this->assertSame('Holiday Specials', $collectionTarget->target_name);
        $this->assertSame('holiday-specials', $collectionTarget->target_identifier);
    }

    public function test_boolean_helpers_reflect_flags(): void
    {
        // Using separate records prevents cross-test pollution and keeps assertions
        // straightforward for the helper wrappers.
        $activeTarget = CampaignProductTarget::factory()->create([
            'is_active'   => true,
            'is_featured' => false,
        ]);
        $featuredTarget = CampaignProductTarget::factory()->create([
            'is_active'   => true,
            'is_featured' => true,
        ]);

        $this->assertTrue($activeTarget->isActive());
        $this->assertFalse($activeTarget->isFeatured());
        $this->assertTrue($featuredTarget->isActive());
        $this->assertTrue($featuredTarget->isFeatured());
    }

    public function test_target_model_helper_returns_related_instance(): void
    {
        // Checking every branch ensures the helper keeps pace with any future target types
        // and validates the null fallback when no association exists.
        $campaign = Campaign::factory()->create();
        $product = Product::factory()->create();
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $collection = Collection::factory()->create();

        $productTarget = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'target_type' => 'product',
            'product_id'  => $product->id,
        ]);
        $categoryTarget = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'target_type' => 'category',
            'category_id' => $category->id,
        ]);
        $brandTarget = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'target_type' => 'brand',
            'brand_id'    => $brand->id,
        ]);
        $collectionTarget = CampaignProductTarget::factory()->create([
            'campaign_id'   => $campaign->id,
            'target_type'   => 'collection',
            'collection_id' => $collection->id,
        ]);
        $unknownTarget = CampaignProductTarget::factory()->create([
            'campaign_id'   => $campaign->id,
            'target_type'   => 'unknown',
            'product_id'    => null,
            'category_id'   => null,
            'brand_id'      => null,
            'collection_id' => null,
        ]);

        $this->assertTrue($productTarget->getTargetModel()?->is($product));
        $this->assertTrue($categoryTarget->getTargetModel()?->is($category));
        $this->assertTrue($brandTarget->getTargetModel()?->is($brand));
        $this->assertTrue($collectionTarget->getTargetModel()?->is($collection));
        $this->assertNull($unknownTarget->getTargetModel());
    }

    public function test_local_scopes_cover_common_filters(): void
    {
        // The collection assertions make it easy to visualise how each scope trims the
        // result set without relying on manual array comparisons.
        $campaignA = Campaign::factory()->create();
        $campaignB = Campaign::factory()->create();

        $activeFeatured = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaignA->id,
            'priority'    => 90,
            'target_type' => 'product',
            'is_active'   => true,
            'is_featured' => true,
        ]);
        $inactive = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaignA->id,
            'priority'    => 20,
            'target_type' => 'category',
            'is_active'   => false,
            'is_featured' => false,
        ]);
        $highPriorityDifferentCampaign = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaignB->id,
            'priority'    => 95,
            'target_type' => 'product',
            'is_active'   => true,
            'is_featured' => false,
        ]);

        $this->assertEqualsCanonicalizing(
            [$activeFeatured->id, $highPriorityDifferentCampaign->id],
            CampaignProductTarget::query()
                ->withoutGlobalScopes()
                ->active()
                ->pluck('id')
                ->all()
        );

        $this->assertEquals(
            [$activeFeatured->id],
            CampaignProductTarget::query()
                ->withoutGlobalScopes()
                ->featured()
                ->pluck('id')
                ->all()
        );

        $this->assertEqualsCanonicalizing(
            [$activeFeatured->id, $highPriorityDifferentCampaign->id],
            CampaignProductTarget::query()
                ->withoutGlobalScopes()
                ->highPriority()
                ->pluck('id')
                ->all()
        );

        $this->assertEquals(
            [$activeFeatured->id, $highPriorityDifferentCampaign->id],
            CampaignProductTarget::query()
                ->withoutGlobalScopes()
                ->byType('product')
                ->pluck('id')
                ->all()
        );

        $this->assertEquals(
            [$activeFeatured->id, $inactive->id],
            CampaignProductTarget::query()
                ->withoutGlobalScopes()
                ->byCampaign($campaignA->id)
                ->pluck('id')
                ->all()
        );
    }

    public function test_global_active_scope_hides_inactive_targets_by_default(): void
    {
        // Using collections gives us expressive assertions while keeping ordering strict
        // and highlights the implicit global scope behaviour for future refactors.
        $activeTarget = CampaignProductTarget::factory()->create([
            'is_active' => true,
        ]);
        CampaignProductTarget::factory()->create([
            'is_active' => false,
        ]);

        $visibleTargets = CampaignProductTarget::all();
        $this->assertInstanceOf(SupportCollection::class, $visibleTargets);

        $visibleIds = $visibleTargets
            ->map(static fn (CampaignProductTarget $target): int => $target->id)
            ->values();

        $this->assertSame([$activeTarget->id], $visibleIds->all());
        $this->assertCount(2, CampaignProductTarget::withoutGlobalScopes()->get());
    }
}
