<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * NotificationCenter
 *
 * Livewire component for NotificationCenter with reactive frontend functionality,
 * real-time updates, and user interaction handling.
 *
 * @property string $filter
 * @property bool   $showUnreadOnly
 */
final class NotificationCenter extends Component
{
    use WithPagination;

    public string $filter = 'all';

    public bool $showUnreadOnly = false;

    protected $listeners = [];

    private const CACHE_TTL = 300; // 5 minutes

    private const PAGINATION_SIZE = 10;

    private const MAX_CACHE_TAGS = 50;

    /**
     * Real-time notification listener using Livewire 3 attributes.
     */
    #[On('notificationReceived')]
    public function handleNotificationReceived(): void
    {
        $this->clearNotificationCache();
        $this->resetPage();
    }

    /**
     * Handle Echo broadcast events for real-time updates.
     */
    #[On('echo:notifications.{userId},NotificationSent')]
    public function handleBroadcastNotification(): void
    {
        $this->handleNotificationReceived();
    }

    /**
     * Initialize the Livewire component with parameters and security checks.
     */
    public function mount(): void
    {
        $this->filter = request()->get('filter', 'all');

        // Validate user has access to notifications
        if (! $this->getUserId()) {
            abort(403, __('notifications.errors.unauthorized'));
        }
    }

    /**
     * Handle updatedFilter functionality with proper error handling.
     */
    public function updatedFilter(): void
    {
        $this->resetPage();
        $this->clearNotificationCache();
    }

    /**
     * Handle updatedShowUnreadOnly functionality with proper error handling.
     */
    public function updatedShowUnreadOnly(): void
    {
        $this->resetPage();
        $this->clearNotificationCache();
    }

    /**
     * Handle markAsRead functionality with proper error handling.
     */
    public function markAsRead(string $notificationId): void
    {
        $notification = $this->findUserNotification($notificationId);

        if ($notification && $notification->unread()) {
            $notification->markAsRead();
            $this->clearNotificationCache();
            $this->dispatch('notificationRead', $notificationId);
        }
    }

    /**
     * Handle markAsUnread functionality with proper error handling.
     */
    public function markAsUnread(string $notificationId): void
    {
        $notification = $this->findUserNotification($notificationId);

        if ($notification && $notification->read()) {
            $notification->markAsUnread();
            $this->clearNotificationCache();
            $this->dispatch('notificationUnread', $notificationId);
        }
    }

    /**
     * Handle markAllAsRead functionality with proper error handling.
     */
    public function markAllAsRead(): void
    {
        $unreadCount = auth()->user()->unreadNotifications()->count();

        if ($unreadCount > 0) {
            auth()->user()->unreadNotifications->markAsRead();
            $this->clearNotificationCache();
            $this->dispatch('allNotificationsRead');
        }
    }

    /**
     * Handle deleteNotification functionality with proper error handling.
     */
    public function deleteNotification(string $notificationId): void
    {
        $notification = $this->findUserNotification($notificationId);

        if ($notification) {
            $notification->delete();
            $this->clearNotificationCache();
            $this->dispatch('notificationDeleted', $notificationId);
        }
    }

    /**
     * Handle clearAllNotifications functionality with proper error handling.
     */
    public function clearAllNotifications(): void
    {
        $deletedCount = auth()->user()->notifications()->delete();

        if ($deletedCount > 0) {
            $this->clearNotificationCache();
            $this->dispatch('allNotificationsCleared');
        }
    }

    /**
     * Get notifications with optimized query and caching using Livewire 3 computed properties.
     */
    #[Computed]
    public function notifications(): LengthAwarePaginator
    {
        $cacheKey = $this->getNotificationsCacheKey();

        return Cache::tags(['notifications', "user:{$this->getUserId()}"])
            ->remember($cacheKey, self::CACHE_TTL, function () {
                return $this->buildNotificationsQuery()
                    ->paginate(self::PAGINATION_SIZE);
            });
    }

    /**
     * Get unread notifications count with caching and tenant isolation.
     */
    #[Computed]
    public function unreadCount(): int
    {
        $cacheKey = 'notifications_unread_count_' . $this->getUserId();

        return Cache::tags(['notifications', "user:{$this->getUserId()}"])
            ->remember($cacheKey, self::CACHE_TTL, function () {
                return $this->getBaseNotificationsQuery()
                    ->whereNull('read_at')
                    ->count();
            });
    }

    /**
     * Get available notification types with caching and performance optimization.
     */
    #[Computed]
    public function notificationTypes(): array
    {
        $cacheKey = 'notification_types_' . $this->getUserId();

        return Cache::tags(['notifications', "user:{$this->getUserId()}"])
            ->remember($cacheKey, 3600, function () {
                return DB::table('notifications')
                    ->select('type')
                    ->where('notifiable_type', 'App\\Models\\User')
                    ->where('notifiable_id', $this->getUserId())
                    ->distinct()
                    ->pluck('type')
                    ->mapWithKeys(function ($type) {
                        return [$type => class_basename($type)];
                    })
                    ->toArray();
            });
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.notification-center', [
            'notifications'     => $this->notifications,
            'unreadCount'       => $this->unreadCount,
            'notificationTypes' => $this->notificationTypes,
        ]);
    }

    /**
     * Get the current user ID with proper null handling.
     */
    private function getUserId(): ?int
    {
        return auth()->id();
    }

    /**
     * Get base notifications query with tenant isolation.
     */
    private function getBaseNotificationsQuery(): Builder
    {
        return DB::table('notifications')
            ->where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', $this->getUserId());
    }

    /**
     * Find a notification that belongs to the current user.
     */
    private function findUserNotification(string $notificationId): ?DatabaseNotification
    {
        return auth()->user()->notifications()->find($notificationId);
    }

    /**
     * Build the notifications query with filters and optimizations.
     */
    private function buildNotificationsQuery()
    {
        $query = auth()->user()->notifications()
            ->select(['id', 'type', 'data', 'read_at', 'created_at', 'notifiable_id'])
            ->latest('created_at');

        if ($this->showUnreadOnly) {
            $query->whereNull('read_at');
        }

        if ($this->filter !== 'all') {
            $query->where('type', $this->filter);
        }

        return $query;
    }

    /**
     * Generate cache key for notifications.
     */
    private function getNotificationsCacheKey(): string
    {
        return sprintf(
            'notifications_%s_%s_%s_%d',
            $this->getUserId(),
            $this->filter,
            $this->showUnreadOnly ? 'unread' : 'all',
            $this->getPage()
        );
    }

    /**
     * Clear notification-related cache with improved tag-based invalidation.
     */
    private function clearNotificationCache(): void
    {
        $userId = $this->getUserId();

        if (! $userId) {
            return;
        }

        // Use cache tags for more efficient invalidation
        Cache::tags(['notifications', "user:{$userId}"])->flush();

        // Also clear specific keys for backward compatibility
        $patterns = [
            "notifications_{$userId}_*",
            "notification_types_{$userId}",
            "notifications_unread_count_{$userId}",
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($pattern, '*')) {
                // For patterns with wildcards, we'd need a more sophisticated approach
                // For now, we rely on cache tags above
                continue;
            }
            Cache::forget($pattern);
        }
    }
}
