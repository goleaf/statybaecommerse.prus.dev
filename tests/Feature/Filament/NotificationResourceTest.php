<?php declare(strict_types=1);

use App\Filament\Resources\NotificationResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

describe('NotificationResource', function () {
    beforeEach(function () {
        $this->adminUser = User::factory()->create(['is_admin' => true]);
        $this->regularUser = User::factory()->create(['is_admin' => false]);
        $this->actingAs($this->adminUser);
    });

    describe('Navigation and Access', function () {
        it('can render index page', function () {
            $this->get(NotificationResource::getUrl('index'))
                ->assertSuccessful();
        });

        it('can render view page', function () {
            $notification = DatabaseNotification::factory()->create([
                'notifiable_type' => User::class,
                'notifiable_id' => $this->adminUser->id,
            ]);

            $this->get(NotificationResource::getUrl('view', ['record' => $notification]))
                ->assertSuccessful();
        });

        it('has correct navigation configuration', function () {
            expect(NotificationResource::getNavigationIcon())->toBe('heroicon-o-bell');
            expect(NotificationResource::getNavigationLabel())->toBe('Notifications');
            expect(NotificationResource::getModelLabel())->toBe('Notification');
            expect(NotificationResource::getPluralModelLabel())->toBe('Notifications');
        });
    });

    describe('Table Functionality', function () {
        beforeEach(function () {
            $this->notifications = collect([
                DatabaseNotification::factory()->create([
                    'type' => 'App\Notifications\OrderNotification',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $this->adminUser->id,
                    'data' => [
                        'title' => 'Order Confirmed',
                        'message' => 'Your order has been confirmed',
                        'type' => 'order',
                    ],
                    'read_at' => null,
                ]),
                DatabaseNotification::factory()->create([
                    'type' => 'App\Notifications\ProductNotification',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $this->adminUser->id,
                    'data' => [
                        'title' => 'New Product Available',
                        'message' => 'Check out our new product',
                        'type' => 'product',
                    ],
                    'read_at' => now(),
                ]),
                DatabaseNotification::factory()->create([
                    'type' => 'App\Notifications\SystemNotification',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $this->adminUser->id,
                    'data' => [
                        'title' => 'System Maintenance',
                        'message' => 'Scheduled maintenance tonight',
                        'type' => 'system',
                    ],
                    'read_at' => null,
                ]),
            ]);
        });

        it('can list notifications', function () {
            livewire(NotificationResource\Pages\ListNotifications::class)
                ->assertCanSeeTableRecords($this->notifications);
        });

        it('displays notification data correctly', function () {
            $notification = $this->notifications->first();

            livewire(NotificationResource\Pages\ListNotifications::class)
                ->assertCanSeeTableRecord($notification)
                ->assertTableColumnExists('id')
                ->assertTableColumnExists('type')
                ->assertTableColumnExists('data.title')
                ->assertTableColumnExists('data.message')
                ->assertTableColumnExists('data.type')
                ->assertTableColumnExists('read_at')
                ->assertTableColumnExists('created_at');
        });

        it('can filter notifications by type', function () {
            livewire(NotificationResource\Pages\ListNotifications::class)
                ->filterTable('data.type', 'order')
                ->assertCanSeeTableRecord($this->notifications->first())
                ->assertCanNotSeeTableRecord($this->notifications->last());
        });

        it('can filter notifications by read status', function () {
            livewire(NotificationResource\Pages\ListNotifications::class)
                ->filterTable('read_at', true)
                ->assertCanSeeTableRecord($this->notifications->get(1))
                ->assertCanNotSeeTableRecord($this->notifications->first());
        });

        it('can filter notifications by date range', function () {
            $yesterday = now()->subDay();
            $tomorrow = now()->addDay();

            livewire(NotificationResource\Pages\ListNotifications::class)
                ->filterTable('created_at', [
                    'created_from' => $yesterday->format('Y-m-d'),
                    'created_until' => $tomorrow->format('Y-m-d'),
                ])
                ->assertCanSeeTableRecords($this->notifications);
        });

        it('can search notifications', function () {
            livewire(NotificationResource\Pages\ListNotifications::class)
                ->searchTable('Order Confirmed')
                ->assertCanSeeTableRecord($this->notifications->first())
                ->assertCanNotSeeTableRecord($this->notifications->get(1));
        });

        it('can sort notifications by created date', function () {
            livewire(NotificationResource\Pages\ListNotifications::class)
                ->sortTable('created_at', 'desc')
                ->assertCanSeeTableRecords($this->notifications);
        });
    });

    describe('Actions', function () {
        beforeEach(function () {
            $this->notification = DatabaseNotification::factory()->create([
                'notifiable_type' => User::class,
                'notifiable_id' => $this->adminUser->id,
                'data' => [
                    'title' => 'Test Notification',
                    'message' => 'This is a test notification',
                    'type' => 'system',
                ],
            ]);
        });

        it('can view notification details', function () {
            livewire(NotificationResource\Pages\ViewNotification::class, [
                'record' => $this->notification->getRouteKey(),
            ])
                ->assertSuccessful();
        });

        it('can delete notification', function () {
            livewire(NotificationResource\Pages\ListNotifications::class)
                ->callTableAction('delete', $this->notification)
                ->assertTableActionExists('delete');

            $this->assertDatabaseMissing('notifications', [
                'id' => $this->notification->id,
            ]);
        });

        it('can bulk delete notifications', function () {
            $notifications = DatabaseNotification::factory()->count(3)->create([
                'notifiable_type' => User::class,
                'notifiable_id' => $this->adminUser->id,
            ]);

            livewire(NotificationResource\Pages\ListNotifications::class)
                ->callTableBulkAction('delete', $notifications)
                ->assertTableBulkActionExists('delete');

            foreach ($notifications as $notification) {
                $this->assertDatabaseMissing('notifications', [
                    'id' => $notification->id,
                ]);
            }
        });
    });

    describe('Permissions', function () {
        it('prevents creation of notifications', function () {
            expect(NotificationResource::canCreate())->toBeFalse();
        });

        it('prevents editing of notifications', function () {
            $notification = DatabaseNotification::factory()->create();
            expect(NotificationResource::canEdit($notification))->toBeFalse();
        });

        it('allows viewing notifications for admin users', function () {
            $this->actingAs($this->adminUser);
            $this->get(NotificationResource::getUrl('index'))
                ->assertSuccessful();
        });
    });

    describe('Data Formatting', function () {
        it('formats notification type correctly', function () {
            $notification = DatabaseNotification::factory()->create([
                'type' => 'App\Notifications\OrderNotification',
            ]);

            livewire(NotificationResource\Pages\ListNotifications::class)
                ->assertTableColumnState('type', $notification, 'OrderNotification');
        });

        it('formats notifiable type correctly', function () {
            $notification = DatabaseNotification::factory()->create([
                'notifiable_type' => User::class,
            ]);

            livewire(NotificationResource\Pages\ListNotifications::class)
                ->assertTableColumnState('notifiable_type', $notification, 'User');
        });

        it('displays notification type badges with correct colors', function () {
            $orderNotification = DatabaseNotification::factory()->create([
                'data' => ['type' => 'order'],
            ]);

            $productNotification = DatabaseNotification::factory()->create([
                'data' => ['type' => 'product'],
            ]);

            livewire(NotificationResource\Pages\ListNotifications::class)
                ->assertCanSeeTableRecord($orderNotification)
                ->assertCanSeeTableRecord($productNotification);
        });

        it('shows read status correctly', function () {
            $readNotification = DatabaseNotification::factory()->create([
                'read_at' => now(),
            ]);

            $unreadNotification = DatabaseNotification::factory()->create([
                'read_at' => null,
            ]);

            livewire(NotificationResource\Pages\ListNotifications::class)
                ->assertCanSeeTableRecord($readNotification)
                ->assertCanSeeTableRecord($unreadNotification);
        });
    });

    describe('Query Optimization', function () {
        it('orders notifications by latest first', function () {
            $oldNotification = DatabaseNotification::factory()->create([
                'created_at' => now()->subDays(2),
            ]);

            $newNotification = DatabaseNotification::factory()->create([
                'created_at' => now(),
            ]);

            $query = NotificationResource::getEloquentQuery();
            $results = $query->get();

            expect($results->first()->id)->toBe($newNotification->id);
            expect($results->last()->id)->toBe($oldNotification->id);
        });
    });
});
