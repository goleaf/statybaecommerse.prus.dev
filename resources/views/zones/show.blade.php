@extends('layouts.app')

@section('title', $zone->translated_name)
@section('description', $zone->translated_description)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-8">
            <ol class="flex items-center space-x-2 text-sm text-gray-500">
                <li><a href="{{ localized_route('home') }}" class="hover:text-gray-700">{{ __('frontend.home') }}</a></li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <a href="{{ route('zones.index') }}" class="hover:text-gray-700">{{ __('zones.zones') }}</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-gray-900">{{ $zone->translated_name }}</span>
                </li>
            </ol>
        </nav>

        <!-- Zone Header -->
        <div class="bg-white rounded-lg shadow-md mb-8">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-3xl font-bold text-gray-900">{{ $zone->translated_name }}</h1>
                    <div class="flex space-x-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($zone->type === 'shipping') bg-blue-100 text-blue-800
                            @elseif($zone->type === 'tax') bg-yellow-100 text-yellow-800
                            @elseif($zone->type === 'payment') bg-green-100 text-green-800
                            @elseif($zone->type === 'delivery') bg-purple-100 text-purple-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ __('zones.type_' . $zone->type) }}
                        </span>
                        
                        @if($zone->is_default)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                {{ __('zones.is_default') }}
                            </span>
                        @endif
                        
                        @if($zone->is_active)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                {{ __('zones.is_active') }}
                            </span>
                        @endif
                    </div>
                </div>

                @if($zone->translated_description)
                    <p class="text-gray-600 text-lg mb-6">{{ $zone->translated_description }}</p>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('zones.code') }}</h3>
                        <p class="text-lg font-semibold text-gray-900">{{ $zone->code }}</p>
                    </div>

                    @if($zone->currency)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('zones.currency') }}</h3>
                            <p class="text-lg font-semibold text-gray-900">{{ $zone->currency->name }} ({{ $zone->currency->code }})</p>
                        </div>
                    @endif

                    @if($zone->tax_rate > 0)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('zones.tax_rate') }}</h3>
                            <p class="text-lg font-semibold text-gray-900">{{ number_format($zone->tax_rate, 2) }}%</p>
                        </div>
                    @endif

                    @if($zone->shipping_rate > 0)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('zones.shipping_rate') }}</h3>
                            <p class="text-lg font-semibold text-gray-900">€{{ number_format($zone->shipping_rate, 2) }}</p>
                        </div>
                    @endif

                    @if($zone->free_shipping_threshold)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('zones.free_shipping_threshold') }}</h3>
                            <p class="text-lg font-semibold text-green-600">€{{ number_format($zone->free_shipping_threshold, 2) }}</p>
                        </div>
                    @endif

                    @if($zone->min_order_amount)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('zones.min_order_amount') }}</h3>
                            <p class="text-lg font-semibold text-gray-900">€{{ number_format($zone->min_order_amount, 2) }}</p>
                        </div>
                    @endif

                    @if($zone->max_order_amount)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('zones.max_order_amount') }}</h3>
                            <p class="text-lg font-semibold text-gray-900">€{{ number_format($zone->max_order_amount, 2) }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Countries Section -->
        @if($zone->countries->count() > 0)
            <div class="bg-white rounded-lg shadow-md mb-8">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('zones.countries') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($zone->countries as $country)
                            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                <span class="text-2xl">{{ $country->flag ?? '🌍' }}</span>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $country->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $country->code }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Shipping Calculator -->
        <div class="bg-white rounded-lg shadow-md mb-8">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('zones.shipping_calculator') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="order-amount" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('zones.order_amount') }}
                        </label>
                        <input type="number" id="order-amount" name="order_amount" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="0.00">
                    </div>
                    <div>
                        <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('zones.weight') }} (kg)
                        </label>
                        <input type="number" id="weight" name="weight" step="0.1" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="0.0">
                    </div>
                </div>
                <button id="calculate-shipping" 
                        class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    {{ __('zones.calculate_shipping') }}
                </button>
                
                <div id="shipping-results" class="mt-6 hidden">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ __('zones.calculation_results') }}</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('zones.shipping_cost') }}:</span>
                                <span id="shipping-cost" class="font-medium"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('zones.tax_amount') }}:</span>
                                <span id="tax-amount" class="font-medium"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('zones.total_with_shipping') }}:</span>
                                <span id="total-with-shipping" class="font-medium text-lg"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="text-center">
            <a href="{{ route('zones.index') }}" 
               class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                {{ __('zones.back_to_zones') }}
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calculateBtn = document.getElementById('calculate-shipping');
    const orderAmountInput = document.getElementById('order-amount');
    const weightInput = document.getElementById('weight');
    const resultsDiv = document.getElementById('shipping-results');
    
    calculateBtn.addEventListener('click', function() {
        const orderAmount = parseFloat(orderAmountInput.value) || 0;
        const weight = parseFloat(weightInput.value) || 0;
        
        if (orderAmount <= 0) {
            alert('{{ __("zones.please_enter_order_amount") }}');
            return;
        }
        
        fetch('{{ route("zones.calculate-shipping", $zone) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                zone_id: {{ $zone->id }},
                order_amount: orderAmount,
                weight: weight
            })
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('shipping-cost').textContent = '€' + data.shipping_cost.toFixed(2);
            document.getElementById('tax-amount').textContent = '€' + data.tax_amount.toFixed(2);
            document.getElementById('total-with-shipping').textContent = '€' + data.total_with_shipping.toFixed(2);
            resultsDiv.classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("zones.calculation_error") }}');
        });
    });
});
</script>
@endsection
