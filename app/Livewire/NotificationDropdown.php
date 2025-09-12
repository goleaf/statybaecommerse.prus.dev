<?php declare(strict_types=1);

namespace App\Livewire;

use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;

final class NotificationDropdown extends Component
{
    public int $unreadCount = 0;
    public $recentNotifications = [];

    protected $listeners = ['notificationReceived' => 'loadNotifications'];

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        if (!auth()->check()) {
            return;
        }

        $this->unreadCount = auth()->user()->unreadNotifications->count();
        
        $this->recentNotifications = auth()->user()->notifications()
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => class_basename($notification->type),
                    'title' => $notification->data['title'] ?? __('Notification'),
                    'message' => $notification->data['message'] ?? '',
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            });
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = DatabaseNotification::find($notificationId);
        
        if ($notification && $notification->notifiable_id === auth()->id()) {
            $notification->markAsRead();
            $this->loadNotifications();
        }
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.notification-dropdown');
    }
}
