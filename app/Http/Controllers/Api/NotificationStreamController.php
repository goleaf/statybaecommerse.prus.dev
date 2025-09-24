<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

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
    public function stream(Request $request): Response
    {
        $user = Auth::user();
        if (! $user) {
            abort(401);
        }
        $response = new Response;
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Headers', 'Cache-Control');
        $response->setCallback(function () use ($user) {
            // Send initial connection confirmation
            echo 'data: '.json_encode(['type' => 'connected', 'message' => 'Connected to live notifications', 'timestamp' => now()->toISOString()])."\n\n";
            // Keep connection alive with periodic heartbeats
            $lastHeartbeat = time();
            $lastNotificationCount = $user->unreadNotifications()->count();
            while (true) {
                // Send heartbeat every 30 seconds
                if (time() - $lastHeartbeat >= 30) {
                    echo 'data: '.json_encode(['type' => 'heartbeat', 'timestamp' => now()->toISOString()])."\n\n";
                    $lastHeartbeat = time();
                }
                // Check for new notifications
                $currentNotificationCount = $user->unreadNotifications()->count();
                if ($currentNotificationCount > $lastNotificationCount) {
                    $newNotifications = $user->unreadNotifications()->latest()->limit($currentNotificationCount - $lastNotificationCount)->get();
                    foreach ($newNotifications as $notification) {
                        echo 'data: '.json_encode(['type' => 'notification', 'id' => $notification->id, 'title' => $notification->data['title'] ?? 'Notification', 'message' => $notification->data['message'] ?? '', 'type' => $notification->data['type'] ?? 'info', 'timestamp' => $notification->created_at->toISOString()])."\n\n";
                    }
                    $lastNotificationCount = $currentNotificationCount;
                }
                // Check if connection is still alive
                if (connection_aborted()) {
                    break;
                }
                // Small delay to prevent excessive CPU usage
                usleep(100000);
                // 0.1 seconds
            }
        });

        return $response;
    }
}
