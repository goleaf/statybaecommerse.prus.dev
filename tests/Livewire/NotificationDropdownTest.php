<?php

declare(strict_types=1);

use App\Livewire\NotificationDropdown;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

// Ensure a guest user cannot see any notifications and that markAllAsRead is a safe no-op.
it('initialises an empty state for guests', function (): void {
    // Explicitly log out to guarantee the component runs as a guest session.
    Auth::logout();

    // Mount the component and inspect the derived state for a visitor.
    $component = Livewire::test(NotificationDropdown::class);

    // Guests should have zero unread notifications and an empty recent collection.
    expect($component->get('unreadCount'))->toBe(0);
    expect($component->get('recentNotifications'))->toBeArray();
    expect($component->get('recentNotifications'))->toBeEmpty();

    // Invoking markAllAsRead must not change state or trigger an exception for guests.
    $component->call('markAllAsRead');
    expect($component->get('unreadCount'))->toBe(0);
});

// Confirm authenticated users see the latest five notifications and that unread counts are accurate.
it('loads the five most recent notifications for authenticated users', function (): void {
    // Freeze time so diffForHumans output is predictable for assertions.
    Carbon::setTestNow('2025-01-02 12:00:00');

    // Create a user whose notifications will be displayed by the component.
    $user = User::factory()->create();

    // Seed six notifications to test ordering and limiting behaviour (only the newest five should show).
    $notifications = collect();

    foreach (range(0, 5) as $offset) {
        $notifications->push(
            Notification::factory()
                ->for($user, 'notifiable')
                ->create([
                    'type'       => 'App\Notifications\SystemNotification',
                    'read_at'    => $offset < 2 ? Carbon::now()->subMinutes($offset) : null,
                    'created_at' => Carbon::now()->subMinutes($offset),
                    'updated_at' => Carbon::now()->subMinutes($offset),
                    'data'       => [
                        'title'   => "Notification {$offset}",
                        'message' => "Message {$offset}",
                    ],
                ])
        );
    }

    // Mount the component as the authenticated user to load the prepared notifications.
    $component = Livewire::actingAs($user)->test(NotificationDropdown::class);

    // Two notifications were pre-marked as read above, so the component should report four unread entries.
    $component->assertSet('unreadCount', 4);

    // Collect the rendered identifiers to assert on ordering and trimming behaviour.
    $recentIds = collect($component->get('recentNotifications'))->pluck('id')->all();

    expect($recentIds)->toHaveCount(5);
    expect($recentIds)->toEqual($notifications->take(5)->pluck('id')->all());

    // The latest notification should expose the transformed type and explicit title payload.
    $firstNotification = collect($component->get('recentNotifications'))->first();

    expect($firstNotification)->toMatchArray([
        'type'  => 'SystemNotification',
        'title' => 'Notification 0',
    ]);

    Carbon::setTestNow();
});

// Validate ownership checks when marking notifications as read either individually or in bulk.
it('marks notifications as read for the authenticated user only', function (): void {
    // Create two users to ensure component actions only touch the acting user's notifications.
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    // Seed a notification for the acting user that will be marked as read.
    $ownNotification = Notification::factory()
        ->for($user, 'notifiable')
        ->create([
            'type'    => 'App\Notifications\OrderNotification',
            'read_at' => null,
            'data'    => [
                'title'   => 'Awaiting Order',
                'message' => 'Order pending',
            ],
        ]);

    // Seed a notification for the other user which must remain untouched by the acting user.
    $otherNotification = Notification::factory()
        ->for($otherUser, 'notifiable')
        ->create([
            'type'    => 'App\Notifications\OrderNotification',
            'read_at' => null,
        ]);

    // Start interacting with the component as the acting user.
    $component = Livewire::actingAs($user)->test(NotificationDropdown::class);
    $component->assertSet('unreadCount', 1);

    // Mark the owned notification as read and validate the updated unread count and payload state.
    $component->call('markAsRead', $ownNotification->getKey());
    Auth::setUser($user->fresh());
    $component->call('loadNotifications');
    $component->assertSet('unreadCount', 0);
    expect($ownNotification->fresh()->read_at)->not->toBeNull();
    $ownEntry = collect($component->get('recentNotifications'))->firstWhere('id', $ownNotification->getKey());
    expect($ownEntry['read_at'] ?? null)->not->toBeNull();

    // Attempting to mark another user's notification should leave it untouched and keep unread count at zero.
    $component->call('markAsRead', $otherNotification->getKey());
    Auth::setUser($user->fresh());
    $component->call('loadNotifications');
    $component->assertSet('unreadCount', 0);
    expect($otherNotification->fresh()->read_at)->toBeNull();

    // Marking all notifications as read should remain a user-scoped operation.
    $component->call('markAllAsRead');
    Auth::setUser($user->fresh());
    $component->call('loadNotifications');
    $component->assertSet('unreadCount', 0);
});

// Ensure markAllAsRead handles multiple unread notifications for the authenticated user.
it('marks all notifications as read when triggered explicitly', function (): void {
    // Prepare a user with several unread notifications that should be updated in bulk.
    $user = User::factory()->create();

    $notifications = Notification::factory()
        ->count(3)
        ->for($user, 'notifiable')
        ->create([
            'type'    => 'App\Notifications\OrderNotification',
            'read_at' => null,
            'data'    => [
                'title'   => 'Queued Order',
                'message' => 'Awaiting processing',
            ],
        ]);

    // Mount the component as the acting user and confirm all notifications start unread.
    $component = Livewire::actingAs($user)->test(NotificationDropdown::class);
    $component->assertSet('unreadCount', 3);

    // Call markAllAsRead to ensure the Livewire action updates every notification.
    $component->call('markAllAsRead');
    Auth::setUser($user->fresh());
    $component->call('loadNotifications');

    $component->assertSet('unreadCount', 0);
    expect($notifications->fresh()->pluck('read_at')->filter())->toHaveCount(3);
});
