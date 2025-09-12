<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.templates.account')] class extends Component {
    use \Livewire\WithPagination;
    
    public array $notifications = [];
    public string $filter = 'all';
    public bool $showUnreadOnly = false;

    protected $listeners = ['refreshNotifications' => 'loadNotifications'];

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        $user = auth()->user();
        if (!$user || !method_exists($user, 'notifications') || !\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
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

        $this->notifications = $query
            ->limit(100)
            ->get(['id', 'type', 'data', 'read_at', 'created_at'])
            ->map(function ($n) {
                $data = $n->data ?? [];
                return [
                    'id' => $n->id,
                    'type' => $data['type'] ?? 'info',
                    'action' => $data['action'] ?? 'updated',
                    'title' => $data['title'] ?? __('notifications.types.info'),
                    'message' => $data['message'] ?? '',
                    'data' => $data,
                    'read_at' => $n->read_at,
                    'created_at' => $n->created_at,
                    'time_ago' => $this->getTimeAgo($n->created_at),
                ];
            })
            ->toArray();
    }

    public function markAsRead(string $notificationId): void
    {
        $user = auth()->user();
        $notification = $user->notifications()->find($notificationId);
        
        if ($notification && !$notification->read_at) {
            $notification->markAsRead();
            $this->loadNotifications();
            $this->dispatch('notification-marked-read');
        }
    }

    public function markAllAsRead(): void
    {
        $user = auth()->user();
        app(\App\Services\NotificationService::class)->markAllAsRead($user);
        $this->loadNotifications();
        $this->dispatch('all-notifications-marked-read');
    }

    public function deleteNotification(string $notificationId): void
    {
        $user = auth()->user();
        $deleted = app(\App\Services\NotificationService::class)->deleteNotification($user, $notificationId);
        
        if ($deleted) {
            $this->loadNotifications();
            $this->dispatch('notification-deleted');
        }
    }

    public function deleteAllNotifications(): void
    {
        $user = auth()->user();
        app(\App\Services\NotificationService::class)->deleteAllNotifications($user);
        $this->notifications = [];
        $this->dispatch('all-notifications-deleted');
    }

    public function updatedFilter(): void
    {
        $this->loadNotifications();
    }

    public function updatedShowUnreadOnly(): void
    {
        $this->loadNotifications();
    }

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

    public function getUnreadCount(): int
    {
        $user = auth()->user();
        return $user ? app(\App\Services\NotificationService::class)->getUnreadCount($user) : 0;
    }
}; ?>

<div class="space-y-6">
    <x-breadcrumbs :items="[['label' => __('My account'), 'url' => route('account.index')], ['label' => __('Notifications')]]" />
    
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('Notifications') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('Stay updated with your account activity') }}</p>
        </div>
        
        @if (!empty($notifications))
            <div class="flex space-x-2">
                <button 
                    wire:click="markAllAsRead" 
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    {{ __('notifications.mark_all_as_read') }}
                </button>
                
                <button 
                    wire:click="deleteAllNotifications" 
                    wire:confirm="{{ __('Are you sure you want to delete all notifications?') }}"
                    class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    {{ __('notifications.delete_all_notifications') }}
                </button>
            </div>
        @endif
    </div>

    <!-- Filters -->
    @if (!empty($notifications))
        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <input 
                            wire:model.live="showUnreadOnly" 
                            id="unread-only" 
                            type="checkbox" 
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        >
                        <label for="unread-only" class="ml-2 text-sm text-gray-700">
                            {{ __('Show unread only') }}
                        </label>
                    </div>
                </div>
                
                <div class="flex items-center space-x-2">
                    <label for="filter" class="text-sm font-medium text-gray-700">{{ __('Filter by type') }}:</label>
                    <select 
                        wire:model.live="filter" 
                        id="filter" 
                        class="block w-40 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                        <option value="all">{{ __('All types') }}</option>
                        <option value="order">{{ __('notifications.types.order') }}</option>
                        <option value="product">{{ __('notifications.types.product') }}</option>
                        <option value="user">{{ __('notifications.types.user') }}</option>
                        <option value="system">{{ __('notifications.types.system') }}</option>
                        <option value="payment">{{ __('notifications.types.payment') }}</option>
                        <option value="shipping">{{ __('notifications.types.shipping') }}</option>
                        <option value="review">{{ __('notifications.types.review') }}</option>
                        <option value="promotion">{{ __('notifications.types.promotion') }}</option>
                        <option value="newsletter">{{ __('notifications.types.newsletter') }}</option>
                        <option value="support">{{ __('notifications.types.support') }}</option>
                    </select>
                </div>
            </div>
        </div>
    @endif

    <!-- Notifications List -->
    @if (empty($notifications))
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-6H4v6zM4 5h6V1H4v4zM15 3h5v6h-5V3z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('notifications.no_notifications') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ __('notifications.check_later') }}</p>
        </div>
    @else
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                @foreach ($notifications as $notification)
                    <li class="relative {{ !$notification['read_at'] ? 'bg-blue-50' : '' }}">
                        <div class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <!-- Notification Icon -->
                                    <div class="flex-shrink-0">
                                        @php
                                            $iconClass = match($notification['type']) {
                                                'order' => 'text-blue-600',
                                                'product' => 'text-green-600',
                                                'user' => 'text-purple-600',
                                                'system' => 'text-orange-600',
                                                'payment' => 'text-yellow-600',
                                                'shipping' => 'text-indigo-600',
                                                'review' => 'text-pink-600',
                                                'promotion' => 'text-red-600',
                                                'newsletter' => 'text-cyan-600',
                                                'support' => 'text-gray-600',
                                                default => 'text-gray-600'
                                            };
                                            
                                            $icon = match($notification['type']) {
                                                'order' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                                                'product' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                                                'user' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                                                'system' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                                                'payment' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
                                                'shipping' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                                                'review' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
                                                'promotion' => 'M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 0h10m-10 0a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2M9 8h6m-6 4h6m-6 4h4',
                                                'newsletter' => 'M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                                                'support' => 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5z',
                                                default => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                                            };
                                        @endphp
                                        
                                        <div class="h-8 w-8 rounded-full {{ $iconClass }} bg-opacity-10 flex items-center justify-center">
                                            <svg class="h-4 w-4 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    <!-- Notification Content -->
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center space-x-2">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                {{ $notification['title'] }}
                                            </p>
                                            @if (!$notification['read_at'])
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ __('New') }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-500 truncate">
                                            {{ $notification['message'] }}
                                        </p>
                                        <div class="mt-1 flex items-center space-x-2 text-xs text-gray-400">
                                            <span>{{ $notification['time_ago'] }}</span>
                                            <span>•</span>
                                            <span class="capitalize">{{ __('notifications.types.' . $notification['type']) }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex items-center space-x-2">
                                    @if (!$notification['read_at'])
                                        <button 
                                            wire:click="markAsRead('{{ $notification['id'] }}')"
                                            class="text-gray-400 hover:text-gray-600"
                                            title="{{ __('notifications.mark_as_read') }}"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                    @endif
                                    
                                    <button 
                                        wire:click="deleteNotification('{{ $notification['id'] }}')"
                                        wire:confirm="{{ __('Are you sure you want to delete this notification?') }}"
                                        class="text-gray-400 hover:text-red-600"
                                        title="{{ __('notifications.delete_notification') }}"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('notification-marked-read', () => {
            // Optional: Show success message
        });
        
        Livewire.on('all-notifications-marked-read', () => {
            // Optional: Show success message
        });
        
        Livewire.on('notification-deleted', () => {
            // Optional: Show success message
        });
        
        Livewire.on('all-notifications-deleted', () => {
            // Optional: Show success message
        });
    });
</script>
@endpush
