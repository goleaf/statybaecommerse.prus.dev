<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CampaignProductTargetResource\Pages\CreateCampaignProductTarget;
use App\Filament\Resources\CampaignProductTargetResource\Pages\EditCampaignProductTarget;
use App\Filament\Resources\CampaignProductTargetResource\Pages\ListCampaignProductTargets;
use App\Filament\Resources\CampaignProductTargetResource\Pages\ViewCampaignProductTarget;
use App\Models\Brand;
use App\Models\Campaign;
use App\Models\CampaignProductTarget;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase as BaseTestCase;

final class CampaignProductTargetResourceTest extends BaseTestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_can_list_campaign_product_targets(): void
    {
        $campaign = Campaign::factory()->create();
        $product = Product::factory()->create();
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $collection = Collection::factory()->create();

        CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'product_id'  => $product->id,
            'target_type' => 'product',
            'priority'    => 80,
            'is_active'   => true,
        ]);

        CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'category_id' => $category->id,
            'target_type' => 'category',
            'priority'    => 60,
            'is_active'   => false,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListCampaignProductTargets::class)
            ->assertCanSeeTableRecords(CampaignProductTarget::all())
            ->assertCanSeeTableColumns([
                'id',
                'campaign.name',
                'target_type',
                'target_name',
                'priority',
                'is_active',
            ]);
    }

    public function test_can_create_campaign_product_target(): void
    {
        $campaign = Campaign::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($this->adminUser);

        Livewire::test(CreateCampaignProductTarget::class)
            ->fillForm([
                'campaign_id' => $campaign->id,
                'target_type' => 'product',
                'product_id'  => $product->id,
                'priority'    => 80,
                'weight'      => 10,
                'sort_order'  => 1,
                'is_active'   => true,
                'is_featured' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('campaign_product_targets', [
            'campaign_id' => $campaign->id,
            'product_id'  => $product->id,
            'target_type' => 'product',
            'priority'    => 80,
            'weight'      => 10,
            'sort_order'  => 1,
            'is_active'   => true,
            'is_featured' => false,
        ]);
    }

    public function test_can_edit_campaign_product_target(): void
    {
        $campaign = Campaign::factory()->create();
        $product = Product::factory()->create();
        $target = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'product_id'  => $product->id,
            'target_type' => 'product',
            'priority'    => 50,
            'is_active'   => false,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(EditCampaignProductTarget::class, [
            'record' => $target->getRouteKey(),
        ])
            ->fillForm([
                'priority'    => 90,
                'is_active'   => true,
                'is_featured' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('campaign_product_targets', [
            'id'          => $target->id,
            'priority'    => 90,
            'is_active'   => true,
            'is_featured' => true,
        ]);
    }

    public function test_can_view_campaign_product_target(): void
    {
        $campaign = Campaign::factory()->create();
        $product = Product::factory()->create();
        $target = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'product_id'  => $product->id,
            'target_type' => 'product',
            // Pin the priority value so the infolist assertion can reliably detect it in the rendered output.
            'priority' => 42,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ViewCampaignProductTarget::class, [
            'record' => $target->getRouteKey(),
        ])
            // Confirm that the infolist payload exposes the expected record context details for the viewer page.
            ->assertSet('record.id', $target->id)
            ->assertSee((string) $target->id)
            ->assertSee($campaign->name)
            ->assertSee((string) $target->priority);
    }

    public function test_can_filter_by_campaign(): void
    {
        $campaign1 = Campaign::factory()->create(['name' => 'Campaign 1']);
        $campaign2 = Campaign::factory()->create(['name' => 'Campaign 2']);

        $target1 = CampaignProductTarget::factory()->create(['campaign_id' => $campaign1->id]);
        $target2 = CampaignProductTarget::factory()->create(['campaign_id' => $campaign2->id]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListCampaignProductTargets::class)
            ->filterTable('campaign_id', $campaign1->id)
            ->assertSet('tableFilters.campaign_id.value', $campaign1->id)
            ->assertCanSeeTableRecords([$target1]);
    }

    public function test_can_filter_by_target_type(): void
    {
        $campaign = Campaign::factory()->create();
        $product = Product::factory()->create();
        $category = Category::factory()->create();

        $productTarget = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'product_id'  => $product->id,
            'target_type' => 'product',
        ]);

        $categoryTarget = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'category_id' => $category->id,
            'target_type' => 'category',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListCampaignProductTargets::class)
            ->filterTable('target_type', 'product')
            ->assertSet('tableFilters.target_type.value', 'product')
            ->assertCanSeeTableRecords([$productTarget]);
    }

    public function test_can_filter_by_active_status(): void
    {
        $campaign = Campaign::factory()->create();

        $activeTarget = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'is_active'   => true,
        ]);

        $inactiveTarget = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'is_active'   => false,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListCampaignProductTargets::class)
            ->filterTable('is_active', true)
            ->assertSet('tableFilters.is_active.value', true)
            ->assertCanSeeTableRecords([$activeTarget]);
    }

    public function test_can_search_campaign_product_targets(): void
    {
        $campaign = Campaign::factory()->create(['name' => 'Summer Sale']);
        $product = Product::factory()->create(['name' => 'Summer T-Shirt']);
        $target = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'product_id'  => $product->id,
            'target_type' => 'product',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListCampaignProductTargets::class)
            ->searchTable('Summer')
            ->assertCanSeeTableRecords([$target]);
    }

    public function test_can_bulk_activate_targets(): void
    {
        $campaign = Campaign::factory()->create();

        $target1 = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'is_active'   => false,
        ]);

        $target2 = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'is_active'   => false,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListCampaignProductTargets::class)
            ->callTableBulkAction('activate', [$target1, $target2])
            ->assertNotified();

        $this->assertDatabaseHas('campaign_product_targets', [
            'id'        => $target1->id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('campaign_product_targets', [
            'id'        => $target2->id,
            'is_active' => true,
        ]);
    }

    public function test_can_bulk_deactivate_targets(): void
    {
        $campaign = Campaign::factory()->create();

        $target1 = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'is_active'   => true,
        ]);

        $target2 = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'is_active'   => true,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListCampaignProductTargets::class)
            ->callTableBulkAction('deactivate', [$target1, $target2])
            ->assertNotified();

        $this->assertDatabaseHas('campaign_product_targets', [
            'id'        => $target1->id,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('campaign_product_targets', [
            'id'        => $target2->id,
            'is_active' => false,
        ]);
    }

    public function test_can_bulk_feature_targets(): void
    {
        $campaign = Campaign::factory()->create();

        $target1 = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'is_featured' => false,
        ]);

        $target2 = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'is_featured' => false,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListCampaignProductTargets::class)
            ->callTableBulkAction('feature', [$target1, $target2])
            ->assertNotified();

        $this->assertDatabaseHas('campaign_product_targets', [
            'id'          => $target1->id,
            'is_featured' => true,
        ]);

        $this->assertDatabaseHas('campaign_product_targets', [
            'id'          => $target2->id,
            'is_featured' => true,
        ]);
    }

    public function test_can_bulk_unfeature_targets(): void
    {
        $campaign = Campaign::factory()->create();

        $target1 = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'is_featured' => true,
        ]);

        $target2 = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'is_featured' => true,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListCampaignProductTargets::class)
            ->callTableBulkAction('unfeature', [$target1, $target2])
            ->assertNotified();

        $this->assertDatabaseHas('campaign_product_targets', [
            'id'          => $target1->id,
            'is_featured' => false,
        ]);

        $this->assertDatabaseHas('campaign_product_targets', [
            'id'          => $target2->id,
            'is_featured' => false,
        ]);
    }

    public function test_can_delete_campaign_product_target(): void
    {
        $campaign = Campaign::factory()->create();
        $product = Product::factory()->create();
        $target = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'product_id'  => $product->id,
            'target_type' => 'product',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(EditCampaignProductTarget::class, [
            'record' => $target->getRouteKey(),
        ])
            ->callAction('delete')
            ->assertRedirect();

        $this->assertDatabaseMissing('campaign_product_targets', [
            'id' => $target->id,
        ]);
    }

    public function test_can_bulk_delete_targets(): void
    {
        $campaign = Campaign::factory()->create();

        $target1 = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
        ]);

        $target2 = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListCampaignProductTargets::class)
            ->callTableBulkAction('delete', [$target1, $target2])
            ->assertNotified();

        $this->assertDatabaseMissing('campaign_product_targets', [
            'id' => $target1->id,
        ]);

        $this->assertDatabaseMissing('campaign_product_targets', [
            'id' => $target2->id,
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(CreateCampaignProductTarget::class)
            ->fillForm([
                'campaign_id' => null,
                'target_type' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['campaign_id', 'target_type']);
    }

    public function test_validates_target_type_specific_fields(): void
    {
        $campaign = Campaign::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($this->adminUser);

        Livewire::test(CreateCampaignProductTarget::class)
            ->fillForm([
                'campaign_id' => $campaign->id,
                'target_type' => 'product',
                'product_id'  => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['product_id']);
    }

    public function test_can_create_target_with_all_types(): void
    {
        $campaign = Campaign::factory()->create();
        $product = Product::factory()->create();
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $collection = Collection::factory()->create();

        $this->actingAs($this->adminUser);

        // Test product target
        Livewire::test(CreateCampaignProductTarget::class)
            ->fillForm([
                'campaign_id' => $campaign->id,
                'target_type' => 'product',
                'product_id'  => $product->id,
                'priority'    => 80,
                'is_active'   => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        // Test category target
        Livewire::test(CreateCampaignProductTarget::class)
            ->fillForm([
                'campaign_id' => $campaign->id,
                'target_type' => 'category',
                'category_id' => $category->id,
                'priority'    => 70,
                'is_active'   => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        // Test brand target
        Livewire::test(CreateCampaignProductTarget::class)
            ->fillForm([
                'campaign_id' => $campaign->id,
                'target_type' => 'brand',
                'brand_id'    => $brand->id,
                'priority'    => 60,
                'is_active'   => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        // Test collection target
        Livewire::test(CreateCampaignProductTarget::class)
            ->fillForm([
                'campaign_id'   => $campaign->id,
                'target_type'   => 'collection',
                'collection_id' => $collection->id,
                'priority'      => 50,
                'is_active'     => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        // The campaign factory seeds one default target, so four manual creates result in five total rows.
        $this->assertDatabaseCount('campaign_product_targets', 5);
    }

    public function test_can_sort_by_priority(): void
    {
        $campaign = Campaign::factory()->create();

        $lowPriority = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'priority'    => 30,
        ]);

        $highPriority = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'priority'    => 90,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListCampaignProductTargets::class)
            ->sortTable('priority', 'desc')
            ->assertCanSeeTableRecords([$highPriority, $lowPriority]);
    }

    public function test_can_filter_by_high_priority(): void
    {
        $campaign = Campaign::factory()->create();

        $highPriority = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'priority'    => 85,
        ]);

        $lowPriority = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'priority'    => 50,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListCampaignProductTargets::class)
            ->filterTable('high_priority')
            ->assertCanSeeTableRecords([$highPriority])
            ->assertCanNotSeeTableRecords([$lowPriority]);
    }

    public function test_can_filter_by_recent(): void
    {
        $campaign = Campaign::factory()->create();

        $recent = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'created_at'  => now()->subDays(3),
        ]);

        $old = CampaignProductTarget::factory()->create([
            'campaign_id' => $campaign->id,
            'created_at'  => now()->subDays(10),
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListCampaignProductTargets::class)
            ->filterTable('recent')
            ->assertCanSeeTableRecords([$recent])
            ->assertCanNotSeeTableRecords([$old]);
    }
}
