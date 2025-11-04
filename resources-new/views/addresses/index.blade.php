@extends('components.layouts.base')

@section('title', __('translations.addresses'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('translations.addresses') }}</h1>
        <a href="{{ route('frontend.addresses.create') }}" 
           class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
            {{ __('translations.add_new_address') }}
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if($addresses->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($addresses as $address)
                <div class="bg-white rounded-lg shadow-md p-6 border {{ $address->is_default ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-200' }}">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center space-x-2">
                            @if($address->is_default)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    {{ __('translations.default') }}
                                </span>
                            @endif
                            
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                @if($address->type->value === 'shipping') bg-blue-100 text-blue-800
                                @elseif($address->type->value === 'billing') bg-green-100 text-green-800
                                @elseif($address->type->value === 'home') bg-purple-100 text-purple-800
                                @elseif($address->type->value === 'work') bg-orange-100 text-orange-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $address->type_label }}
                            </span>
                        </div>
                        
                        <div class="flex space-x-2">
                            <a href="{{ route('frontend.addresses.edit', $address) }}" 
                               class="text-blue-600 hover:text-blue-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            
                            <form action="{{ route('frontend.addresses.destroy', $address) }}" 
                                  method="POST" class="inline"
                                  onsubmit="return confirm('{{ __('translations.are_you_sure') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <h3 class="font-semibold text-gray-900">{{ $address->display_name }}</h3>
                        
                        @if($address->company_name)
                            <p class="text-sm text-gray-600">{{ $address->company_name }}</p>
                        @endif
                        
                        <div class="text-sm text-gray-600">
                            <p>{{ $address->address_line_1 }}</p>
                            @if($address->address_line_2)
                                <p>{{ $address->address_line_2 }}</p>
                            @endif
                            @if($address->apartment)
                                <p>{{ $address->apartment }}</p>
                            @endif
                            <p>{{ $address->city }}, {{ $address->postal_code }}</p>
                            @if($address->state)
                                <p>{{ $address->state }}</p>
                            @endif
                            <p>{{ $address->country->name ?? $address->country_code }}</p>
                        </div>
                        
                        @if($address->phone)
                            <p class="text-sm text-gray-600">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                {{ $address->phone }}
                            </p>
                        @endif
                    </div>

                    <div class="mt-4 flex space-x-2">
                        @if(!$address->is_default)
                            <form action="{{ route('frontend.addresses.set-default', $address) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="text-xs bg-yellow-100 hover:bg-yellow-200 text-yellow-800 px-2 py-1 rounded">
                                    {{ __('translations.set_as_default') }}
                                </button>
                            </form>
                        @endif
                        
                        <form action="{{ route('frontend.addresses.duplicate', $address) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-800 px-2 py-1 rounded">
                                {{ __('translations.duplicate') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('translations.no_addresses') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ __('translations.no_addresses_description') }}</p>
            <div class="mt-6">
                <a href="{{ route('frontend.addresses.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    {{ __('translations.add_new_address') }}
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
