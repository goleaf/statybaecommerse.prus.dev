<div class="space-y-6">
    <header class="flex items-center justify-between border-b border-gray-200 pb-5">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('frontend.account.navigation.notifications') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('frontend.account.notifications_description') }}</p>
        </div>
        
        @if (!empty($notifications))
            <div class="flex space-x-2">
                <button 
                    wire:click="markAllAsRead" 
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    {{ __('frontend.account.notifications.mark_all_read') }}
                </button>
                
                <button 
                    wire:click="deleteAllNotifications" 
                    wire:confirm="{{ __('notifications.confirmations.delete_all') }}"
                    class="inline-flex items-center rounded-md border border-red-300 bg-white px-3 py-2 text-sm font-medium leading-4 text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    {{ __('frontend.account.notifications.delete_all') }}
                </button>
            </div>
        @endif
    </header>

    <!-- Filters -->
    @if (!empty($notifications))
        <div class="rounded-lg border border-gray-200 p-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <input 
                            wire:model.live="showUnreadOnly" 
                            id="unread-only" 
                            type="checkbox" 
                            class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                        >
                        <label for="unread-only" class="ml-2 text-sm text-gray-700">
                            {{ __('frontend.account.notifications.unread_only') }}
                        </label>
                    </div>
                </div>
                
                <div class="flex items-center space-x-2">
                    <label for="filter" class="text-sm font-medium text-gray-700">{{ __('frontend.account.notifications.filter_label') }}:</label>
                    <select 
                        wire:model.live="filter" 
                        id="filter" 
                        class="block w-40 rounded-md border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="all">{{ __('frontend.account.notifications.type_all') }}</option>
                        <option value="order">{{ __('frontend.account.notifications.type_order') }}</option>
                        <option value="product">{{ __('frontend.account.notifications.type_product') }}</option>
                        <option value="user">{{ __('frontend.account.notifications.type_user') }}</option>
                        <option value="system">{{ __('frontend.account.notifications.type_system') }}</option>
                        <option value="payment">{{ __('frontend.account.notifications.type_payment') }}</option>
                        <option value="shipping">{{ __('frontend.account.notifications.type_shipping') }}</option>
                        <option value="review">{{ __('frontend.account.notifications.type_review') }}</option>
                        <option value="promotion">{{ __('frontend.account.notifications.type_promotion') }}</option>
                        <option value="newsletter">{{ __('frontend.account.notifications.type_newsletter') }}</option>
                        <option value="support">{{ __('frontend.account.notifications.type_support') }}</option>
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
            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('frontend.account.notifications.empty_title') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ __('frontend.account.notifications.empty_description') }}</p>
        </div>
    @else
        <div class="overflow-hidden rounded-lg border border-gray-200">
            <ul class="divide-y divide-gray-200">
                @foreach ($notifications as $notification)
                    <li class="relative {{ !$notification['read_at'] ? 'bg-gray-50' : '' }}">
                        <div class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <!-- Notification Icon -->
                                    <div class="flex-shrink-0">
                                        @php
                                            $iconClass = 'text-gray-500';
                                            
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
                                        
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100">
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
                                                <span class="inline-flex items-center rounded-full bg-gray-200 px-2 py-0.5 text-xs font-medium text-gray-800">
                                                    {{ __('frontend.account.notifications.unread_badge') }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-500 truncate">
                                            {{ $notification['message'] }}
                                        </p>
                                        <div class="mt-1 flex items-center space-x-2 text-xs text-gray-400">
                                            <span>{{ $notification['time_ago'] }}</span>
                                            <span>•</span>
                                            <span class="capitalize">{{ __(sprintf('notifications.types.%s', $notification['type'])) }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex items-center space-x-2">
                                    @if (!$notification['read_at'])
                                        <button 
                                            wire:click="markAsRead('{{ $notification['id'] }}')"
                                            class="text-gray-400 hover:text-gray-600"
                                            title="{{ __('frontend.account.notifications.mark_read') }}"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                    @endif
                                    
                                    <button 
                                        wire:click="deleteNotification('{{ $notification['id'] }}')"
                                        wire:confirm="{{ __('notifications.confirmations.delete_one') }}"
                                        class="text-gray-400 hover:text-red-600"
                                        title="{{ __('frontend.account.notifications.delete_one') }}"
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
