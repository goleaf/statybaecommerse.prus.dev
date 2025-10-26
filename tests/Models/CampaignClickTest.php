<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Campaign;
use App\Models\CampaignClick;
use App\Models\CampaignConversion;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversClass(CampaignClick::class)]
final class CampaignClickTest extends TestCase
{
    use RefreshDatabase;

    public function test_clicked_at_mutator_normalises_timestamps_to_utc(): void
    {
        // Freeze the clock to make timezone calculations deterministic for the assertion.
        Carbon::setTestNow('2024-01-01 17:00:00');

        try {
            // Persist a click with a local timezone so we can confirm the accessor normalises it to UTC.
            $click = CampaignClick::factory()->create([
                'clicked_at' => Carbon::create(2024, 1, 1, 12, 0, 0, 'America/New_York'),
            ]);

            $click = $click->fresh();

            // Assert that the stored value is normalised to a UTC timestamp string.
            self::assertSame('2024-01-01 17:00:00', $click->getRawOriginal('clicked_at'));
            self::assertTrue($click->clicked_at->equalTo(CarbonImmutable::parse('2024-01-01 17:00:00', 'UTC')));
        } finally {
            // Always clear the mocked time so other tests operate with the real clock.
            Carbon::setTestNow();
        }
    }

    public function test_booted_hook_backfills_missing_clicked_at_and_conversion_value(): void
    {
        // Freeze the current time to keep created timestamps reproducible in assertions.
        Carbon::setTestNow('2025-05-05 12:34:56');

        try {
            // Create a click without timestamps or conversion amounts to trigger the booted callbacks.
            $click = CampaignClick::factory()->create([
                'clicked_at'       => null,
                'conversion_value' => null,
            ]);

            $click = $click->fresh();

            // Ensure the model automatically fills the missing clicked_at attribute with the mocked now instance.
            self::assertTrue($click->clicked_at?->equalTo(CarbonImmutable::parse('2025-05-05 12:34:56', config('app.timezone', 'UTC'))));
            // Confirm the conversion amount gracefully defaults to zero when not provided by the UI.
            self::assertSame('0.00', $click->conversion_value);
        } finally {
            // Reset the time mock so remaining tests are unaffected by this setup.
            Carbon::setTestNow();
        }
    }

    public function test_scope_by_campaign_filters_results(): void
    {
        // Create two campaigns so we can assert the scope only returns clicks for the first one.
        $campaignA = Campaign::factory()->create();
        $campaignB = Campaign::factory()->create();

        // Seed matching clicks for each campaign to validate the filter logic.
        $matching = CampaignClick::factory()->count(2)->create([
            'campaign_id' => $campaignA->id,
        ]);
        CampaignClick::factory()->create([
            'campaign_id' => $campaignB->id,
        ]);

        // Execute the scope and compare the identifiers to the expected set.
        $results = CampaignClick::query()->byCampaign($campaignA->id)->pluck('id')->all();
        self::assertSame($matching->pluck('id')->sort()->values()->all(), collect($results)->sort()->values()->all());
    }

    public function test_scope_recent_restricts_results_to_configured_window(): void
    {
        // Fix the current time so relative date calculations behave consistently.
        Carbon::setTestNow('2024-06-01 00:00:00');

        try {
            // Create one fresh click and one stale click to verify the date window filtering.
            $recent = CampaignClick::factory()->create([
                'clicked_at' => Carbon::now()->subDays(3),
            ]);
            $stale = CampaignClick::factory()->create([
                'clicked_at' => Carbon::now()->subDays(45),
            ]);

            // Confirm the scope only returns the recent click within the default 30-day window.
            $results = CampaignClick::query()->recent()->get();
            self::assertTrue($results->contains($recent));
            self::assertFalse($results->contains($stale));
        } finally {
            // Remove the mocked time value after the assertions have executed.
            Carbon::setTestNow();
        }
    }

    public function test_conversion_helpers_surface_aggregated_insights(): void
    {
        // Create a click that we can attach conversions to for aggregation checks.
        $click = CampaignClick::factory()->create();

        // Seed a pair of conversions with deterministic values to validate the helper outputs.
        $firstConversion = CampaignConversion::factory()->make([
            'conversion_value' => 10.50,
        ]);
        $firstConversion->timestamps = false;
        $click->conversions()->save($firstConversion);

        $secondConversion = CampaignConversion::factory()->make([
            'conversion_value' => 5.25,
        ]);
        $secondConversion->timestamps = false;
        $click->conversions()->save($secondConversion);

        // Flag the click as converted so the simple helper mirrors the expected business behaviour.
        $click->update(['is_converted' => true]);

        // Ensure the convenience helpers reflect the conversion presence and aggregate amounts correctly.
        self::assertTrue($click->fresh()->isConverted());
        self::assertSame(100.0, $click->fresh()->getConversionRate());
        self::assertEqualsWithDelta(15.75, $click->fresh()->getTotalConversionValue(), 0.0001);
    }

    public function test_metadata_helpers_expose_stored_context(): void
    {
        // Persist a click with extended metadata so we can assert the helper arrays mirror the stored data.
        $click = CampaignClick::factory()->create([
            'utm_source'   => 'newsletter',
            'utm_medium'   => 'email',
            'utm_campaign' => 'spring_sale',
            'utm_term'     => 'discount',
            'utm_content'  => 'cta_button',
            'country'      => 'LT',
            'city'         => 'Vilnius',
            'ip_address'   => '203.0.113.10',
            'device_type'  => 'mobile',
            'browser'      => 'chrome',
            'os'           => 'android',
            'user_agent'   => 'Mozilla/5.0',
        ]);

        // Assert that the metadata helper accessors return the expected associative arrays.
        self::assertSame([
            'utm_source'   => 'newsletter',
            'utm_medium'   => 'email',
            'utm_campaign' => 'spring_sale',
            'utm_term'     => 'discount',
            'utm_content'  => 'cta_button',
        ], $click->getUtmParams());

        self::assertSame([
            'country'    => 'LT',
            'city'       => 'Vilnius',
            'ip_address' => '203.0.113.10',
        ], $click->getLocationInfo());

        self::assertSame([
            'device_type' => 'mobile',
            'browser'     => 'chrome',
            'os'          => 'android',
            'user_agent'  => 'Mozilla/5.0',
        ], $click->getDeviceInfo());
    }
}
