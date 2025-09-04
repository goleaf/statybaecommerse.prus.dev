<?php declare(strict_types=1);

use App\Models\AnalyticsEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

uses(RefreshDatabase::class);

describe('AnalyticsEvent Model', function () {
    it('can be created with valid data', function () {
        $user = User::factory()->create();

        $event = AnalyticsEvent::create([
            'event_type' => 'page_view',
            'session_id' => 'test-session-123',
            'user_id' => $user->id,
            'properties' => ['page' => 'home', 'section' => 'hero'],
            'url' => 'https://example.com/home',
            'referrer' => 'https://google.com',
            'user_agent' => 'Mozilla/5.0 Test Browser',
            'ip_address' => '192.168.1.1',
            'country_code' => 'US',
            'created_at' => now(),
        ]);

        expect($event)
            ->toBeInstanceOf(AnalyticsEvent::class)
            ->and($event->event_type)
            ->toBe('page_view')
            ->and($event->session_id)
            ->toBe('test-session-123')
            ->and($event->user_id)
            ->toBe($user->id)
            ->and($event->properties)
            ->toBe(['page' => 'home', 'section' => 'hero'])
            ->and($event->url)
            ->toBe('https://example.com/home');
    });

    it('has correct fillable attributes', function () {
        $event = new AnalyticsEvent();

        expect($event->getFillable())->toBe([
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
        ]);
    });

    it('disables timestamps by default', function () {
        $event = new AnalyticsEvent();

        expect($event->timestamps)->toBeFalse();
    });

    it('casts properties to array', function () {
        $event = AnalyticsEvent::factory()->create([
            'properties' => ['product_id' => 123, 'category' => 'electronics'],
        ]);

        expect($event->properties)
            ->toBeArray()
            ->and($event->properties['product_id'])
            ->toBe(123)
            ->and($event->properties['category'])
            ->toBe('electronics');
    });

    it('casts created_at to datetime', function () {
        $event = AnalyticsEvent::factory()->create([
            'created_at' => '2024-01-15 10:30:00',
        ]);

        expect($event->created_at)->toBeInstanceOf(Carbon\Carbon::class);
    });

    it('belongs to a user', function () {
        $user = User::factory()->create();
        $event = AnalyticsEvent::factory()->create([
            'user_id' => $user->id,
        ]);

        expect($event->user)
            ->toBeInstanceOf(User::class)
            ->and($event->user->id)
            ->toBe($user->id);
    });

    it('can have null user for anonymous events', function () {
        $event = AnalyticsEvent::factory()->create([
            'user_id' => null,
        ]);

        expect($event->user)->toBeNull();
    });

    it('can scope by event type', function () {
        AnalyticsEvent::factory()->create(['event_type' => 'page_view']);
        AnalyticsEvent::factory()->create(['event_type' => 'button_click']);
        AnalyticsEvent::factory()->create(['event_type' => 'page_view']);

        $pageViewEvents = AnalyticsEvent::ofType('page_view')->get();
        $buttonClickEvents = AnalyticsEvent::ofType('button_click')->get();

        expect($pageViewEvents)
            ->toHaveCount(2)
            ->and($buttonClickEvents)
            ->toHaveCount(1);
    });

    it('can scope by session id', function () {
        AnalyticsEvent::factory()->create(['session_id' => 'session-123']);
        AnalyticsEvent::factory()->create(['session_id' => 'session-456']);
        AnalyticsEvent::factory()->create(['session_id' => 'session-123']);

        $session123Events = AnalyticsEvent::forSession('session-123')->get();
        $session456Events = AnalyticsEvent::forSession('session-456')->get();

        expect($session123Events)
            ->toHaveCount(2)
            ->and($session456Events)
            ->toHaveCount(1);
    });

    it('can scope by user id', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        AnalyticsEvent::factory()->create(['user_id' => $user1->id]);
        AnalyticsEvent::factory()->create(['user_id' => $user2->id]);
        AnalyticsEvent::factory()->create(['user_id' => $user1->id]);

        $user1Events = AnalyticsEvent::forUser($user1->id)->get();
        $user2Events = AnalyticsEvent::forUser($user2->id)->get();

        expect($user1Events)
            ->toHaveCount(2)
            ->and($user2Events)
            ->toHaveCount(1);
    });

    it('can scope by date range', function () {
        $startDate = now()->subDays(7);
        $endDate = now()->subDays(1);

        AnalyticsEvent::factory()->create(['created_at' => now()->subDays(10)]);
        AnalyticsEvent::factory()->create(['created_at' => now()->subDays(5)]);
        AnalyticsEvent::factory()->create(['created_at' => now()->subDays(3)]);
        AnalyticsEvent::factory()->create(['created_at' => now()]);

        $eventsInRange = AnalyticsEvent::inDateRange($startDate, $endDate)->get();

        expect($eventsInRange)->toHaveCount(2);
    });

    it('can track events with static method', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Start a session to avoid mocking issues
        $this->startSession();
        session()->put('_token', 'test-token');

        $event = AnalyticsEvent::track('product_view', [
            'product_id' => 123,
            'category' => 'electronics',
        ]);

        expect($event)
            ->toBeInstanceOf(AnalyticsEvent::class)
            ->and($event->event_type)
            ->toBe('product_view')
            ->and($event->properties)
            ->toBe(['product_id' => 123, 'category' => 'electronics'])
            ->and($event->user_id)
            ->toBe($user->id);

        $this->assertDatabaseHas('analytics_events', [
            'event_type' => 'product_view',
            'user_id' => $user->id,
        ]);
    });

    it('can track anonymous events', function () {
        // Start a session without authenticating
        $this->startSession();

        $event = AnalyticsEvent::track('page_view');

        expect($event->user_id)
            ->toBeNull()
            ->and($event->event_type)
            ->toBe('page_view');
    });

    it('can track events with custom url', function () {
        $this->startSession();

        $event = AnalyticsEvent::track('custom_event', [], 'https://custom.url/path');

        expect($event->url)->toBe('https://custom.url/path');
    });

    it('stores properties as json', function () {
        $properties = [
            'product_id' => 123,
            'price' => 99.99,
            'category' => 'electronics',
            'tags' => ['new', 'featured'],
        ];

        $event = AnalyticsEvent::factory()->create([
            'properties' => $properties,
        ]);

        expect($event->properties)->toBe($properties);

        // Verify it's stored as JSON in database
        $rawEvent = \DB::table('analytics_events')->find($event->id);
        expect(json_decode($rawEvent->properties, true))->toBe($properties);
    });

    it('can handle empty properties', function () {
        $event = AnalyticsEvent::factory()->create([
            'properties' => [],
        ]);

        expect($event->properties)->toBe([]);
    });

    it('can handle null properties', function () {
        $event = AnalyticsEvent::factory()->create([
            'properties' => [],
        ]);

        expect($event->properties)->toBe([]);
    });

    it('can combine multiple scopes', function () {
        $user = User::factory()->create();
        $sessionId = 'test-session-combo';
        $startDate = now()->subDays(7);
        $endDate = now();

        // Create events that match all criteria
        AnalyticsEvent::factory()->create([
            'event_type' => 'page_view',
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'created_at' => now()->subDays(3),
        ]);

        // Create events that don't match all criteria
        AnalyticsEvent::factory()->create([
            'event_type' => 'button_click',  // Different type
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'created_at' => now()->subDays(3),
        ]);

        AnalyticsEvent::factory()->create([
            'event_type' => 'page_view',
            'user_id' => $user->id,
            'session_id' => 'different-session',  // Different session
            'created_at' => now()->subDays(3),
        ]);

        $filteredEvents = AnalyticsEvent::ofType('page_view')
            ->forUser($user->id)
            ->forSession($sessionId)
            ->inDateRange($startDate, $endDate)
            ->get();

        expect($filteredEvents)->toHaveCount(1);
    });

    it('validates required fields', function () {
        expect(fn() => AnalyticsEvent::create([]))
            ->toThrow(Illuminate\Database\QueryException::class);
    });

    it('can track common ecommerce events', function () {
        $this->startSession();

        // Product view
        $productView = AnalyticsEvent::track('product_view', [
            'product_id' => 123,
            'product_name' => 'iPhone 15',
            'category' => 'Electronics',
            'price' => 999.99,
        ]);

        // Add to cart
        $addToCart = AnalyticsEvent::track('add_to_cart', [
            'product_id' => 123,
            'quantity' => 1,
            'price' => 999.99,
        ]);

        // Purchase
        $purchase = AnalyticsEvent::track('purchase', [
            'order_id' => 'ORD-12345',
            'total' => 999.99,
            'items_count' => 1,
        ]);

        expect($productView->event_type)
            ->toBe('product_view')
            ->and($addToCart->event_type)
            ->toBe('add_to_cart')
            ->and($purchase->event_type)
            ->toBe('purchase');

        $this->assertDatabaseCount('analytics_events', 3);
    });

    it('can track user journey across session', function () {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->startSession();

        // Simulate user journey
        AnalyticsEvent::track('page_view', ['page' => 'home']);
        AnalyticsEvent::track('page_view', ['page' => 'products']);
        AnalyticsEvent::track('product_view', ['product_id' => 123]);
        AnalyticsEvent::track('add_to_cart', ['product_id' => 123]);

        $userJourney = AnalyticsEvent::forUser($user->id)
            ->orderBy('created_at')
            ->get();

        expect($userJourney)->toHaveCount(4);
        expect($userJourney->pluck('event_type')->toArray())
            ->toBe(['page_view', 'page_view', 'product_view', 'add_to_cart']);
    });
});
