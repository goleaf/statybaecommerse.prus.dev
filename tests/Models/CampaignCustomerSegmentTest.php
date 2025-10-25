<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Campaign;
use App\Models\CampaignCustomerSegment;
use App\Models\CustomerGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 */
final class CampaignCustomerSegmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_segment(): void
    {
        // Act: create a segment through the factory to ensure the HasFactory integration works end-to-end.
        $segment = CampaignCustomerSegment::factory()->create();

        // Assert: the created model should be persisted and resolvable from the database layer.
        $this->assertInstanceOf(CampaignCustomerSegment::class, $segment);
        $this->assertDatabaseHas('campaign_customer_segments', ['id' => $segment->id]);
    }

    public function test_fillable_attributes_are_mass_assignable(): void
    {
        // Arrange: build related models so the foreign keys remain valid during creation.
        $campaign = Campaign::factory()->create();
        $customerGroup = CustomerGroup::factory()->create();

        $payload = [
            'campaign_id'       => $campaign->id,
            'customer_group_id' => $customerGroup->id,
            'segment_type'      => 'demographic',
            'segment_criteria'  => ['age_range' => '18-24'],
            'targeting_tags'    => ['youth', 'urban'],
            'custom_conditions' => ['minimum_orders' => 2],
            'track_performance' => true,
            'auto_optimize'     => false,
            'is_active'         => true,
            'sort_order'        => 5,
        ];

        // Act: mass assign the payload to confirm all fillable properties work without triggering MassAssignmentException.
        $segment = CampaignCustomerSegment::create($payload);

        // Assert: verify both persistence and casted accessors to guarantee the payload survived intact.
        $this->assertSame($payload['segment_type'], $segment->segment_type);
        $this->assertSame($payload['segment_criteria'], $segment->segment_criteria);
        $this->assertSame($payload['targeting_tags'], $segment->targeting_tags);
        $this->assertSame($payload['custom_conditions'], $segment->custom_conditions);
        $this->assertTrue($segment->track_performance);
        $this->assertFalse($segment->auto_optimize);
        $this->assertTrue($segment->is_active);
        $this->assertSame(5, $segment->sort_order);
    }

    public function test_casts_transform_attributes(): void
    {
        // Arrange: prepare attributes with mixed value types to validate array and boolean casting behaviour.
        $segment = CampaignCustomerSegment::factory()->create([
            'segment_criteria'  => ['key' => 'value'],
            'targeting_tags'    => ['first', 'second'],
            'track_performance' => true,
            'auto_optimize'     => false,
            'is_active'         => true,
            'sort_order'        => 10,
        ]);

        // Assert: make sure each attribute resolves to the expected PHP type once retrieved from the model instance.
        $this->assertIsArray($segment->segment_criteria);
        $this->assertIsArray($segment->targeting_tags);
        $this->assertIsBool($segment->track_performance);
        $this->assertTrue($segment->track_performance);
        $this->assertIsBool($segment->auto_optimize);
        $this->assertFalse($segment->auto_optimize);
        $this->assertIsBool($segment->is_active);
        $this->assertTrue($segment->is_active);
        $this->assertIsInt($segment->sort_order);
        $this->assertSame(10, $segment->sort_order);
    }

    public function test_campaign_relationship_returns_expected_model(): void
    {
        // Arrange: explicitly persist a campaign and use its key when creating the segment to avoid lazy creation.
        $campaign = Campaign::factory()->create();
        $segment = CampaignCustomerSegment::factory()->create(['campaign_id' => $campaign->id]);

        // Assert: ensure the belongsTo relation returns the same campaign instance that seeded the record.
        $this->assertTrue($segment->campaign->is($campaign));
    }

    public function test_customer_group_relationship_returns_expected_model(): void
    {
        // Arrange: explicitly persist a customer group and use its key when creating the segment to avoid lazy creation.
        $customerGroup = CustomerGroup::factory()->create();
        $segment = CampaignCustomerSegment::factory()->create(['customer_group_id' => $customerGroup->id]);

        // Assert: ensure the belongsTo relation returns the same customer group instance that seeded the record.
        $this->assertTrue($segment->customerGroup->is($customerGroup));
    }

    public function test_scope_of_segment_type_filters_results(): void
    {
        // Arrange: build distinct segment types so the scoped query can be evaluated accurately.
        $demographicSegments = CampaignCustomerSegment::factory()->count(2)->create(['segment_type' => 'demographic']);
        $behavioralSegment = CampaignCustomerSegment::factory()->create(['segment_type' => 'behavioral']);

        // Act: apply the local scope targeting demographic segments.
        $results = CampaignCustomerSegment::ofSegmentType('demographic')->get();

        // Assert: confirm the scope only returns rows matching the requested segment type.
        $this->assertTrue($results->contains($demographicSegments[0]));
        $this->assertTrue($results->contains($demographicSegments[1]));
        $this->assertFalse($results->contains($behavioralSegment));
        $this->assertTrue($results->every(fn (CampaignCustomerSegment $segment) => $segment->segment_type === 'demographic'));
    }

    public function test_active_and_inactive_scopes_split_segments(): void
    {
        // Arrange: create active and inactive records so both scopes can be evaluated independently.
        $activeSegment = CampaignCustomerSegment::factory()->active()->create();
        $inactiveSegment = CampaignCustomerSegment::factory()->inactive()->create();

        // Act: run the paired scopes to collect each subset of segments.
        $active = CampaignCustomerSegment::active()->get();
        $inactive = CampaignCustomerSegment::inactive()->get();

        // Assert: each scope should yield exactly one record with the expected boolean flag state.
        $this->assertTrue($active->contains($activeSegment));
        $this->assertFalse($active->contains($inactiveSegment));
        $this->assertTrue($inactive->contains($inactiveSegment));
        $this->assertFalse($inactive->contains($activeSegment));
        $this->assertTrue($active->every(fn (CampaignCustomerSegment $segment) => $segment->is_active));
        $this->assertTrue($inactive->every(fn (CampaignCustomerSegment $segment) => $segment->is_active === false));
    }

    public function test_scope_for_campaign_limits_results(): void
    {
        // Arrange: craft two campaigns and assign segments to each so the scope filter can be isolated.
        $campaignA = Campaign::factory()->create();
        $campaignB = Campaign::factory()->create();
        $campaignASegments = CampaignCustomerSegment::factory()->count(2)->create(['campaign_id' => $campaignA->id]);
        $campaignBSegment = CampaignCustomerSegment::factory()->create(['campaign_id' => $campaignB->id]);

        // Act: filter by the first campaign identifier using the dedicated scope.
        $results = CampaignCustomerSegment::forCampaign($campaignA->id)->get();

        // Assert: every result should point back to the expected campaign id.
        $this->assertTrue($results->contains($campaignASegments[0]));
        $this->assertTrue($results->contains($campaignASegments[1]));
        $this->assertFalse($results->contains($campaignBSegment));
        $this->assertTrue($results->every(fn (CampaignCustomerSegment $segment) => $segment->campaign_id === $campaignA->id));
    }

    public function test_scope_for_customer_group_limits_results(): void
    {
        // Arrange: craft two customer groups and assign segments to each so the scope filter can be isolated.
        $groupA = CustomerGroup::factory()->create();
        $groupB = CustomerGroup::factory()->create();
        CampaignCustomerSegment::factory()->count(2)->create(['customer_group_id' => $groupA->id]);
        CampaignCustomerSegment::factory()->create(['customer_group_id' => $groupB->id]);

        // Act: filter by the first customer group identifier using the dedicated scope.
        $results = CampaignCustomerSegment::forCustomerGroup($groupA->id)->get();

        // Assert: every result should point back to the expected customer group id.
        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn (CampaignCustomerSegment $segment) => $segment->customer_group_id === $groupA->id));
    }

    public function test_scope_ordered_sorts_by_sort_order_column(): void
    {
        // Arrange: deliberately shuffle sort orders to validate the sanitised direction handling.
        $first = CampaignCustomerSegment::factory()->create(['sort_order' => 10]);
        $second = CampaignCustomerSegment::factory()->create(['sort_order' => 5]);
        $third = CampaignCustomerSegment::factory()->create(['sort_order' => 15]);

        // Act: apply the ordered scope in both ascending and descending directions.
        $ascending = CampaignCustomerSegment::ordered()->pluck('id')->all();
        $descending = CampaignCustomerSegment::ordered('desc')->pluck('id')->all();

        // Assert: ensure the ids return in the expected sequence when toggling the direction parameter.
        $this->assertSame(
            [$second->id, $first->id, $third->id],
            array_values(array_intersect($ascending, [$second->id, $first->id, $third->id]))
        );
        $this->assertSame(
            [$third->id, $first->id, $second->id],
            array_values(array_intersect($descending, [$third->id, $first->id, $second->id]))
        );
    }

    public function test_soft_deletes_hide_segments_from_default_queries(): void
    {
        // Arrange: create a segment and soft delete it to ensure the soft deletes trait is active.
        $segment = CampaignCustomerSegment::factory()->create();
        $segment->delete();

        // Act: fetch default and trashed-inclusive collections for comparison.
        $defaultResults = CampaignCustomerSegment::find($segment->id);
        $withTrashed = CampaignCustomerSegment::withTrashed()->find($segment->id);

        // Assert: the soft deleted record should be excluded from default queries but present with trashed scope.
        $this->assertNull($defaultResults);
        $this->assertNotNull($withTrashed);
        $this->assertTrue($withTrashed->is($segment));
    }
}
