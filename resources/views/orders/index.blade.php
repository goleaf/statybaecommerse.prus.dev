@extends('layouts.app')

@section('title', __('orders.my_orders'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ __('orders.my_orders') }}
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                {{ __('orders.manage_your_orders') }}
            </p>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <form method="GET" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-64">
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('orders.search') }}
                    </label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="{{ __('orders.search_placeholder') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div class="min-w-48">
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('orders.status') }}
                    </label>
                    <select id="status" 
                            name="status"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">{{ __('orders.all_statuses') }}</option>
                        @foreach(['pending', 'processing', 'confirmed', 'shipped', 'delivered', 'completed', 'cancelled'] as $status)
                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                {{ __("orders.statuses.{$status}") }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        {{ __('orders.filter') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Orders List -->
        @if($orders->count() > 0)
            <div class="space-y-4">
                @foreach($orders as $order)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ __('orders.order') }} #{{ $order->number }}
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ __('orders.placed_on') }} {{ $order->created_at->format('d.m.Y H:i') }}
                                    </p>
                                </div>
                                
                                <div class="text-right">
                                    <div class="text-lg font-bold text-gray-900 dark:text-white">
                                        €{{ number_format($order->total, 2) }}
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($order->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @elseif($order->status === 'processing') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                        @elseif(in_array($order->status, ['confirmed', 'shipped', 'delivered', 'completed'])) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                        @endif">
                                        {{ __("orders.statuses.{$order->status}") }}
                                    </span>
                                </div>
                            </div>

                            <!-- Order Items -->
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('orders.items') }} ({{ $order->items->count() }})
                                </h4>
                                <div class="space-y-2">
                                    @foreach($order->items->take(3) as $item)
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-900 dark:text-white">
                                                {{ $item->name }} × {{ $item->quantity }}
                                            </span>
                                            <span class="text-gray-600 dark:text-gray-400">
                                                €{{ number_format($item->total, 2) }}
                                            </span>
                                        </div>
                                    @endforeach
                                    @if($order->items->count() > 3)
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ __('orders.and_more_items', ['count' => $order->items->count() - 3]) }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-between">
                                <div class="flex space-x-3">
                                    <a href="{{ route('frontend.orders.show', $order) }}" 
                                       class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        {{ __('orders.view_details') }}
                                    </a>
                                    
                                    @if($order->canBeCancelled())
                                        <form method="POST" action="{{ route('frontend.orders.cancel', $order) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    onclick="return confirm('{{ __('orders.confirm_cancel') }}')"
                                                    class="inline-flex items-center px-3 py-2 border border-red-300 dark:border-red-600 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 dark:text-red-300 bg-white dark:bg-gray-700 hover:bg-red-50 dark:hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                {{ __('orders.cancel_order') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                @if($order->shipping && $order->shipping->tracking_number)
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        <span class="font-medium">{{ __('orders.tracking_number') }}:</span>
                                        {{ $order->shipping->tracking_number }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $orders->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="mx-auto h-24 w-24 text-gray-400 dark:text-gray-500">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                    {{ __('orders.no_orders') }}
                </h3>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    {{ __('orders.no_orders_description') }}
                </p>
                <div class="mt-6">
                    <a href="{{ route('frontend.products.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{ __('orders.start_shopping') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

