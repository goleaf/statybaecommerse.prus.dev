<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\CampaignClick;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class CampaignClickListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_caps_page_size_and_falls_back_to_default_sort(): void
    {
        CampaignClick::factory()->count(3)->sequence(
            ['clicked_at' => Carbon::parse('2024-01-01 12:00:00')],
            ['clicked_at' => Carbon::parse('2024-01-02 12:00:00')],
            ['clicked_at' => Carbon::parse('2024-01-03 12:00:00')],
        )->create();

        $response = $this->getJson('/api/campaign-clicks?per_page=500&sort=-invalid');

        $response->assertOk();
        $payload = $response->json();

        $this->assertSame(100, $payload['meta']['query']['per_page']);
        $this->assertSame('clicked_at', $payload['meta']['query']['sort'][0]['key']);
        $this->assertSame('desc', $payload['meta']['query']['sort'][0]['direction']);

        $timestamps = array_column($payload['data'], 'clicked_at');
        $this->assertSame(
            ['2024-01-03T12:00:00.000000Z', '2024-01-02T12:00:00.000000Z', '2024-01-01T12:00:00.000000Z'],
            $timestamps
        );
    }

    public function test_it_applies_boolean_and_date_filters(): void
    {
        CampaignClick::factory()->count(2)->create([
            'is_converted' => true,
            'clicked_at'   => Carbon::parse('2024-02-10 08:00:00'),
        ]);
        CampaignClick::factory()->create([
            'is_converted' => false,
            'clicked_at'   => Carbon::parse('2024-02-15 08:00:00'),
        ]);

        $response = $this->getJson('/api/campaign-clicks?is_converted=true&date_from=2024-02-01&date_to=2024-02-28');

        $response->assertOk();
        $payload = $response->json();

        $this->assertSame(2, count($payload['data']));
        $this->assertSame(true, $payload['meta']['query']['filters']['is_converted']);
        $this->assertSame('2024-02-01 00:00:00', $payload['meta']['query']['filters']['date_from']);
        $this->assertSame('2024-02-28 00:00:00', $payload['meta']['query']['filters']['date_to']);
    }
}
