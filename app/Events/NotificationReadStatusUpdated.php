<?php

declare (strict_types=1);
namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
/**
 * NotificationReadStatusUpdated
 * 
 * Event class for NotificationReadStatusUpdated application events with comprehensive data payload and listener integration.
 * 
 */
final class NotificationReadStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * Initialize the class instance with required dependencies.
     * @param Notification $notification
     */
    public function __construct(public Notification $notification)
    {
    }
    /**
     * Handle broadcastOn functionality with proper error handling.
     * @return array
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->notification->notifiable_id), new Channel('notifications')];
    }
    /**
     * Handle broadcastWith functionality with proper error handling.
     * @return array
     */
    public function broadcastWith(): array
    {
        return ['id' => $this->notification->id, 'read_at' => $this->notification->read_at, 'is_read' => !is_null($this->notification->read_at)];
    }
    /**
     * Handle broadcastAs functionality with proper error handling.
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'notification.read_status_updated';
    }
}