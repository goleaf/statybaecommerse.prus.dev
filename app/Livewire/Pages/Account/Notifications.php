<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Account;

use App\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Notifications
 *
 * Livewire component for Notifications with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property array $notifications
 * @property string $filter
 * @property bool $showUnreadOnly
 * @property mixed $listeners
 */
final class Notifications extends Component
{
    use WithPagination;

    public array $notifications = [];

    public string $filter = 'all';

    public bool $showUnreadOnly = false;

    protected $listeners = ['refreshNotifications' => 'loadNotifications'];

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        $this->loadNotifications();
    }

    /**
     * Handle loadNotifications functionality with proper error handling.
     */
    public function loadNotifications(): void
    {
        $user = auth()->user();
        if (! $user || ! method_exists($user, 'notifications') || ! Schema::hasTable('notifications')) {
            $this->notifications = [];

            return;
        }
        $query = $user->notifications()->latest();
        if ($this->showUnreadOnly) {
            $query->whereNull('read_at');
        }
        if ($this->filter !== 'all') {
            $query->whereJsonContains('data->type', $this->filter);
        }
        $this->notifications = $query->limit(100)->get(['id', 'type', 'data', 'read_at', 'created_at'])->map(function ($n) {
            $data = $n->data ?? [];

            return ['id' => $n->id, 'type' => $data['type'] ?? 'info', 'action' => $data['action'] ?? 'updated', 'title' => $data['title'] ?? __('notifications.types.info'), 'message' => $data['message'] ?? '', 'data' => $data, 'read_at' => $n->read_at, 'created_at' => $n->created_at, 'time_ago' => $this->getTimeAgo($n->created_at)];
        })->toArray();
    }

    /**
     * Handle markAsRead functionality with proper error handling.
     */
    public function markAsRead(string $notificationId): void
    {
        $user = auth()->user();
        $notification = $user->notifications()->find($notificationId);
        if ($notification && ! $notification->read_at) {
            $notification->markAsRead();
            $this->loadNotifications();
            $this->dispatch('notification-marked-read');
        }
    }

    /**
     * Handle markAllAsRead functionality with proper error handling.
     */
    public function markAllAsRead(): void
    {
        $user = auth()->user();
        app(NotificationService::class)->markAllAsRead($user);
        $this->loadNotifications();
        $this->dispatch('all-notifications-marked-read');
    }

    /**
     * Handle deleteNotification functionality with proper error handling.
     */
    public function deleteNotification(string $notificationId): void
    {
        $user = auth()->user();
        $deleted = app(NotificationService::class)->deleteNotification($user, $notificationId);
        if ($deleted) {
            $this->loadNotifications();
            $this->dispatch('notification-deleted');
        }
    }

    /**
     * Handle deleteAllNotifications functionality with proper error handling.
     */
    public function deleteAllNotifications(): void
    {
        $user = auth()->user();
        app(NotificationService::class)->deleteAllNotifications($user);
        $this->notifications = [];
        $this->dispatch('all-notifications-deleted');
    }

    /**
     * Handle updatedFilter functionality with proper error handling.
     */
    public function updatedFilter(): void
    {
        $this->loadNotifications();
    }

    /**
     * Handle updatedShowUnreadOnly functionality with proper error handling.
     */
    public function updatedShowUnreadOnly(): void
    {
        $this->loadNotifications();
    }

    /**
     * Handle getTimeAgo functionality with proper error handling.
     *
     * @param  mixed  $datetime
     */
    private function getTimeAgo($datetime): string
    {
        $now = now();
        $diff = $now->diffInMinutes($datetime);
        if ($diff < 1) {
            return __('notifications.time.just_now');
        } elseif ($diff < 60) {
            return __('notifications.time.minutes_ago', ['count' => $diff]);
        } elseif ($diff < 1440) {
            $hours = floor($diff / 60);

            return __('notifications.time.hours_ago', ['count' => $hours]);
        } elseif ($diff < 10080) {
            $days = floor($diff / 1440);

            return __('notifications.time.days_ago', ['count' => $days]);
        } elseif ($diff < 43200) {
            $weeks = floor($diff / 10080);

            return __('notifications.time.weeks_ago', ['count' => $weeks]);
        } elseif ($diff < 525600) {
            $months = floor($diff / 43200);

            return __('notifications.time.months_ago', ['count' => $months]);
        } else {
            $years = floor($diff / 525600);

            return __('notifications.time.years_ago', ['count' => $years]);
        }
    }

    /**
     * Handle getUnreadCount functionality with proper error handling.
     */
    public function getUnreadCount(): int
    {
        $user = auth()->user();

        return $user ? app(NotificationService::class)->getUnreadCount($user) : 0;
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.pages.account.notifications');
    }
}
