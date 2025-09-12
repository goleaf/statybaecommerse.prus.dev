<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                {{ __('Event Details') }}
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">{{ __('Event Type') }}</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $record->event_type }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-700">{{ __('Session ID') }}</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $record->session_id }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-700">{{ __('User') }}</label>
                    <p class="mt-1 text-sm text-gray-900">
                        @if($record->user)
                            {{ $record->user->name }} (ID: {{ $record->user_id }})
                        @elseif($record->user_id)
                            User ID: {{ $record->user_id }}
                        @else
                            Anonymous
                        @endif
                    </p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-700">{{ __('Created At') }}</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $record->created_at?->format('Y-m-d H:i:s') }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-700">{{ __('URL') }}</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $record->url ?? 'N/A' }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-700">{{ __('Referrer') }}</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $record->referrer ?? 'N/A' }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-700">{{ __('IP Address') }}</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $record->ip_address ?? 'N/A' }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-700">{{ __('Country Code') }}</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $record->country_code ?? 'N/A' }}</p>
                </div>
            </div>
        </x-filament::section>
        
        @if($record->properties && count($record->properties) > 0)
        <x-filament::section>
            <x-slot name="heading">
                {{ __('Event Properties') }}
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
