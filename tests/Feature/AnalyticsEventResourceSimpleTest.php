<?php

declare(strict_types=1);

use App\Filament\Resources\AnalyticsEventResource;
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

it('can get analytics event resource model', function () {
    expect(AnalyticsEventResource::getModel())->toBe(AnalyticsEvent::class);
});

it('can get analytics event resource navigation group', function () {
    expect(AnalyticsEventResource::getNavigationGroup())->toBe('Analytics');
});

it('can get analytics event resource navigation label', function () {
    expect(AnalyticsEventResource::getNavigationLabel())->toBeString();
});

it('can get analytics event resource plural model label', function () {
    expect(AnalyticsEventResource::getPluralModelLabel())->toBeString();
});

it('can get analytics event resource model label', function () {
    expect(AnalyticsEventResource::getModelLabel())->toBeString();
});
