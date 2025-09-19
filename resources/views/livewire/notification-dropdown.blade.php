<div class="relative" x-data="{ open: false }">
    <!-- Notification Bell Button -->
    <button 
        @click="open = !open"
        class="relative p-2 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
        aria-label="{{ __('Notifications') }}"
    >
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-6H4v6zM4 5h6V1H4v4zM15 3h5l-5-5v5z"></path>
        </svg>
        
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 h-5 w-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown Panel -->
    <div 
        x-show="open"
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-cloak
        class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50"
    >
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-900">
                    {{ __('Notifications') }}
                </h3>
                @if($unreadCount > 0)
                    <button 
                        wire:click="markAllAsRead"
                        class="text-xs text-indigo-600 hover:text-indigo-500"
                    >
                        {{ __('Mark all read') }}
                    </button>
                @endif
            </div>
        </div>

        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto">
            @if(count($recentNotifications) > 0)
                @foreach($recentNotifications as $notification)
                    <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition-colors duration-150 {{ $notification['read_at'] ? 'bg-white' : 'bg-blue-50' }}">
                        <div class="flex items-start space-x-3">
                            @if(!$notification['read_at'])
                                <div class="flex-shrink-0 mt-1">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                </div>
                            @endif
                            
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $notification['title'] }}
                                </p>
                                
                                @if($notification['message'])
                                    <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                        {{ $notification['message'] }}
                                    </p>
                                @endif
                                
                                <p class="text-xs text-gray-500 mt-2">
                                    {{ $notification['created_at'] }}
                                </p>
                            </div>
                            
                            @if(!$notification['read_at'])
                                <button 
                                    wire:click="markAsRead('{{ $notification['id'] }}')"
                                    class="flex-shrink-0 text-blue-400 hover:text-blue-600 transition-colors duration-150"
                                    title="{{ __('Mark as read') }}"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <div class="px-4 py-8 text-center">
                    <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-6H4v6zM4 5h6V1H4v4zM15 3h5l-5-5v5z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">
                        {{ __('No notifications') }}
                    </p>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
            <a 
                href="{{ route('notifications.index') }}"
                class="block text-center text-sm text-indigo-600 hover:text-indigo-500 font-medium"
            >
                {{ __('View all notifications') }}
            </a>
        </div>
    </div>
</div>

