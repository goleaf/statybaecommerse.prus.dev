<div class="bg-gradient-to-b from-slate-50 via-white to-white" wire:loading.attr="aria-busy" aria-busy="false">
    @php
        $locale = app()->getLocale();
    @endphp
    @section('meta')
        <x-meta
                :title="$product->trans('seo_title') ?? $product->name"
                :description="$product->trans('seo_description') ?? Str::limit(strip_tags($product->description), 150)"
                :og-image="$ogImage"
                ogType="product"
                :canonical="url()->current()"
                :preload-image="(string) ($ogImage ?: '')" />
    @endsection
    @if (session('status'))
        <x-container class="max-w-7xl"><x-alert type="success"
                     class="mb-6">{{ session('status') }}</x-alert></x-container>
    @endif
    @if (session('error'))
        <x-container class="max-w-7xl"><x-alert type="error"
                     class="mb-6">{{ session('error') }}</x-alert></x-container>
    @endif

    <div class="py-12 lg:py-16">
        <x-container class="max-w-7xl space-y-10">
            <x-breadcrumbs :items="[
                [
                    'label' => __('frontend.navigation.products'),
                    'url' => route('localized.products.index', ['locale' => $locale]),
                ],
                [
                    'label' => $this->brandLabel,
                    'url' =>
                        $product->brand && function_exists('route') && Route::has('localized.brands.show')
                            ? route('localized.brands.show', [
                                'locale' => $locale,
                                'slug' => $product->brand->trans('slug') ?? $product->brand->slug,
                            ])
                            : null,
                ],
                ['label' => $product->trans('name') ?? $product->name],
            ]" aria-label="{{ __('frontend.navigation.breadcrumbs') }}" />

            <div class="grid gap-10 lg:grid-cols-12">
                <div class="lg:col-span-7 space-y-8">
                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="p-6 lg:p-8">
                            <livewire:components.product-image-gallery :product="$product" image-size="xl" />
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="space-y-6 p-6 lg:p-8">
                            <div class="flex flex-wrap items-center gap-3">
                                @if ($this->brandLabel)
                                    <span
                                          class="inline-flex items-center rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700">
                                        {{ $this->brandLabel }}
                                    </span>
                                @endif
                                @foreach ($this->categoryLabels as $categoryName)
                                    <span
                                          class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                        {{ $categoryName }}
                                    </span>
                                @endforeach
                            </div>

                            <div>
                                <h1 class="text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
                                    {{ $product->trans('name') ?? $product->name }}
                                </h1>
                                <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-slate-600">
                                    @if ($product->sku)
                                        <span class="text-slate-500">{{ __('messages.sku') }}:
                                            {{ $product->sku }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    @if ($this->pricingSummary['current'])
                                        <p class="text-4xl font-semibold text-primary-600">
                                            {{ \Illuminate\Support\Number::currency((float) $this->pricingSummary['current'], $this->pricingSummary['currency'], $locale) }}
                                        </p>
                                    @endif
                                    @if ($this->pricingSummary['compare'] && $this->pricingSummary['current'] && $this->pricingSummary['compare'] > $this->pricingSummary['current'])
                                        <p class="flex items-center gap-2 text-sm text-slate-500">
                                            <span class="line-through">
                                                {{ \Illuminate\Support\Number::currency((float) $this->pricingSummary['compare'], $this->pricingSummary['currency'], $locale) }}
                                            </span>
                                            @if ($this->pricingSummary['discount'])
                                                <span
                                                      class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-600">
                                                    -{{ number_format($this->pricingSummary['discount'], 0) }}%
                                                </span>
                                            @endif
                                        </p>
                                    @endif
                                    @if ($this->stockMessage)
                                        <p class="text-sm font-medium {{ $this->stockToneClass }}">
                                            {{ $this->stockMessage }}
                                        </p>
                                    @endif
                                </div>
                                <div class="sm:text-right">
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                        {{ __('product_page.delivery_eta_2_weeks') }}</p>
                                    <p class="text-lg font-semibold text-slate-900">
                                        {{ \Illuminate\Support\Number::format((float) $this->availableQuantity) }}
                                    </p>
                                </div>
                            </div>

                            @if ($this->shortDescription)
                                <p class="text-base leading-relaxed text-slate-600">
                                    {{ $this->shortDescription }}
                                </p>
                            @endif
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="space-y-6 p-6 lg:p-8">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-slate-900">
                                    {{ __('product_page.features_title') }}</h2>
                                <span
                                      class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ $this->attributeFeatures->count() }}
                                    {{ __('product_page.features_count') }}</span>
                            </div>

                            @if ($this->attributeFeatures->isEmpty())
                                <p class="text-sm text-slate-500">{{ __('product_page.features_empty') }}</p>
                            @else
                                <div class="grid gap-4 sm:grid-cols-2">
                                    @foreach ($this->attributeFeatures as $feature)
                                        <div
                                             class="flex items-start gap-3 rounded-2xl border border-slate-100 bg-slate-50/60 p-4">
                                            <div
                                                 class="flex h-9 w-9 items-center justify-center rounded-full bg-white shadow-sm">
                                                <x-heroicon-o-check-badge class="h-5 w-5 text-emerald-500" />
                                            </div>
                                            <div class="space-y-1">
                                                <p class="text-sm font-semibold text-slate-900">{{ $feature['label'] }}
                                                </p>
                                                <p class="text-sm text-slate-600">{{ $feature['value'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="space-y-6 p-6 lg:p-8">
                            <h2 class="text-lg font-semibold text-slate-900">
                                {{ __('product_page.detailed_description') }}</h2>
                            <div class="prose prose-slate max-w-none text-slate-700">
                                {!! $product->trans('description') ??
                                    ($product->description ?? '<p>' . __('product_page.no_description') . '</p>') !!}
                            </div>
                        </div>
                    </section>

                    @php
                        $technicalMetrics = [
                            [
                                'label' => __('messages.height'),
                                'value' => $product->height
                                    ? \Illuminate\Support\Number::format((float) $product->height) .
                                        ' ' .
                                        ($product->height_unit?->value ?? 'cm')
                                    : null,
                            ],
                            [
                                'label' => __('messages.width'),
                                'value' => $product->width
                                    ? \Illuminate\Support\Number::format((float) $product->width) .
                                        ' ' .
                                        ($product->width_unit?->value ?? 'cm')
                                    : null,
                            ],
                            [
                                'label' => __('messages.length'),
                                'value' => $product->length
                                    ? \Illuminate\Support\Number::format((float) $product->length) .
                                        ' ' .
                                        ($product->depth_unit?->value ?? 'cm')
                                    : null,
                            ],
                            [
                                'label' => __('messages.product_weight'),
                                'value' => $this->formatMeasurement(
                                    $product->weight,
                                    $product->weight_unit?->value ?? null,
                                ),
                            ],
                            [
                                'label' => __('messages.quantity'),
                                'value' => $product->getMinimumQuantity() > 1 ? $product->getMinimumQuantity() : null,
                            ],
                        ];
                    @endphp

                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="space-y-6 p-6 lg:p-8">
                            <h2 class="text-lg font-semibold text-slate-900">{{ __('messages.product_specifications') }}
                            </h2>
                            <dl class="grid gap-4 sm:grid-cols-2">
                                @foreach ($technicalMetrics as $metric)
                                    @if (filled($metric['value']))
                                        <div class="rounded-2xl bg-slate-50/80 p-4">
                                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                                {{ $metric['label'] }}</dt>
                                            <dd class="mt-2 text-sm font-semibold text-slate-900">
                                                {{ $metric['value'] }}</dd>
                                        </div>
                                    @endif
                                @endforeach
                            </dl>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="space-y-6 p-6 lg:p-8">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <h2 class="text-lg font-semibold text-slate-900">
                                    {{ __('messages.product_history') }}
                                </h2>
                                <a href="{{ route('localized.products.history', ['locale' => $locale, 'product' => $product->trans('slug') ?? $product->slug]) }}"
                                   class="inline-flex items-center gap-2 text-sm font-medium text-primary-600 hover:text-primary-700">
                                    <x-heroicon-o-clock class="h-4 w-4" />
                                    {{ __('product_page.view_full_history') }}
                                </a>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                        {{ __('frontend.total_changes') }}</p>
                                    <p class="mt-2 text-2xl font-semibold text-slate-900">
                                        {{ $product->getChangeCount(30) }}</p>
                                    <p class="text-xs text-slate-400">{{ __('frontend.last_30_days') }}</p>
                                </div>
                                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                        {{ __('frontend.price_changes') }}</p>
                                    <p class="mt-2 text-2xl font-semibold text-slate-900">
                                        {{ $product->getPriceChangeCount(30) }}</p>
                                    <p class="text-xs text-slate-400">{{ __('frontend.last_30_days') }}</p>
                                </div>
                                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                        {{ __('product_page.change_frequency_30') }}</p>
                                    <p class="mt-2 text-2xl font-semibold text-slate-900">
                                        {{ $product->getChangeFrequency(30) }}</p>
                                    <p class="text-xs text-slate-400">{{ __('product_page.avg_changes_30') }}</p>
                                </div>
                            </div>

                            @if ($recentHistories->isNotEmpty())
                                <ul class="space-y-3">
                                    @foreach ($recentHistories as $history)
                                        <li
                                            class="flex items-start justify-between rounded-2xl border border-slate-100 bg-slate-50/60 p-3 text-xs text-slate-600">
                                            <div class="flex items-center gap-2">
                                                @switch($history->action)
                                                    @case('price_changed')
                                                        <x-heroicon-o-currency-euro class="h-4 w-4 text-emerald-500" />
                                                    @break

                                                    @case('stock_updated')
                                                        <x-heroicon-o-cube class="h-4 w-4 text-sky-500" />
                                                    @break

                                                    @case('status_changed')
                                                        <x-heroicon-o-check-circle class="h-4 w-4 text-amber-500" />
                                                    @break

                                                    @default
                                                        <x-heroicon-o-pencil class="h-4 w-4 text-slate-400" />
                                                @endswitch
                                                <span
                                                      class="font-medium text-slate-700">{{ __('frontend.events.' . $history->action) }}</span>
                                            </div>
                                            <span
                                                  class="text-slate-400">{{ $history->created_at->diffForHumans() }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="space-y-6 p-6 lg:p-8">
                            <h2 class="text-lg font-semibold text-slate-900">
                                {{ __('translations.product_variants') }}</h2>
                            @if ($this->variantOptionGroups->isNotEmpty())
                                {{-- Render attribute-level variant selectors to mirror marketplace option matrices. --}}
                                <div class="space-y-5">
                                    @foreach ($this->variantOptionGroups as $group)
                                        <div class="space-y-2">
                                            <p class="text-sm font-semibold text-slate-800">{{ $group['name'] }}</p>
                                            <div class="flex flex-wrap gap-3">
                                                @foreach ($group['values'] as $value)
                                                    <div class="flex flex-col items-start">
                                                        <button
                                                            type="button"
                                                            @if ($value['primary_variant_id'] ?? null) wire:click="selectVariant({{ $value['primary_variant_id'] }})" @endif
                                                            class="flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-medium transition focus:outline-none focus-visible:ring focus-visible:ring-primary-400 {{ ($value['is_active'] ?? false) ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-slate-200 bg-white text-slate-600 hover:border-primary-300 hover:text-primary-600' }} {{ ! ($value['is_available'] ?? false) ? 'opacity-60' : '' }}"
                                                            aria-pressed="{{ ($value['is_active'] ?? false) ? 'true' : 'false' }}"
                                                            @disabled(! ($value['primary_variant_id'] ?? null))
                                                        >
                                                            @if ($value['hex_color'])
                                                                <span
                                                                    class="h-3 w-3 rounded-full border border-slate-200"
                                                                    style="background-color: {{ $value['hex_color'] }};"
                                                                    aria-hidden="true"></span>
                                                            @endif
                                                            <span>{{ $value['label'] }}</span>
                                                        </button>
                                                        @if ($value['price_hint'])
                                                            <span class="mt-1 text-[11px] text-slate-400">{{ $value['price_hint'] }}</span>
                                                        @endif
                                                        @if (! ($value['is_available'] ?? false))
                                                            <span class="mt-0.5 text-[11px] font-medium text-rose-500">{{ __('product_page.all_variants_options') }}</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            @if ($this->variantMatrix->isEmpty())
                                <p class="text-sm text-slate-600">{{ __('messages.detailed_description_coming_soon') }}</p>
                            @else
                                <div class="space-y-4">
                                    @foreach ($this->variantMatrix as $variant)
                                        <button
                                            type="button"
                                            wire:click="selectVariant({{ $variant['id'] }})"
                                            class="w-full flex flex-col gap-4 rounded-2xl border border-slate-100 bg-slate-50/70 p-5 sm:flex-row sm:items-center sm:justify-between text-left transition hover:border-slate-200 hover:bg-slate-50 focus:outline-none focus-visible:ring focus-visible:ring-primary-400 {{ ($variant['is_active'] ?? false) ? 'border-primary-300 bg-primary-50/50' : '' }} {{ ($variant['is_out_of_stock'] ?? false) ? 'opacity-70' : '' }}"
                                        >
                                            <div class="flex items-start gap-4">
                                                @if ($variant['thumbnail'])
                                                    <img src="{{ $variant['thumbnail'] }}"
                                                         alt="{{ $variant['name'] }}"
                                                         class="h-16 w-16 rounded-xl object-cover shadow-sm">
                                                @endif
                                                <div class="space-y-2">
                                                    <div>
                                                        <p class="text-base font-semibold text-slate-900">
                                                            {{ $variant['name'] }}</p>
                                                        @if ($variant['sku'])
                                                            <p class="text-xs text-slate-500">
                                                                {{ __('messages.sku') }}: {{ $variant['sku'] }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                    <dl class="flex flex-wrap gap-2 text-xs text-slate-600">
                                                        @foreach ($variant['attributes'] as $attribute)
                                                            <div
                                                                 class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 shadow-sm">
                                                                <span
                                                                      class="font-medium text-slate-700">{{ $attribute['attribute'] }}:</span>
                                                                <span>{{ $attribute['value'] }}</span>
                                                            </div>
                                                        @endforeach
                                                    </dl>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                @if ($variant['price'])
                                                    <p class="text-lg font-semibold text-primary-600">
                                                        {{ $variant['price'] }}</p>
                                                @endif
                                                @if ($variant['compare_price'])
                                                    <p class="text-xs text-slate-500 line-through">
                                                        {{ $variant['compare_price'] }}</p>
                                                @endif
                                                <p
                                                   class="mt-2 text-xs font-medium uppercase tracking-wide {{ ($variant['is_out_of_stock'] ?? false) ? 'text-red-500' : 'text-emerald-500' }}">
                                                    {{ ($variant['is_out_of_stock'] ?? false) ? __('product_page.single_configuration') : __('messages.product_in_stock') }}
                                                </p>
                                                <p class="text-xs text-slate-400">{{ __('product_page.available_quantity') }}:
                                                    {{ $variant['available_quantity'] }}</p>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </section>

                    @if ($product->documents && $product->documents->isNotEmpty())
                        <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                            <div class="space-y-6 p-6 lg:p-8">
                                <h2 class="text-lg font-semibold text-slate-900">
                                    {{ __('messages.admin_documents') }}</h2>
                                <ul class="space-y-3 text-sm text-slate-600">
                                    @foreach ($product->documents as $document)
                                        <li
                                            class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                            <div class="flex items-center gap-3">
                                                <x-heroicon-o-document-text class="h-5 w-5 text-primary-500" />
                                                <span class="font-medium text-slate-700">{{ $document->name }}</span>
                                            </div>
                                            <a href="{{ $document->url }}" target="_blank" rel="noopener"
                                               class="text-sm font-medium text-primary-600 hover:text-primary-700">
                                                {{ __('messages.view') }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </section>
                    @endif

                </div>

                <div class="lg:col-span-5 space-y-6">
                    <section class="rounded-3xl border border-slate-100 bg-white shadow-lg">
                        <div class="space-y-6 p-6 lg:p-8">
                            <div class="flex items-start justify-between gap-4">
                                <div class="space-y-1">
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                        {{ __('messages.product_brand') }}</p>
                                    <p class="text-lg font-semibold text-slate-900">
                                        {{ $this->brandLabel ?? $product->name }}</p>
                                </div>
                                @if ($this->pricingSummary['current'])
                                    <p class="text-3xl font-semibold text-primary-600">
                                        {{ \Illuminate\Support\Number::currency((float) $this->pricingSummary['current'], $this->pricingSummary['currency'], $locale) }}
                                    </p>
                                @endif
                            </div>
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 text-sm text-slate-600">
                                <p class="flex items-center gap-2 text-slate-700">
                                    <x-heroicon-o-shield-check class="h-5 w-5 text-primary-500" />
                                    {{ __('messages.footer_secure_payment') }}
                                </p>
                            </div>
                            <div class="variant-selector-card" wire:loading.class="opacity-50 pointer-events-none">
                                <livewire:product-variant-selector :product="$product" />
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="space-y-4 p-6 lg:p-8">
                            <h2 class="text-base font-semibold text-slate-900">{{ __('messages.details') }}
                            </h2>
                            <dl class="grid gap-4 sm:grid-cols-2">
                                @foreach ($this->productQuickFacts as $fact)
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                            {{ $fact['label'] }}</dt>
                                        <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $fact['value'] }}
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="space-y-4 p-6 lg:p-8">
                            <h2 class="text-base font-semibold text-slate-900">
                                {{ __('messages.Shipping') }}</h2>
                            <div class="space-y-4">
                                <div
                                     class="flex items-start gap-3 rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                    <x-untitledui-globe-05 class="h-6 w-6 text-slate-500" />
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">
                                            {{ __('product_page.international_delivery') }}</p>
                                        <p class="text-sm text-slate-600">
                                            {{ __('product_page.delivery_eta_2_weeks') }}</p>
                                    </div>
                                </div>
                                <div
                                     class="flex items-start gap-3 rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                    <x-untitledui-gift-02 class="h-6 w-6 text-slate-500" />
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">
                                            {{ __('product_page.loyalty_rewards') }}</p>
                                        <p class="text-sm text-slate-600">
                                            {{ __('product_page.loyalty_rewards_desc') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <livewire:components.product-request-form :product="$product" />

                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="space-y-4 p-6 lg:p-8">
                            <h2 class="text-base font-semibold text-slate-900">
                                {{ __('product_page.need_tailored_offer') }}</h2>
                            <p class="text-sm text-slate-600">{{ __('product_page.contact_us_description') }}</p>
                            <a href="{{ $contactUrl }}"
                               class="inline-flex items-center justify-center rounded-full bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                                <x-heroicon-o-phone class="mr-2 h-4 w-4" />
                                {{ __('product_page.need_tailored_offer') }}
                            </a>
                        </div>
                    </section>
                </div>
            </div>

            <div class="text-center">
                <a href="{{ route('localized.products.index', ['locale' => $locale]) }}"
                   class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-2 text-sm font-medium text-slate-600 hover:border-slate-300 hover:text-slate-900">
                    <x-heroicon-o-arrow-left class="h-4 w-4" />
                    {{ __('frontend.buttons.back_to_products') }}
                </a>
            </div>
        </x-container>
    </div>

    <livewire:components.related-products :product="$product" />

    @if ($product->brand)
        <div class="bg-slate-50">
            <livewire:components.advanced-related-products :product="$product" type="brand" :limit="4"
                                                           class="bg-slate-50" />
        </div>
    @endif

    @if ($product->categories->isNotEmpty())
        <livewire:components.advanced-related-products :product="$product" type="category" :limit="4" />
    @endif
</div>


@push('scripts')
    <script type="application/ld+json">{!! json_encode($this->productSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
