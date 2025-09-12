@extends('frontend.layouts.app')

@section('title', __('orders.order_details', ['number' => $order->number]))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ __('orders.order') }} #{{ $order->number }}
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        {{ __('orders.placed_on') }} {{ $order->created_at->format('d.m.Y H:i') }}
                    </p>
                </div>
                
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                        €{{ number_format($order->total, 2) }}
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($order->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                        @elseif($order->status === 'processing') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                        @elseif(in_array($order->status, ['confirmed', 'shipped', 'delivered', 'completed'])) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                        @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                        @endif">
                        {{ __("orders.statuses.{$order->status}") }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Items -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('orders.order_items') }}
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($order->items as $item)
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        @if($item->product && $item->product->images->count() > 0)
                                            <img src="{{ $item->product->images->first()->url }}" 
                                                 alt="{{ $item->name }}"
                                                 class="h-16 w-16 rounded-lg object-cover">
                                        @else
                                            <div class="h-16 w-16 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $item->name }}
                                        </h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('orders.sku') }}: {{ $item->sku }}
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('orders.quantity') }}: {{ $item->quantity }}
                                        </p>
                                    </div>
                                    
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            €{{ number_format($item->unit_price, 2) }} × {{ $item->quantity }}
                                        </p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            €{{ number_format($item->total, 2) }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Shipping Information -->
                @if($order->shipping)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ __('orders.shipping_information') }}
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ __('orders.carrier_name') }}
                                    </p>
                                    <p class="text-sm text-gray-900 dark:text-white">
                                        {{ $order->shipping->carrier_name }}
                                    </p>
                                </div>
                                
                                @if($order->shipping->service)
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ __('orders.service') }}
                                        </p>
                                        <p class="text-sm text-gray-900 dark:text-white">
                                            {{ $order->shipping->service }}
                                        </p>
                                    </div>
                                @endif
                                
                                @if($order->shipping->tracking_number)
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ __('orders.tracking_number') }}
                                        </p>
                                        <p class="text-sm text-gray-900 dark:text-white">
                                            {{ $order->shipping->tracking_number }}
                                        </p>
                                    </div>
                                @endif
                                
                                @if($order->shipping->tracking_url)
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ __('orders.tracking_url') }}
                                        </p>
                                        <a href="{{ $order->shipping->tracking_url }}" 
                                           target="_blank"
                                           class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                            {{ __('orders.track_package') }}
                                        </a>
                                    </div>
                                @endif
                                
                                @if($order->shipping->shipped_at)
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ __('orders.shipped_at') }}
                                        </p>
                                        <p class="text-sm text-gray-900 dark:text-white">
                                            {{ $order->shipping->shipped_at->format('d.m.Y H:i') }}
                                        </p>
                                    </div>
                                @endif
                                
                                @if($order->shipping->estimated_delivery)
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ __('orders.estimated_delivery') }}
                                        </p>
                                        <p class="text-sm text-gray-900 dark:text-white">
                                            {{ $order->shipping->estimated_delivery->format('d.m.Y') }}
                                        </p>
                                    </div>
                                @endif
                                
                                @if($order->shipping->delivered_at)
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ __('orders.delivered_at') }}
                                        </p>
                                        <p class="text-sm text-gray-900 dark:text-white">
                                            {{ $order->shipping->delivered_at->format('d.m.Y H:i') }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Documents -->
                @if($order->documents->count() > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ __('orders.documents') }}
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                @foreach($order->documents as $document)
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $document->name }}
                                            </h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ __("orders.document_types.{$document->type}") }}
                                            </p>
                                        </div>
                                        <a href="{{ route('documents.download', $document) }}" 
                                           class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            {{ __('orders.download') }}
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Order Summary -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('orders.order_summary') }}
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">{{ __('orders.subtotal') }}</span>
                                <span class="text-gray-900 dark:text-white">€{{ number_format($order->subtotal, 2) }}</span>
                            </div>
                            
                            @if($order->tax_amount > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">{{ __('orders.tax_amount') }}</span>
                                    <span class="text-gray-900 dark:text-white">€{{ number_format($order->tax_amount, 2) }}</span>
                                </div>
                            @endif
                            
                            @if($order->shipping_amount > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">{{ __('orders.shipping_amount') }}</span>
                                    <span class="text-gray-900 dark:text-white">€{{ number_format($order->shipping_amount, 2) }}</span>
                                </div>
                            @endif
                            
                            @if($order->discount_amount > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">{{ __('orders.discount_amount') }}</span>
                                    <span class="text-gray-900 dark:text-white">-€{{ number_format($order->discount_amount, 2) }}</span>
                                </div>
                            @endif
                            
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                                <div class="flex justify-between text-base font-semibold">
                                    <span class="text-gray-900 dark:text-white">{{ __('orders.total') }}</span>
                                    <span class="text-gray-900 dark:text-white">€{{ number_format($order->total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Addresses -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('orders.addresses') }}
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- Billing Address -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('orders.billing_address') }}
                            </h3>
                            <div class="text-sm text-gray-900 dark:text-white">
                                @if(is_array($order->billing_address))
                                    @foreach($order->billing_address as $key => $value)
                                        <div>{{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}</div>
                                    @endforeach
                                @else
                                    {{ $order->billing_address }}
                                @endif
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('orders.shipping_address') }}
                            </h3>
                            <div class="text-sm text-gray-900 dark:text-white">
                                @if(is_array($order->shipping_address))
                                    @foreach($order->shipping_address as $key => $value)
                                        <div>{{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}</div>
                                    @endforeach
                                @else
                                    {{ $order->shipping_address }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('orders.actions') }}
                        </h2>
                    </div>
                    <div class="p-6 space-y-3">
                        @if($order->canBeCancelled())
                            <form method="POST" action="{{ route('frontend.orders.cancel', $order) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        onclick="return confirm('{{ __('orders.confirm_cancel') }}')"
                                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-red-300 dark:border-red-600 shadow-sm text-sm font-medium rounded-md text-red-700 dark:text-red-300 bg-white dark:bg-gray-700 hover:bg-red-50 dark:hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    {{ __('orders.cancel_order') }}
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('frontend.orders.index') }}" 
                           class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('orders.back_to_orders') }}
                        </a>
                    </div>
                </div>

                <!-- Notes -->
                @if($order->notes)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ __('orders.notes') }}
                            </h2>
                        </div>
                        <div class="p-6">
                            <p class="text-sm text-gray-900 dark:text-white">
                                {{ $order->notes }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

