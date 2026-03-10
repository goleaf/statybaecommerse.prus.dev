<div class="bg-gray-50 min-h-screen">
    @php
        $itemsCollection = collect($items ?? [])->map(static fn ($item) => is_array($item) ? (object) $item : $item);
        $totalQuantity = (int) $itemsCollection->sum(static fn ($item) => (int) ($item->quantity ?? 0));
        $productIds = $itemsCollection
            ->pluck('product_id')
            ->filter(static fn ($id): bool => is_numeric($id) && (int) $id > 0)
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values();
        $productSlugById = $productIds->isNotEmpty()
            ? \App\Models\Product::query()->whereIn('id', $productIds->all())->pluck('slug', 'id')
            : collect();
        $cartSubtotal = (float) ($subtotal ?? 0);
        $cartBreakdown = app(\App\Services\Pricing\PriceCalculator::class)->breakdown($cartSubtotal, 0.0, 0.0);
        $taxAmount = (float) ($cartBreakdown->tax ?? 0);
        $shippingAmount = (float) ($cartBreakdown->shipping ?? 0);
        $cartTotal = (float) ($cartBreakdown->total ?? ($cartSubtotal + $taxAmount + $shippingAmount));
        $orderSummaryItems = $itemsCollection->map(static function ($item): array {
            $quantity = (int) ($item->quantity ?? 0);
            $lineTotal = (float) ($item->price ?? 0) * $quantity;

            return [
                'name'                 => (string) ($item->name ?? __('ui.unknown_product')),
                'quantity'             => $quantity,
                'line_total'           => $lineTotal,
                'formatted_line_total' => \Illuminate\Support\Number::currency($lineTotal, current_currency(), app()->getLocale()),
            ];
        })->values();
        $orderSummary = [
            'subtotal'                  => $cartSubtotal,
            'shipping_amount'           => $shippingAmount,
            'tax_amount'                => $taxAmount,
            'discount_amount'           => 0,
            'total'                     => $cartTotal,
            'currency'                  => current_currency(),
            'formatted_subtotal'        => \Illuminate\Support\Number::currency($cartSubtotal, current_currency(), app()->getLocale()),
            'formatted_shipping_amount' => \Illuminate\Support\Number::currency($shippingAmount, current_currency(), app()->getLocale()),
            'formatted_tax_amount'      => \Illuminate\Support\Number::currency($taxAmount, current_currency(), app()->getLocale()),
            'formatted_discount_amount' => \Illuminate\Support\Number::currency(0, current_currency(), app()->getLocale()),
            'formatted_total'           => \Illuminate\Support\Number::currency($cartTotal, current_currency(), app()->getLocale()),
        ];
    @endphp

    <section class="border-b border-gray-200 bg-white">
        <x-container class="px-4 py-10">
            <div class="mx-auto w-full max-w-6xl">
                <div class="space-y-3">
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">{{ __('messages.your_cart') }}</h1>
                    <p class="text-sm text-gray-600">{{ __('messages.review_your_selected_items_and_proceed_to_checkout') }}</p>
                    @if ($itemsCollection->isNotEmpty())
                        <p class="text-sm font-medium text-gray-700">{{ $totalQuantity }} {{ __('messages.items') }}</p>
                    @endif
                </div>
            </div>
        </x-container>
    </section>

    <main class="py-10">
        <x-container class="px-4">
            <div class="mx-auto w-full max-w-6xl">
                @if ($itemsCollection->isEmpty())
                    <div class="max-w-3xl mx-auto rounded-xl border border-gray-200 bg-white p-10 text-center">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                            <svg class="h-7 w-7 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900">{{ __('messages.your_cart_is_empty') }}</h2>
                        <p class="mt-2 text-sm text-gray-600">{{ __('messages.start_adding_items_to_your_cart_to_see_them_here') }}</p>
                        <a href="{{ route('home', []) }}"
                           class="mt-6 inline-flex items-center justify-center rounded-lg bg-brand-primary px-5 py-3 text-sm font-semibold text-white transition">
                            {{ __('messages.continue_shopping') }}
                        </a>
                    </div>
                @else
                    <div class="grid gap-10 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] lg:items-start">
                        <section class="space-y-4">
                            <div class="space-y-4">
                                @foreach ($itemsCollection as $item)
                                    @php
                                        $cartItemId = (int) ($item->id ?? 0);
                                        $productId = (int) ($item->product_id ?? 0);
                                        $productSlug = $productId > 0 ? $productSlugById->get($productId) : null;
                                        $productUrl = is_string($productSlug) && $productSlug !== ''
                                            ? (\Illuminate\Support\Facades\Route::has('products.show')
                                                ? route('products.show', ['product' => $productSlug])
                                                : (\Illuminate\Support\Facades\Route::has('frontend.products.show')
                                                    ? route('frontend.products.show', ['product' => $productSlug])
                                                    : '#'))
                                            : '#';
                                        $itemQuantity = (int) ($item->quantity ?? 0);
                                    @endphp
                                    <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                                        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                                            <div class="flex min-w-0 w-full flex-1 items-center gap-4">
                                                <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-gray-100">
                                                    @if ($productUrl !== '#')
                                                        <a href="{{ $productUrl }}" class="block h-full w-full">
                                                    @endif
                                                    @if ($thumb = $this->getItemThumbnail($item))
                                                        <img src="{{ $thumb }}" alt="{{ $item->name }}" class="h-full w-full object-cover" />
                                                    @else
                                                        <div class="flex h-full w-full items-center justify-center">
                                                            <svg class="h-7 w-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                            </svg>
                                                        </div>
                                                    @endif
                                                    @if ($productUrl !== '#')
                                                        </a>
                                                    @endif
                                                </div>

                                                <div class="min-w-0">
                                                    <h3 class="whitespace-normal break-words text-base font-semibold leading-6 text-gray-900">
                                                        @if ($productUrl !== '#')
                                                            <a href="{{ $productUrl }}" class="hover:text-brand-primary">
                                                                {{ $item->name }}
                                                            </a>
                                                        @else
                                                            {{ $item->name }}
                                                        @endif
                                                    </h3>
                                                    <p class="mt-2 text-sm text-gray-600">
                                                        {{ __('messages.unit_price') }}: {{ \Illuminate\Support\Number::currency((float) $item->price, current_currency(), app()->getLocale()) }}
                                                    </p>
                                                    <p class="mt-2 inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                                        {{ trans_choice('messages.quantity_pieces', $itemQuantity, ['count' => $itemQuantity]) }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center sm:justify-end">
                                                <div class="inline-flex items-center self-start rounded-lg border border-gray-300 sm:self-auto">
                                                    <button type="button"
                                                            wire:click="decrementItem({{ $cartItemId }}, {{ $productId }})"
                                                            wire:loading.attr="disabled"
                                                            class="px-3 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                                                            title="{{ __('translations.decrease_quantity') }}"
                                                            {{ $itemQuantity <= 1 ? 'disabled' : '' }}>
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                        </svg>
                                                    </button>
                                                    <input type="number"
                                                           min="1"
                                                           step="1"
                                                           value="{{ $itemQuantity }}"
                                                           wire:change="updateItemQuantity({{ $cartItemId }}, $el.value, {{ $productId }})"
                                                           wire:loading.attr="disabled"
                                                           inputmode="numeric"
                                                           class="w-16 px-3 py-2 text-center border-0 focus:ring-0 focus:outline-none bg-transparent" />
                                                    <button type="button"
                                                            wire:click="incrementItem({{ $cartItemId }}, {{ $productId }})"
                                                            wire:loading.attr="disabled"
                                                            class="px-3 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                                                            title="{{ __('translations.increase_quantity') }}">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                        </svg>
                                                    </button>
                                                </div>

                                                <p class="text-lg font-bold text-gray-900 sm:min-w-[110px] sm:text-right">
                                                        {{ \Illuminate\Support\Number::currency((float) $item->price * (int) $item->quantity, current_currency(), app()->getLocale()) }}
                                                </p>

                                                <button wire:click="removeItem({{ $cartItemId }}, {{ $productId }})"
                                                        wire:confirm="{{ __('translations.confirm_remove_cart_item') }}"
                                                        wire:loading.attr="disabled"
                                                        title="{{ __('translations.remove_item_from_cart') }}"
                                                        class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-red-200 bg-red-50 px-4 text-sm font-medium text-red-600 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                                                    {{ __('messages.remove') }}
                                                </button>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <a href="{{ auth()->check() ? route('frontend.checkout.index') : route('register') }}"
                                   class="inline-flex w-full items-center justify-center rounded-lg bg-primary-600 px-5 py-3 text-sm font-semibold text-white transition">
                                    {{ __('translations.proceed_to_checkout') }}
                                </a>

                                <a href="{{ route('home', []) }}"
                                   class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition">
                                    {{ __('messages.continue_shopping') }}
                                </a>
                            </div>
                        </section>

                        <aside class="space-y-6 lg:sticky lg:top-24">
                            <x-order.right-panel
                                :items="$orderSummaryItems"
                                :summary="$orderSummary"
                                :item-count="$totalQuantity"
                                :show-coupon="(bool) (config('app-features.features.discount') ?? true)"
                            />
                        </aside>
                    </div>
                @endif
            </div>
        </x-container>
    </main>
</div>
