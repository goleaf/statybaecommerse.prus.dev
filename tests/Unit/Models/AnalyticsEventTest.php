<?php declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\AnalyticsEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AnalyticsEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_analytics_event_can_be_created(): void
    {
        $user = User::factory()->create();

        $event = AnalyticsEvent::create([
            'event_type' => 'product_view',
            'session_id' => 'test-session-123',
            'user_id' => $user->id,
            'properties' => ['product_id' => 1, 'page' => 'home'],
            'url' => 'https://example.com/products/1',
            'referrer' => 'https://google.com',
            'user_agent' => 'Mozilla/5.0 Test Browser',
            'ip_address' => '192.168.1.1',
            'country_code' => 'LT',
            'created_at' => now(),
        ]);

        $this->assertInstanceOf(AnalyticsEvent::class, $event);
        $this->assertEquals('product_view', $event->event_type);
        $this->assertEquals('test-session-123', $event->session_id);
        $this->assertEquals($user->id, $event->user_id);
        $this->assertEquals(['product_id' => 1, 'page' => 'home'], $event->properties);
        $this->assertEquals('https://example.com/products/1', $event->url);
    }

    public function test_analytics_event_has_correct_fillable_attributes(): void
    {
        $event = new AnalyticsEvent();

        $expected = [
            'event_type',
            'session_id',
            'user_id',
            'properties',
            'url',
            'referrer',
            'user_agent',
            'ip_address',
            'country_code',
            'created_at',
        ];

        $this->assertEquals($expected, $event->getFillable());
    }

    public function test_analytics_event_disables_timestamps(): void
    {
        $event = new AnalyticsEvent();

        $this->assertFalse($event->timestamps);
    }

    public function test_analytics_event_casts_properties_to_array(): void
    {
        $event = AnalyticsEvent::factory()->create([
            'properties' => ['test' => 'value', 'number' => 123],
        ]);

        $this->assertIsArray($event->properties);
        $this->assertEquals(['test' => 'value', 'number' => 123], $event->properties);
    }

    public function test_analytics_event_casts_created_at_to_datetime(): void
    {
        $event = AnalyticsEvent::factory()->create([
            'created_at' => '2024-01-01 12:00:00',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $event->created_at);
    }

    public function test_analytics_event_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $event = AnalyticsEvent::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $event->user);
        $this->assertEquals($user->id, $event->user->id);
    }

    public function test_analytics_event_can_have_null_user(): void
    {
        $event = AnalyticsEvent::factory()->create(['user_id' => null]);

        $this->assertNull($event->user_id);
        $this->assertNull($event->user);
    }

    public function test_scope_of_type(): void
    {
        AnalyticsEvent::factory()->create(['event_type' => 'product_view']);
        AnalyticsEvent::factory()->create(['event_type' => 'add_to_cart']);
        AnalyticsEvent::factory()->create(['event_type' => 'product_view']);

        $productViews = AnalyticsEvent::ofType('product_view')->get();
        $cartAdds = AnalyticsEvent::ofType('add_to_cart')->get();

        $this->assertCount(2, $productViews);
        $this->assertCount(1, $cartAdds);
    }

    public function test_scope_for_session(): void
    {
        $sessionId = 'test-session-123';

        AnalyticsEvent::factory()->create(['session_id' => $sessionId]);
        AnalyticsEvent::factory()->create(['session_id' => 'other-session']);
        AnalyticsEvent::factory()->create(['session_id' => $sessionId]);

        $sessionEvents = AnalyticsEvent::forSession($sessionId)->get();

        $this->assertCount(2, $sessionEvents);
        $sessionEvents->each(function ($event) use ($sessionId) {
            $this->assertEquals($sessionId, $event->session_id);
        });
    }

    public function test_scope_for_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        AnalyticsEvent::factory()->create(['user_id' => $user1->id]);
        AnalyticsEvent::factory()->create(['user_id' => $user2->id]);
        AnalyticsEvent::factory()->create(['user_id' => $user1->id]);

        $user1Events = AnalyticsEvent::forUser($user1->id)->get();

        $this->assertCount(2, $user1Events);
        $user1Events->each(function ($event) use ($user1) {
            $this->assertEquals($user1->id, $event->user_id);
        });
    }

    public function test_scope_in_date_range(): void
    {
        $startDate = now()->subDays(7);
        $endDate = now()->subDays(1);

        AnalyticsEvent::factory()->create(['created_at' => now()->subDays(10)]);  // Outside range
        AnalyticsEvent::factory()->create(['created_at' => now()->subDays(5)]);  // Inside range
        AnalyticsEvent::factory()->create(['created_at' => now()->subDays(3)]);  // Inside range
        AnalyticsEvent::factory()->create(['created_at' => now()]);  // Outside range

        $rangeEvents = AnalyticsEvent::inDateRange($startDate, $endDate)->get();

        $this->assertCount(2, $rangeEvents);
    }

    public function test_track_static_method_creates_event(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Mock request data
        request()->merge(['url' => 'https://test.com/products/1']);
        request()->headers->set('referer', 'https://google.com');
        request()->headers->set('user-agent', 'Test Browser');
        request()->server->set('REMOTE_ADDR', '192.168.1.1');

        $event = AnalyticsEvent::track('product_view', [
            'product_id' => 1,
            'category' => 'electronics',
        ]);

        $this->assertInstanceOf(AnalyticsEvent::class, $event);
        $this->assertEquals('product_view', $event->event_type);
        $this->assertEquals($user->id, $event->user_id);
        $this->assertEquals(['product_id' => 1, 'category' => 'electronics'], $event->properties);
        $this->assertNotNull($event->session_id);
    }

    public function test_track_method_works_without_authenticated_user(): void
    {
        // Don't authenticate any user

        $event = AnalyticsEvent::track('page_view', ['page' => 'home']);

        $this->assertInstanceOf(AnalyticsEvent::class, $event);
        $this->assertEquals('page_view', $event->event_type);
        $this->assertNull($event->user_id);
        $this->assertEquals(['page' => 'home'], $event->properties);
    }

    public function test_track_method_uses_custom_url(): void
    {
        $customUrl = 'https://custom.com/special-page';

        $event = AnalyticsEvent::track('custom_event', [], $customUrl);

        $this->assertEquals($customUrl, $event->url);
    }

    public function test_analytics_event_factory_creates_valid_data(): void
    {
        $event = AnalyticsEvent::factory()->create();

        $this->assertNotNull($event->event_type);
        $this->assertNotNull($event->session_id);
        $this->assertIsArray($event->properties);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $event->created_at);
    }

    public function test_analytics_event_factory_can_create_specific_event_type(): void
    {
        $event = AnalyticsEvent::factory()->create([
            'event_type' => 'purchase',
            'properties' => ['order_id' => 123, 'total' => 99.99],
        ]);

        $this->assertEquals('purchase', $event->event_type);
        $this->assertEquals(['order_id' => 123, 'total' => 99.99], $event->properties);
    }
}
