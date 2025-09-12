<?php declare(strict_types=1);

namespace App\Filament\Components;

use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;

final class LiveNotificationFeed extends Component
{
    public $notifications = [];
    public $unreadCount = 0;
    public $isOpen = false;

    protected $listeners = [
        'refreshNotifications' => 'loadNotifications',
        'notificationReceived' => 'handleNewNotification',
    ];

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        $user = auth()->user();
        
        if (!$user) {
            return;
        }

        $this->notifications = $user->notifications()
            ->latest()
            ->limit(10)
            ->get()
            ->map(function (DatabaseNotification $notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? '',
                    'type' => $notification->data['type'] ?? 'info',
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                    'time_ago' => $notification->created_at->diffForHumans(),
                ];
            })
            ->toArray();

        $this->unreadCount = $user->unreadNotifications()->count();
    }

    public function handleNewNotification($notificationData): void
    {
        $this->loadNotifications();
        
        // Show a toast notification for new notifications
        Notification::make()
            ->title($notificationData['title'] ?? 'New Notification')
            ->body($notificationData['message'] ?? '')
            ->icon($this->getNotificationIcon($notificationData['type'] ?? 'info'))
            ->color($this->getNotificationColor($notificationData['type'] ?? 'info'))
            ->send();
    }

    public function toggleNotifications(): void
    {
        $this->isOpen = !$this->isOpen;
        
        if ($this->isOpen) {
            $this->loadNotifications();
        }
    }

    public function markAsRead($notificationId): void
    {
        $user = auth()->user();
        $notification = $user->notifications()->find($notificationId);
        
        if ($notification && !$notification->read_at) {
            $notification->markAsRead();
            $this->loadNotifications();
        }
    }

    public function markAllAsRead(): void
    {
        $user = auth()->user();
        $user->unreadNotifications()->update(['read_at' => now()]);
        $this->loadNotifications();
    }

    public function deleteNotification($notificationId): void
    {
        $user = auth()->user();
        $user->notifications()->find($notificationId)?->delete();
        $this->loadNotifications();
    }

    public function clearAllNotifications(): void
    {
        $user = auth()->user();
        $user->notifications()->delete();
        $this->loadNotifications();
    }

    private function getNotificationIcon(string $type): string
    {
        return match ($type) {
            'success' => 'heroicon-o-check-circle',
            'error' => 'heroicon-o-x-circle',
            'warning' => 'heroicon-o-exclamation-triangle',
            'info' => 'heroicon-o-information-circle',
            default => 'heroicon-o-bell',
        };
    }

    private function getNotificationColor(string $type): string
    {
        return match ($type) {
            'success' => 'success',
            'error' => 'danger',
            'warning' => 'warning',
            'info' => 'info',
            default => 'gray',
        };
    }

    public function render()
    {
        return view('filament.components.live-notification-feed');
    }
}
