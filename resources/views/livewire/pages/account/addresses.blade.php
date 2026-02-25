<div class="space-y-6">
    <header class="border-b border-gray-200 dark:border-white/10 pb-5 mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('frontend.account.addresses.manage_title', 'Manage Addresses') }}</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Manage your shipping and billing addresses.</p>
    </header>

    <div class="space-y-8">
        <div class="flex justify-start">
            <button
                type="button"
                wire:click="$dispatch('openModal', { component: 'modals.account.address-form' })"
                class="inline-flex justify-center items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors w-full sm:w-auto"
            >
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                {{ __('frontend.account.addresses.add_new') }}
            </button>
        </div>

        @if ($addresses->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($addresses as $address)
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm overflow-hidden flex flex-col transition-all duration-200 hover:shadow-md h-full">
                        <!-- Address Header / Summary -->
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/80 border-b border-gray-200 dark:border-white/5 flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-primary-100 dark:bg-primary-900/30 rounded-lg shrink-0 mt-0.5">
                                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $address->full_name ?? ($address->first_name.' '.$address->last_name) }}</h3>
                                    <div class="mt-1 flex flex-wrap gap-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-300">
                                            {{ ucfirst($address->type) }}
                                        </span>
                                        @if ($address->is_default)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/50 dark:text-primary-300 border border-primary-200 dark:border-primary-800/30">
                                                {{ __('messages.Default') ?? 'Default' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Address Details -->
                        <div class="px-6 py-5 flex-grow text-sm text-gray-600 dark:text-gray-400 space-y-1">
                            <p>{{ $address->address_line_1 }}</p>
                            @if ($address->address_line_2)
                                <p>{{ $address->address_line_2 }}</p>
                            @endif
                            <p>{{ $address->postal_code }}, {{ $address->city }}</p>
                            <p>{{ $address->country_code }}</p>
                            @if ($address->phone)
                                <p class="pt-2 flex items-center text-gray-500 dark:text-gray-500">
                                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.896-1.596-5.48-4.08-7.074-6.97l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                    </svg>
                                    {{ $address->phone }}
                                </p>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="px-6 py-4 border-t border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-gray-800/30 flex items-center justify-between">
                            @if(!$address->is_default)
                                <button type="button" wire:click="setDefaultAddress({{ $address->id }})" class="text-xs font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 transition-colors">
                                    Set as Default
                                </button>
                            @else
                                <div></div>
                            @endif
                            
                            <div class="flex items-center gap-4 border-l border-gray-200 dark:border-gray-700 pl-4">
                                <button type="button" wire:click="$dispatch('openModal', { component: 'modals.account.address-form', arguments: { addressId: {{ $address->id }} }})" class="text-sm font-medium text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white transition-colors flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                    </svg>
                                    {{ __('messages.Edit') }}
                                </button>
                                
                                <button type="button" wire:click="removeAddress({{ $address->id }})" wire:confirm="{{ __('frontend.account.addresses.confirm_delete') }}" class="text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                    {{ __('messages.Remove') }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-white/5 border-dashed">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('messages.frontend', 'No addresses found.') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add a new address above to get started.</p>
            </div>
        @endif
    </div>
</div>
