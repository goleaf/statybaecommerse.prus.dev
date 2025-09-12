@extends('frontend.layouts.app')

@section('title', __('zones.zones'))
@section('description', __('zones.zones_description'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ __('zones.zones') }}</h1>
        <p class="text-gray-600">{{ __('zones.zones_description') }}</p>
    </div>

    @if($zones->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($zones as $zone)
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gray-900">
                                {{ $zone->translated_name }}
                            </h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($zone->type === 'shipping') bg-blue-100 text-blue-800
                                @elseif($zone->type === 'tax') bg-yellow-100 text-yellow-800
                                @elseif($zone->type === 'payment') bg-green-100 text-green-800
                                @elseif($zone->type === 'delivery') bg-purple-100 text-purple-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ __('zones.type_' . $zone->type) }}
                            </span>
                        </div>

                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('zones.code') }}:</span>
                                <span class="font-medium">{{ $zone->code }}</span>
                            </div>
                            
                            @if($zone->currency)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">{{ __('zones.currency') }}:</span>
                                    <span class="font-medium">{{ $zone->currency->name }}</span>
                                </div>
                            @endif

                            @if($zone->tax_rate > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">{{ __('zones.tax_rate') }}:</span>
                                    <span class="font-medium">{{ number_format($zone->tax_rate, 2) }}%</span>
                                </div>
                            @endif

                            @if($zone->shipping_rate > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">{{ __('zones.shipping_rate') }}:</span>
                                    <span class="font-medium">€{{ number_format($zone->shipping_rate, 2) }}</span>
                                </div>
                            @endif

                            @if($zone->free_shipping_threshold)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">{{ __('zones.free_shipping_threshold') }}:</span>
                                    <span class="font-medium text-green-600">€{{ number_format($zone->free_shipping_threshold, 2) }}</span>
                                </div>
                            @endif

                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('zones.countries_count') }}:</span>
                                <span class="font-medium">{{ $zone->countries_count }}</span>
                            </div>
                        </div>

                        @if($zone->translated_description)
                            <p class="text-gray-600 text-sm mb-4">{{ Str::limit($zone->translated_description, 100) }}</p>
                        @endif

                        <div class="flex items-center justify-between">
                            <div class="flex space-x-2">
                                @if($zone->is_default)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ __('zones.is_default') }}
                                    </span>
                                @endif
                                
                                @if($zone->is_active)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ __('zones.is_active') }}
                                    </span>
                                @endif
                            </div>

                            <a href="{{ route('zones.show', $zone) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                {{ __('zones.view_details') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $zones->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <div class="mx-auto h-12 w-12 text-gray-400">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
            </div>
            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('zones.no_zones_found') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ __('zones.no_zones_available') }}</p>
        </div>
    @endif
</div>
@endsection
