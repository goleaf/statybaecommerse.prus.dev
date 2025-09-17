<?php

declare (strict_types=1);
namespace App\Observers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
/**
 * NotificationObserver
 * 
 * Model observer for NotificationObserver Eloquent model events with automatic side effect handling and data consistency.
 * 
 */
final class NotificationObserver
{
    /**
     * Initialize the class instance with required dependencies.
     * @param NotificationService $notificationService
     */
    public function __construct(private readonly NotificationService $notificationService)
    {
    }
    /**
     * Handle created functionality with proper error handling.
     * @param Notification $notification
     * @return void
     */
    public function created(Notification $notification): void
    {
        // Skip logging during tests to prevent test output issues
        if (!app()->environment('testing')) {
            // Log notification creation
            Log::info('Notification created', ['notification_id' => $notification->id, 'type' => $notification->type, 'notifiable_type' => $notification->notifiable_type, 'notifiable_id' => $notification->notifiable_id, 'urgent' => $notification->data['urgent'] ?? false]);
        }
        // Send real-time notification if needed
        $this->sendRealTimeNotification($notification);
    }
    /**
     * Handle updated functionality with proper error handling.
     * @param Notification $notification
     * @return void
     */
    public function updated(Notification $notification): void
    {
        // Skip logging during tests to prevent test output issues
        if (!app()->environment('testing')) {
            // Log notification updates
            Log::info('Notification updated', ['notification_id' => $notification->id, 'read_at' => $notification->read_at]);
        }
        // Send real-time update if read status changed
        if ($notification->wasChanged('read_at')) {
            $this->sendReadStatusUpdate($notification);
        }
    }
    /**
     * Handle deleted functionality with proper error handling.
     * @param Notification $notification
     * @return void
     */
    public function deleted(Notification $notification): void
    {
        // Skip logging during tests to prevent test output issues
        if (!app()->environment('testing')) {
            // Log notification deletion
            Log::info('Notification deleted', ['notification_id' => $notification->id, 'type' => $notification->type]);
        }
    }
    /**
     * Handle sendRealTimeNotification functionality with proper error handling.
     * @param Notification $notification
     * @return void
     */
    private function sendRealTimeNotification(Notification $notification): void
    {
        // Skip logging during tests to prevent test output issues
        if (!app()->environment('testing')) {
            // This would integrate with your real-time system (WebSocket, Pusher, etc.)
            // For now, we'll just log it
            Log::info('Real-time notification sent', ['notification_id' => $notification->id, 'user_id' => $notification->notifiable_id]);
        }
        // Example integration with Laravel WebSockets or Pusher:
        // broadcast(new NotificationCreated($notification))->toOthers();
    }
    /**
     * Handle sendReadStatusUpdate functionality with proper error handling.
     * @param Notification $notification
     * @return void
     */
    private function sendReadStatusUpdate(Notification $notification): void
    {
        // Skip logging during tests to prevent test output issues
        if (!app()->environment('testing')) {
            Log::info('Read status update sent', ['notification_id' => $notification->id, 'read_at' => $notification->read_at]);
        }
        // Example integration:
        // broadcast(new NotificationReadStatusUpdated($notification))->toOthers();
    }
}