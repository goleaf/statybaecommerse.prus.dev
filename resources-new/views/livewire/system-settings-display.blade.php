<div class="system-settings-display">
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                <select wire:model.live="group" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach($groups as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>

                <label class="flex items-center">
                    <input type="checkbox" wire:model.live="showPublicOnly" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-600">{{ __('system_settings.show_public_only') }}</span>
                </label>
            </div>

            <div class="w-full sm:w-auto">
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="{{ __('system_settings.search_settings') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>
    </div>

    @if(empty($settings))
        <div class="text-center py-12">
            <div class="mx-auto h-12 w-12 text-gray-400">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('system_settings.no_settings_found') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ __('system_settings.no_settings_found_description') }}</p>
        </div>
    @else
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                @foreach($settings as $key => $value)
                    <li class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $key }}
                                    </p>
                                    <div class="ml-2 flex-shrink-0 flex">
                                        @if($showPublicOnly)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ __('system_settings.public') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-1">
                                    <p class="text-sm text-gray-500">
                                        {{ $this->formatValue($value) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="mt-4 text-sm text-gray-500 text-center">
            {{ __('system_settings.showing_count', ['count' => count($settings)]) }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('settings-updated', () => {
            // Refresh the component when settings are updated
            Livewire.dispatch('$refresh');
        });
    });
</script>
@endpush
