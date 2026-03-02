<x-layouts.base title="{{ __('frontend.cart.view_cart') }}">
    <div class="bg-sage">
    <!-- Hero Banner (centered) -->
    <section class="relative bg-sage z-10 overflow-hidden">
        <x-container class="px-4 py-16">
            <div class="mx-auto w-full max-w-7xl space-y-6 text-dark text-center">
                <p class="uppercase text-3xl md:text-4xl font-medium">
                    {{ __('frontend.cart.view_cart') }}
                </p>
                <p class="text-sm max-w-2xl mx-auto">
                    {{ __('frontend.cart.review_prompt') }}
                </p>
                @if (!empty($items))
                    @php($itemCount = (int) data_get($summary ?? [], 'item_count', 0))
                    <p class="uppercase font-semibold text-2xl sm:text-3xl md:text-4xl">
                        {{ $itemCount }} {{ trans_choice('frontend.cart.items', $itemCount) }}
                    </p>
                @endif
            </div>
        </x-container>
    </section>
    <div class="w-full h-[1px] bg-brand-primary relative">
        <div class="aspect-square h-10 bg-brand-primary absolute -top-5 left-1/2 -translate-x-1/2 z-20 rotate-45 flex items-center justify-center">
            <svg class="text-white w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>
    <x-container class="px-4 py-10">
        <div class="mx-auto w-full max-w-7xl grid grid-cols-1 gap-10 lg:grid-cols-12">
            <div class="col-span-full space-y-8 lg:col-span-9">
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ __('frontend.cart.view_cart') }}</h1>
        @if ($items)
                    <section class="bg-dark border border-sage/30 rounded-2xl shadow-sm">
                        <div class="p-6 space-y-6 divide-y divide-sage/30 text-sage">
                    @foreach ($items as $item)
                                <div class="pt-4 first:pt-0 flex flex-col md:flex-row md:items-center md:justify-between gap-4 hover:bg-dark/70 transition-colors rounded-xl px-2">
                                    <div class="text-sage">
                                        <h2 class="text-lg font-semibold">
                                            <a href="#" class="text-white hover:text-sage hover:underline" aria-label="{{ __('frontend.cart.view_product', ['name' => $item['name']]) }}">{{ $item['name'] }}</a>
                                        </h2>
                                        <p class="text-sm text-sage/80">{{ __('frontend.cart.unit_price', ['price' => app_money_format($item['price'])]) }}</p>
                                        <p class="text-sm text-sage/80">{{ __('frontend.cart.quantity_label', ['quantity' => $item['quantity']]) }}</p>
                            </div>
                                    <div class="flex items-center justify-end gap-3 text-sm text-sage">
                                        <dt class="text-sage/80">{{ __('messages.Tax') }}:</dt>
                                        <dd class="font-semibold text-white">{{ $summary['formatted_tax_amount'] }}</dd>
                            </div>
                                    <div class="flex items-center justify-end gap-3 text-sm text-sage">
                                        <dt class="text-sage/80">{{ __('messages.Shipping') }}:</dt>
                                        <dd class="font-semibold text-white">{{ $summary['formatted_shipping_amount'] }}</dd>
                            </div>
                            @if(($summary['discount_amount'] ?? 0) > 0)
                                        <div class="flex items-center justify-end gap-3 text-sm text-green-400">
                                    <dt>{{ __('messages.Discount') }}:</dt>
                                    <dd class="font-semibold">-{{ $summary['formatted_discount_amount'] }}</dd>
                                </div>
                            @endif
                                    <div class="flex items-center justify-end gap-3 text-base font-semibold text-white border-t border-sage/30 pt-2">
                                <dt>{{ __('messages.Total') }}:</dt>
                                <dd>{{ $summary['formatted_total'] }}</dd>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @else
            <p class="text-gray-500 dark:text-gray-400">{{ __('frontend.cart.empty_description') }}</p>
        @endif
            </div>
            <aside class="col-span-full lg:col-span-3">
                <section class="bg-dark border border-sage/30 rounded-2xl shadow-sm p-6">
            <h2 class="text-2xl font-semibold mb-4">{{ __('frontend.cart.order_summary') }}</h2>
                    <dl class="space-y-3 text-sm text-sage">
                <div class="flex justify-between">
                            <dt class="text-sage/80">{{ __('messages.Subtotal') }}</dt>
                            <dd class="text-white">{{ app_money_format($subtotal) }}</dd>
                </div>
                <div class="flex justify-between">
                            <dt class="text-sage/80">{{ __('messages.Tax') }}</dt>
                            <dd class="text-white">{{ app_money_format($tax) }}</dd>
                </div>
                <div class="flex justify-between">
                            <dt class="text-sage/80">{{ __('messages.Shipping') }}</dt>
                            <dd class="text-white">{{ app_money_format($shipping) }}</dd>
                </div>
                        <div class="flex justify-between">
                            <dt class="text-sage/80">{{ __('messages.Discount') }}</dt>
                            <dd class="text-red-400">-{{ app_money_format($discount) }}</dd>
                </div>
                        <div class="flex justify-between items-center text-lg font-semibold text-white bg-sage/10 rounded-xl px-4 py-3 border border-sage/30">
                            <dt class="text-white">{{ __('messages.Total') }}</dt>
                            <dd class="text-white">{{ app_money_format($total) }}</dd>
                </div>
            </dl>

                    <div class="mt-6 flex flex-wrap items-center gap-4 text-sage">
                <form method="POST" action="{{ route('frontend.cart.clear') }}">
                    @csrf
                            <button type="submit" class="px-4 py-2 border border-sage/30 rounded-lg text-sm hover:bg-sage/10">{{ __('frontend.cart.clear_cart') }}</button>
                </form>

                <form method="POST" action="{{ route('frontend.discounts.apply-coupon') }}" class="flex items-center gap-2">
                    @csrf
                            <label for="code" class="text-sm text-sage/80">{{ __('frontend.cart.coupon_code') }}</label>
                            <input id="code" name="code" class="rounded-lg border border-sage/30 bg-dark/30 text-white placeholder:text-sage/50" placeholder="{{ __('frontend.cart.enter_code') }}">
                            <button type="submit" class="px-4 py-2 rounded-lg bg-sage text-dark">{{ __('frontend.cart.apply_coupon') }}</button>
                </form>

                <form method="POST" action="{{ route('frontend.discounts.remove-coupon') }}">
                    @csrf
                            <button type="submit" class="text-sm text-red-400 hover:text-red-300">{{ __('frontend.cart.remove_coupon') }}</button>
                </form>

                        <div class="w-full flex flex-col sm:flex-row gap-3">
                            <a href="{{ route('frontend.checkout.index') }}" class="flex-1 inline-flex items-center justify-center px-5 py-3 rounded-full bg-sage text-dark hover:bg-sage/90">
                                {{ __('frontend.cart.proceed_to_checkout') }}
                            </a>
                            <a href="{{ route('frontend.products.index', ['locale' => app()->getLocale()]) ?? '/products' }}" class="flex-1 inline-flex items-center justify-center px-5 py-3 border border-sage/30 rounded-full text-sage hover:bg-sage/10">
                                {{ __('frontend.cart.continue_shopping') }}
                            </a>
                        </div>
            </div>
        </section>
            </aside>
        </div>
    </x-container>
    </div>
</x-layouts.base>
