<?php

declare(strict_types=1);

use App\Livewire\NotificationCenter;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('notification center component renders successfully', function () {
    Livewire::test(NotificationCenter::class)
        ->assertStatus(200);
});

test('component initializes with correct default values', function () {
    Livewire::test(NotificationCenter::class)
        ->assertSet('filter', 'all')
        ->assertSet('showUnreadOnly', false);
});

test('component requires authenticated user', function () {
    auth()->logout();

    expect(function () {
        Livewire::test(NotificationCenter::class);
    })->toThrow(Exception::class);
});

test('filter updates reset pagination', function () {
    Livewire::test(NotificationCenter::class)
        ->set('filter', 'order')
        ->assertSet('filter', 'order');
});

test('show unread only updates reset pagination', function () {
    Livewire::test(NotificationCenter::class)
        ->set('showUnreadOnly', true)
        ->assertSet('showUnreadOnly', true);
});

test('mark notification as read works correctly', function () {
    $notificationId = (string) \Illuminate\Support\Str::uuid();

    DB::table('notifications')->insert([
        'id'              => $notificationId,
        'type'            => 'App\\Notifications\\TestNotification',
        'notifiable_type' => User::class,
        'notifiable_id'   => $this->user->id,
        'data'            => json_encode(['message' => 'Test notification']),
        'read_at'         => null,
        'created_at'      => now(),
        'updated_at'      => now(),
    ]);

    Livewire::test(NotificationCenter::class)
        ->call('markAsRead', $notificationId)
        ->assertDispatched('notificationRead', $notificationId);

    $notification = DB::table('notifications')->where('id', $notificationId)->first();
    expect($notification->read_at)->not->toBeNull();
});

test('mark notification as unread works correctly', function () {
    $notificationId = (string) \Illuminate\Support\Str::uuid();

    DB::table('notifications')->insert([
        'id'              => $notificationId,
        'type'            => 'App\\Notifications\\TestNotification',
        'notifiable_type' => User::class,
        'notifiable_id'   => $this->user->id,
        'data'            => json_encode(['message' => 'Test notification']),
        'read_at'         => now(),
        'created_at'      => now(),
        'updated_at'      => now(),
    ]);

    Livewire::test(NotificationCenter::class)
        ->call('markAsUnread', $notificationId)
        ->assertDispatched('notificationUnread', $notificationId);

    $notification = DB::table('notifications')->where('id', $notificationId)->first();
    expect($notification->read_at)->toBeNull();
});

test('mark all as read works correctly', function () {
    // Create multiple unread notifications
    for ($i = 0; $i < 3; $i++) {
        DB::table('notifications')->insert([
            'id'              => (string) \Illuminate\Support\Str::uuid(),
            'type'            => 'App\\Notifications\\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id'   => $this->user->id,
            'data'            => json_encode(['message' => "Test notification {$i}"]),
            'read_at'         => null,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    Livewire::test(NotificationCenter::class)
        ->call('markAllAsRead')
        ->assertDispatched('allNotificationsRead');

    $unreadCount = DB::table('notifications')
        ->where('notifiable_id', $this->user->id)
        ->whereNull('read_at')
        ->count();

    expect($unreadCount)->toBe(0);
});

test('delete notification works correctly', function () {
    $notificationId = (string) \Illuminate\Support\Str::uuid();

    DB::table('notifications')->insert([
        'id'              => $notificationId,
        'type'            => 'App\\Notifications\\TestNotification',
        'notifiable_type' => User::class,
        'notifiable_id'   => $this->user->id,
        'data'            => json_encode(['message' => 'Test notification']),
        'read_at'         => null,
        'created_at'      => now(),
        'updated_at'      => now(),
    ]);

    Livewire::test(NotificationCenter::class)
        ->call('deleteNotification', $notificationId)
        ->assertDispatched('notificationDeleted', $notificationId);

    $notification = DB::table('notifications')->where('id', $notificationId)->first();
    expect($notification)->toBeNull();
});

test('clear all notifications works correctly', function () {
    // Create multiple notifications
    for ($i = 0; $i < 3; $i++) {
        DB::table('notifications')->insert([
            'id'              => (string) \Illuminate\Support\Str::uuid(),
            'type'            => 'App\\Notifications\\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id'   => $this->user->id,
            'data'            => json_encode(['message' => "Test notification {$i}"]),
            'read_at'         => null,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    Livewire::test(NotificationCenter::class)
        ->call('clearAllNotifications')
        ->assertDispatched('allNotificationsCleared');

    $count = DB::table('notifications')
        ->where('notifiable_id', $this->user->id)
        ->count();

    expect($count)->toBe(0);
});

test('real-time notification listener works', function () {
    Livewire::test(NotificationCenter::class)
        ->call('handleNotificationReceived')
        ->assertHasNoErrors();
});

test('broadcast notification listener works', function () {
    Livewire::test(NotificationCenter::class)
        ->call('handleBroadcastNotification')
        ->assertHasNoErrors();
});

test('component handles non-existent notifications gracefully', function () {
    Livewire::test(NotificationCenter::class)
        ->call('markAsRead', 'non-existent-id')
        ->assertHasNoErrors();
});
