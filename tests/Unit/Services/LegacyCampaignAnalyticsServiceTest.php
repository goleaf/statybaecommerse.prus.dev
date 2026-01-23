<?php

declare(strict_types=1);

use App\Models\Campaign;
use App\Models\CampaignView;
use App\Services\LegacyCampaignAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new LegacyCampaignAnalyticsService;
});

test('getCampaignAnalytics returns safe defaults for removed functionality', function () {
    $campaign = Campaign::factory()->create();
    CampaignView::factory()->count(5)->create(['campaign_id' => $campaign->id]);

    $analytics = $this->service->getCampaignAnalytics($campaign);

    expect($analytics)
        ->toHaveKey('total_views')
        ->toHaveKey('total_clicks')
        ->toHaveKey('total_conversions')
        ->toHaveKey('click_through_rate')
        ->toHaveKey('conversion_rate');

    expect($analytics['total_views'])->toBe(5);
    expect($analytics['total_clicks'])->toBe(0);
    expect($analytics['total_conversions'])->toBe(0);
    expect($analytics['click_through_rate'])->toBe(0.0);
    expect($analytics['conversion_rate'])->toBe(0.0);
});

test('handleLegacyClickTracking logs attempt and returns success', function () {
    $data = [
        'campaign_id' => 1,
        'click_type'  => 'cta',
        'url'         => 'https://example.com',
    ];

    $result = $this->service->handleLegacyClickTracking($data);

    expect($result)
        ->toHaveKey('success')
        ->toHaveKey('message')
        ->toHaveKey('tracked');

    expect($result['success'])->toBe(true);
    expect($result['tracked'])->toBe(false);
    expect($result['message'])->toBe('Click tracking has been deprecated');
});

test('getBulkCampaignStats processes multiple campaigns', function () {
    $campaigns = Campaign::factory()->count(3)->create();

    $stats = $this->service->getBulkCampaignStats($campaigns);

    expect($stats)->toHaveCount(3);
    expect($stats[0])
        ->toHaveKey('id')
        ->toHaveKey('name')
        ->toHaveKey('analytics');
});
