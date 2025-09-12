<?php
use function Livewire\Volt\{layout, title};

layout('components.layouts.templates.frontend');
title(__('users.addresses'));

?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        {{ __('nav.home') }}
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('users.dashboard') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">{{ __('users.dashboard') }}</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('users.addresses') }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('users.my_addresses') }}</h1>
                    <p class="mt-2 text-gray-600">{{ __('users.addresses_description') }}</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <button 
                        type="button"
                        onclick="openAddressModal()"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        {{ __('users.add_new_address') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Addresses List -->
        @if($addresses->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($addresses as $address)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 relative {{ $address->is_default ? 'ring-2 ring-blue-500' : '' }}">
                        <!-- Default Badge -->
                        @if($address->is_default)
                            <div class="absolute top-4 right-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ __('users.default') }}
                                </span>
                            </div>
                        @endif

                        <!-- Address Type -->
                        <div class="mb-4">
                            <h3 class="text-lg font-medium text-gray-900">{{ $address->type_text }}</h3>
                            @if($address->label)
                                <p class="text-sm text-gray-600">{{ $address->label }}</p>
                            @endif
                        </div>

                        <!-- Address Details -->
                        <div class="space-y-2">
                            <p class="text-sm text-gray-900">
                                {{ $address->first_name }} {{ $address->last_name }}
                            </p>
                            @if($address->company)
                                <p class="text-sm text-gray-600">{{ $address->company }}</p>
                            @endif
                            <p class="text-sm text-gray-600">{{ $address->address_line_1 }}</p>
                            @if($address->address_line_2)
                                <p class="text-sm text-gray-600">{{ $address->address_line_2 }}</p>
                            @endif
                            <p class="text-sm text-gray-600">
                                {{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}
                            </p>
                            <p class="text-sm text-gray-600">{{ $address->country_text }}</p>
                            @if($address->phone_number)
                                <p class="text-sm text-gray-600">{{ $address->phone_number }}</p>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="mt-6 flex space-x-3">
                            <button 
                                type="button"
                                onclick="editAddress({{ $address->id }})"
                                class="flex-1 inline-flex justify-center items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                {{ __('users.edit') }}
                            </button>
                            
                            @if(!$address->is_default)
                                <form method="POST" action="{{ route('users.addresses.set-default', $address) }}" class="flex-1">
                                    @csrf
                                    @method('PUT')
                                    <button 
                                        type="submit"
                                        class="w-full inline-flex justify-center items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        {{ __('users.set_default') }}
                                    </button>
                                </form>
                            @endif
                            
                            <form method="POST" action="{{ route('users.addresses.destroy', $address) }}" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button 
                                    type="submit"
                                    onclick="return confirm('{{ __("users.confirm_delete_address") }}')"
                                    class="w-full inline-flex justify-center items-center px-3 py-2 border border-red-300 text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                >
                                    {{ __('users.delete') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('users.no_addresses') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('users.no_addresses_description') }}</p>
                <div class="mt-6">
                    <button 
                        type="button"
                        onclick="openAddressModal()"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                    >
                        {{ __('users.add_first_address') }}
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Address Modal -->
<div id="address-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900" id="modal-title">{{ __('users.add_new_address') }}</h3>
                <button 
                    type="button"
                    onclick="closeAddressModal()"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="address-form" method="POST" action="{{ route('users.addresses.store') }}" class="mt-6 space-y-6">
                @csrf
                <input type="hidden" id="address-id" name="address_id">
                <input type="hidden" id="form-method" name="_method" value="POST">

                <!-- Address Type and Label -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.address_type') }}</label>
                        <select 
                            id="type" 
                            name="type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required
                        >
                            <option value="">{{ __('users.select_type') }}</option>
                            <option value="home">{{ __('users.home') }}</option>
                            <option value="work">{{ __('users.work') }}</option>
                            <option value="billing">{{ __('users.billing') }}</option>
                            <option value="shipping">{{ __('users.shipping') }}</option>
                            <option value="other">{{ __('users.other') }}</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="label" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.label') }} ({{ __('users.optional') }})</label>
                        <input 
                            type="text" 
                            id="label" 
                            name="label"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="{{ __('users.address_label_placeholder') }}"
                        >
                        @error('label')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Name Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.first_name') }}</label>
                        <input 
                            type="text" 
                            id="first_name" 
                            name="first_name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required
                        >
                        @error('first_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.last_name') }}</label>
                        <input 
                            type="text" 
                            id="last_name" 
                            name="last_name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required
                        >
                        @error('last_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Company -->
                <div>
                    <label for="company" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.company') }} ({{ __('users.optional') }})</label>
                    <input 
                        type="text" 
                        id="company" 
                        name="company"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                    @error('company')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address Lines -->
                <div class="space-y-4">
                    <div>
                        <label for="address_line_1" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.address_line_1') }}</label>
                        <input 
                            type="text" 
                            id="address_line_1" 
                            name="address_line_1"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required
                        >
                        @error('address_line_1')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="address_line_2" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.address_line_2') }} ({{ __('users.optional') }})</label>
                        <input 
                            type="text" 
                            id="address_line_2" 
                            name="address_line_2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('address_line_2')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- City, State, Postal Code -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.city') }}</label>
                        <input 
                            type="text" 
                            id="city" 
                            name="city"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required
                        >
                        @error('city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="state" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.state') }}</label>
                        <input 
                            type="text" 
                            id="state" 
                            name="state"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required
                        >
                        @error('state')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.postal_code') }}</label>
                        <input 
                            type="text" 
                            id="postal_code" 
                            name="postal_code"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required
                        >
                        @error('postal_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Country and Phone -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.country') }}</label>
                        <select 
                            id="country" 
                            name="country"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required
                        >
                            <option value="">{{ __('users.select_country') }}</option>
                            <option value="LT">{{ __('users.lithuania') }}</option>
                            <option value="LV">{{ __('users.latvia') }}</option>
                            <option value="EE">{{ __('users.estonia') }}</option>
                            <option value="PL">{{ __('users.poland') }}</option>
                            <option value="DE">{{ __('users.germany') }}</option>
                            <option value="FR">{{ __('users.france') }}</option>
                            <option value="GB">{{ __('users.united_kingdom') }}</option>
                            <option value="US">{{ __('users.united_states') }}</option>
                            <option value="CA">{{ __('users.canada') }}</option>
                            <option value="AU">{{ __('users.australia') }}</option>
                        </select>
                        @error('country')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.phone_number') }} ({{ __('users.optional') }})</label>
                        <input 
                            type="tel" 
                            id="phone_number" 
                            name="phone_number"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('phone_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Default Address -->
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="is_default" 
                        name="is_default"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    >
                    <label for="is_default" class="ml-2 block text-sm text-gray-900">
                        {{ __('users.set_as_default_address') }}
                    </label>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <button 
                        type="button"
                        onclick="closeAddressModal()"
                        class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        {{ __('users.cancel') }}
                    </button>
                    <button 
                        type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        {{ __('users.save_address') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Address Modal Functions
function openAddressModal(addressId = null) {
    const modal = document.getElementById('address-modal');
    const form = document.getElementById('address-form');
    const title = document.getElementById('modal-title');
    const addressIdInput = document.getElementById('address-id');
    const methodInput = document.getElementById('form-method');
    
    if (addressId) {
        // Edit mode
        title.textContent = '{{ __("users.edit_address") }}';
        form.action = '{{ route("users.addresses.update", ":id") }}'.replace(':id', addressId);
        methodInput.value = 'PUT';
        addressIdInput.value = addressId;
        
        // Load address data
        loadAddressData(addressId);
    } else {
        // Create mode
        title.textContent = '{{ __("users.add_new_address") }}';
        form.action = '{{ route("users.addresses.store") }}';
        methodInput.value = 'POST';
        addressIdInput.value = '';
        form.reset();
    }
    
    modal.classList.remove('hidden');
}

function closeAddressModal() {
    const modal = document.getElementById('address-modal');
    modal.classList.add('hidden');
}

function editAddress(addressId) {
    openAddressModal(addressId);
}

function loadAddressData(addressId) {
    // This would typically fetch data via AJAX
    // For now, we'll use the address data from the page
    const addresses = @json($addresses);
    const address = addresses.find(addr => addr.id === addressId);
    
    if (address) {
        document.getElementById('type').value = address.type;
        document.getElementById('label').value = address.label || '';
        document.getElementById('first_name').value = address.first_name;
        document.getElementById('last_name').value = address.last_name;
        document.getElementById('company').value = address.company || '';
        document.getElementById('address_line_1').value = address.address_line_1;
        document.getElementById('address_line_2').value = address.address_line_2 || '';
        document.getElementById('city').value = address.city;
        document.getElementById('state').value = address.state;
        document.getElementById('postal_code').value = address.postal_code;
        document.getElementById('country').value = address.country;
        document.getElementById('phone_number').value = address.phone_number || '';
        document.getElementById('is_default').checked = address.is_default;
    }
}

// Close modal when clicking outside
document.getElementById('address-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAddressModal();
    }
});
</script>
