<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\AnalyticsEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AnalyticsEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_ordered_by_name_prioritises_named_events(): void
    {
        // Create events with varying name casing alongside an unnamed record to exercise ordering.
        $alpha = AnalyticsEvent::factory()->create(['event_name' => 'Alpha']);
        $beta = AnalyticsEvent::factory()->create(['event_name' => 'beta']);
        $unnamed = AnalyticsEvent::factory()->create(['event_name' => null]);

        // Retrieve the ordered list of event names via the dedicated scope.
        $orderedNames = AnalyticsEvent::orderedByName()->pluck('event_name')->all();

        // Confirm the named events appear alphabetically before the unnamed fallback entry.
        $this->assertSame([
            $alpha->event_name,
            $beta->event_name,
            $unnamed->event_name,
        ], $orderedNames);
    }

    public function test_track_uses_authenticated_context_and_merges_properties(): void
    {
        // Stub the HTTP request so contextual metadata is available during tracking.
        $request = Request::create('https://example.com/products?ref=nav', 'GET', [], [], [], [
            'HTTP_REFERER'    => 'https://google.com/search?q=products',
            'REMOTE_ADDR'     => '203.0.113.10',
            'HTTP_USER_AGENT' => 'PHPUnit/Analytics',
        ]);
        $originalRequest = app()->bound('request') ? app('request') : null;
        app()->instance('request', $request);

        // Authenticate a storefront user so the helper picks up their identifier.
        $user = User::factory()->create();
        $this->actingAs($user);

        try {
            // Trigger the tracking helper with mixed property data to validate the merge logic.
            $event = AnalyticsEvent::track('page_view', [
                'event_name'  => 'Product View',
                'properties'  => ['foo' => 'bar'],
                'description' => 'value',
            ]);

            // Validate the core context derived from the authenticated session and HTTP request.
            $this->assertSame($user->id, $event->user_id);
            $this->assertSame('https://example.com/products?ref=nav', $event->url);
            $this->assertSame('https://google.com/search?q=products', $event->referrer);
            $this->assertSame('203.0.113.10', $event->ip_address);
            $this->assertSame('PHPUnit/Analytics', $event->user_agent);

            // Ensure the explicit payload values are preserved after persistence.
            $this->assertSame('Product View', $event->event_name);
            $this->assertSame('value', $event->description);
            $this->assertSame(['foo' => 'bar'], $event->properties);
        } finally {
            // Restore the previous request binding to avoid side effects across tests.
            if ($originalRequest !== null) {
                app()->instance('request', $originalRequest);
            } else {
                app()->forgetInstance('request');
            }
        }
    }

    public function test_conversion_currency_defaults_to_eur_when_null(): void
    {
        // Directly create an event with a null conversion currency to trigger the mutator safeguard.
        $event = AnalyticsEvent::create([
            'event_name'          => 'Checkout Complete',
            'event_type'          => 'purchase',
            'session_id'          => Str::uuid()->toString(),
            'conversion_value'    => 199.95,
            'conversion_currency' => null,
        ]);

        // Confirm the accessor fallback normalises the attribute to the default EUR currency code.
        $this->assertSame('EUR', $event->conversion_currency);
    }
}
