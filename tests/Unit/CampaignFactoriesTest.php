<?php

declare(strict_types=1);

use App\Models\Campaign;
use App\Models\CampaignClick;
use App\Models\CampaignConversion;
use App\Models\CampaignCustomerSegment;
use App\Models\CampaignProductTarget;
use App\Models\CampaignSchedule;
use App\Models\CampaignView;
use App\Models\CustomerGroup;
use Illuminate\Support\Facades\Schema;

it('unit: creates campaign via factory with relationships', function () {
    $campaign = Campaign::factory()->active()->create();

    expect($campaign->id)
        ->not
        ->toBeNull()
        ->and($campaign->status)
        ->toBe('active')
        ->and($campaign->productTargets()->count())
        ->toBeGreaterThanOrEqual(1)
        ->and($campaign->customerSegments()->count())
        ->toBeGreaterThanOrEqual(1)
        ->and($campaign->schedules()->count())
        ->toBeGreaterThanOrEqual(1);

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

it('unit: creates product target, customer segment and schedule via factories', function () {
    $campaign = Campaign::factory()->create();

    $ensureActive = static fn (string $table) => Schema::hasColumn($table, 'is_active') ? ['is_active' => true] : [];

    $target = CampaignProductTarget::factory()
        ->category()
        ->for($campaign)
        ->state($ensureActive('campaign_product_targets'))
        ->create();

    $segment = CampaignCustomerSegment::factory()
        ->demographic()
        ->for($campaign)
        ->state(array_merge(
            ['customer_group_id' => CustomerGroup::factory()],
            $ensureActive('campaign_customer_segments'),
        ))
        ->create();

    $schedule = CampaignSchedule::factory()
        ->daily()
        ->for($campaign)
        ->state($ensureActive('campaign_schedules'))
        ->create();

    $campaign->refresh();

    expect($campaign->productTargets()->count())
        ->toBeGreaterThanOrEqual(2)
        ->and($campaign->productTargets()->pluck('id')->all())
        ->toContain($target->id)
        ->and($campaign->customerSegments()->count())
        ->toBeGreaterThanOrEqual(2)
        ->and($campaign->customerSegments()->pluck('id')->all())
        ->toContain($segment->id)
        ->and($campaign->schedules()->count())
        ->toBeGreaterThanOrEqual(2)
        ->and($campaign->schedules()->pluck('id')->all())
        ->toContain($schedule->id);
});

// Seeder is heavy and out of scope for factory tests; covered elsewhere.
