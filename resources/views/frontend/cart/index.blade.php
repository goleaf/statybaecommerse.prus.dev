@extends('components.layouts.base')

@section('title', __('Cart'))

@section('content')
    <div class="container mx-auto px-4 py-10">
        <div class="max-w-5xl mx-auto space-y-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Shopping cart') }}</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">{{ __('Review the items in your cart before checking out.') }}</p>
            </div>

            @if($cartItems->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-white/60 dark:bg-gray-900/40 p-12 text-center">
                    <svg class="mx-auto mb-6 h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01" />
                    </svg>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('Your cart is empty') }}</h2>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ __('Browse our catalog and add items to your cart to continue.') }}</p>
                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <a href="{{ route('products.index', ['locale' => app()->getLocale()]) ?? '/products' }}"
                           class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-3 text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            {{ __('Start shopping') }}
                        </a>
                        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}"
                           class="inline-flex items-center rounded-lg border border-gray-300 px-5 py-3 text-gray-700 hover:border-gray-400 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            {{ __('Back to home') }}
                        </a>
                    </div>
                </div>
            @else
                <div class="space-y-6">
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-800/60">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Item') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Price') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Quantity') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Total') }}</th>
                                    <th class="px-6 py-3"><span class="sr-only">{{ __('Actions') }}</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                @foreach($cartItems as $item)
                                    <tr class="bg-white transition hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800/80">
                                        <td class="px-6 py-4 align-top">
                                            <div class="flex items-center gap-4">
                                                @php($product = $item->product)
                                                @php($image = $item->product_snapshot['image'] ?? ($product?->getFirstMediaUrl(config('media.storage.thumbnail_collection')) ?? $product?->getFirstMediaUrl(config('media.storage.collection_name'))))
                                                @if($image)
                                                    <img src="{{ $image }}" alt="{{ $item->product_snapshot['name'] ?? $product?->name }}" class="h-16 w-16 rounded-md object-cover">
                                                @endif
                                                <div>
                                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $item->product_snapshot['name'] ?? $product?->name }}</p>
                                                    @if($sku = $item->product_snapshot['sku'] ?? $product?->sku)
                                                        <p class="text-sm text-gray-500">SKU: {{ $sku }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ app_money_format($item->price) }}</td>
                                        <td class="px-6 py-4">
                                            <form method="POST" action="{{ route('frontend.cart.update', $item) }}" class="inline-flex items-center gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <input type="number" name="quantity" min="1" value="{{ $item->quantity }}" class="w-20 rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <button type="submit" class="inline-flex items-center rounded-md border border-gray-300 px-3 py-1 text-sm font-medium text-gray-700 hover:border-blue-500 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ __('Update') }}</button>
                                            </form>
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white">{{ app_money_format($item->calculateSubtotal()) }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <form method="POST" action="{{ route('frontend.cart.remove', $item) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-500" onclick="return confirm('{{ __('Are you sure you want to remove this item?') }}')">
                                                    {{ __('Remove') }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-col items-end gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <form method="POST" action="{{ route('frontend.cart.clear') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:border-red-400 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-500" onclick="return confirm('{{ __('Clear all items from your cart?') }}')">
                                {{ __('Clear cart') }}
                            </button>
                        </form>
                        <dl class="space-y-1 text-right">
                            <div class="flex items-center justify-end gap-3 text-sm text-gray-600 dark:text-gray-300">
                                <dt>{{ __('Subtotal') }}:</dt>
                                <dd class="font-semibold text-gray-900 dark:text-white">{{ $summary['formatted_subtotal'] }}</dd>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Taxes and shipping calculated during checkout.') }}</div>
                        </dl>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <a href="{{ route('frontend.checkout.index') }}"
                           class="inline-flex items-center justify-center rounded-lg bg-green-600 px-5 py-3 text-center text-white shadow hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                            {{ __('Proceed to checkout') }}
                        </a>
                        <a href="{{ route('products.index', ['locale' => app()->getLocale()]) ?? '/products' }}"
                           class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-5 py-3 text-center text-gray-700 hover:border-blue-500 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            {{ __('Continue shopping') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
