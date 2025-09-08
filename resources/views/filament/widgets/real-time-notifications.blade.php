<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-bell class="w-5 h-5" />
                {{ __('admin.widgets.notifications') }}
            </div>
        </x-slot>

        <div class="space-y-3">
            @forelse($this->getNotifications() as $notification)
                <div class="flex items-start gap-3 p-3 rounded-lg border @if($notification['type'] === 'danger') border-red-200 bg-red-50 @elseif($notification['type'] === 'warning') border-yellow-200 bg-yellow-50 @elseif($notification['type'] === 'success') border-green-200 bg-green-50 @else border-blue-200 bg-blue-50 @endif">
                    <div class="flex-shrink-0">
                        <x-dynamic-component 
                            :component="$notification['icon']" 
                            class="w-5 h-5 @if($notification['type'] === 'danger') text-red-500 @elseif($notification['type'] === 'warning') text-yellow-500 @elseif($notification['type'] === 'success') text-green-500 @else text-blue-500 @endif"
                        />
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-gray-900">{{ $notification['title'] }}</div>
                        <div class="text-sm text-gray-600">{{ $notification['message'] }}</div>
                        <div class="text-xs text-gray-500 mt-1">{{ $notification['time'] }}</div>
                    </div>
                    
                    @if(isset($notification['action_url']))
                        <div class="flex-shrink-0">
                            <a href="{{ $notification['action_url'] }}" 
                               class="text-sm font-medium @if($notification['type'] === 'danger') text-red-600 hover:text-red-700 @elseif($notification['type'] === 'warning') text-yellow-600 hover:text-yellow-700 @elseif($notification['type'] === 'success') text-green-600 hover:text-green-700 @else text-blue-600 hover:text-blue-700 @endif">
                                {{ __('admin.actions.view') }}
                            </a>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-6 text-gray-500">
                    <x-heroicon-o-check-circle class="w-8 h-8 mx-auto mb-2 text-green-500" />
                    <div class="font-medium">{{ __('admin.notifications.all_clear') }}</div>
                    <div class="text-sm">{{ __('admin.notifications.no_urgent_notifications') }}</div>
                </div>
            @endforelse
        </div>

        @if($this->getNotifications()->isNotEmpty())
            <div class="mt-4 text-center">
                <button 
                    wire:click="$refresh" 
                    class="text-sm text-gray-500 hover:text-gray-700 font-medium"
                >
                    <x-heroicon-o-arrow-path class="w-4 h-4 inline mr-1" />
                    {{ __('admin.actions.refresh') }}
                </button>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>


