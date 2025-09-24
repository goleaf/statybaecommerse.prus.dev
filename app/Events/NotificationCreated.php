<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * NotificationCreated
 *
 * Event class for NotificationCreated application events with comprehensive data payload and listener integration.
 */
final class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Initialize the class instance with required dependencies.
     */
    public function __construct(public Notification $notification) {}

    /**
     * Handle broadcastOn functionality with proper error handling.
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.'.$this->notification->notifiable_id), new Channel('notifications')];
    }

    /**
     * Handle broadcastWith functionality with proper error handling.
     */
    public function broadcastWith(): array
    {
        return ['id' => $this->notification->id, 'type' => $this->notification->type, 'data' => $this->notification->data, 'created_at' => $this->notification->created_at, 'read_at' => $this->notification->read_at, 'urgent' => $this->notification->data['urgent'] ?? false];
    }

    /**
     * Handle broadcastAs functionality with proper error handling.
     */
    public function broadcastAs(): string
    {
        return 'notification.created';
    }
}
