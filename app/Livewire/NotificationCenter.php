<?php declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;
use Livewire\WithPagination;

final class NotificationCenter extends Component
{
    use WithPagination;

    public string $filter = 'all';
    public bool $showUnreadOnly = false;

    protected $listeners = ['notificationReceived' => '$refresh'];

    public function mount(): void
    {
        $this->filter = request()->get('filter', 'all');
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function updatedShowUnreadOnly(): void
    {
        $this->resetPage();
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = DatabaseNotification::find($notificationId);
        
        if ($notification && $notification->notifiable_id === auth()->id()) {
            $notification->markAsRead();
            $this->dispatch('notificationRead', $notificationId);
        }
    }

    public function markAsUnread(string $notificationId): void
    {
        $notification = DatabaseNotification::find($notificationId);
        
        if ($notification && $notification->notifiable_id === auth()->id()) {
            $notification->markAsUnread();
            $this->dispatch('notificationUnread', $notificationId);
        }
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->dispatch('allNotificationsRead');
    }

    public function deleteNotification(string $notificationId): void
    {
        $notification = DatabaseNotification::find($notificationId);
        
        if ($notification && $notification->notifiable_id === auth()->id()) {
            $notification->delete();
            $this->dispatch('notificationDeleted', $notificationId);
        }
    }

    public function clearAllNotifications(): void
    {
        auth()->user()->notifications()->delete();
        $this->dispatch('allNotificationsCleared');
    }

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

    public function getUnreadCountProperty(): int
    {
        return auth()->user()->unreadNotifications->count();
    }

    public function getNotificationTypesProperty(): array
    {
        return auth()->user()->notifications()
            ->select('type')
            ->distinct()
            ->pluck('type')
            ->mapWithKeys(function ($type) {
                $shortType = class_basename($type);
                return [$type => $shortType];
            })
            ->toArray();
    }

    public function render()
    {
        return view('livewire.notification-center', [
            'notifications' => $this->notifications,
            'unreadCount' => $this->unreadCount,
            'notificationTypes' => $this->notificationTypes,
        ]);
    }
}
