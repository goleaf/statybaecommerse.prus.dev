<?php

declare(strict_types=1);

use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;

it('requires authentication for the notifications API', function (): void {
    getJson(route('api.v1.notifications.index'))->assertUnauthorized();
});

it('validates notification listing parameters', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['notifications.read']);

    getJson(route('api.v1.notifications.index', ['per_page' => 1000]))
        ->assertStatus(422)
        ->assertJson(['success' => false]);
});

it('returns 403 when token lacks notification read ability', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['notifications.manage']);

    getJson(route('api.v1.notifications.index'))->assertForbidden();
});

it('returns 404 when attempting to delete another users notification', function (): void {
    $actor = User::factory()->create();
    $other = User::factory()->create();
    $notification = Notification::factory()->forUser($other)->create();

    Sanctum::actingAs($actor, ['notifications.manage']);

    deleteJson(route('api.v1.notifications.destroy', $notification))
        ->assertNotFound()
        ->assertJson(['success' => false]);
});

it('throttles excessive notification requests', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['notifications.read']);

    Config::set('api.rate_limits.notifications', 1);

    $limiterKey = 'user:' . $user->id . '|notifications';
    RateLimiter::clear($limiterKey);

    getJson(route('api.v1.notifications.index'))->assertOk();

    getJson(route('api.v1.notifications.index'))->assertStatus(429);

    RateLimiter::clear($limiterKey);
});

it('returns 500 when the notification service fails', function (): void {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['notifications.read']);

    $failingService = new class
    {
        public function getUserNotifications(): never
        {
            throw new \RuntimeException('notifications failed');
        }
    };

    app()->instance(NotificationService::class, $failingService);

    getJson(route('api.v1.notifications.index'))
        ->assertStatus(500)
        ->assertJsonPath('message', 'Server Error');

    app()->forgetInstance(NotificationService::class);
});
