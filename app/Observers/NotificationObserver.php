<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

final class NotificationObserver
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    /**
     * Handle the Notification "created" event.
     */
    public function created(Notification $notification): void
    {
        // Log notification creation
        Log::info('Notification created', [
            'notification_id' => $notification->id,
            'type' => $notification->type,
            'notifiable_type' => $notification->notifiable_type,
            'notifiable_id' => $notification->notifiable_id,
            'urgent' => $notification->data['urgent'] ?? false,
        ]);

        // Send real-time notification if needed
        $this->sendRealTimeNotification($notification);
    }

    /**
     * Handle the Notification "updated" event.
     */
    public function updated(Notification $notification): void
    {
        // Log notification updates
        Log::info('Notification updated', [
            'notification_id' => $notification->id,
            'read_at' => $notification->read_at,
        ]);

        // Send real-time update if read status changed
        if ($notification->wasChanged('read_at')) {
            $this->sendReadStatusUpdate($notification);
        }
    }

    /**
     * Handle the Notification "deleted" event.
     */
    public function deleted(Notification $notification): void
    {
        // Log notification deletion
        Log::info('Notification deleted', [
            'notification_id' => $notification->id,
            'type' => $notification->type,
        ]);
    }

    /**
     * Send real-time notification via WebSocket or similar
     */
    private function sendRealTimeNotification(Notification $notification): void
    {
        // This would integrate with your real-time system (WebSocket, Pusher, etc.)
        // For now, we'll just log it
        Log::info('Real-time notification sent', [
            'notification_id' => $notification->id,
            'user_id' => $notification->notifiable_id,
        ]);

        // Example integration with Laravel WebSockets or Pusher:
        // broadcast(new NotificationCreated($notification))->toOthers();
    }

    /**
     * Send read status update
     */
    private function sendReadStatusUpdate(Notification $notification): void
    {
        Log::info('Read status update sent', [
            'notification_id' => $notification->id,
            'read_at' => $notification->read_at,
        ]);

        // Example integration:
        // broadcast(new NotificationReadStatusUpdated($notification))->toOthers();
    }
}
