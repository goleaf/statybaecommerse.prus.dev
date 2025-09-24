<?php

declare(strict_types=1);

use App\Models\AnalyticsEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create analytics event', function () {
    $user = User::factory()->create();

    $event = AnalyticsEvent::create([
        'event_name' => 'Test Event',
        'event_type' => 'page_view',
        'description' => 'Test description',
        'user_id' => $user->id,
        'session_id' => 'test-session-123',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0',
        'is_important' => false,
        'is_conversion' => false,
    ]);

    expect($event)->toBeInstanceOf(AnalyticsEvent::class);
    expect($event->event_name)->toBe('Test Event');
    expect($event->event_type)->toBe('page_view');
    expect($event->user_id)->toBe($user->id);
});

it('can track analytics event using static method', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $event = AnalyticsEvent::track('page_view', [
        'page' => '/test-page',
        'title' => 'Test Page',
    ], $user);

    expect($event)->toBeInstanceOf(AnalyticsEvent::class);
    expect($event->event_type)->toBe('page_view');
    expect($event->user_id)->toBe($user->id);
    expect($event->properties)->toHaveKey('page');
    expect($event->properties['page'])->toBe('/test-page');
});

it('belongs to user', function () {
    $user = User::factory()->create();
    $event = AnalyticsEvent::factory()->create(['user_id' => $user->id]);

    expect($event->user)->toBeInstanceOf(User::class);
    expect($event->user->id)->toBe($user->id);
});

it('can scope by event type', function () {
    $pageViewEvent = AnalyticsEvent::factory()->create(['event_type' => 'page_view']);
    $clickEvent = AnalyticsEvent::factory()->create(['event_type' => 'click']);

    $pageViewEvents = AnalyticsEvent::byEventType('page_view')->get();

    expect($pageViewEvents)->toHaveCount(1);
    expect($pageViewEvents->first()->id)->toBe($pageViewEvent->id);
});

it('can scope by user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $event1 = AnalyticsEvent::factory()->create(['user_id' => $user1->id]);
    $event2 = AnalyticsEvent::factory()->create(['user_id' => $user2->id]);

    $user1Events = AnalyticsEvent::byUser($user1->id)->get();

    expect($user1Events)->toHaveCount(1);
    expect($user1Events->first()->id)->toBe($event1->id);
});

it('can scope by session', function () {
    $session1 = 'session-123';
    $session2 = 'session-456';

    $event1 = AnalyticsEvent::factory()->create(['session_id' => $session1]);
    $event2 = AnalyticsEvent::factory()->create(['session_id' => $session2]);

    $session1Events = AnalyticsEvent::bySession($session1)->get();

    expect($session1Events)->toHaveCount(1);
    expect($session1Events->first()->id)->toBe($event1->id);
});

it('can scope with value', function () {
    $eventWithValue = AnalyticsEvent::factory()->create(['value' => 99.99]);
    $eventWithoutValue = AnalyticsEvent::factory()->create(['value' => null]);

    $eventsWithValue = AnalyticsEvent::withValue()->get();

    expect($eventsWithValue)->toHaveCount(1);
    expect($eventsWithValue->first()->id)->toBe($eventWithValue->id);
});

it('can scope registered users', function () {
    $user = User::factory()->create();
    $registeredEvent = AnalyticsEvent::factory()->create(['user_id' => $user->id]);
    $anonymousEvent = AnalyticsEvent::factory()->create(['user_id' => null]);

    $registeredEvents = AnalyticsEvent::registeredUsers()->get();

    expect($registeredEvents)->toHaveCount(1);
    expect($registeredEvents->first()->id)->toBe($registeredEvent->id);
});

it('can scope anonymous users', function () {
    $user = User::factory()->create();
    $registeredEvent = AnalyticsEvent::factory()->create(['user_id' => $user->id]);
    $anonymousEvent = AnalyticsEvent::factory()->create(['user_id' => null]);

    $anonymousEvents = AnalyticsEvent::anonymousUsers()->get();

    expect($anonymousEvents)->toHaveCount(1);
    expect($anonymousEvents->first()->id)->toBe($anonymousEvent->id);
});

it('can scope by device type', function () {
    $desktopEvent = AnalyticsEvent::factory()->create(['device_type' => 'desktop']);
    $mobileEvent = AnalyticsEvent::factory()->create(['device_type' => 'mobile']);

    $desktopEvents = AnalyticsEvent::byDeviceType('desktop')->get();

    expect($desktopEvents)->toHaveCount(1);
    expect($desktopEvents->first()->id)->toBe($desktopEvent->id);
});

it('can scope by browser', function () {
    $chromeEvent = AnalyticsEvent::factory()->create(['browser' => 'Chrome']);
    $firefoxEvent = AnalyticsEvent::factory()->create(['browser' => 'Firefox']);

    $chromeEvents = AnalyticsEvent::byBrowser('Chrome')->get();

    expect($chromeEvents)->toHaveCount(1);
    expect($chromeEvents->first()->id)->toBe($chromeEvent->id);
});

it('can scope by date range', function () {
    $oldEvent = AnalyticsEvent::factory()->create(['created_at' => now()->subDays(10)]);
    $recentEvent = AnalyticsEvent::factory()->create(['created_at' => now()->subDays(2)]);
    $newEvent = AnalyticsEvent::factory()->create(['created_at' => now()]);

    $recentEvents = AnalyticsEvent::byDateRange(
        now()->subDays(5)->format('Y-m-d H:i:s'),
        now()->format('Y-m-d H:i:s')
    )->get();

    expect($recentEvents)->toHaveCount(2);
    expect($recentEvents->pluck('id'))->toContain($recentEvent->id);
    expect($recentEvents->pluck('id'))->toContain($newEvent->id);
});

it('can scope today', function () {
    $todayEvent = AnalyticsEvent::factory()->create(['created_at' => now()]);
    $yesterdayEvent = AnalyticsEvent::factory()->create(['created_at' => now()->subDay()]);

    $todayEvents = AnalyticsEvent::today()->get();

    expect($todayEvents)->toHaveCount(1);
    expect($todayEvents->first()->id)->toBe($todayEvent->id);
});

it('can scope this week', function () {
    $thisWeekEvent = AnalyticsEvent::factory()->create(['created_at' => now()]);
    $lastWeekEvent = AnalyticsEvent::factory()->create(['created_at' => now()->subWeek()]);

    $thisWeekEvents = AnalyticsEvent::thisWeek()->get();

    expect($thisWeekEvents)->toHaveCount(1);
    expect($thisWeekEvents->first()->id)->toBe($thisWeekEvent->id);
});

it('can scope this month', function () {
    $thisMonthEvent = AnalyticsEvent::factory()->create(['created_at' => now()]);
    $lastMonthEvent = AnalyticsEvent::factory()->create(['created_at' => now()->subMonth()]);

    $thisMonthEvents = AnalyticsEvent::thisMonth()->get();

    expect($thisMonthEvents)->toHaveCount(1);
    expect($thisMonthEvents->first()->id)->toBe($thisMonthEvent->id);
});

it('can get event type label', function () {
    $event = AnalyticsEvent::factory()->create(['event_type' => 'page_view']);

    expect($event->event_type_label)->toBeString();
});

it('can get device icon', function () {
    $desktopEvent = AnalyticsEvent::factory()->create(['device_type' => 'desktop']);
    $mobileEvent = AnalyticsEvent::factory()->create(['device_type' => 'mobile']);
    $tabletEvent = AnalyticsEvent::factory()->create(['device_type' => 'tablet']);
    $unknownEvent = AnalyticsEvent::factory()->create(['device_type' => 'unknown']);

    expect($desktopEvent->device_icon)->toBe('heroicon-o-computer-desktop');
    expect($mobileEvent->device_icon)->toBe('heroicon-o-device-phone-mobile');
    expect($tabletEvent->device_icon)->toBe('heroicon-o-device-tablet');
    expect($unknownEvent->device_icon)->toBe('heroicon-o-question-mark-circle');
});

it('can get formatted value', function () {
    $eventWithValue = AnalyticsEvent::factory()->create(['value' => 99.99, 'currency' => 'EUR']);
    $eventWithoutValue = AnalyticsEvent::factory()->create(['value' => null]);

    expect($eventWithValue->formatted_value)->toBe('99.99 EUR');
    expect($eventWithoutValue->formatted_value)->toBeNull();
});

it('can check if user is registered', function () {
    $user = User::factory()->create();
    $registeredEvent = AnalyticsEvent::factory()->create(['user_id' => $user->id]);
    $anonymousEvent = AnalyticsEvent::factory()->create(['user_id' => null]);

    expect($registeredEvent->is_registered_user)->toBeTrue();
    expect($anonymousEvent->is_registered_user)->toBeFalse();
});

it('can check if user is anonymous', function () {
    $user = User::factory()->create();
    $registeredEvent = AnalyticsEvent::factory()->create(['user_id' => $user->id]);
    $anonymousEvent = AnalyticsEvent::factory()->create(['user_id' => null]);

    expect($registeredEvent->is_anonymous_user)->toBeFalse();
    expect($anonymousEvent->is_anonymous_user)->toBeTrue();
});

it('can get event types', function () {
    $eventTypes = AnalyticsEvent::getEventTypes();

    expect($eventTypes)->toBeArray();
    expect($eventTypes)->toHaveKey('page_view');
    expect($eventTypes)->toHaveKey('click');
    expect($eventTypes)->toHaveKey('purchase');
});

it('can get device types', function () {
    $deviceTypes = AnalyticsEvent::getDeviceTypes();

    expect($deviceTypes)->toBeArray();
    expect($deviceTypes)->toHaveKey('desktop');
    expect($deviceTypes)->toHaveKey('mobile');
    expect($deviceTypes)->toHaveKey('tablet');
});

it('can get browsers', function () {
    $browsers = AnalyticsEvent::getBrowsers();

    expect($browsers)->toBeArray();
    expect($browsers)->toHaveKey('Chrome');
    expect($browsers)->toHaveKey('Firefox');
    expect($browsers)->toHaveKey('Safari');
    expect($browsers)->toHaveKey('Edge');
});

it('can get event type stats', function () {
    AnalyticsEvent::factory()->count(3)->create(['event_type' => 'page_view']);
    AnalyticsEvent::factory()->count(2)->create(['event_type' => 'click']);
    AnalyticsEvent::factory()->count(1)->create(['event_type' => 'purchase']);

    $stats = AnalyticsEvent::getEventTypeStats();

    expect($stats)->toBeArray();
    expect($stats['page_view'])->toBe(3);
    expect($stats['click'])->toBe(2);
    expect($stats['purchase'])->toBe(1);
});

it('can get device type stats', function () {
    AnalyticsEvent::factory()->count(3)->create(['device_type' => 'desktop']);
    AnalyticsEvent::factory()->count(2)->create(['device_type' => 'mobile']);
    AnalyticsEvent::factory()->count(1)->create(['device_type' => null]);

    $stats = AnalyticsEvent::getDeviceTypeStats();

    expect($stats)->toBeArray();
    expect($stats['desktop'])->toBe(3);
    expect($stats['mobile'])->toBe(2);
});

it('can get browser stats', function () {
    AnalyticsEvent::factory()->count(3)->create(['browser' => 'Chrome']);
    AnalyticsEvent::factory()->count(2)->create(['browser' => 'Firefox']);
    AnalyticsEvent::factory()->count(1)->create(['browser' => null]);

    $stats = AnalyticsEvent::getBrowserStats();

    expect($stats)->toBeArray();
    expect($stats['Chrome'])->toBe(3);
    expect($stats['Firefox'])->toBe(2);
});

it('can get revenue stats', function () {
    $today = now()->format('Y-m-d');
    $yesterday = now()->subDay()->format('Y-m-d');

    AnalyticsEvent::factory()->create([
        'value' => 100,
        'created_at' => $today,
    ]);
    AnalyticsEvent::factory()->create([
        'value' => 50,
        'created_at' => $today,
    ]);
    AnalyticsEvent::factory()->create([
        'value' => 75,
        'created_at' => $yesterday,
    ]);

    $stats = AnalyticsEvent::getRevenueStats();

    expect($stats)->toBeArray();
    expect($stats[$today])->toBe(150.0);
    expect($stats[$yesterday])->toBe(75.0);
});

it('can handle trackable morph relationship', function () {
    $user = User::factory()->create();
    $event = AnalyticsEvent::factory()->create([
        'trackable_type' => User::class,
        'trackable_id' => $user->id,
    ]);

    expect($event->trackable)->toBeInstanceOf(User::class);
    expect($event->trackable->id)->toBe($user->id);
});

it('can cast properties to array', function () {
    $properties = ['page' => '/test', 'title' => 'Test Page'];
    $event = AnalyticsEvent::factory()->create(['properties' => $properties]);

    expect($event->properties)->toBe($properties);
});

it('can cast event_data to array', function () {
    $eventData = ['custom_field' => 'value', 'another_field' => 123];
    $event = AnalyticsEvent::factory()->create(['event_data' => $eventData]);

    expect($event->event_data)->toBe($eventData);
});

it('can cast boolean fields', function () {
    $event = AnalyticsEvent::factory()->create([
        'is_important' => true,
        'is_conversion' => false,
    ]);

    expect($event->is_important)->toBeTrue();
    expect($event->is_conversion)->toBeFalse();
});

it('can cast decimal fields', function () {
    $event = AnalyticsEvent::factory()->create(['conversion_value' => 99.99]);

    expect($event->conversion_value)->toBe(99.99);
});

it('can cast datetime fields', function () {
    $now = now();
    $event = AnalyticsEvent::factory()->create(['created_at' => $now]);

    expect($event->created_at)->toBeInstanceOf(\Carbon\Carbon::class);
    expect($event->created_at->format('Y-m-d H:i:s'))->toBe($now->format('Y-m-d H:i:s'));
});
