<x-layouts.base title="{{ __('frontend.cart.view_cart') }}">
    @php
        $itemsCollection = collect($items ?? []);
        $itemCount = (int) data_get($summary ?? [], 'item_count', $itemsCollection->sum(fn (array $item): int => (int) data_get($item, 'quantity', 0)));

        $orderSummaryItems = $itemsCollection
            ->map(static function (array $item): array {
                $quantity = (int) data_get($item, 'quantity', 0);
                $lineTotal = (float) data_get($item, 'total', ((float) data_get($item, 'price', 0) * $quantity));

                return [
                    'name'                 => (string) data_get($item, 'name', __('ui.unknown_product')),
                    'quantity'             => $quantity,
                    'line_total'           => $lineTotal,
                    'formatted_line_total' => \Illuminate\Support\Number::currency($lineTotal, current_currency(), app()->getLocale()),
                ];
            })
            ->values();
    @endphp

    <div class="min-h-screen bg-gray-50">
        <section class="border-b border-gray-200 bg-white">
            <x-container class="px-4 py-10">
                <div class="mx-auto w-full max-w-7xl space-y-3">
                    <h1 class="text-2xl font-bold text-gray-900 md:text-3xl">
                        {{ __('frontend.cart.view_cart') }}
                    </h1>
                    <p class="max-w-3xl text-sm text-gray-600">
                        {{ __('frontend.cart.review_prompt') }}
                    </p>
                    @if ($itemCount > 0)
                        <p class="text-sm font-medium text-gray-700">
                            {{ $itemCount }} {{ trans_choice('frontend.cart.items', $itemCount) }}
                        </p>
                    @endif
                </div>
            </x-container>
        </section>

        <x-container class="px-4 py-10">
            <div class="mx-auto grid w-full max-w-7xl grid-cols-1 gap-10 lg:grid-cols-12">
                <section class="col-span-full space-y-5 lg:col-span-8">
                    @if ($itemsCollection->isEmpty())
                        <div class="rounded-xl border border-gray-200 bg-white p-10 text-center shadow-sm">
                            <h2 class="text-xl font-semibold text-gray-900">{{ __('messages.your_cart_is_empty') }}</h2>
                            <p class="mt-2 text-sm text-gray-600">{{ __('frontend.cart.empty_description') }}</p>
                            <a
                                href="{{ \Illuminate\Support\Facades\Route::has('localized.products.index') ? route('localized.products.index', ['locale' => app()->getLocale()]) : route('frontend.products.index') }}"
                                class="mt-6 inline-flex items-center justify-center rounded-lg bg-brand-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-brand-primary/90"
                            >
                                {{ __('frontend.cart.continue_shopping') }}
                            </a>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach ($itemsCollection as $item)
                                @php
                                    $productName = (string) data_get($item, 'name', __('ui.unknown_product'));
                                    $unitPrice = (float) data_get($item, 'price', 0);
                                    $quantity = (int) data_get($item, 'quantity', 0);
                                    $lineTotal = (float) data_get($item, 'total', $unitPrice * $quantity);
                                    $imageUrl = data_get($item, 'image');
                                @endphp

                                <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                                    <div class="flex flex-wrap items-center gap-4 md:flex-nowrap md:justify-between">
                                        <div class="flex min-w-0 items-center gap-4">
                                            <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-gray-100">
                                                @if (is_string($imageUrl) && $imageUrl !== '')
                                                    <img src="{{ $imageUrl }}" alt="{{ $productName }}" class="h-full w-full object-cover" loading="lazy" />
                                                @else
                                                    <div class="flex h-full w-full items-center justify-center">
                                                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="min-w-0">
                                                <h3 class="truncate text-base font-semibold text-gray-900">{{ $productName }}</h3>
                                                <p class="mt-1 text-sm text-gray-600">
                                                    {{ __('frontend.cart.unit_price', ['price' => app_money_format($unitPrice)]) }}
                                                </p>
                                                <p class="mt-2 inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                                    {{ __('frontend.cart.quantity_label', ['quantity' => $quantity]) }}
                                                </p>
                                            </div>
                                        </div>

                                        <p class="text-lg font-semibold text-gray-900">
                                            {{ \Illuminate\Support\Number::currency($lineTotal, current_currency(), app()->getLocale()) }}
                                        </p>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>

                <aside class="col-span-full space-y-5 lg:col-span-4 lg:sticky lg:top-24 lg:self-start">
                    <x-order.right-panel
                        :items="$orderSummaryItems"
                        :summary="$summary ?? []"
                        :item-count="$itemCount"
                        :show-coupon="(bool) (config('app-features.features.discount') ?? true)"
                    />

                    <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="space-y-3">
                            <a
                                href="{{ auth()->check() ? route('frontend.checkout.index') : route('register') }}"
                                class="inline-flex w-full items-center justify-center rounded-lg bg-brand-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-brand-primary/90"
                            >
                                {{ __('frontend.cart.proceed_to_checkout') }}
                            </a>

                            <a
                                href="{{ \Illuminate\Support\Facades\Route::has('localized.products.index') ? route('localized.products.index', ['locale' => app()->getLocale()]) : route('frontend.products.index') }}"
                                class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                            >
                                {{ __('frontend.cart.continue_shopping') }}
                            </a>

                            @if ($itemsCollection->isNotEmpty())
                                <form method="POST" action="{{ route('frontend.cart.clear') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="inline-flex w-full items-center justify-center rounded-lg border border-red-200 bg-red-50 px-5 py-3 text-sm font-semibold text-red-700 transition hover:bg-red-100"
                                    >
                                        {{ __('frontend.cart.clear_cart') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </section>
                </aside>
            </div>
        </x-container>
    </div>
</x-layouts.base>
