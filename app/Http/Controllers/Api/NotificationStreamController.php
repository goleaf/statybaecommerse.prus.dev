<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use DateTimeInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use JsonException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * NotificationStreamController
 *
 * HTTP controller handling NotificationStreamController related web requests, responses, and business logic with proper validation and error handling.
 */
final class NotificationStreamController extends Controller
{
    /**
     * Handle stream functionality with proper error handling.
     */
    public function stream(Request $request): StreamedResponse
    {
        $user = $request->user();

        if ($user === null) {
            // Abort with HTTP 401 when the user is not authenticated.
            abort(401, 'Unauthorized');
        }

        // Establish configuration values that control the stream timing behaviour.
        $heartbeatIntervalSeconds = 30;
        $pollIntervalMicroseconds = 250000; // 0.25 seconds between database polls.
        $streamLifetimeSeconds = 300; // Stop streaming after five minutes to avoid runaway PHP workers.

        return response()->stream(
            callback: function () use ($user, $heartbeatIntervalSeconds, $pollIntervalMicroseconds, $streamLifetimeSeconds): void {
                // Allow the script to continue even if the client disconnects so we can exit gracefully.
                ignore_user_abort(true);

                // Send a connection confirmation payload when the stream starts.
                $this->sendEvent(
                    eventType: 'connected',
                    payload: [
                        'message' => 'Connected to live notifications',
                    ]
                );

                // Track timestamps to manage heartbeat cadence and polling windows.
                $streamStartedAt = Date::now();
                $lastHeartbeatAt = Date::now();
                $lastNotificationTimestamp = Date::now()->subSeconds(1);

                // Push any unread notifications immediately so the client is up to date.
                $this->pushUnreadNotifications($user, $lastNotificationTimestamp);

                while (true) {
                    // Capture the current timestamp once per loop iteration for consistent comparisons.
                    $currentTime = Date::now();

                    // Exit the loop when the client disconnects or the configured lifetime elapses.
                    if (connection_aborted() || $currentTime->diffInSeconds($streamStartedAt) >= $streamLifetimeSeconds) {
                        break;
                    }

                    // Emit periodic heartbeat packets so the client knows the connection is alive.
                    if ($currentTime->diffInSeconds($lastHeartbeatAt) >= $heartbeatIntervalSeconds) {
                        $this->sendEvent(eventType: 'heartbeat');
                        $lastHeartbeatAt = $currentTime;
                    }

                    // Deliver any new unread notifications that have arrived since the last poll.
                    $lastNotificationTimestamp = $this->pushUnreadNotifications($user, $lastNotificationTimestamp);

                    // Sleep briefly to limit database load while still providing near real-time updates.
                    usleep($pollIntervalMicroseconds);
                }
            },
            status: 200,
            headers: [
                'Content-Type'      => 'text/event-stream',
                'Cache-Control'     => 'no-cache, must-revalidate',
                'Connection'        => 'keep-alive',
                'X-Accel-Buffering' => 'no',
            ]
        );
    }

    /**
     * Emit new unread notifications over the stream and return the latest timestamp seen.
     */
    private function pushUnreadNotifications(Authenticatable $user, DateTimeInterface $lastNotificationTimestamp): DateTimeInterface
    {
        // Fetch notifications created after the last processed timestamp.
        $unreadNotificationsRelation = $user->unreadNotifications();

        /** @var \Illuminate\Database\Eloquent\Collection<int, DatabaseNotification> $notifications */
        $notifications = $unreadNotificationsRelation
            ->where('created_at', '>', $lastNotificationTimestamp)
            ->orderBy('created_at')
            ->get();

        // Loop through notifications and emit them individually to the stream.
        foreach ($notifications as $notification) {
            /** @var array<string, mixed> $notificationData */
            $notificationData = (array) $notification->data;

            $notificationTimestamp = $notification->created_at;
            $timestampString = $notificationTimestamp instanceof DateTimeInterface
                ? $notificationTimestamp->format(DATE_ATOM)
                : null;

            $this->sendEvent(
                eventType: 'notification',
                payload: [
                    'id'        => $notification->id,
                    'title'     => Arr::get($notificationData, 'title', 'Notification'),
                    'message'   => Arr::get($notificationData, 'message', ''),
                    'type'      => Arr::get($notificationData, 'type', 'info'),
                    'timestamp' => $timestampString,
                ]
            );

            // Update the timestamp tracker using the notification creation time.
            if ($notificationTimestamp instanceof DateTimeInterface) {
                $lastNotificationTimestamp = $notificationTimestamp;
            }
        }

        return $lastNotificationTimestamp;
    }

    /**
     * Helper method to emit a properly formatted Server-Sent Event payload.
     *
     * @param array<string, mixed> $payload
     */
    private function sendEvent(string $eventType, array $payload = []): void
    {
        // Merge the event type into the payload so the client can distinguish packets.
        /** @var array<string, mixed> $eventPayload */
        $eventPayload = array_merge([
            'type'      => $eventType,
            'timestamp' => Date::now()->toISOString(),
        ], $payload);

        // Echo the payload following the SSE protocol expectations.
        try {
            // Encode the payload as JSON while surfacing issues through JsonException if encoding fails.
            $encodedPayload = json_encode($eventPayload, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            // Fallback to a safe string representation so the stream continues even when encoding fails.
            $encodedPayload = json_encode([
                'type'      => 'error',
                'message'   => 'Failed to encode notification payload.',
                'details'   => $exception->getMessage(),
                'timestamp' => Date::now()->toISOString(),
            ]);
        }

        echo 'data: ' . $encodedPayload . "\n\n";

        // Flush output buffers so data reaches the client immediately.
        if (function_exists('ob_flush')) {
            @ob_flush();
        }

        flush();
    }
}
