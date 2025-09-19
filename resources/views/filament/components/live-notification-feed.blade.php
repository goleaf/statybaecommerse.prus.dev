<div class="relative" x-data="{ open: @entangle('isOpen') }">
    <!-- Bell Icon Button -->
    <button 
        @click="open = !open; $wire.toggleNotifications()"
        class="relative p-2 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 rounded-md"
        :class="{ 'text-primary-500': open }"
    >
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.828 7l2.586 2.586a2 2 0 001.414.586H15a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9.828a2 2 0 01-.586-1.414L4.828 7z" />
        </svg>
        
        <!-- Notification Badge -->
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 h-5 w-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center animate-pulse">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Notification Panel -->
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.away="open = false"
        class="absolute right-0 mt-2 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50"
        x-cloak
    >
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                {{ __('admin.notifications.title') }}
            </h3>
            <div class="flex space-x-2">
                @if($unreadCount > 0)
                    <button 
                        wire:click="markAllAsRead"
                        class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                    >
                        {{ __('admin.notifications.mark_all_as_read') }}
                    </button>
                @endif
                <button 
                    wire:click="clearAllNotifications"
                    class="text-sm text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                >
                    {{ __('Clear All') }}
                </button>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto">
            @if(count($notifications) > 0)
                @foreach($notifications as $notification)
                    <div 
                        class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150"
                        :class="{ 'bg-blue-50 dark:bg-blue-900/20': !{{ $notification['read_at'] ? 'true' : 'false' }} }"
                    >
                        <div class="flex items-start space-x-3">
                            <!-- Notification Icon -->
                            <div class="flex-shrink-0">
                                @php
                                    $iconClass = match($notification['type']) {
                                        'success' => 'text-green-500',
                                        'error' => 'text-red-500',
                                        'warning' => 'text-yellow-500',
                                        'info' => 'text-blue-500',
                                        default => 'text-gray-500'
                                    };
                                    $icon = match($notification['type']) {
                                        'success' => 'heroicon-o-check-circle',
                                        'error' => 'heroicon-o-x-circle',
                                        'warning' => 'heroicon-o-exclamation-triangle',
                                        'info' => 'heroicon-o-information-circle',
                                        default => 'heroicon-o-bell'
                                    };
                                @endphp
                                <div class="h-8 w-8 rounded-full flex items-center justify-center {{ $iconClass }}">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($icon === 'heroicon-o-check-circle')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        @elseif($icon === 'heroicon-o-x-circle')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        @elseif($icon === 'heroicon-o-exclamation-triangle')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                        @elseif($icon === 'heroicon-o-information-circle')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.828 7l2.586 2.586a2 2 0 001.414.586H15a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9.828a2 2 0 01-.586-1.414L4.828 7z" />
                                        @endif
                                    </svg>
                                </div>
                            </div>

                            <!-- Notification Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $notification['title'] }}
                                    </p>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $notification['time_ago'] }}
                                        </span>
                                        @if(!$notification['read_at'])
                                            <button 
                                                wire:click="markAsRead({{ $notification['id'] }})"
                                                class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400"
                                            >
                                                {{ __('Mark as read') }}
                                            </button>
                                        @endif
                                        <button 
                                            wire:click="deleteNotification({{ $notification['id'] }})"
                                            class="text-xs text-red-600 hover:text-red-700 dark:text-red-400"
                                        >
                                            {{ __('Delete') }}
                                        </button>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                    {{ $notification['message'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="px-4 py-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.828 7l2.586 2.586a2 2 0 001.414.586H15a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9.828a2 2 0 01-.586-1.414L4.828 7z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                        {{ __('admin.notifications.no_notifications') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('You\'re all caught up!') }}
                    </p>
                </div>
            @endif
        </div>

        <!-- Footer -->
        @if(count($notifications) > 0)
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 text-center">
                <a 
                    href="{{ route('filament.admin.resources.notifications.index') }}"
                    class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
                >
                    {{ __('View all notifications') }}
                </a>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('livewire:init', () => {
    // Auto-refresh notifications every 10 seconds
    setInterval(() => {
        if (@this.isOpen) {
            @this.loadNotifications();
        }
    }, 10000);

    // Listen for new notifications via WebSocket or Server-Sent Events
    // This would be implemented with Laravel Echo or similar
    window.addEventListener('new-notification', (event) => {
        @this.handleNewNotification(event.detail);
    });
});
</script>
