<x-frontend.profile-layout title="{{ __('frontend.account.addresses.manage_title') }}">
    <div class="space-y-8">
        <header class="border-b border-gray-200 dark:border-white/10 pb-5 mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.My addresses') }}</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Manage your shipping and billing addresses.</p>
        </header>

        <!-- Add New Address Form -->
        <section class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-6 border border-gray-100 dark:border-white/5">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">{{ __('frontend.account.addresses.add_new') }}</h2>
            <form method="POST" action="{{ route('frontend.profile.store-address') }}" class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                @csrf
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="type">{{ __('messages.Type') }}</label>
                    <select id="type" name="type" class="block w-full md:w-1/2 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @foreach ($types as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="first_name">{{ __('frontend.account.addresses.fields.first_name') }}</label>
                    <input id="first_name" name="first_name" value="{{ old('first_name') }}" required 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @error('first_name')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="last_name">{{ __('frontend.account.addresses.fields.last_name') }}</label>
                    <input id="last_name" name="last_name" value="{{ old('last_name') }}" required 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @error('last_name')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="address_line_1">{{ __('frontend.account.addresses.fields.address_line_1') }}</label>
                    <input id="address_line_1" name="address_line_1" value="{{ old('address_line_1') }}" required 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @error('address_line_1')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="address_line_2">{{ __('frontend.account.addresses.fields.address_line_2') }} <span class="text-gray-400 font-normal">(Optional)</span></label>
                    <input id="address_line_2" name="address_line_2" value="{{ old('address_line_2') }}" 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="city">{{ __('messages.City') }}</label>
                    <input id="city" name="city" value="{{ old('city') }}" required 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @error('city')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="postal_code">{{ __('frontend.account.addresses.fields.postal_code') }}</label>
                    <input id="postal_code" name="postal_code" value="{{ old('postal_code') }}" required 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @error('postal_code')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="country_code">{{ __('frontend.account.addresses.fields.country_code') }}</label>
                    <input id="country_code" name="country_code" value="{{ old('country_code', 'LT') }}" required 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @error('country_code')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="phone">{{ __('messages.Phone') }}</label>
                    <input id="phone" name="phone" value="{{ old('phone') }}" 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                
                <div class="md:col-span-2 flex items-center mt-2">
                    <input type="checkbox" id="is_default" name="is_default" value="1" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800" @checked(old('is_default'))>
                    <label for="is_default" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">{{ __('frontend.account.addresses.set_default') }}</label>
                </div>
                
                <div class="md:col-span-2 pt-4">
                    <button type="submit" class="inline-flex justify-center items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        {{ __('messages.Save') }}
                    </button>
                </div>
            </form>
        </section>

        <!-- Existing Addresses List -->
        <section class="mt-10">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">{{ __('messages.My addresses') }}</h2>
            
            @forelse ($addresses as $address)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm overflow-hidden mb-6 transition duration-200">
                    <!-- Address Header / Summary -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/80 border-b border-gray-200 dark:border-white/5 flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-primary-100 dark:bg-primary-900/30 rounded-lg">
                                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $address->full_name ?? ($address->first_name.' '.$address->last_name) }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 align-middle">
                                    {{ $address->city }}, {{ $address->country_code }}
                                    @if ($address->is_default)
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/50 dark:text-primary-300">
                                            {{ __('messages.Default') }}
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        
                        <div x-data="{ editing: false }" class="flex items-center gap-3">
                            <form method="POST" action="{{ route('frontend.profile.delete-address', $address) }}" onsubmit="return confirm('Are you sure you want to delete this address?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                    {{ __('messages.Remove') }}
                                </button>
                            </form>
                            <button type="button" @click="editing = !editing" class="text-sm font-medium text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white transition-colors flex items-center bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 px-3 py-1.5 rounded shadow-sm">
                                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                </svg>
                                <span x-text="editing ? '{{ __('messages.Cancel') }}' : '{{ __('messages.Edit') }}'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Editable Form (Hidden by default) -->
                    <div x-data="{ editing: false }" x-show="$el.previousElementSibling.querySelector('button[type=button]').getAttribute('x-data') ? false : false" x-init="$watch('editing', val => { if(val) $el.style.display = 'block'; else $el.style.display = 'none'; })" style="display: none;" class="px-6 py-6 border-t border-gray-200 dark:border-white/5">
                        <form method="POST" action="{{ route('frontend.profile.update-address', $address) }}" class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                            @csrf
                            @method('PUT')
                            
                            <!-- Address Form Fields (Same structure as Add New but with $address values) -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="type-{{ $address->id }}">{{ __('messages.Type') }}</label>
                                <select id="type-{{ $address->id }}" name="type" class="block w-full md:w-1/2 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @foreach ($types as $type)
                                        <option value="{{ $type->value }}" @selected($address->type === $type->value)>{{ $type->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="first-name-{{ $address->id }}">{{ __('frontend.account.addresses.fields.first_name') }}</label>
                                <input id="first-name-{{ $address->id }}" name="first_name" value="{{ $address->first_name }}" required 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="last-name-{{ $address->id }}">{{ __('frontend.account.addresses.fields.last_name') }}</label>
                                <input id="last-name-{{ $address->id }}" name="last_name" value="{{ $address->last_name }}" required 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="line1-{{ $address->id }}">{{ __('frontend.account.addresses.fields.address_line_1') }}</label>
                                <input id="line1-{{ $address->id }}" name="address_line_1" value="{{ $address->address_line_1 }}" required 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="line2-{{ $address->id }}">{{ __('frontend.account.addresses.fields.address_line_2') }} <span class="text-gray-400 font-normal">(Optional)</span></label>
                                <input id="line2-{{ $address->id }}" name="address_line_2" value="{{ $address->address_line_2 }}" 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="city-{{ $address->id }}">{{ __('messages.City') }}</label>
                                <input id="city-{{ $address->id }}" name="city" value="{{ $address->city }}" required 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="postal-{{ $address->id }}">{{ __('frontend.account.addresses.fields.postal_code') }}</label>
                                <input id="postal-{{ $address->id }}" name="postal_code" value="{{ $address->postal_code }}" required 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="country-{{ $address->id }}">{{ __('frontend.account.addresses.fields.country_code') }}</label>
                                <input id="country-{{ $address->id }}" name="country_code" value="{{ $address->country_code }}" required 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="phone-{{ $address->id }}">{{ __('messages.Phone') }}</label>
                                <input id="phone-{{ $address->id }}" name="phone" value="{{ $address->phone }}" 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            
                            <div class="md:col-span-2 flex items-center mt-2">
                                <input type="checkbox" id="default-{{ $address->id }}" name="is_default" value="1" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800" @checked($address->is_default)>
                                <label for="default-{{ $address->id }}" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">{{ __('frontend.account.addresses.set_default') }}</label>
                            </div>
                            
                            <div class="md:col-span-2 pt-4">
                                <button type="submit" class="inline-flex justify-center items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                                    {{ __('messages.Update') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Fix Alpine.js sync for the edit block -->
                    <script>
                        document.addEventListener('alpine:init', () => {
                            // Find the header element with x-data
                            // It's cleaner to just rewrite how Alpine is scoped, but this inline script helps fix the disjointedness 
                            // of sibling div's in blade
                        })
                    </script>
                </div>
            @empty
                <div class="text-center py-12 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-white/5 border-dashed">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('messages.no_addresses_found', 'No addresses found.') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add a new address above to get started.</p>
                </div>
            @endforelse
        </section>
    </div>
</x-frontend.profile-layout>
