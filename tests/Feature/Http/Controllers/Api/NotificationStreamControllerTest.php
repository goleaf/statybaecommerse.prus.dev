<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\NotificationStreamController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class NotificationStreamControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_stream_requires_authenticated_user(): void
    {
        $controller = app(NotificationStreamController::class);
        $request = Request::create('/api/users/placeholder/notifications/stream', 'GET');
        $targetUser = User::factory()->create();

        try {
            $controller->stream($request, $targetUser);
            $this->fail('Expected an HttpException to be thrown for unauthenticated access.');
        } catch (HttpException $exception) {
            // Assert unauthenticated requests abort with 401 so unauthorised visitors cannot open SSE sessions.
            $this->assertSame(401, $exception->getStatusCode());
        }
    }

    public function test_stream_forbids_streaming_other_user_channel(): void
    {
        $controller = app(NotificationStreamController::class);
        $authenticatedUser = User::factory()->create();
        $request = Request::create('/api/users/' . $authenticatedUser->getKey() . '/notifications/stream', 'GET');
        $request->setUserResolver(static fn (): User => $authenticatedUser);
        $targetUser = User::factory()->create();

        try {
            $controller->stream($request, $targetUser);
            $this->fail('Expected an HttpException to be thrown for mismatched user streaming.');
        } catch (HttpException $exception) {
            // Assert mismatched identifiers trigger a 403 so sessions cannot snoop on other user channels.
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_stream_returns_streamed_response_with_expected_headers(): void
    {
        $controller = app(NotificationStreamController::class);
        $user = User::factory()->create();
        $request = Request::create('/api/users/' . $user->getKey() . '/notifications/stream', 'GET');
        $request->setUserResolver(static fn () => $user);

        $response = $controller->stream($request, $user);

        // Assert the controller produces a StreamedResponse so the SSE pipeline can run lazily.
        $this->assertInstanceOf(StreamedResponse::class, $response);
        // Assert the Content-Type header advertises text/event-stream for compliant SSE clients.
        $this->assertSame('text/event-stream', $response->headers->get('Content-Type'));
        // Assert proxy buffering is disabled so heartbeat packets flush immediately to the browser.
        $this->assertSame('no', $response->headers->get('X-Accel-Buffering'));
    }
}
