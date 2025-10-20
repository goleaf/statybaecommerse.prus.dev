<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
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
            ->assertJsonFragment(['success' => true])
            ->assertJsonStructure([
                'data',
                'meta' => ['query', 'pagination'],
                'links' => ['first', 'last', 'prev', 'next'],
            ]);
    }

    public function test_notifications_index_requires_authentication(): void
    {
        $response = $this->getJson(route('api.v1.notifications.index'));

        $response->assertUnauthorized();
    }

    public function test_mark_as_read_requires_manage_ability(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->forUser($user)->unread()->create();

        Sanctum::actingAs($user, ['notifications.read']);

        $response = $this->postJson(route('api.v1.notifications.mark-as-read', $notification));

        $response->assertForbidden();
    }

    public function test_mark_as_read_returns_not_found_for_foreign_notification(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $notification = Notification::factory()->forUser($otherUser)->unread()->create();

        Sanctum::actingAs($user, ['notifications.manage']);

        $response = $this->postJson(route('api.v1.notifications.mark-as-read', $notification));

        $response->assertNotFound();
    }

    public function test_notification_requests_are_rate_limited(): void
    {
        $user = User::factory()->create();
        Notification::factory()->count(2)->forUser($user)->create();

        $originalUserLimit = config('security.rate_limiting.api.notifications.read.per_user');
        $originalIpLimit = config('security.rate_limiting.api.notifications.read.per_ip');

        config([
            'security.rate_limiting.api.notifications.read.per_user' => 1,
            'security.rate_limiting.api.notifications.read.per_ip' => 100,
        ]);

        RateLimiter::clear('user:'.$user->id.'|api.notifications.read');
        RateLimiter::clear('ip:127.0.0.1|api.notifications.read');

        Sanctum::actingAs($user, ['notifications.read']);

        $firstResponse = $this->getJson(route('api.v1.notifications.index'));
        $firstResponse->assertOk();

        $secondResponse = $this->getJson(route('api.v1.notifications.index'));
        $secondResponse->assertStatus(429);

        config([
            'security.rate_limiting.api.notifications.read.per_user' => $originalUserLimit,
            'security.rate_limiting.api.notifications.read.per_ip' => $originalIpLimit,
        ]);

        RateLimiter::clear('user:'.$user->id.'|api.notifications.read');
        RateLimiter::clear('ip:127.0.0.1|api.notifications.read');
    }
}
