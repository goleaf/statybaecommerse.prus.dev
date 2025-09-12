<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\AnalyticsEvent;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_analytics_event_can_be_created(): void
    {
        $user = User::factory()->create();
        
        $event = AnalyticsEvent::factory()->create([
            'user_id' => $user->id,
            'event_type' => 'page_view',
            'event_name' => 'product_viewed',
            'properties' => ['product_id' => 123],
        ]);

        $this->assertDatabaseHas('analytics_events', [
            'user_id' => $user->id,
            'event_type' => 'page_view',
            'event_name' => 'product_viewed',
        ]);
    }

    public function test_analytics_event_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $event = AnalyticsEvent::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $event->user);
        $this->assertEquals($user->id, $event->user->id);
    }

    public function test_analytics_event_casts_work_correctly(): void
    {
        $event = AnalyticsEvent::factory()->create([
            'properties' => ['key' => 'value'],
            'created_at' => now(),
        ]);

        $this->assertIsArray($event->properties);
        $this->assertEquals('value', $event->properties['key']);
        $this->assertInstanceOf(\Carbon\Carbon::class, $event->created_at);
    }

    public function test_analytics_event_fillable_attributes(): void
    {
        $event = new AnalyticsEvent();
        $fillable = $event->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('event_type', $fillable);
        $this->assertContains('event_name', $fillable);
        $this->assertContains('properties', $fillable);
    }

    public function test_analytics_event_scope_by_type(): void
    {
        $pageViewEvent = AnalyticsEvent::factory()->create(['event_type' => 'page_view']);
        $clickEvent = AnalyticsEvent::factory()->create(['event_type' => 'click']);

        $pageViewEvents = AnalyticsEvent::byType('page_view')->get();

        $this->assertTrue($pageViewEvents->contains($pageViewEvent));
        $this->assertFalse($pageViewEvents->contains($clickEvent));
    }

    public function test_analytics_event_scope_by_name(): void
    {
        $productViewEvent = AnalyticsEvent::factory()->create(['event_name' => 'product_viewed']);
        $cartAddEvent = AnalyticsEvent::factory()->create(['event_name' => 'cart_added']);

        $productViewEvents = AnalyticsEvent::byName('product_viewed')->get();

        $this->assertTrue($productViewEvents->contains($productViewEvent));
        $this->assertFalse($productViewEvents->contains($cartAddEvent));
    }

    public function test_analytics_event_scope_for_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $event1 = AnalyticsEvent::factory()->create(['user_id' => $user1->id]);
        $event2 = AnalyticsEvent::factory()->create(['user_id' => $user2->id]);

        $user1Events = AnalyticsEvent::forUser($user1->id)->get();

        $this->assertTrue($user1Events->contains($event1));
        $this->assertFalse($user1Events->contains($event2));
    }

    public function test_analytics_event_scope_today(): void
    {
        $todayEvent = AnalyticsEvent::factory()->create(['created_at' => now()]);
        $yesterdayEvent = AnalyticsEvent::factory()->create(['created_at' => now()->subDay()]);

        $todayEvents = AnalyticsEvent::today()->get();

        $this->assertTrue($todayEvents->contains($todayEvent));
        $this->assertFalse($todayEvents->contains($yesterdayEvent));
    }

    public function test_analytics_event_scope_this_week(): void
    {
        $thisWeekEvent = AnalyticsEvent::factory()->create(['created_at' => now()]);
        $lastWeekEvent = AnalyticsEvent::factory()->create(['created_at' => now()->subWeek()]);

        $thisWeekEvents = AnalyticsEvent::thisWeek()->get();

        $this->assertTrue($thisWeekEvents->contains($thisWeekEvent));
        $this->assertFalse($thisWeekEvents->contains($lastWeekEvent));
    }

    public function test_analytics_event_scope_this_month(): void
    {
        $thisMonthEvent = AnalyticsEvent::factory()->create(['created_at' => now()]);
        $lastMonthEvent = AnalyticsEvent::factory()->create(['created_at' => now()->subMonth()]);

        $thisMonthEvents = AnalyticsEvent::thisMonth()->get();

        $this->assertTrue($thisMonthEvents->contains($thisMonthEvent));
        $this->assertFalse($thisMonthEvents->contains($lastMonthEvent));
    }

    public function test_analytics_event_can_have_session_id(): void
    {
        $event = AnalyticsEvent::factory()->create([
            'session_id' => 'test-session-123',
        ]);

        $this->assertEquals('test-session-123', $event->session_id);
    }

    public function test_analytics_event_can_have_ip_address(): void
    {
        $event = AnalyticsEvent::factory()->create([
            'ip_address' => '192.168.1.1',
        ]);

        $this->assertEquals('192.168.1.1', $event->ip_address);
    }

    public function test_analytics_event_can_have_user_agent(): void
    {
        $event = AnalyticsEvent::factory()->create([
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ]);

        $this->assertStringContainsString('Mozilla', $event->user_agent);
    }

    public function test_analytics_event_can_have_referrer(): void
    {
        $event = AnalyticsEvent::factory()->create([
            'referrer' => 'https://google.com',
        ]);

        $this->assertEquals('https://google.com', $event->referrer);
    }

    public function test_analytics_event_can_have_url(): void
    {
        $event = AnalyticsEvent::factory()->create([
            'url' => '/products/test-product',
        ]);

        $this->assertEquals('/products/test-product', $event->url);
    }

    public function test_analytics_event_can_have_metadata(): void
    {
        $event = AnalyticsEvent::factory()->create([
            'metadata' => [
                'page_title' => 'Test Product',
                'category' => 'Electronics',
                'value' => 99.99,
            ],
        ]);

        $this->assertIsArray($event->metadata);
        $this->assertEquals('Test Product', $event->metadata['page_title']);
        $this->assertEquals('Electronics', $event->metadata['category']);
        $this->assertEquals(99.99, $event->metadata['value']);
    }

    public function test_analytics_event_can_track_ecommerce_events(): void
    {
        $product = Product::factory()->create();
        
        $event = AnalyticsEvent::factory()->create([
            'event_type' => 'ecommerce',
            'event_name' => 'purchase',
            'properties' => [
                'transaction_id' => 'TXN-123',
                'value' => 199.99,
                'currency' => 'EUR',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                        'price' => 99.99,
                    ],
                ],
            ],
        ]);

        $this->assertEquals('ecommerce', $event->event_type);
        $this->assertEquals('purchase', $event->event_name);
        $this->assertEquals('TXN-123', $event->properties['transaction_id']);
        $this->assertEquals(199.99, $event->properties['value']);
    }

    public function test_analytics_event_can_track_user_engagement(): void
    {
        $event = AnalyticsEvent::factory()->create([
            'event_type' => 'engagement',
            'event_name' => 'time_on_page',
            'properties' => [
                'duration' => 120,
                'page' => '/products/test-product',
            ],
        ]);

        $this->assertEquals('engagement', $event->event_type);
        $this->assertEquals('time_on_page', $event->event_name);
        $this->assertEquals(120, $event->properties['duration']);
    }
}
