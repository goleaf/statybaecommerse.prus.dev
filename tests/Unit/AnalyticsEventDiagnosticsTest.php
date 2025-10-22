<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\AnalyticsEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit-level assertions that cover the analytics event model behaviour.
 */
final class AnalyticsEventDiagnosticsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The conversion currency accessor should gracefully fall back to EUR when null is provided.
     */
    public function testConversionCurrencyDefaultsToEuro(): void
    {
        $event = AnalyticsEvent::factory()->create(['conversion_currency' => null]);

        $this->assertSame('EUR', $event->conversion_currency);
    }

    /**
     * The scoped query helpers must filter by event type correctly.
     */
    public function testScopeFiltersByEventType(): void
    {
        $matching = AnalyticsEvent::factory()->create(['event_type' => 'page_view']);
        AnalyticsEvent::factory()->create(['event_type' => 'purchase']);

        $events = AnalyticsEvent::query()->byEventType('page_view')->get();

        $this->assertCount(1, $events);
        $this->assertTrue($events->first()?->is($matching));
    }

    /**
     * Analytics events should retain the owning user relationship when present.
     */
    public function testUserRelationshipResolves(): void
    {
        $user = User::factory()->create();
        $event = AnalyticsEvent::factory()->for($user)->create();

        $this->assertTrue($event->user()->is($user));
    }
}
