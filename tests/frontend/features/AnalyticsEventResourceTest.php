<?php

declare(strict_types=1);

use App\Filament\Resources\AnalyticsEventResource;
use App\Models\AnalyticsEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

describe('AnalyticsEvent Resource', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($this->admin);
    });

    it('can render index page', function () {
        $this
            ->get(AnalyticsEventResource::getUrl('index'))
            ->assertSuccessful();
    });

    it('can list analytics events', function () {
        $events = AnalyticsEvent::factory()->count(10)->create();

        livewire(AnalyticsEventResource\Pages\ListAnalyticsEvents::class)
            ->assertCanSeeTableRecords($events);
    });

    it('can render view page', function () {
        $event = AnalyticsEvent::factory()->create();

        $this
            ->get(AnalyticsEventResource::getUrl('view', ['record' => $event]))
            ->assertSuccessful();
    });

    it('can view analytics event', function () {
        $event = AnalyticsEvent::factory()->create([
            'event_type' => 'product_view',
            'properties' => ['product_id' => 123],
        ]);

        livewire(AnalyticsEventResource\Pages\ViewAnalyticsEvent::class, ['record' => $event->getRouteKey()])
            ->assertFormSet([
                'event_type' => 'product_view',
                'properties' => ['product_id' => 123],
            ]);
    });

    it('can search events by type', function () {
        $pageViewEvents = AnalyticsEvent::factory()->count(3)->create(['event_type' => 'page_view']);
        $clickEvents = AnalyticsEvent::factory()->count(2)->create(['event_type' => 'button_click']);

        livewire(AnalyticsEventResource\Pages\ListAnalyticsEvents::class)
            ->searchTable('page_view')
            ->assertCanSeeTableRecords($pageViewEvents)
            ->assertCanNotSeeTableRecords($clickEvents);
    });

    it('can filter events by event type', function () {
        $pageViewEvents = AnalyticsEvent::factory()->count(3)->create(['event_type' => 'page_view']);
        $purchaseEvents = AnalyticsEvent::factory()->count(2)->create(['event_type' => 'purchase']);

        livewire(AnalyticsEventResource\Pages\ListAnalyticsEvents::class)
            ->filterTable('event_type', 'page_view')
            ->assertCanSeeTableRecords($pageViewEvents)
            ->assertCanNotSeeTableRecords($purchaseEvents);
    });

    it('can filter events by user', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $user1Events = AnalyticsEvent::factory()->count(3)->create(['user_id' => $user1->id]);
        $user2Events = AnalyticsEvent::factory()->count(2)->create(['user_id' => $user2->id]);

        livewire(AnalyticsEventResource\Pages\ListAnalyticsEvents::class)
            ->filterTable('user_id', $user1->id)
            ->assertCanSeeTableRecords($user1Events)
            ->assertCanNotSeeTableRecords($user2Events);
    });

    it('can filter events by date range', function () {
        $recentEvents = AnalyticsEvent::factory()->count(3)->create([
            'created_at' => now()->subDays(1),
        ]);
        $oldEvents = AnalyticsEvent::factory()->count(2)->create([
            'created_at' => now()->subDays(30),
        ]);

        livewire(AnalyticsEventResource\Pages\ListAnalyticsEvents::class)
            ->filterTable('created_at', [
                'from' => now()->subDays(7)->toDateString(),
                'until' => now()->toDateString(),
            ])
            ->assertCanSeeTableRecords($recentEvents)
            ->assertCanNotSeeTableRecords($oldEvents);
    });

    it('can sort events by created date', function () {
        $oldEvent = AnalyticsEvent::factory()->create(['created_at' => now()->subDays(2)]);
        $newEvent = AnalyticsEvent::factory()->create(['created_at' => now()]);

        livewire(AnalyticsEventResource\Pages\ListAnalyticsEvents::class)
            ->sortTable('created_at', 'desc')
            ->assertCanSeeTableRecords([$newEvent, $oldEvent], inOrder: true);
    });

    it('can bulk delete events', function () {
        $events = AnalyticsEvent::factory()->count(3)->create();

        livewire(AnalyticsEventResource\Pages\ListAnalyticsEvents::class)
            ->callTableBulkAction('delete', $events);

        foreach ($events as $event) {
            $this->assertModelMissing($event);
        }
    });

    it('displays event properties correctly', function () {
        $event = AnalyticsEvent::factory()->create([
            'properties' => [
                'product_id' => 123,
                'product_name' => 'Test Product',
                'price' => 99.99,
            ],
        ]);

        livewire(AnalyticsEventResource\Pages\ViewAnalyticsEvent::class, ['record' => $event->getRouteKey()])
            ->assertSee('123')
            ->assertSee('Test Product')
            ->assertSee('99.99');
    });

    it('shows user information when available', function () {
        $user = User::factory()->create(['name' => 'John Doe']);
        $event = AnalyticsEvent::factory()->create(['user_id' => $user->id]);

        livewire(AnalyticsEventResource\Pages\ViewAnalyticsEvent::class, ['record' => $event->getRouteKey()])
            ->assertSee('John Doe');
    });

    it('handles anonymous events correctly', function () {
        $event = AnalyticsEvent::factory()->create(['user_id' => null]);

        livewire(AnalyticsEventResource\Pages\ViewAnalyticsEvent::class, ['record' => $event->getRouteKey()])
            ->assertSee('Anonymous');
    });

    it('can export events', function () {
        AnalyticsEvent::factory()->count(5)->create();

        $response = livewire(AnalyticsEventResource\Pages\ListAnalyticsEvents::class)
            ->callAction('export');

        expect($response)->toBeInstanceOf(\Symfony\Component\HttpFoundation\BinaryFileResponse::class);
    });

    it('restricts access to non-admin users', function () {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $this
            ->get(AnalyticsEventResource::getUrl('index'))
            ->assertForbidden();
    });

    it('displays correct event counts in stats', function () {
        AnalyticsEvent::factory()->count(10)->create(['event_type' => 'page_view']);
        AnalyticsEvent::factory()->count(5)->create(['event_type' => 'purchase']);

        livewire(AnalyticsEventResource\Pages\ListAnalyticsEvents::class)
            ->assertSee('15');  // Total events count
    });

    it('can view event timeline', function () {
        $sessionId = 'test-session-123';
        $user = User::factory()->create();

        // Create events in chronological order
        AnalyticsEvent::factory()->create([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'event_type' => 'page_view',
            'created_at' => now()->subMinutes(10),
        ]);

        AnalyticsEvent::factory()->create([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'event_type' => 'product_view',
            'created_at' => now()->subMinutes(5),
        ]);

        AnalyticsEvent::factory()->create([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'event_type' => 'add_to_cart',
            'created_at' => now(),
        ]);

        livewire(AnalyticsEventResource\Pages\ListAnalyticsEvents::class)
            ->filterTable('session_id', $sessionId)
            ->assertCanSeeTableRecords(
                AnalyticsEvent::where('session_id', $sessionId)->get()
            );
    });

    it('can analyze conversion funnel', function () {
        $sessionId = 'conversion-session';

        // Create a complete conversion funnel
        AnalyticsEvent::factory()->create([
            'session_id' => $sessionId,
            'event_type' => 'page_view',
            'properties' => ['page' => 'home'],
        ]);

        AnalyticsEvent::factory()->create([
            'session_id' => $sessionId,
            'event_type' => 'product_view',
            'properties' => ['product_id' => 123],
        ]);

        AnalyticsEvent::factory()->create([
            'session_id' => $sessionId,
            'event_type' => 'add_to_cart',
            'properties' => ['product_id' => 123],
        ]);

        AnalyticsEvent::factory()->create([
            'session_id' => $sessionId,
            'event_type' => 'purchase',
            'properties' => ['order_id' => 'ORD-123'],
        ]);

        $funnelEvents = AnalyticsEvent::where('session_id', $sessionId)
            ->orderBy('created_at')
            ->get();

        expect($funnelEvents)->toHaveCount(4);
        expect($funnelEvents->pluck('event_type')->toArray())
            ->toBe(['page_view', 'product_view', 'add_to_cart', 'purchase']);
    });

    it('can track performance metrics', function () {
        // Create events with performance data
        AnalyticsEvent::factory()->count(100)->create(['event_type' => 'page_view']);
        AnalyticsEvent::factory()->count(50)->create(['event_type' => 'product_view']);
        AnalyticsEvent::factory()->count(10)->create(['event_type' => 'purchase']);

        $pageViews = AnalyticsEvent::where('event_type', 'page_view')->count();
        $productViews = AnalyticsEvent::where('event_type', 'product_view')->count();
        $purchases = AnalyticsEvent::where('event_type', 'purchase')->count();

        expect($pageViews)
            ->toBe(100)
            ->and($productViews)
            ->toBe(50)
            ->and($purchases)
            ->toBe(10);

        // Calculate conversion rates
        $productViewRate = ($productViews / $pageViews) * 100;
        $purchaseRate = ($purchases / $productViews) * 100;

        expect($productViewRate)
            ->toBe(50.0)
            ->and($purchaseRate)
            ->toBe(20.0);
    });
});
