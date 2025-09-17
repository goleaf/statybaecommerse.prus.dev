<?php

declare (strict_types=1);
namespace App\Livewire;

use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;
/**
 * NotificationDropdown
 * 
 * Livewire component for NotificationDropdown with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property int $unreadCount
 * @property mixed $recentNotifications
 * @property mixed $listeners
 */
final class NotificationDropdown extends Component
{
    public int $unreadCount = 0;
    public $recentNotifications = [];
    protected $listeners = ['notificationReceived' => 'loadNotifications'];
    /**
     * Initialize the Livewire component with parameters.
     * @return void
     */
    public function mount(): void
    {
        $this->loadNotifications();
    }
    /**
     * Handle loadNotifications functionality with proper error handling.
     * @return void
     */
    public function loadNotifications(): void
    {
        if (!auth()->check()) {
            return;
        }
        $this->unreadCount = auth()->user()->unreadNotifications->count();
        $this->recentNotifications = auth()->user()->notifications()->latest()->limit(5)->get()->map(function ($notification) {
            return ['id' => $notification->id, 'type' => class_basename($notification->type), 'title' => $notification->data['title'] ?? __('Notification'), 'message' => $notification->data['message'] ?? '', 'read_at' => $notification->read_at, 'created_at' => $notification->created_at->diffForHumans()];
        });
    }
    /**
     * Handle markAsRead functionality with proper error handling.
     * @param string $notificationId
     * @return void
     */
    public function markAsRead(string $notificationId): void
    {
        $notification = DatabaseNotification::find($notificationId);
        if ($notification && $notification->notifiable_id === auth()->id()) {
            $notification->markAsRead();
            $this->loadNotifications();
        }
    }
    /**
     * Handle markAllAsRead functionality with proper error handling.
     * @return void
     */
    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }
    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.notification-dropdown');
    }
}