<div class="bg-gradient-to-b from-slate-50 via-white to-white" wire:loading.attr="aria-busy" aria-busy="false">
    @php
        $locale = app()->getLocale();
    @endphp
    @section('meta')
        <x-meta
                :title="$product->getTranslatedSeoTitle()"
                :description="$product->getTranslatedSeoDescription()"
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
                    'url' => route('frontend.products.index', []),
                ],
                [
                    'label' => $this->brandLabel,
                    'url' =>
                        $product->brand && function_exists('route') && Route::has('localized.brands.show')
                            ? route('localized.brands.show', ['slug' => $product->brand->trans('slug') ?? $product->brand->slug,
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
                                        {{ __('products.page.available_quantity') }}</p>
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
                            @if ($this->attributeFeatures->isEmpty())
                                <p class="text-sm text-slate-500">{{ __('products.page.features_empty') }}</p>
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
                                                @php
                                                    $featureValues = collect(
                                                        preg_split('/\s*,\s*/u', (string) ($feature['value'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: []
                                                    )
                                                        ->map(static fn (string $value): string => trim($value))
                                                        ->filter()
                                                        ->values();
                                                @endphp
                                                @if ($featureValues->count() > 1)
                                                    <div class="space-y-1">
                                                        @foreach ($featureValues as $featureValue)
                                                            <p class="text-sm leading-relaxed text-slate-600">
                                                                {{ $featureValue }}
                                                            </p>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <p class="text-sm text-slate-600">
                                                        {{ $featureValues->first() ?? $feature['value'] }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </section>

                    @php
                        $description = $product->trans('description') ?? $product->description;
                        $description = is_string($description) ? trim($description) : '';
                        $descriptionLooksLikeHtml = $description !== '' && $description !== strip_tags($description);
                    @endphp

                    @if ($description !== '')
                        <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                            <div class="space-y-6 p-6 lg:p-8">
                                <h2 class="text-lg font-semibold text-slate-900">
                                    {{ __('products.page.description') }}</h2>
                                <div class="prose prose-slate max-w-none text-slate-700">
                                    @if ($descriptionLooksLikeHtml)
                                        {!! $description !!}
                                    @else
                                        {!! nl2br(e($description)) !!}
                                    @endif
                                </div>
                            </div>
                        </section>
                    @endif

                    @if($product->trans('detailed_description') || $product->detailed_description)
                        <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                            <div class="space-y-6 p-6 lg:p-8">
                                <h2 class="text-lg font-semibold text-slate-900">
                                    {{ __('products.page.detailed_description') }}</h2>
                                <div class="prose prose-slate max-w-none text-slate-700">
                                    {!! $product->trans('detailed_description') ?? $product->detailed_description !!}
                                </div>
                            </div>
                        </section>
                    @endif

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
                            <div class="variant-selector-card" wire:loading.class="opacity-50 pointer-events-none">
                                <livewire:product-variant-selector :product="$product" />
                            </div>
                        </div>
                    </section>
                    <section class="rounded-3xl border border-slate-100 bg-white shadow-lg">
                        <div class="space-y-6 p-5 sm:p-6 lg:p-8"
                             wire:loading.class="pointer-events-none opacity-80"
                             wire:target="selectVariantOption,selectVariant,clearVariantSelection">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h2 class="text-lg font-semibold text-slate-900">
                                        {{ __('translations.product_variants') }}</h2>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ __('products.page.filtered_variants') }}</p>
                                </div>
                                <span wire:loading.flex
                                      wire:target="selectVariantOption,selectVariant,clearVariantSelection"
                                      class="hidden items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-600 shadow-sm">
                                    <svg class="h-3.5 w-3.5 animate-spin text-primary-600" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    <span class="sr-only">{{ __('translations.loading') }}</span>
                                </span>
                            </div>

                            @if ($this->variantMatrix->isEmpty())
                                <p class="text-sm text-slate-600">{{ __('messages.detailed_description_coming_soon') }}</p>
                            @else
                                @php
                                    $variantOptionGroups = $this->variantOptionGroups;
                                    $variantGroupsBySlug = $variantOptionGroups->keyBy('slug');
                                    $selectedColor = data_get($variantGroupsBySlug->get('color'), 'selected_value_label');
                                    $selectedSize = data_get($variantGroupsBySlug->get('size'), 'selected_value_label');
                                    $hasColorGroup = $variantGroupsBySlug->has('color');
                                    $hasSizeGroup = $variantGroupsBySlug->has('size');
                                    $isPrimarySelectionComplete = (! $hasColorGroup || filled($selectedColor)) && (! $hasSizeGroup || filled($selectedSize));
                                @endphp
                                <div class="space-y-5">
                                    @if ($hasColorGroup || $hasSizeGroup)
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:p-5">
                                            <div class="flex flex-wrap items-center gap-2.5">
                                                @if ($hasColorGroup)
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200">
                                                        <span>{{ __('products.attributes.color') }}:</span>
                                                        <span class="text-slate-900">
                                                            {{ $selectedColor ?: __('products.page.not_selected') }}
                                                        </span>
                                                    </span>
                                                @endif
                                                @if ($hasSizeGroup)
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200">
                                                        <span>{{ __('products.attributes.size') }}:</span>
                                                        <span class="text-slate-900">
                                                            {{ $selectedSize ?: __('products.page.not_selected') }}
                                                        </span>
                                                    </span>
                                                @endif
                                                <button
                                                    type="button"
                                                    wire:click="clearVariantSelection"
                                                    wire:loading.attr="disabled"
                                                    wire:target="clearVariantSelection"
                                                    class="ml-auto inline-flex min-h-10 w-full items-center justify-center rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-100 focus:outline-none focus-visible:ring focus-visible:ring-primary-400 sm:w-auto"
                                                >
                                                    {{ __('products.page.show_all_variants') }}
                                                </button>
                                            </div>
                                            @if (! $isPrimarySelectionComplete)
                                                <p class="mt-2 text-xs text-slate-500">
                                                    {{ __('products.page.variant_filters_incomplete') }}
                                                </p>
                                            @endif
                                        </div>
                                    @endif

                                    @if ($variantOptionGroups->isNotEmpty())
                                        <div class="space-y-4">
                                            @foreach ($variantOptionGroups as $group)
                                                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                                        <p class="text-sm font-semibold text-slate-900">{{ $group['name'] }}</p>
                                                        <div class="flex flex-wrap items-center justify-end gap-2">
                                                            @if (filled($group['selected_value_label'] ?? null))
                                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200">
                                                                    {{ __('products.page.selected_value', ['value' => $group['selected_value_label']]) }}
                                                                </span>
                                                            @endif
                                                            @if (filled($group['selected_value_key'] ?? null))
                                                                <button
                                                                    type="button"
                                                                    wire:click="clearVariantSelection('{{ $group['slug'] }}')"
                                                                    wire:loading.attr="disabled"
                                                                    wire:target="clearVariantSelection"
                                                                    class="inline-flex min-h-9 items-center rounded-lg border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-600 transition hover:border-slate-400 hover:text-slate-800 focus:outline-none focus-visible:ring focus-visible:ring-primary-400"
                                                                >
                                                                    {{ __('products.page.clear_selection') }}
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    @if (($group['presentation'] ?? 'chips') === 'compact_list')
                                                        <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                                                            @foreach ($group['values'] as $value)
                                                                <button
                                                                    type="button"
                                                                    wire:key="variant-option-compact-{{ $group['slug'] }}-{{ $value['key'] }}"
                                                                    @if ($value['primary_variant_id'] ?? null) wire:click="selectVariantOption('{{ $group['slug'] }}', '{{ $value['key'] }}', {{ $value['primary_variant_id'] }})" @endif
                                                                    wire:loading.attr="disabled"
                                                                    wire:target="selectVariantOption"
                                                                    class="min-h-11 rounded-xl border px-3 py-2 text-left text-xs font-semibold leading-tight transition duration-150 focus:outline-none focus-visible:ring focus-visible:ring-primary-400 sm:text-sm {{ ($value['is_active'] ?? false) ? 'border-slate-700 bg-slate-800 text-white shadow-sm' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-400 hover:bg-slate-50' }} {{ ! ($value['primary_variant_id'] ?? null) ? 'cursor-not-allowed opacity-50' : 'cursor-pointer' }}"
                                                                    aria-pressed="{{ ($value['is_active'] ?? false) ? 'true' : 'false' }}"
                                                                    @disabled(! ($value['primary_variant_id'] ?? null))
                                                                >
                                                                    <span class="line-clamp-2 block">{{ $value['label'] }}</span>
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="mt-3 flex flex-wrap gap-2">
                                                            @foreach ($group['values'] as $value)
                                                                <button
                                                                    type="button"
                                                                    wire:key="variant-option-chip-{{ $group['slug'] }}-{{ $value['key'] }}"
                                                                    @if ($value['primary_variant_id'] ?? null) wire:click="selectVariantOption('{{ $group['slug'] }}', '{{ $value['key'] }}', {{ $value['primary_variant_id'] }})" @endif
                                                                    wire:loading.attr="disabled"
                                                                    wire:target="selectVariantOption"
                                                                    class="inline-flex min-h-10 items-center gap-2 rounded-xl border px-3 py-2 text-left text-xs font-semibold leading-tight transition duration-150 focus:outline-none focus-visible:ring focus-visible:ring-primary-400 sm:text-sm {{ ($value['is_active'] ?? false) ? 'border-slate-700 bg-slate-800 text-white shadow-sm' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-400 hover:bg-slate-50' }} {{ ! ($value['primary_variant_id'] ?? null) ? 'cursor-not-allowed opacity-45' : 'cursor-pointer' }}"
                                                                    aria-pressed="{{ ($value['is_active'] ?? false) ? 'true' : 'false' }}"
                                                                    @disabled(! ($value['primary_variant_id'] ?? null))
                                                                >
                                                                    @if ($value['hex_color'])
                                                                        <span
                                                                            class="h-3.5 w-3.5 rounded-full border border-white/80 ring-1 ring-slate-300"
                                                                            style="background-color: {{ $value['hex_color'] }};"
                                                                            aria-hidden="true"></span>
                                                                    @endif
                                                                    <span class="line-clamp-2">{{ $value['label'] }}</span>
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @php($activeVariant = $this->activeVariantData)
                                    @if (is_array($activeVariant))
                                        <div
                                             class="rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-50 to-white p-4 shadow-sm sm:p-5 {{ ($activeVariant['is_out_of_stock'] ?? false) ? 'opacity-80' : '' }}">
                                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                                <div class="flex items-start gap-4">
                                                    @if ($activeVariant['thumbnail'])
                                                        <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                                                            <img src="{{ $activeVariant['thumbnail'] }}"
                                                                 alt="{{ $activeVariant['name'] }}"
                                                                 class="h-auto w-auto max-h-full max-w-full object-contain">
                                                        </div>
                                                    @endif
                                                    <div class="space-y-2">
                                                        <div>
                                                            <p class="text-[11px] font-semibold uppercase tracking-wide text-primary-600">
                                                                {{ __('products.page.selected_variant') }}
                                                            </p>
                                                            <p class="text-base font-semibold text-slate-900">
                                                                {{ $activeVariant['name'] }}
                                                            </p>
                                                            @if ($activeVariant['sku'])
                                                                <p class="text-xs text-slate-500">
                                                                    {{ __('messages.sku') }}: {{ $activeVariant['sku'] }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                        <dl class="flex flex-wrap gap-2 text-xs text-slate-600">
                                                            @foreach ($activeVariant['attributes'] as $attribute)
                                                                <div
                                                                     class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 shadow-sm ring-1 ring-slate-200">
                                                                    <span
                                                                          class="font-medium text-slate-700">{{ $attribute['attribute'] }}:</span>
                                                                    <span>{{ $attribute['value'] }}</span>
                                                                </div>
                                                            @endforeach
                                                        </dl>
                                                    </div>
                                                </div>
                                                <div class="text-left sm:text-right">
                                                    @if ($activeVariant['price'])
                                                        <p class="text-xl font-semibold text-primary-700">
                                                            {{ $activeVariant['price'] }}
                                                        </p>
                                                    @endif
                                                    <p
                                                       class="mt-1 text-xs font-semibold uppercase tracking-wide {{ ($activeVariant['is_out_of_stock'] ?? false) ? 'text-red-500' : 'text-emerald-600' }}">
                                                        {{ ($activeVariant['is_out_of_stock'] ?? false) ? __('translations.out_of_stock') : __('messages.product_in_stock') }}
                                                    </p>
                                                    <p class="text-xs text-slate-500">
                                                        {{ __('products.page.available_quantity') }}:
                                                        {{ $activeVariant['available_quantity'] }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @php($filteredVariants = $this->filteredVariantData)
                                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5"
                                         wire:loading.class="opacity-70"
                                         wire:target="selectVariantOption,selectVariant,clearVariantSelection">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <p class="text-sm font-semibold text-slate-800">
                                                {{ __('products.page.filtered_variants') }}
                                            </p>
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                                                {{ trans_choice('products.page.variants_count', $filteredVariants->count(), ['count' => $filteredVariants->count()]) }}
                                            </span>
                                        </div>

                                        @if ($filteredVariants->isEmpty())
                                            <p class="mt-3 text-sm text-slate-500">
                                                {{ __('products.page.no_variants_match_filters') }}
                                            </p>
                                        @else
                                            <div class="mt-3 grid grid-cols-1 gap-3">
                                                @foreach ($filteredVariants as $variant)
                                                    <button
                                                        type="button"
                                                        wire:key="variant-list-item-{{ $variant['id'] }}"
                                                        wire:click="selectVariant({{ $variant['id'] }})"
                                                        wire:loading.attr="disabled"
                                                        wire:target="selectVariant"
                                                        class="min-h-28 cursor-pointer rounded-xl border p-3 text-left transition duration-150 focus:outline-none focus-visible:ring focus-visible:ring-primary-400 {{ ((int) ($variant['id'] ?? 0) === (int) ($this->activeVariantId ?? 0)) ? 'border-slate-300 bg-slate-100 ring-1 ring-slate-200' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50' }}"
                                                    >
                                                        <p class="text-sm font-semibold text-slate-900">{{ $variant['name'] }}</p>
                                                        @if (filled($variant['sku']))
                                                            <p class="mt-1 text-xs text-slate-500">
                                                                {{ __('messages.sku') }}: {{ $variant['sku'] }}
                                                            </p>
                                                        @endif
                                                        @if (filled($variant['attribute_summary']))
                                                            <p class="mt-1 line-clamp-2 text-xs text-slate-600">
                                                                {{ $variant['attribute_summary'] }}
                                                            </p>
                                                        @endif
                                                        <div class="mt-3 flex items-start justify-between gap-3 text-xs">
                                                            <span class="font-semibold text-primary-700">
                                                                {{ $variant['price'] ?: '—' }}
                                                            </span>
                                                            <span class="text-right {{ ($variant['is_out_of_stock'] ?? false) ? 'text-red-500' : 'text-emerald-600' }}">
                                                                {{ ($variant['is_out_of_stock'] ?? false) ? __('translations.out_of_stock') : __('messages.product_in_stock') }}
                                                            </span>
                                                        </div>
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </section>

                    @if (! empty($this->productQuickFacts))
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
                    @endif

                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="space-y-4 p-6 lg:p-8">
                            <h2 class="text-base font-semibold text-slate-900">
                                {{ __('messages.shipping') }}</h2>
                            <div class="space-y-4">
                                <div
                                     class="flex items-start gap-3 rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                    <x-untitledui-globe-05 class="h-6 w-6 text-slate-500" />
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">
                                            {{ __('products.page.international_delivery') }}</p>

                                    </div>
                                </div>
                                <div
                                     class="flex items-start gap-3 rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                    <x-untitledui-gift-02 class="h-6 w-6 text-slate-500" />
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">
                                            {{ __('products.page.loyalty_rewards') }}</p>
                                        <p class="text-sm text-slate-600">
                                            {{ __('products.page.loyalty_rewards_desc') }}</p>
                                    </div>
                                </div>
                                <div
                                     class="flex items-start gap-3 rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                    <x-heroicon-o-shield-check class="h-6 w-6 text-primary-500" />
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">
                                            {{ __('messages.footer_secure_payment') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <livewire:components.product-request-form :product="$product" />

                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="space-y-4 p-6 lg:p-8">
                            <h2 class="text-base font-semibold text-slate-900">
                                {{ __('products.page.need_tailored_offer') }}</h2>
                            <p class="text-sm text-slate-600">{{ __('products.page.contact_us_description') }}</p>
                            <a href="{{ $this->contactUrl }}"
                               class="inline-flex items-center justify-center rounded-full bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                                <x-heroicon-o-phone class="mr-2 h-4 w-4" />
                                {{ __('products.page.need_tailored_offer') }}
                            </a>
                        </div>
                    </section>
                </div>
            </div>

            <div class="text-center">
                <a href="{{ route('frontend.products.index', []) }}"
                   class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-2 text-sm font-medium text-slate-600 hover:border-slate-300 hover:text-slate-900">
                    <x-heroicon-o-arrow-left class="h-4 w-4" />
                    {{ __('frontend.buttons.back_to_products') }}
                </a>
            </div>
        </x-container>
    </div>

    <livewire:components.related-products :product="$product" />
</div>


@push('scripts')
    <script type="application/ld+json">{!! json_encode($this->productSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

