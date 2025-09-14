<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\AnalyticsEvent;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AnalyticsEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_analytics_event_can_be_created(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $event = AnalyticsEvent::factory()->create([
            'event_type' => 'product_view',
            'session_id' => 'test_session_123',
            'user_id' => $user->id,
            'url' => 'https://example.com/products/1',
            'referrer' => 'https://google.com',
            'ip_address' => '192.168.1.1',
            'country_code' => 'LT',
            'device_type' => 'desktop',
            'browser' => 'Chrome',
            'os' => 'Windows',
            'screen_resolution' => '1920x1080',
            'trackable_type' => Product::class,
            'trackable_id' => $product->id,
            'value' => 25.99,
            'currency' => 'EUR',
            'properties' => ['category' => 'electronics', 'brand' => 'Samsung'],
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ]);

        $this->assertInstanceOf(AnalyticsEvent::class, $event);
        $this->assertEquals('product_view', $event->event_type);
        $this->assertEquals('test_session_123', $event->session_id);
        $this->assertEquals($user->id, $event->user_id);
        $this->assertEquals('https://example.com/products/1', $event->url);
        $this->assertEquals('https://google.com', $event->referrer);
        $this->assertEquals('192.168.1.1', $event->ip_address);
        $this->assertEquals('LT', $event->country_code);
        $this->assertEquals('desktop', $event->device_type);
        $this->assertEquals('Chrome', $event->browser);
        $this->assertEquals('Windows', $event->os);
        $this->assertEquals('1920x1080', $event->screen_resolution);
        $this->assertEquals(Product::class, $event->trackable_type);
        $this->assertEquals($product->id, $event->trackable_id);
        $this->assertEquals(25.99, $event->value);
        $this->assertEquals('EUR', $event->currency);
        $this->assertEquals(['category' => 'electronics', 'brand' => 'Samsung'], $event->properties);
        $this->assertNotEmpty($event->user_agent);
    }

    public function test_analytics_event_fillable_attributes(): void
    {
        $event = new AnalyticsEvent();
        $fillable = $event->getFillable();

        $expectedFillable = [
            'event_type',
            'session_id',
            'user_id',
            'url',
            'referrer',
            'ip_address',
            'country_code',
            'device_type',
            'browser',
            'os',
            'screen_resolution',
            'trackable_type',
            'trackable_id',
            'value',
            'currency',
            'properties',
            'user_agent',
            'created_at',
            'updated_at',
        ];

        foreach ($expectedFillable as $field) {
            $this->assertContains($field, $fillable);
        }
    }

    public function test_analytics_event_casts(): void
    {
        $event = AnalyticsEvent::factory()->create([
            'properties' => ['key' => 'value', 'number' => 123],
            'value' => 99.99,
        ]);

        $this->assertIsArray($event->properties);
        $this->assertEquals(['key' => 'value', 'number' => 123], $event->properties);
        $this->assertInstanceOf(\Carbon\Carbon::class, $event->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $event->updated_at);
    }

    public function test_analytics_event_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $event = AnalyticsEvent::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $event->user);
        $this->assertEquals($user->id, $event->user->id);
    }

    public function test_analytics_event_morph_to_trackable(): void
    {
        $product = Product::factory()->create();
        $event = AnalyticsEvent::factory()->create([
            'trackable_type' => Product::class,
            'trackable_id' => $product->id,
        ]);

        $this->assertInstanceOf(Product::class, $event->trackable);
        $this->assertEquals($product->id, $event->trackable->id);
    }

    public function test_analytics_event_scope_by_event_type(): void
    {
        AnalyticsEvent::factory()->create(['event_type' => 'product_view']);
        AnalyticsEvent::factory()->create(['event_type' => 'page_view']);
        AnalyticsEvent::factory()->create(['event_type' => 'product_view']);

        $events = AnalyticsEvent::withoutGlobalScopes()->byEventType('product_view')->get();

        $this->assertCount(2, $events);
        $this->assertTrue($events->every(fn($event) => $event->event_type === 'product_view'));
    }

    public function test_analytics_event_scope_by_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        AnalyticsEvent::factory()->create(['user_id' => $user1->id]);
        AnalyticsEvent::factory()->create(['user_id' => $user2->id]);
        AnalyticsEvent::factory()->create(['user_id' => $user1->id]);

        $events = AnalyticsEvent::withoutGlobalScopes()->byUser($user1->id)->get();

        $this->assertCount(2, $events);
        $this->assertTrue($events->every(fn($event) => $event->user_id === $user1->id));
    }

    public function test_analytics_event_scope_by_session(): void
    {
        $sessionId = 'unique_session_123';
        
        AnalyticsEvent::factory()->create(['session_id' => $sessionId]);
        AnalyticsEvent::factory()->create(['session_id' => 'other_session']);
        AnalyticsEvent::factory()->create(['session_id' => $sessionId]);

        $events = AnalyticsEvent::withoutGlobalScopes()->bySession($sessionId)->get();

        $this->assertCount(2, $events);
        $this->assertTrue($events->every(fn($event) => $event->session_id === $sessionId));
    }

    public function test_analytics_event_scope_with_value(): void
    {
        AnalyticsEvent::factory()->create(['value' => 25.99]);
        AnalyticsEvent::factory()->create(['value' => null]);
        AnalyticsEvent::factory()->create(['value' => 50.00]);

        $events = AnalyticsEvent::withoutGlobalScopes()->withValue()->get();

        $this->assertCount(2, $events);
        $this->assertTrue($events->every(fn($event) => $event->value !== null));
    }

    public function test_analytics_event_scope_registered_users(): void
    {
        $user = User::factory()->create();
        
        AnalyticsEvent::factory()->create(['user_id' => $user->id]);
        AnalyticsEvent::factory()->create(['user_id' => null]);
        AnalyticsEvent::factory()->create(['user_id' => $user->id]);

        $events = AnalyticsEvent::withoutGlobalScopes()->registeredUsers()->get();

        $this->assertCount(2, $events);
        $this->assertTrue($events->every(fn($event) => $event->user_id !== null));
    }

    public function test_analytics_event_scope_anonymous_users(): void
    {
        $user = User::factory()->create();
        
        AnalyticsEvent::factory()->create(['user_id' => null]);
        AnalyticsEvent::factory()->create(['user_id' => $user->id]);
        AnalyticsEvent::factory()->create(['user_id' => null]);

        $events = AnalyticsEvent::withoutGlobalScopes()->anonymousUsers()->get();

        $this->assertCount(2, $events);
        $this->assertTrue($events->every(fn($event) => $event->user_id === null));
    }

    public function test_analytics_event_scope_by_device_type(): void
    {
        AnalyticsEvent::factory()->create(['device_type' => 'mobile']);
        AnalyticsEvent::factory()->create(['device_type' => 'desktop']);
        AnalyticsEvent::factory()->create(['device_type' => 'mobile']);

        $events = AnalyticsEvent::withoutGlobalScopes()->byDeviceType('mobile')->get();

        $this->assertCount(2, $events);
        $this->assertTrue($events->every(fn($event) => $event->device_type === 'mobile'));
    }

    public function test_analytics_event_scope_by_browser(): void
    {
        AnalyticsEvent::factory()->create(['browser' => 'Chrome']);
        AnalyticsEvent::factory()->create(['browser' => 'Firefox']);
        AnalyticsEvent::factory()->create(['browser' => 'Chrome']);

        $events = AnalyticsEvent::withoutGlobalScopes()->byBrowser('Chrome')->get();

        $this->assertCount(2, $events);
        $this->assertTrue($events->every(fn($event) => $event->browser === 'Chrome'));
    }

    public function test_analytics_event_scope_by_date_range(): void
    {
        $startDate = '2024-01-01';
        $endDate = '2024-01-31';
        
        AnalyticsEvent::factory()->create(['created_at' => '2024-01-15 12:00:00']);
        AnalyticsEvent::factory()->create(['created_at' => '2024-02-15 12:00:00']);
        AnalyticsEvent::factory()->create(['created_at' => '2024-01-20 12:00:00']);

        $events = AnalyticsEvent::withoutGlobalScopes()->byDateRange($startDate, $endDate)->get();

        $this->assertCount(2, $events);
    }

    public function test_analytics_event_scope_today(): void
    {
        AnalyticsEvent::factory()->create(['created_at' => now()]);
        AnalyticsEvent::factory()->create(['created_at' => now()->subDay()]);
        AnalyticsEvent::factory()->create(['created_at' => now()]);

        $events = AnalyticsEvent::withoutGlobalScopes()->today()->get();

        $this->assertCount(2, $events);
    }

    public function test_analytics_event_scope_this_week(): void
    {
        AnalyticsEvent::factory()->create(['created_at' => now()]);
        AnalyticsEvent::factory()->create(['created_at' => now()->subWeek()]);
        AnalyticsEvent::factory()->create(['created_at' => now()->subDays(3)]);

        $events = AnalyticsEvent::withoutGlobalScopes()->thisWeek()->get();

        $this->assertCount(2, $events);
    }

    public function test_analytics_event_scope_this_month(): void
    {
        AnalyticsEvent::factory()->create(['created_at' => now()]);
        AnalyticsEvent::factory()->create(['created_at' => now()->subMonth()]);
        AnalyticsEvent::factory()->create(['created_at' => now()->subDays(15)]);

        $events = AnalyticsEvent::withoutGlobalScopes()->thisMonth()->get();

        // Only the event created with now() should be in this month
        // The other two are in previous months
        $this->assertCount(1, $events);
    }

    public function test_analytics_event_table_name(): void
    {
        $event = new AnalyticsEvent();
        $this->assertEquals('analytics_events', $event->getTable());
    }

    public function test_analytics_event_factory(): void
    {
        $event = AnalyticsEvent::factory()->create();

        $this->assertInstanceOf(AnalyticsEvent::class, $event);
        $this->assertNotEmpty($event->event_type);
        $this->assertNotEmpty($event->session_id);
        $this->assertIsArray($event->properties);
        $this->assertInstanceOf(\Carbon\Carbon::class, $event->created_at);
    }
}