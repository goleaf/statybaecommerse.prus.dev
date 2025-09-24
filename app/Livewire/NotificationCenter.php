<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * NotificationCenter
 *
 * Livewire component for NotificationCenter with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property string $filter
 * @property bool $showUnreadOnly
 * @property mixed $listeners
 */
final class NotificationCenter extends Component
{
    use WithPagination;

    public string $filter = 'all';

    public bool $showUnreadOnly = false;

    protected $listeners = ['notificationReceived' => '$refresh'];

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        $this->filter = request()->get('filter', 'all');
    }

    /**
     * Handle updatedFilter functionality with proper error handling.
     */
    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Handle updatedShowUnreadOnly functionality with proper error handling.
     */
    public function updatedShowUnreadOnly(): void
    {
        $this->resetPage();
    }

    /**
     * Handle markAsRead functionality with proper error handling.
     */
    public function markAsRead(string $notificationId): void
    {
        $notification = DatabaseNotification::find($notificationId);
        if ($notification && $notification->notifiable_id === auth()->id()) {
            $notification->markAsRead();
            $this->dispatch('notificationRead', $notificationId);
        }
    }

    /**
     * Handle markAsUnread functionality with proper error handling.
     */
    public function markAsUnread(string $notificationId): void
    {
        $notification = DatabaseNotification::find($notificationId);
        if ($notification && $notification->notifiable_id === auth()->id()) {
            $notification->markAsUnread();
            $this->dispatch('notificationUnread', $notificationId);
        }
    }

    /**
     * Handle markAllAsRead functionality with proper error handling.
     */
    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->dispatch('allNotificationsRead');
    }

    /**
     * Handle deleteNotification functionality with proper error handling.
     */
    public function deleteNotification(string $notificationId): void
    {
        $notification = DatabaseNotification::find($notificationId);
        if ($notification && $notification->notifiable_id === auth()->id()) {
            $notification->delete();
            $this->dispatch('notificationDeleted', $notificationId);
        }
    }

    /**
     * Handle clearAllNotifications functionality with proper error handling.
     */
    public function clearAllNotifications(): void
    {
        auth()->user()->notifications()->delete();
        $this->dispatch('allNotificationsCleared');
    }

    /**
     * Handle getNotificationsProperty functionality with proper error handling.
     */
    public function getNotificationsProperty()
    {
        $query = auth()->user()->notifications()->latest();
        if ($this->showUnreadOnly) {
            $query->whereNull('read_at');
        }
        if ($this->filter !== 'all') {
            $query->where('type', $this->filter);
        }

        return $query->paginate(10);
    }

    /**
     * Handle getUnreadCountProperty functionality with proper error handling.
     */
    public function getUnreadCountProperty(): int
    {
        return auth()->user()->unreadNotifications->count();
    }

    /**
     * Handle getNotificationTypesProperty functionality with proper error handling.
     */
    public function getNotificationTypesProperty(): array
    {
        return auth()->user()->notifications()->select('type')->distinct()->pluck('type')->mapWithKeys(function ($type) {
            $shortType = class_basename($type);

            return [$type => $shortType];
        })->toArray();
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.notification-center', ['notifications' => $this->notifications, 'unreadCount' => $this->unreadCount, 'notificationTypes' => $this->notificationTypes]);
    }
}
