<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Campaign;
use App\Models\CampaignClick;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CampaignClickListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_results_with_default_sort(): void
    {
        CampaignClick::factory()->count(3)->sequence(fn (int $index) => [
            'clicked_at' => now()->subMinutes($index * 5),
        ])->create();

        $response = $this->getJson('/campaign-clicks?per_page=2');

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data',
            'meta' => ['pagination' => ['total', 'per_page', 'current_page', 'last_page', 'count', 'from', 'to'], 'sort' => ['by', 'direction'], 'filters'],
            'links' => ['first', 'last', 'prev', 'next'],
        ]);

        $payload = $response->json();

        $this->assertSame(2, count($payload['data']));
        $this->assertSame('clicked_at', $payload['meta']['sort']['by']);
        $this->assertSame('desc', $payload['meta']['sort']['direction']);
        $this->assertSame(2, $payload['meta']['pagination']['per_page']);
        $this->assertSame(3, $payload['meta']['pagination']['total']);
    }

    public function test_index_applies_filters_and_exposes_meta_filters(): void
    {
        $campaign = Campaign::factory()->create();
        $converted = CampaignClick::factory()->converted()->create([
            'clicked_at' => now()->subDay(),
            'campaign_id' => $campaign->id,
            'device_type' => 'mobile',
        ]);

        CampaignClick::factory()->create([
            'clicked_at' => now()->subDays(5),
            'campaign_id' => Campaign::factory(),
            'device_type' => 'desktop',
            'is_converted' => false,
        ]);

        $dateFrom = now()->subDays(2)->toDateString();
        $response = $this->getJson('/campaign-clicks?is_converted=1&campaign_id='.$campaign->id.'&date_from='.$dateFrom);

        $response->assertOk();
        $payload = $response->json();

        $this->assertCount(1, $payload['data']);
        $this->assertSame($converted->id, $payload['data'][0]['id']);
        $this->assertTrue($payload['meta']['filters']['is_converted']);
        $this->assertSame($campaign->id, $payload['meta']['filters']['campaign_id']);
        $this->assertSame($dateFrom, $payload['meta']['filters']['date_from']);
    }

    public function test_index_rejects_invalid_sort_parameter(): void
    {
        $response = $this->getJson('/campaign-clicks?sort_by=invalid-column');

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['sort_by']);
    }

    public function test_index_limits_results_to_authenticated_customer(): void
    {
        $user = User::factory()->create();
        $ownClick = CampaignClick::factory()->withCustomer()->create([
            'customer_id' => $user->id,
        ]);

        CampaignClick::factory()->count(2)->create();

        $response = $this->actingAs($user)->getJson('/campaign-clicks');

        $response->assertOk();
        $payload = $response->json();

        $this->assertCount(1, $payload['data']);
        $this->assertSame($ownClick->id, $payload['data'][0]['id']);
    }
}
