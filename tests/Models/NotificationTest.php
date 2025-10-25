<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_scopes_segment_records(): void
    {
        // Seed a consistent user so all notifications share the same recipient.
        $user = User::factory()->create();

        // Create a read notification with a non-urgent payload for baseline expectations.
        $readNotification = Notification::factory()
            ->forUser($user)
            ->read()
            ->state(fn (array $attributes) => [
                'type' => 'App\\Notifications\\OrderNotification',
                'data' => array_merge(is_array($attributes['data'] ?? null) ? $attributes['data'] : [], [
                    'notification_type' => 'order',
                    'urgent'            => false,
                    'tags'              => ['orders'],
                ]),
                'created_at' => Carbon::now()->subDay(),
            ])
            ->create();

        // Create an urgent unread notification to verify the complementary scopes.
        $urgentNotification = Notification::factory()
            ->forUser($user)
            ->unread()
            ->urgent()
            ->state(fn (array $attributes) => [
                'type' => 'App\\Notifications\\OrderNotification',
                'data' => array_merge(is_array($attributes['data'] ?? null) ? $attributes['data'] : [], [
                    'notification_type' => 'order',
                    'tags'              => ['orders', 'priority'],
                ]),
                'created_at' => Carbon::now()->subHours(3),
            ])
            ->create();

        // Create an older notification to exercise the date-based scopes.
        $oldNotification = Notification::factory()
            ->forUser($user)
            ->unread()
            ->state(fn (array $attributes) => [
                'type' => 'App\\Notifications\\SupportNotification',
                'data' => array_merge(is_array($attributes['data'] ?? null) ? $attributes['data'] : [], [
                    'notification_type' => 'support',
                    'urgent'            => false,
                    'tags'              => ['helpdesk'],
                ]),
                'created_at' => Carbon::now()->subDays(45),
            ])
            ->create();

        // Verify the read scope only returns notifications with a populated read_at column.
        self::assertSame([$readNotification->id], Notification::query()->read()->pluck('id')->all());

        // Confirm unread scope includes both the urgent and old notifications.
        self::assertEqualsCanonicalizing([
            $urgentNotification->id,
            $oldNotification->id,
        ], Notification::query()->unread()->pluck('id')->all());

        // Validate the urgent and normal scopes split the dataset correctly.
        self::assertSame([$urgentNotification->id], Notification::query()->urgent()->pluck('id')->all());
        self::assertEqualsCanonicalizing([
            $readNotification->id,
            $oldNotification->id,
        ], Notification::query()->normal()->pluck('id')->all());

        // Ensure the type scope respects both payload metadata and class name fallbacks.
        self::assertCount(2, Notification::query()->byType('order')->get());
        self::assertCount(1, Notification::query()->byType('support')->get());
        self::assertEqualsCanonicalizing([
            $readNotification->id,
            $urgentNotification->id,
        ], Notification::query()->byNotificationType('App\\Notifications\\OrderNotification')->pluck('id')->all());

        // Confirm the user scope pairs notifiable id/type correctly.
        self::assertCount(3, Notification::query()->forUser($user->id)->get());

        // Check the recency and age-based scopes leverage created_at timestamps.
        self::assertEqualsCanonicalizing([
            $readNotification->id,
            $urgentNotification->id,
        ], Notification::query()->recent(7)->pluck('id')->all());
        self::assertSame([$oldNotification->id], Notification::query()->old(30)->pluck('id')->all());

        // The tag scope should match notifications containing any provided tag.
        self::assertEqualsCanonicalizing([
            $readNotification->id,
            $urgentNotification->id,
        ], Notification::query()->withTags(['orders'])->pluck('id')->all());
        self::assertSame([$urgentNotification->id], Notification::query()->withTags(['priority'])->pluck('id')->all());

        // Ensure the range scope captures records between the specified window.
        self::assertSame(2, Notification::query()->byDateRange(Carbon::now()->subDay(), Carbon::now())->count());
    }

    public function test_accessors_normalize_payload_and_dates(): void
    {
        // Freeze time so formatted accessors have deterministic output.
        Carbon::setTestNow(Carbon::create(2024, 5, 1, 12, 0, 0));

        // Persist a notification lacking explicit payload metadata to exercise the fallback logic.
        $notification = Notification::factory()
            ->state(fn (array $attributes) => [
                'type' => 'App\\Notifications\\OrderNotification',
                'data' => array_merge(is_array($attributes['data'] ?? null) ? $attributes['data'] : [], [
                    'title'      => 'Order ready',
                    'message'    => 'An order is ready for pickup.',
                    'urgent'     => false,
                    'tags'       => ['orders'],
                    'color'      => 'green',
                    'attachment' => null,
                ]),
                'read_at' => Carbon::now(),
            ])
            ->create();

        // Reset the test clock to avoid side effects on other tests.
        Carbon::setTestNow();

        // Validate boolean-style accessors that expose derived state.
        self::assertTrue($notification->is_read);
        self::assertFalse($notification->is_urgent);

        // The derived notification_type should fall back to the class basename.
        self::assertSame('order', $notification->notification_type);

        // Formatted timestamps should match the expected display convention.
        self::assertNotNull($notification->created_at);
        self::assertNotNull($notification->read_at);
        self::assertSame($notification->created_at->format('d/m/Y H:i'), $notification->formatted_created_at);
        self::assertSame($notification->read_at->format('d/m/Y H:i'), $notification->formatted_read_at);

        // String-based accessors should surface payload values directly.
        self::assertSame('Order ready', $notification->title);
        self::assertSame('An order is ready for pickup.', $notification->message);
        self::assertSame('green', $notification->color);

        // Optional payload elements should surface the stored array structure without mutation.
        self::assertSame(['orders'], $notification->tags);
        self::assertNull($notification->attachment);

        // Helper methods should lean on the configured maps for presentation metadata.
        self::assertSame('blue', $notification->getNotificationTypeColor());
        self::assertSame('heroicon-o-shopping-cart', $notification->getNotificationTypeIcon());
        self::assertNotEmpty($notification->getTimeAgo());
        self::assertNotEmpty($notification->getReadTimeAgo());
    }

    public function test_mutators_update_payload_collections_consistently(): void
    {
        // Create a notification with a lean payload to exercise mutation helpers.
        $notification = Notification::factory()
            ->state(fn (array $attributes) => [
                'data' => array_merge(is_array($attributes['data'] ?? null) ? $attributes['data'] : [], [
                    'notification_type' => 'promotion',
                    'tags'              => ['initial'],
                ]),
            ])
            ->create();

        // Adding a tag should append the value to the stored array.
        self::assertTrue($notification->addTag('secondary'));
        $notification->refresh();
        self::assertEqualsCanonicalizing(['initial', 'secondary'], $notification->tags);

        // Attempting to add the same tag should leave the payload unchanged.
        self::assertFalse($notification->addTag('secondary'));

        // Removing an existing tag should reindex the collection to maintain JSON order.
        self::assertTrue($notification->removeTag('initial'));
        $notification->refresh();
        self::assertSame(['secondary'], $notification->tags);

        // Removing a non-existent tag should not modify the payload.
        self::assertFalse($notification->removeTag('missing'));

        // Updating the urgent flag, color, and attachment should merge with existing data.
        self::assertTrue($notification->setUrgent());
        self::assertTrue($notification->setColor('purple'));
        self::assertTrue($notification->setAttachment('https://example.com/manual.pdf'));
        $notification->refresh();

        // Ensure the payload reflects the merged mutations.
        self::assertTrue($notification->is_urgent);
        self::assertSame('purple', $notification->color);
        self::assertSame('https://example.com/manual.pdf', $notification->attachment);
    }

    public function test_marking_and_duplication_behaviour(): void
    {
        // Create an unread notification so the markAsRead helper has observable impact.
        $notification = Notification::factory()->unread()->create();

        // Marking as read should populate the read_at column.
        self::assertTrue($notification->markAsRead());
        $notification->refresh();
        self::assertNotNull($notification->read_at);

        // Toggling the read status should revert to an unread state.
        self::assertTrue($notification->toggleReadStatus());
        $notification->refresh();
        self::assertNull($notification->read_at);

        // Marking as unread explicitly should remain idempotent on an unread record.
        self::assertTrue($notification->markAsUnread());

        // Duplicate should create a new record with cleared read status and fresh timestamps.
        $duplicate = $notification->duplicate();
        self::assertNotSame($notification->id, $duplicate->id);
        self::assertNull($duplicate->read_at);
        self::assertNotNull($duplicate->created_at);
        self::assertNotNull($notification->created_at);
        self::assertTrue($duplicate->created_at->greaterThanOrEqualTo($notification->created_at));
    }

    public function test_static_helpers_manage_bulk_updates_and_statistics(): void
    {
        // Create two users so we can check per-user bulk operations alongside global stats.
        $users = User::factory()->count(2)->create();
        $firstUser = $users->first();
        $secondUser = $users->last();
        self::assertNotNull($firstUser);
        self::assertNotNull($secondUser);

        // Seed notifications across both users with distinct types and read states.
        $firstUnread = Notification::factory()
            ->forUser($firstUser)
            ->unread()
            ->state(fn (array $attributes) => [
                'type' => 'App\\Notifications\\ProductNotification',
                'data' => array_merge(is_array($attributes['data'] ?? null) ? $attributes['data'] : [], [
                    'notification_type' => 'product',
                ]),
                'created_at' => Carbon::now()->subDay(),
            ])
            ->create();

        $firstRead = Notification::factory()
            ->forUser($firstUser)
            ->read()
            ->state(fn (array $attributes) => [
                'type' => 'App\\Notifications\\ProductNotification',
                'data' => array_merge(is_array($attributes['data'] ?? null) ? $attributes['data'] : [], [
                    'notification_type' => 'product',
                ]),
                'created_at' => Carbon::now()->subHours(2),
            ])
            ->create();

        Notification::factory()
            ->forUser($secondUser)
            ->unread()
            ->state(fn (array $attributes) => [
                'type' => 'App\\Notifications\\SupportNotification',
                'data' => array_merge(is_array($attributes['data'] ?? null) ? $attributes['data'] : [], [
                    'notification_type' => 'support',
                ]),
                'created_at' => Carbon::now()->subDays(31),
            ])
            ->create();

        // markAllAsReadForUser should convert unread notifications to read for the targeted user.
        self::assertSame(1, Notification::markAllAsReadForUser($firstUser->id));
        $firstUnread->refresh();
        self::assertNotNull($firstUnread->read_at);

        // markAllAsUnreadForUser should revert the previously read notifications.
        self::assertSame(2, Notification::markAllAsUnreadForUser($firstUser->id));
        $firstUnread->refresh();
        $firstRead->refresh();
        self::assertNull($firstUnread->read_at);
        self::assertNull($firstRead->read_at);

        // cleanupOld should remove notifications older than the specified threshold.
        self::assertSame(1, Notification::cleanupOld(30));

        // getStats should return counts for the key lifecycle buckets.
        $stats = Notification::getStats();
        self::assertSame(Notification::count(), $stats['total']);
        self::assertSame(Notification::query()->unread()->count(), $stats['unread']);
        self::assertSame(Notification::query()->read()->count(), $stats['read']);

        // getTypeStats should include all canonical keys even when zero.
        $typeStats = Notification::getTypeStats();
        self::assertArrayHasKey('product', $typeStats);
        self::assertArrayHasKey('support', $typeStats);
        self::assertSame(2, $typeStats['product']);
        self::assertSame(0, $typeStats['order']);
    }
}
