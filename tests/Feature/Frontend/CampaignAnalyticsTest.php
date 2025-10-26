<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

final class CampaignAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_performance_endpoint_flags_low_conversion_and_low_click_through(): void
    {
        // Create a campaign that should be flagged purely because of its low conversion rate.
        $lowConversion = Campaign::factory()->create([
            'conversion_rate' => 1.5,
            'total_views'     => 120,
            'total_clicks'    => 90,
            'status'          => 'active',
            'starts_at'       => now()->subDay(),
            'ends_at'         => now()->addDay(),
        ]);

        // Create a campaign with healthy conversions but a poor click-through ratio.
        $lowClickThrough = Campaign::factory()->create([
            'conversion_rate' => 4.5,
            'total_views'     => 400,
            'total_clicks'    => 1,
            'status'          => 'active',
            'starts_at'       => now()->subDay(),
            'ends_at'         => now()->addDay(),
        ]);

        $response = $this->getJson(route('frontend.campaigns.api.performance'));

        $response
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json
                // Ensure the low conversion campaign is captured in the low performing bucket.
                ->where('data.low_performing', 1)
                // Verify both problematic campaigns appear in the "needs attention" bucket.
                ->where('data.needs_attention', 2)
                ->where('success', true)
            );

        $this->assertDatabaseHas('discount_campaigns', ['id' => $lowConversion->id]);
        $this->assertDatabaseHas('discount_campaigns', ['id' => $lowClickThrough->id]);
    }

    public function test_performance_endpoint_skips_zero_view_campaigns(): void
    {
        // Add a campaign with zero impressions to make sure the API ignores it when calculating attention metrics.
        Campaign::factory()->create([
            'conversion_rate' => 3.5,
            'total_views'     => 0,
            'total_clicks'    => 0,
            'status'          => 'active',
            'starts_at'       => now()->subDay(),
            'ends_at'         => now()->addDay(),
        ]);

        $response = $this->getJson(route('frontend.campaigns.api.performance'));

        $response
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('data.low_performing', 0)
                ->where('data.needs_attention', 0)
                ->where('success', true)
            );
    }

    public function test_analytics_endpoint_normalises_invalid_period_requests(): void
    {
        // Freeze time to ensure the computed start and end dates are deterministic.
        Carbon::setTestNow(Carbon::parse('2024-02-10 12:00:00'));

        try {
            Campaign::factory()->create([
                'created_at' => now()->subDays(5),
                'starts_at'  => now()->subDays(5),
                'ends_at'    => now()->addDays(10),
                'status'     => 'active',
            ]);

            $response = $this->getJson(route('frontend.campaigns.api.analytics', ['period' => 'invalid-value']));

            $response
                ->assertOk()
                ->assertJson(fn (AssertableJson $json) => $json
                    ->where('success', true)
                    ->where('data.period', 30)
                    ->where('data.start_date', '2024-01-11')
                    ->where('data.end_date', '2024-02-10')
                );
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_analytics_endpoint_clamps_excessive_periods(): void
    {
        // Clamp behaviour should stop runaway queries when a massive period is requested.
        Carbon::setTestNow(Carbon::parse('2024-05-01 09:30:00'));

        try {
            $response = $this->getJson(route('frontend.campaigns.api.analytics', ['period' => 999]));

            $response
                ->assertOk()
                ->assertJson(fn (AssertableJson $json) => $json
                    ->where('success', true)
                    ->where('data.period', 365)
                    ->where('data.start_date', '2023-05-02')
                    ->where('data.end_date', '2024-05-01')
                );
        } finally {
            Carbon::setTestNow();
        }
    }
}
