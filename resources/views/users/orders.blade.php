<?php
use function Livewire\Volt\{layout, title};

layout('components.layouts.templates.frontend');
title(__('users.orders'));

?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ localized_route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
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
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('users.orders') }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('users.my_orders') }}</h1>
            <p class="mt-2 text-gray-600">{{ __('users.orders_description') }}</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <form method="GET" action="{{ route('users.orders') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.filter_by_status') }}</label>
                    <select 
                        id="status" 
                        name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">{{ __('users.all_statuses') }}</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('orders.status.pending') }}</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>{{ __('orders.status.processing') }}</option>
                        <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>{{ __('orders.status.shipped') }}</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>{{ __('orders.status.delivered') }}</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('orders.status.cancelled') }}</option>
                        <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>{{ __('orders.status.refunded') }}</option>
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.date_from') }}</label>
                    <input 
                        type="date" 
                        id="date_from" 
                        name="date_from" 
                        value="{{ request('date_from') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                <!-- Date To -->
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">{{ __('users.date_to') }}</label>
                    <input 
                        type="date" 
                        id="date_to" 
                        name="date_to" 
                        value="{{ request('date_to') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                <!-- Filter Button -->
                <div class="flex items-end">
                    <button 
                        type="submit"
                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        {{ __('users.filter') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Orders List -->
        @if($orders->count() > 0)
            <div class="space-y-6">
                @foreach($orders as $order)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <!-- Order Header -->
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center space-x-4">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        {{ __('users.order') }} #{{ $order->id }}
                                    </h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $order->status_color }}">
                                        {{ $order->status_text }}
                                    </span>
                                </div>
                                <div class="mt-2 sm:mt-0 text-sm text-gray-500">
                                    {{ __('users.ordered_on') }} {{ $order->created_at->format('Y-m-d') }}
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="px-6 py-4">
                            <div class="space-y-4">
                                @foreach($order->items as $item)
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            @if($item->productVariant && $item->productVariant->product && $item->productVariant->product->featured_image)
                                                <img 
                                                    src="{{ Storage::disk('public')->url($item->productVariant->product->featured_image) }}" 
                                                    alt="{{ $item->productVariant->product->name }}"
                                                    class="h-16 w-16 rounded-md object-cover"
                                                >
                                            @else
                                                <div class="h-16 w-16 rounded-md bg-gray-200 flex items-center justify-center">
                                                    <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-sm font-medium text-gray-900">
                                                {{ $item->productVariant ? $item->productVariant->product->name : $item->product_name }}
                                            </h4>
                                            @if($item->productVariant && $item->productVariant->name)
                                                <p class="text-sm text-gray-500">{{ $item->productVariant->name }}</p>
                                            @endif
                                            <p class="text-sm text-gray-500">
                                                {{ __('users.quantity') }}: {{ $item->quantity }}
                                            </p>
                                        </div>
                                        <div class="text-sm font-medium text-gray-900">
                                            €{{ number_format($item->total_price, 2) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-gray-600">
                                    <p>{{ __('users.total_items') }}: {{ $order->items->sum('quantity') }}</p>
                                    <p>{{ __('users.shipping_address') }}: {{ $order->shippingAddress ? $order->shippingAddress->city . ', ' . $order->shippingAddress->country : __('users.not_available') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-600">{{ __('users.subtotal') }}</p>
                                    <p class="text-lg font-medium text-gray-900">€{{ number_format($order->total_amount, 2) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Order Actions -->
                        <div class="px-6 py-4 bg-white border-t border-gray-200">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                                <div class="flex space-x-4">
                                    <a 
                                        href="{{ route('users.orders.show', $order) }}"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        {{ __('users.view_details') }}
                                    </a>
                                    
                                    @if($order->status === 'delivered')
                                        <a 
                                            href="{{ route('users.orders.reorder', $order) }}"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        >
                                            {{ __('users.reorder') }}
                                        </a>
                                    @endif
                                    
                                    @if(in_array($order->status, ['pending', 'processing']) && $order->can_be_cancelled)
                                        <form method="POST" action="{{ route('users.orders.cancel', $order) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <button 
                                                type="submit"
                                                onclick="return confirm('{{ __("users.confirm_cancel_order") }}')"
                                                class="inline-flex items-center px-3 py-2 border border-red-300 text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                            >
                                                {{ __('users.cancel_order') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                
                                <div class="text-sm text-gray-500">
                                    @if($order->tracking_number)
                                        <p>{{ __('users.tracking_number') }}: {{ $order->tracking_number }}</p>
                                    @endif
                                    @if($order->estimated_delivery_date)
                                        <p>{{ __('users.estimated_delivery') }}: {{ $order->estimated_delivery_date->format('Y-m-d') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($orders->hasPages())
                <div class="mt-8">
                    {{ $orders->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('users.no_orders') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('users.no_orders_description') }}</p>
                <div class="mt-6">
                    <a href="{{ localized_route('products.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        {{ __('users.start_shopping') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
