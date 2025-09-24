<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CampaignCustomerSegmentResource\Pages;
use App\Models\Campaign;
use App\Models\CampaignCustomerSegment;
use App\Models\CustomerGroup;
use App\Models\User;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase as BaseTestCase;

final class CampaignCustomerSegmentResourceTest extends BaseTestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private Campaign $campaign;

    private CustomerGroup $customerGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->campaign = Campaign::factory()->create();
        $this->customerGroup = CustomerGroup::factory()->create();
    }

    public function test_can_list_campaign_customer_segments(): void
    {
        $segment = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(Pages\ListCampaignCustomerSegments::class)
            ->assertCanSeeTableRecords([$segment])
            ->assertCanRenderTableColumn('campaign.name')
            ->assertCanRenderTableColumn('customerGroup.name')
            ->assertCanRenderTableColumn('segment_type')
            ->assertCanRenderTableColumn('is_active');
    }

    public function test_can_create_campaign_customer_segment(): void
    {
        $segmentData = [
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
            'segment_type' => 'demographic',
            'segment_criteria' => [
                'age_range' => '25-35',
                'gender' => 'female',
            ],
            'targeting_tags' => ['young_adults', 'female'],
            'custom_conditions' => 'Age between 25-35 and female',
            'track_performance' => true,
            'auto_optimize' => false,
            'is_active' => true,
            'sort_order' => 1,
        ];

        $this->actingAs($this->adminUser);

        Livewire::test(Pages\CreateCampaignCustomerSegment::class)
            ->fillForm($segmentData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('campaign_customer_segments', [
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
            'segment_type' => 'demographic',
            'is_active' => true,
            'track_performance' => true,
            'auto_optimize' => false,
            'sort_order' => 1,
        ]);
    }

    public function test_can_edit_campaign_customer_segment(): void
    {
        $segment = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
            'segment_type' => 'demographic',
        ]);

        $this->actingAs($this->adminUser);

        $updatedData = [
            'segment_type' => 'behavioral',
            'segment_criteria' => [
                'purchase_frequency' => 'high',
                'loyalty_level' => 'vip',
            ],
            'targeting_tags' => ['frequent_buyers', 'vip_customers'],
            'custom_conditions' => 'High purchase frequency and VIP loyalty',
            'track_performance' => true,
            'auto_optimize' => true,
            'is_active' => false,
            'sort_order' => 5,
        ];

        Livewire::test(Pages\EditCampaignCustomerSegment::class, ['record' => $segment->getRouteKey()])
            ->fillForm($updatedData)
            ->call('save')
            ->assertHasNoFormErrors();

        $segment->refresh();
        $this->assertEquals('behavioral', $segment->segment_type);
        $this->assertFalse($segment->is_active);
        $this->assertTrue($segment->auto_optimize);
        $this->assertEquals(5, $segment->sort_order);
    }

    public function test_can_view_campaign_customer_segment(): void
    {
        $segment = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
            'segment_type' => 'geographic',
            'segment_criteria' => [
                'country' => 'Lithuania',
                'region' => 'Vilnius',
            ],
            'targeting_tags' => ['lithuania', 'vilnius'],
            'custom_conditions' => 'Located in Vilnius, Lithuania',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(Pages\ViewCampaignCustomerSegment::class, ['record' => $segment->getRouteKey()])
            ->assertCanSeeText('geographic')
            ->assertCanSeeText('Lithuania')
            ->assertCanSeeText('Vilnius');
    }

    public function test_can_delete_campaign_customer_segment(): void
    {
        $segment = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(Pages\ListCampaignCustomerSegments::class)
            ->callTableAction(DeleteAction::class, $segment)
            ->assertHasNoTableActionErrors();

        $this->assertSoftDeleted('campaign_customer_segments', ['id' => $segment->id]);
    }

    public function test_can_filter_by_segment_type(): void
    {
        $demographicSegment = CampaignCustomerSegment::factory()->demographic()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $behavioralSegment = CampaignCustomerSegment::factory()->behavioral()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(Pages\ListCampaignCustomerSegments::class)
            ->filterTable('segment_type', 'demographic')
            ->assertCanSeeTableRecords([$demographicSegment])
            ->assertCanNotSeeTableRecords([$behavioralSegment]);
    }

    public function test_can_filter_by_active_status(): void
    {
        $activeSegment = CampaignCustomerSegment::factory()->active()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $inactiveSegment = CampaignCustomerSegment::factory()->inactive()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(Pages\ListCampaignCustomerSegments::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords([$activeSegment])
            ->assertCanNotSeeTableRecords([$inactiveSegment]);
    }

    public function test_can_search_campaign_customer_segments(): void
    {
        $segment1 = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $segment2 = CampaignCustomerSegment::factory()->create([
            'campaign_id' => Campaign::factory()->create(['name' => 'Special Campaign'])->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(Pages\ListCampaignCustomerSegments::class)
            ->searchTable('Special')
            ->assertCanSeeTableRecords([$segment2])
            ->assertCanNotSeeTableRecords([$segment1]);
    }

    public function test_validates_required_fields(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(Pages\CreateCampaignCustomerSegment::class)
            ->fillForm([
                'campaign_id' => null,
                'customer_group_id' => null,
                'segment_type' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['campaign_id' => 'required'])
            ->assertHasFormErrors(['customer_group_id' => 'required'])
            ->assertHasFormErrors(['segment_type' => 'required']);
    }

    public function test_can_use_tabs_in_list_view(): void
    {
        $demographicSegment = CampaignCustomerSegment::factory()->demographic()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $behavioralSegment = CampaignCustomerSegment::factory()->behavioral()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $activeSegment = CampaignCustomerSegment::factory()->active()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $this->actingAs($this->adminUser);

        $component = Livewire::test(Pages\ListCampaignCustomerSegments::class);

        // Test demographic tab
        $component
            ->set('activeTab', 'demographic')
            ->assertCanSeeTableRecords([$demographicSegment])
            ->assertCanNotSeeTableRecords([$behavioralSegment]);

        // Test behavioral tab
        $component
            ->set('activeTab', 'behavioral')
            ->assertCanSeeTableRecords([$behavioralSegment])
            ->assertCanNotSeeTableRecords([$demographicSegment]);

        // Test active tab
        $component
            ->set('activeTab', 'active')
            ->assertCanSeeTableRecords([$activeSegment]);
    }

    public function test_can_bulk_delete_campaign_customer_segments(): void
    {
        $segment1 = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $segment2 = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(Pages\ListCampaignCustomerSegments::class)
            ->callTableBulkAction('delete', [$segment1, $segment2])
            ->assertHasNoTableBulkActionErrors();

        $this->assertSoftDeleted('campaign_customer_segments', ['id' => $segment1->id]);
        $this->assertSoftDeleted('campaign_customer_segments', ['id' => $segment2->id]);
    }

    public function test_can_restore_deleted_campaign_customer_segment(): void
    {
        $segment = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $segment->delete();

        $this->actingAs($this->adminUser);

        Livewire::test(Pages\ListCampaignCustomerSegments::class)
            ->filterTable('trashed', true)
            ->callTableAction('restore', $segment)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('campaign_customer_segments', [
            'id' => $segment->id,
            'deleted_at' => null,
        ]);
    }

    public function test_can_force_delete_campaign_customer_segment(): void
    {
        $segment = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $segment->delete();

        $this->actingAs($this->adminUser);

        Livewire::test(Pages\ListCampaignCustomerSegments::class)
            ->filterTable('trashed', true)
            ->callTableAction('forceDelete', $segment)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('campaign_customer_segments', [
            'id' => $segment->id,
        ]);
    }

    public function test_segment_criteria_is_stored_as_json(): void
    {
        $criteria = [
            'age_range' => '25-35',
            'gender' => 'female',
            'income_level' => 'high',
            'location' => 'Vilnius',
        ];

        $segment = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
            'segment_criteria' => $criteria,
        ]);

        $segment->refresh();
        $this->assertEquals($criteria, $segment->segment_criteria);
        $this->assertIsArray($segment->segment_criteria);
    }

    public function test_targeting_tags_is_stored_as_json(): void
    {
        $tags = ['young_adults', 'female', 'high_income', 'vilnius'];

        $segment = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
            'targeting_tags' => $tags,
        ]);

        $segment->refresh();
        $this->assertEquals($tags, $segment->targeting_tags);
        $this->assertIsArray($segment->targeting_tags);
    }
}
