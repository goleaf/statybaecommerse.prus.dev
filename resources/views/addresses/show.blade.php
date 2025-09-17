@extends('layouts.app')

@section('title', __('translations.address_details'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center mb-8">
            <a href="{{ route('frontend.addresses.index') }}" 
               class="text-blue-600 hover:text-blue-800 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('translations.address_details') }}</h1>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Address Information -->
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('translations.address_information') }}</h2>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">{{ __('translations.address_type') }}</label>
                            <p class="text-gray-900">{{ $address->type_label }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">{{ __('translations.display_name') }}</label>
                            <p class="text-gray-900">{{ $address->display_name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">{{ __('translations.full_address') }}</label>
                            <p class="text-gray-900">{{ $address->full_address }}</p>
                        </div>

                        @if($address->company_name)
                        <div>
                            <label class="block text-sm font-medium text-gray-600">{{ __('translations.company_name') }}</label>
                            <p class="text-gray-900">{{ $address->company_name }}</p>
                        </div>
                        @endif

                        @if($address->company_vat)
                        <div>
                            <label class="block text-sm font-medium text-gray-600">{{ __('translations.company_vat') }}</label>
                            <p class="text-gray-900">{{ $address->company_vat }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Contact Information -->
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('translations.contact_information') }}</h2>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">{{ __('translations.first_name') }}</label>
                            <p class="text-gray-900">{{ $address->first_name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">{{ __('translations.last_name') }}</label>
                            <p class="text-gray-900">{{ $address->last_name }}</p>
                        </div>

                        @if($address->phone)
                        <div>
                            <label class="block text-sm font-medium text-gray-600">{{ __('translations.phone') }}</label>
                            <p class="text-gray-900">{{ $address->phone }}</p>
                        </div>
                        @endif

                        @if($address->email)
                        <div>
                            <label class="block text-sm font-medium text-gray-600">{{ __('translations.email') }}</label>
                            <p class="text-gray-900">{{ $address->email }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            @if($address->apartment || $address->floor || $address->building || $address->landmark || $address->instructions)
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('translations.additional_information') }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($address->apartment)
                    <div>
                        <label class="block text-sm font-medium text-gray-600">{{ __('translations.apartment') }}</label>
                        <p class="text-gray-900">{{ $address->apartment }}</p>
                    </div>
                    @endif

                    @if($address->floor)
                    <div>
                        <label class="block text-sm font-medium text-gray-600">{{ __('translations.floor') }}</label>
                        <p class="text-gray-900">{{ $address->floor }}</p>
                    </div>
                    @endif

                    @if($address->building)
                    <div>
                        <label class="block text-sm font-medium text-gray-600">{{ __('translations.building') }}</label>
                        <p class="text-gray-900">{{ $address->building }}</p>
                    </div>
                    @endif

                    @if($address->landmark)
                    <div>
                        <label class="block text-sm font-medium text-gray-600">{{ __('translations.landmark') }}</label>
                        <p class="text-gray-900">{{ $address->landmark }}</p>
                    </div>
                    @endif

                    @if($address->instructions)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600">{{ __('translations.delivery_instructions') }}</label>
                        <p class="text-gray-900">{{ $address->instructions }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Status Information -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('translations.status_information') }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">{{ __('translations.is_default') }}</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $address->is_default ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $address->is_default ? __('translations.yes') : __('translations.no') }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600">{{ __('translations.is_billing') }}</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $address->is_billing ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $address->is_billing ? __('translations.yes') : __('translations.no') }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600">{{ __('translations.is_shipping') }}</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $address->is_shipping ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $address->is_shipping ? __('translations.yes') : __('translations.no') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 pt-6 border-t border-gray-200 flex flex-col sm:flex-row gap-4">
                <a href="{{ route('frontend.addresses.edit', $address) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    {{ __('translations.edit_address') }}
                </a>

                <a href="{{ route('frontend.addresses.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('translations.back_to_addresses') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
