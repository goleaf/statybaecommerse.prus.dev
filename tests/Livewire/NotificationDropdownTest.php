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

it('initialises an empty state for guests', function (): void {
    Auth::logout();

    $component = Livewire::test(NotificationDropdown::class);

    expect($component->get('unreadCount'))->toBe(0);
    expect($component->get('recentNotifications'))->toBeArray();
    expect($component->get('recentNotifications'))->toBeEmpty();

    $component->call('markAllAsRead');
    expect($component->get('unreadCount'))->toBe(0);
});

it('loads the five most recent notifications for authenticated users', function (): void {
    Carbon::setTestNow('2025-01-02 12:00:00');

    $user = User::factory()->create();

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

    $component = Livewire::actingAs($user)->test(NotificationDropdown::class);

    $component->assertSet('unreadCount', 4);

    $recentIds = collect($component->get('recentNotifications'))->pluck('id')->all();

    expect($recentIds)->toHaveCount(5);
    expect($recentIds)->toEqual($notifications->take(5)->pluck('id')->all());

    $firstNotification = collect($component->get('recentNotifications'))->first();

    expect($firstNotification)->toMatchArray([
        'type'  => 'SystemNotification',
        'title' => 'Notification 0',
    ]);

    Carbon::setTestNow();
});

it('marks notifications as read for the authenticated user only', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

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

    $otherNotification = Notification::factory()
        ->for($otherUser, 'notifiable')
        ->create([
            'type'    => 'App\Notifications\OrderNotification',
            'read_at' => null,
        ]);

    $component = Livewire::actingAs($user)->test(NotificationDropdown::class);
    $component->assertSet('unreadCount', 1);

    $component->call('markAsRead', $ownNotification->getKey());
    Auth::setUser($user->fresh());
    $component->call('loadNotifications');
    $component->assertSet('unreadCount', 0);
    expect($ownNotification->fresh()->read_at)->not->toBeNull();
    $ownEntry = collect($component->get('recentNotifications'))->firstWhere('id', $ownNotification->getKey());
    expect($ownEntry['read_at'] ?? null)->not->toBeNull();

    $component->call('markAsRead', $otherNotification->getKey());
    Auth::setUser($user->fresh());
    $component->call('loadNotifications');
    $component->assertSet('unreadCount', 0);
    expect($otherNotification->fresh()->read_at)->toBeNull();

    $component->call('markAllAsRead');
    Auth::setUser($user->fresh());
    $component->call('loadNotifications');
    $component->assertSet('unreadCount', 0);
});
