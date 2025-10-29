<div class="notification-center">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <h2 class="text-xl font-semibold text-gray-900">
                    {{ __('Notifications') }}
                </h2>
                @if($unreadCount > 0)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        {{ $unreadCount }} {{ __('unread') }}
                    </span>
                @endif
            </div>
            
            <div class="flex items-center space-x-2">
                @if($unreadCount > 0)
                    <button 
                        wire:click="markAllAsRead"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('Mark All Read') }}
                    </button>
                @endif
                
                <button 
                    wire:click="clearAllNotifications"
                    wire:confirm="{{ __('Are you sure you want to clear all notifications? This action cannot be undone.') }}"
                    class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    {{ __('Clear All') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
        <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-2">
                <label for="filter" class="text-sm font-medium text-gray-700">
                    {{ __('Filter by type:') }}
                </label>
                <select 
                    wire:model.live="filter" 
                    id="filter"
                    class="block w-48 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                >
                    <option value="all">{{ __('All Types') }}</option>
                    @foreach($notificationTypes as $type => $label)
                        <option value="{{ $type }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            
            <label class="flex items-center">
                <input 
                    type="checkbox" 
                    wire:model.live="showUnreadOnly"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                >
                <span class="ml-2 text-sm text-gray-700">{{ __('Show unread only') }}</span>
            </label>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-white">
        @if($notifications->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($notifications as $notification)
                    <div class="px-6 py-4 hover:bg-gray-50 transition-colors duration-150 {{ $notification->read_at ? 'bg-white' : 'bg-blue-50' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3">
                                    @if(!$notification->read_at)
                                        <div class="flex-shrink-0">
                                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                        </div>
                                    @endif
                                    
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ class_basename($notification->type) }}
                                        </p>
                                        
                                        @if(isset($notification->data['message']))
                                            <p class="text-sm text-gray-600 mt-1">
                                                {{ $notification->data['message'] }}
                                            </p>
                                        @endif
                                        
                                        @if(isset($notification->data['title']))
                                            <p class="text-sm font-semibold text-gray-900 mt-1">
                                                {{ $notification->data['title'] }}
                                            </p>
                                        @endif
                                        
                                        <p class="text-xs text-gray-500 mt-2">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-2 ml-4">
                                @if($notification->read_at)
                                    <button 
                                        wire:click="markAsUnread('{{ $notification->id }}')"
                                        class="text-gray-400 hover:text-gray-600 transition-colors duration-150"
                                        title="{{ __('Mark as unread') }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                @else
                                    <button 
                                        wire:click="markAsRead('{{ $notification->id }}')"
                                        class="text-blue-400 hover:text-blue-600 transition-colors duration-150"
                                        title="{{ __('Mark as read') }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                @endif
                                
                                <button 
                                    wire:click="deleteNotification('{{ $notification->id }}')"
                                    wire:confirm="{{ __('Are you sure you want to delete this notification?') }}"
                                    class="text-red-400 hover:text-red-600 transition-colors duration-150"
                                    title="{{ __('Delete notification') }}"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $notifications->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-6H4v6zM4 5h6V1H4v4zM15 3h5l-5-5v5z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No notifications') }}</h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('You don\'t have any notifications yet.') }}
                </p>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('notificationRead', (notificationId) => {
        // Update UI to show notification as read
        const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
        if (notificationElement) {
            notificationElement.classList.remove('bg-blue-50');
            notificationElement.classList.add('bg-white');
        }
    });
    
    Livewire.on('notificationUnread', (notificationId) => {
        // Update UI to show notification as unread
        const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
        if (notificationElement) {
            notificationElement.classList.remove('bg-white');
            notificationElement.classList.add('bg-blue-50');
        }
    });
    
    Livewire.on('allNotificationsRead', () => {
        // Update UI to show all notifications as read
        document.querySelectorAll('.bg-blue-50').forEach(element => {
            element.classList.remove('bg-blue-50');
            element.classList.add('bg-white');
        });
    });
    
    Livewire.on('notificationDeleted', (notificationId) => {
        // Remove notification from UI
        const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
        if (notificationElement) {
            notificationElement.remove();
        }
    });
    
    Livewire.on('allNotificationsCleared', () => {
        // Clear all notifications from UI
        document.querySelectorAll('[data-notification-id]').forEach(element => {
            element.remove();
        });
    });
});
</script>
