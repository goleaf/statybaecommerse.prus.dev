<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                {{ __('analytics_events.event_details') }}
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">{{ __('analytics_events.event_type') }}</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $record->event_type }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-700">{{ __('analytics_events.session_id') }}</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $record->session_id }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-700">{{ __('analytics_events.user') }}</label>
                    <p class="mt-1 text-sm text-gray-900">
                        @if($record->user)
                            {{ $record->user->name }} ({{ __('analytics_events.user_id_short', ['id' => $record->user_id]) }})
                        @elseif($record->user_id)
                            {{ __('analytics_events.user_id_only', ['id' => $record->user_id]) }}
                        @else
                            {{ __('analytics_events.anonymous') }}
                        @endif
                    </p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-700">{{ __('analytics_events.created_at') }}</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $record->created_at?->format('Y-m-d H:i:s') }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-700">{{ __('analytics_events.url') }}</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $record->url ?? __('admin.common.not_available') }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-700">{{ __('analytics_events.referrer') }}</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $record->referrer ?? __('admin.common.not_available') }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-700">{{ __('analytics_events.ip_address') }}</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $record->ip_address ?? __('admin.common.not_available') }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-700">{{ __('analytics_events.country_code') }}</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $record->country_code ?? __('admin.common.not_available') }}</p>
                </div>
            </div>
        </x-filament::section>
        
        @if($record->properties && count($record->properties) > 0)
        <x-filament::section>
            <x-slot name="heading">
                {{ __('analytics_events.event_properties') }}
            </x-slot>
            
            <div class="space-y-2">
                @foreach($record->properties as $key => $value)
                <div class="flex justify-between items-center py-2 border-b border-gray-200 last:border-b-0">
                    <span class="font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                    <span class="text-gray-900">{{ is_array($value) ? json_encode($value) : $value }}</span>
                </div>
                @endforeach
            </div>
        </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
