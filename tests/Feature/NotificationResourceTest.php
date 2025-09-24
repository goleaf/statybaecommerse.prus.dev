<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\NavigationGroup;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\TestCase;

final class NotificationResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_can_list_notifications(): void
    {
        $this->actingAs($this->adminUser);

        $notification = Notification::factory()->create([
            'notifiable_id' => $this->adminUser->id,
            'notifiable_type' => User::class,
            'type' => 'App\\Notifications\\TestNotification',
            'data' => [
                'title' => 'Test Notification',
                'body' => 'Test body',
                'type' => 'info',
            ],
        ]);

        Livewire::test(\App\Filament\Resources\NotificationResource\Pages\ListNotifications::class)
            ->assertCanSeeTableRecords(Notification::all());
    }

    public function test_can_create_notification(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\NotificationResource\Pages\CreateNotification::class)
            ->fillForm([
                'user_id' => $this->adminUser->id,
                'type' => 'info',
                'title' => 'Test Notification',
                'body' => 'Test body',
                'is_read' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->adminUser->id,
            'notifiable_type' => User::class,
            'type' => 'info',
        ]);
    }

    public function test_can_edit_notification(): void
    {
        $this->actingAs($this->adminUser);

        $notification = Notification::factory()->create([
            'notifiable_id' => $this->adminUser->id,
            'notifiable_type' => User::class,
            'type' => 'info',
            'data' => [
                'title' => 'Original Title',
                'body' => 'Original body',
            ],
        ]);

        Livewire::test(\App\Filament\Resources\NotificationResource\Pages\EditNotification::class, [
            'record' => $notification->getRouteKey(),
        ])
            ->fillForm([
                'title' => 'Updated Title',
                'body' => 'Updated body',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
        ]);
    }

    public function test_can_view_notification(): void
    {
        $this->actingAs($this->adminUser);

        $notification = Notification::factory()->create([
            'notifiable_id' => $this->adminUser->id,
            'notifiable_type' => User::class,
            'type' => 'info',
            'data' => [
                'title' => 'Test Notification',
                'body' => 'Test body',
            ],
        ]);

        Livewire::test(\App\Filament\Resources\NotificationResource\Pages\ViewNotification::class, [
            'record' => $notification->getRouteKey(),
        ])
            ->assertCanSeeFormData([
                'title' => 'Test Notification',
            ]);
    }

    public function test_can_delete_notification(): void
    {
        $this->actingAs($this->adminUser);

        $notification = Notification::factory()->create([
            'notifiable_id' => $this->adminUser->id,
            'notifiable_type' => User::class,
        ]);

        Livewire::test(\App\Filament\Resources\NotificationResource\Pages\ListNotifications::class)
            ->callTableAction('delete', $notification);

        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id,
        ]);
    }

    public function test_can_filter_by_user(): void
    {
        $this->actingAs($this->adminUser);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $notification1 = Notification::factory()->create([
            'notifiable_id' => $user1->id,
            'notifiable_type' => User::class,
        ]);
        $notification2 = Notification::factory()->create([
            'notifiable_id' => $user2->id,
            'notifiable_type' => User::class,
        ]);

        Livewire::test(\App\Filament\Resources\NotificationResource\Pages\ListNotifications::class)
            ->filterTable('user_id', $user1->id)
            ->assertCanSeeTableRecords([$notification1])
            ->assertCanNotSeeTableRecords([$notification2]);
    }

    public function test_can_filter_by_type(): void
    {
        $this->actingAs($this->adminUser);

        $infoNotification = Notification::factory()->create([
            'notifiable_id' => $this->adminUser->id,
            'notifiable_type' => User::class,
            'type' => 'info',
        ]);
        $successNotification = Notification::factory()->create([
            'notifiable_id' => $this->adminUser->id,
            'notifiable_type' => User::class,
            'type' => 'success',
        ]);

        Livewire::test(\App\Filament\Resources\NotificationResource\Pages\ListNotifications::class)
            ->filterTable('type', 'info')
            ->assertCanSeeTableRecords([$infoNotification])
            ->assertCanNotSeeTableRecords([$successNotification]);
    }

    public function test_can_filter_by_read_status(): void
    {
        $this->actingAs($this->adminUser);

        $readNotification = Notification::factory()->create([
            'notifiable_id' => $this->adminUser->id,
            'notifiable_type' => User::class,
            'read_at' => now(),
        ]);
        $unreadNotification = Notification::factory()->create([
            'notifiable_id' => $this->adminUser->id,
            'notifiable_type' => User::class,
            'read_at' => null,
        ]);

        Livewire::test(\App\Filament\Resources\NotificationResource\Pages\ListNotifications::class)
            ->filterTable('is_read', true)
            ->assertCanSeeTableRecords([$readNotification])
            ->assertCanNotSeeTableRecords([$unreadNotification]);
    }

    public function test_can_mark_notification_as_read(): void
    {
        $this->actingAs($this->adminUser);

        $notification = Notification::factory()->create([
            'notifiable_id' => $this->adminUser->id,
            'notifiable_type' => User::class,
            'read_at' => null,
        ]);

        Livewire::test(\App\Filament\Resources\NotificationResource\Pages\ListNotifications::class)
            ->callTableAction('mark_as_read', $notification);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'read_at' => now(),
        ]);
    }

    public function test_can_mark_notification_as_unread(): void
    {
        $this->actingAs($this->adminUser);

        $notification = Notification::factory()->create([
            'notifiable_id' => $this->adminUser->id,
            'notifiable_type' => User::class,
            'read_at' => now(),
        ]);

        Livewire::test(\App\Filament\Resources\NotificationResource\Pages\ListNotifications::class)
            ->callTableAction('mark_as_unread', $notification);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'read_at' => null,
        ]);
    }

    public function test_can_bulk_mark_as_read(): void
    {
        $this->actingAs($this->adminUser);

        $notifications = Notification::factory()->count(3)->create([
            'notifiable_id' => $this->adminUser->id,
            'notifiable_type' => User::class,
            'read_at' => null,
        ]);

        Livewire::test(\App\Filament\Resources\NotificationResource\Pages\ListNotifications::class)
            ->callTableBulkAction('bulk_mark_as_read', $notifications);

        foreach ($notifications as $notification) {
            $this->assertDatabaseHas('notifications', [
                'id' => $notification->id,
                'read_at' => now(),
            ]);
        }
    }

    public function test_can_bulk_mark_as_unread(): void
    {
        $this->actingAs($this->adminUser);

        $notifications = Notification::factory()->count(3)->create([
            'notifiable_id' => $this->adminUser->id,
            'notifiable_type' => User::class,
            'read_at' => now(),
        ]);

        Livewire::test(\App\Filament\Resources\NotificationResource\Pages\ListNotifications::class)
            ->callTableBulkAction('bulk_mark_as_unread', $notifications);

        foreach ($notifications as $notification) {
            $this->assertDatabaseHas('notifications', [
                'id' => $notification->id,
                'read_at' => null,
            ]);
        }
    }

    public function test_validation_requires_user(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\NotificationResource\Pages\CreateNotification::class)
            ->fillForm([
                'type' => 'info',
                'title' => 'Test Notification',
                'body' => 'Test body',
            ])
            ->call('create')
            ->assertHasFormErrors(['user_id']);
    }

    public function test_validation_requires_type(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\NotificationResource\Pages\CreateNotification::class)
            ->fillForm([
                'user_id' => $this->adminUser->id,
                'title' => 'Test Notification',
                'body' => 'Test body',
            ])
            ->call('create')
            ->assertHasFormErrors(['type']);
    }

    public function test_validation_requires_title(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\NotificationResource\Pages\CreateNotification::class)
            ->fillForm([
                'user_id' => $this->adminUser->id,
                'type' => 'info',
                'body' => 'Test body',
            ])
            ->call('create')
            ->assertHasFormErrors(['title']);
    }

    public function test_validation_requires_body(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\NotificationResource\Pages\CreateNotification::class)
            ->fillForm([
                'user_id' => $this->adminUser->id,
                'type' => 'info',
                'title' => 'Test Notification',
            ])
            ->call('create')
            ->assertHasFormErrors(['body']);
    }

    public function test_navigation_group_is_system(): void
    {
        $this->assertEquals(NavigationGroup::System, \App\Filament\Resources\NotificationResource::getNavigationGroup());
    }

    public function test_has_correct_navigation_sort(): void
    {
        $this->assertEquals(3, \App\Filament\Resources\NotificationResource::getNavigationSort());
    }

    public function test_has_correct_navigation_icon(): void
    {
        $this->assertEquals('heroicon-o-bell', \App\Filament\Resources\NotificationResource::getNavigationIcon());
    }
}
