<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Campaign;
use App\Models\CampaignCustomerSegment;
use App\Models\CustomerGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CampaignCustomerSegmentTest extends TestCase
{
    use RefreshDatabase;

    private Campaign $campaign;

    private CustomerGroup $customerGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->campaign = Campaign::factory()->create();
        $this->customerGroup = CustomerGroup::factory()->create();
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

        $segment = CampaignCustomerSegment::create($segmentData);

        $this->assertInstanceOf(CampaignCustomerSegment::class, $segment);
        $this->assertEquals($this->campaign->id, $segment->campaign_id);
        $this->assertEquals($this->customerGroup->id, $segment->customer_group_id);
        $this->assertEquals('demographic', $segment->segment_type);
        $this->assertTrue($segment->track_performance);
        $this->assertFalse($segment->auto_optimize);
        $this->assertTrue($segment->is_active);
        $this->assertEquals(1, $segment->sort_order);
    }

    public function test_belongs_to_campaign(): void
    {
        $segment = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $this->assertInstanceOf(Campaign::class, $segment->campaign);
        $this->assertEquals($this->campaign->id, $segment->campaign->id);
    }

    public function test_belongs_to_customer_group(): void
    {
        $segment = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $this->assertInstanceOf(CustomerGroup::class, $segment->customerGroup);
        $this->assertEquals($this->customerGroup->id, $segment->customerGroup->id);
    }

    public function test_segment_criteria_is_cast_to_array(): void
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
        $this->assertIsArray($segment->segment_criteria);
        $this->assertEquals($criteria, $segment->segment_criteria);
    }

    public function test_targeting_tags_is_cast_to_array(): void
    {
        $tags = ['young_adults', 'female', 'high_income', 'vilnius'];

        $segment = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
            'targeting_tags' => $tags,
        ]);

        $segment->refresh();
        $this->assertIsArray($segment->targeting_tags);
        $this->assertEquals($tags, $segment->targeting_tags);
    }

    public function test_boolean_fields_are_cast_correctly(): void
    {
        $segment = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
            'track_performance' => true,
            'auto_optimize' => false,
            'is_active' => true,
        ]);

        $segment->refresh();
        $this->assertTrue($segment->track_performance);
        $this->assertFalse($segment->auto_optimize);
        $this->assertTrue($segment->is_active);
        $this->assertIsBool($segment->track_performance);
        $this->assertIsBool($segment->auto_optimize);
        $this->assertIsBool($segment->is_active);
    }

    public function test_sort_order_is_cast_to_integer(): void
    {
        $segment = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
            'sort_order' => 5,
        ]);

        $segment->refresh();
        $this->assertIsInt($segment->sort_order);
        $this->assertEquals(5, $segment->sort_order);
    }

    public function test_has_soft_deletes(): void
    {
        $segment = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $segmentId = $segment->id;
        $segment->delete();

        $this->assertSoftDeleted('campaign_customer_segments', ['id' => $segmentId]);

        $segment->restore();
        $this->assertDatabaseHas('campaign_customer_segments', [
            'id' => $segmentId,
            'deleted_at' => null,
        ]);
    }

    public function test_can_scope_by_segment_type(): void
    {
        $demographicSegment = CampaignCustomerSegment::factory()->demographic()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $behavioralSegment = CampaignCustomerSegment::factory()->behavioral()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $demographicSegments = CampaignCustomerSegment::where('segment_type', 'demographic')->get();
        $behavioralSegments = CampaignCustomerSegment::where('segment_type', 'behavioral')->get();

        $this->assertCount(1, $demographicSegments);
        $this->assertCount(1, $behavioralSegments);
        $this->assertEquals($demographicSegment->id, $demographicSegments->first()->id);
        $this->assertEquals($behavioralSegment->id, $behavioralSegments->first()->id);
    }

    public function test_can_scope_by_active_status(): void
    {
        $activeSegment = CampaignCustomerSegment::factory()->active()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $inactiveSegment = CampaignCustomerSegment::factory()->inactive()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $activeSegments = CampaignCustomerSegment::where('is_active', true)->get();
        $inactiveSegments = CampaignCustomerSegment::where('is_active', false)->get();

        $this->assertCount(1, $activeSegments);
        $this->assertCount(1, $inactiveSegments);
        $this->assertEquals($activeSegment->id, $activeSegments->first()->id);
        $this->assertEquals($inactiveSegment->id, $inactiveSegments->first()->id);
    }

    public function test_can_scope_by_campaign(): void
    {
        $campaign2 = Campaign::factory()->create();

        $segment1 = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $segment2 = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $campaign2->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $campaign1Segments = CampaignCustomerSegment::where('campaign_id', $this->campaign->id)->get();
        $campaign2Segments = CampaignCustomerSegment::where('campaign_id', $campaign2->id)->get();

        $this->assertCount(1, $campaign1Segments);
        $this->assertCount(1, $campaign2Segments);
        $this->assertEquals($segment1->id, $campaign1Segments->first()->id);
        $this->assertEquals($segment2->id, $campaign2Segments->first()->id);
    }

    public function test_can_scope_by_customer_group(): void
    {
        $customerGroup2 = CustomerGroup::factory()->create();

        $segment1 = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $segment2 = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $customerGroup2->id,
        ]);

        $group1Segments = CampaignCustomerSegment::where('customer_group_id', $this->customerGroup->id)->get();
        $group2Segments = CampaignCustomerSegment::where('customer_group_id', $customerGroup2->id)->get();

        $this->assertCount(1, $group1Segments);
        $this->assertCount(1, $group2Segments);
        $this->assertEquals($segment1->id, $group1Segments->first()->id);
        $this->assertEquals($segment2->id, $group2Segments->first()->id);
    }

    public function test_can_order_by_sort_order(): void
    {
        $segment1 = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
            'sort_order' => 3,
        ]);

        $segment2 = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
            'sort_order' => 1,
        ]);

        $segment3 = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
            'sort_order' => 2,
        ]);

        $orderedSegments = CampaignCustomerSegment::orderBy('sort_order')->get();

        $this->assertEquals($segment2->id, $orderedSegments[0]->id);
        $this->assertEquals($segment3->id, $orderedSegments[1]->id);
        $this->assertEquals($segment1->id, $orderedSegments[2]->id);
    }

    public function test_fillable_attributes(): void
    {
        $fillableAttributes = [
            'campaign_id',
            'customer_group_id',
            'segment_type',
            'segment_criteria',
            'targeting_tags',
            'custom_conditions',
            'track_performance',
            'auto_optimize',
            'is_active',
            'sort_order',
        ];

        $segment = new CampaignCustomerSegment;
        $this->assertEquals($fillableAttributes, $segment->getFillable());
    }

    public function test_can_access_campaign_through_relationship(): void
    {
        $segment = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $this->assertTrue($segment->campaign()->exists());
        $this->assertEquals($this->campaign->name, $segment->campaign->name);
    }

    public function test_can_access_customer_group_through_relationship(): void
    {
        $segment = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
        ]);

        $this->assertTrue($segment->customerGroup()->exists());
        $this->assertEquals($this->customerGroup->name, $segment->customerGroup->name);
    }

    public function test_segment_criteria_can_be_null(): void
    {
        $segment = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
            'segment_criteria' => null,
        ]);

        $segment->refresh();
        $this->assertNull($segment->segment_criteria);
    }

    public function test_targeting_tags_can_be_null(): void
    {
        $segment = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
            'targeting_tags' => null,
        ]);

        $segment->refresh();
        $this->assertNull($segment->targeting_tags);
    }

    public function test_custom_conditions_can_be_null(): void
    {
        $segment = CampaignCustomerSegment::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_group_id' => $this->customerGroup->id,
            'custom_conditions' => null,
        ]);

        $segment->refresh();
        $this->assertNull($segment->custom_conditions);
    }
}
