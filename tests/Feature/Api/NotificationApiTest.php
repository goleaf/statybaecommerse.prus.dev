<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Notification;
use App\Models\User;
use App\Support\ErrorCodes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_index_returns_data_for_authorized_user(): void
    {
        $user = User::factory()->create();
        Notification::factory()->count(3)->forUser($user)->create();

        Sanctum::actingAs($user, ['notifications.read']);

        $response = $this->getJson(route('api.v1.notifications.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'notification_type',
                    'category',
                    'title',
                    'message',
                    'urgent',
                    'color',
                    'tags',
                    'read_at',
                    'created_at',
                    'meta',
                ]],
                'meta'  => ['query', 'pagination'],
                'links' => ['first', 'last', 'prev', 'next'],
            ]);
    }

    public function test_notifications_index_requires_authentication(): void
    {
        $response = $this->getJson(route('api.v1.notifications.index'));

        $response->assertUnauthorized();
    }

    public function test_notifications_index_rejects_out_of_range_per_page_values(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['notifications.read']);

        $response = $this->getJson(route('api.v1.notifications.index', ['per_page' => 0]));

        $response->assertStatus(422)
            ->assertJsonPath('error.code', ErrorCodes::VALIDATION_FAILED)
            ->assertJsonPath('error.context.violations.0.field', 'per_page');
    }

    public function test_notifications_index_rejects_invalid_sort_direction(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['notifications.read']);

        $response = $this->getJson(route('api.v1.notifications.index', [
            'sort' => 'invalid',
        ]));

        $response->assertStatus(422)
            ->assertJsonPath('error.code', ErrorCodes::VALIDATION_FAILED)
            ->assertJsonPath('error.context.violations.0.field', 'sort');

        $response = $this->getJson(route('api.v1.notifications.index', [
            'sort'      => 'created_at',
            'direction' => 'sideways',
        ]));

        $response->assertStatus(422)
            ->assertJsonPath('error.code', ErrorCodes::VALIDATION_FAILED)
            ->assertJsonPath('error.context.violations.0.field', 'direction');
    }

    public function test_mark_as_read_requires_manage_ability(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->forUser($user)->unread()->create();

        Sanctum::actingAs($user, ['notifications.read']);

        $response = $this->postJson(route('api.v1.notifications.mark-as-read', $notification));

        $response->assertForbidden();
    }

    public function test_mark_as_read_returns_payload_for_owner(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->forUser($user)->unread()->create();

        Sanctum::actingAs($user, ['notifications.manage']);

        $response = $this->postJson(route('api.v1.notifications.mark-as-read', $notification));

        $response->assertOk()
            ->assertJsonPath('data.is_read', true)
            ->assertJsonPath('data.id', $notification->id);
    }

    public function test_mark_as_read_returns_not_found_for_foreign_notification(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $notification = Notification::factory()->forUser($otherUser)->unread()->create();

        Sanctum::actingAs($user, ['notifications.manage']);

        $response = $this->postJson(route('api.v1.notifications.mark-as-read', $notification));

        $response->assertNotFound()
            ->assertJsonPath('error.code', ErrorCodes::NOT_FOUND);
    }

    public function test_mark_as_read_returns_payload_for_owned_notification(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->forUser($user)->unread()->create([
            'data' => [
                'title'   => 'Order created',
                'message' => 'Order #123 created',
                'type'    => 'order',
            ],
        ]);

        Sanctum::actingAs($user, ['notifications.manage']);

        $response = $this->postJson(route('api.v1.notifications.mark-as-read', $notification));

        $response->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('success', true)
                ->where('data.id', $notification->id)
                ->where('data.title', 'Order created')
                ->where('data.category', 'order')
                ->whereType('data.read_at', 'string')
            );
    }

    public function test_notification_show_returns_not_found_for_missing_resource(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['notifications.read']);

        $response = $this->getJson(route('api.v1.notifications.show', [
            'notification' => Str::uuid()->toString(),
        ]));

        $response->assertNotFound()
            ->assertJsonPath('error.code', ErrorCodes::NOT_FOUND);
    }

    public function test_notification_search_requires_query_parameter(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['notifications.read']);

        $response = $this->getJson(route('api.v1.notifications.search'));

        $response->assertStatus(422)
            ->assertJsonPath('error.code', ErrorCodes::VALIDATION_FAILED)
            ->assertJsonPath('error.context.violations.0.field', 'q');
    }

    public function test_notification_stats_requires_read_ability(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['notifications.manage']);

        $response = $this->getJson(route('api.v1.notifications.stats'));

        $response->assertForbidden();
    }

    public function test_notification_requests_are_rate_limited(): void
    {
        $user = User::factory()->create();
        Notification::factory()->count(2)->forUser($user)->create();

        $originalUserLimit = config('security.rate_limiting.api.notifications.read.per_user');
        $originalIpLimit = config('security.rate_limiting.api.notifications.read.per_ip');

        config([
            // Enforce a tight per-user quota to trigger the limiter quickly during the test.
            'security.rate_limiting.api.notifications.read.per_user' => 1,
            'security.rate_limiting.api.notifications.read.per_ip'   => 100,
        ]);

        RateLimiter::clear('user:' . $user->id . '|api.notifications.read');
        RateLimiter::clear('ip:127.0.0.1|api.notifications.read');

        Sanctum::actingAs($user, ['notifications.read']);

        $firstResponse = $this->getJson(route('api.v1.notifications.index'));
        $firstResponse->assertOk();

        $secondResponse = $this->getJson(route('api.v1.notifications.index'));
        $secondResponse->assertStatus(429);

        config([
            'security.rate_limiting.api.notifications.read.per_user' => $originalUserLimit,
            'security.rate_limiting.api.notifications.read.per_ip'   => $originalIpLimit,
        ]);

        RateLimiter::clear('user:' . $user->id . '|api.notifications.read');
        RateLimiter::clear('ip:127.0.0.1|api.notifications.read');
    }
}
