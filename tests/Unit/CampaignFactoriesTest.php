<?php

declare(strict_types=1);

use App\Models\Campaign;
use App\Models\CampaignClick;
use App\Models\CampaignConversion;
use App\Models\CampaignCustomerSegment;
use App\Models\CampaignProductTarget;
use App\Models\CampaignSchedule;
use App\Models\CampaignView;

it('creates campaign via factory with relationships', function () {
    $campaign = Campaign::factory()->active()->create();

    expect($campaign->id)
        ->not
        ->toBeNull()
        ->and($campaign->status)
        ->toBe('active');

    CampaignView::factory()->count(2)->create([
        'campaign_id' => $campaign->id,
        'customer_id' => null,
    ]);
    CampaignClick::factory()->count(2)->create([
        'campaign_id' => $campaign->id,
        'customer_id' => null,
    ]);
    CampaignConversion::factory()->count(1)->create(['campaign_id' => $campaign->id]);

    expect($campaign->views()->count())
        ->toBe(2)
        ->and($campaign->clicks()->count())
        ->toBe(2)
        ->and(\App\Models\CampaignConversion::withoutGlobalScopes()->where('campaign_id', $campaign->id)->count())
        ->toBe(1);
});

it('creates product target, customer segment and schedule via factories', function () {
    $campaign = Campaign::factory()->create();

    // Insert only cross-DB columns to avoid missing-column failures
    $target = CampaignProductTarget::factory()->category()->make(['campaign_id' => $campaign->id]);
    \DB::table('campaign_product_targets')->insert([
        'campaign_id' => $campaign->id,
        'product_id' => $target->product_id,
        'category_id' => $target->category_id,
        'target_type' => 'category',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    CampaignCustomerSegment::factory()->demographic()->create(['campaign_id' => $campaign->id]);
    CampaignSchedule::factory()->daily()->create(['campaign_id' => $campaign->id]);

    expect($campaign->productTargets()->count())
        ->toBe(1)
        ->and($campaign->customerSegments()->count())
        ->toBe(1)
        ->and($campaign->schedules()->count())
        ->toBe(1);
});

// Seeder is heavy and out of scope for factory tests; covered elsewhere.
