<div class="bg-gradient-to-b from-slate-50 via-white to-white" wire:loading.attr="aria-busy" aria-busy="false">
    @section('meta')
        @php
            $ogImage =
                $product->getFirstMediaUrl(config('media.storage.collection_name'), 'large') ?:
                $product->getFirstMediaUrl(config('media.storage.collection_name'));
        @endphp
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
                    'url' => route('products.index', ['locale' => app()->getLocale()]),
                ],
                [
                    'label' => $product->brand?->trans('name') ?? $product->brand?->name,
                    'url' =>
                        $product->brand && function_exists('route') && Route::has('brands.show')
                            ? route('brands.show', [
                                'locale' => app()->getLocale(),
                                'brand' => $product->brand->trans('slug') ?? $product->brand->slug,
                            ])
                            : null,
                ],
                ['label' => $product->trans('name') ?? $product->name],
            ]" aria-label="{{ __('frontend.navigation.breadcrumbs') }}" />

            @php
                $brandLabel = $product->brand?->trans('name') ?? $product->brand?->name;
                $categoryLabels = $product->categories
                    ->map(fn($category) => $category->trans('name') ?? $category->name)
                    ->filter()
                    ->values();
                $averageRating = round((float) ($product->average_rating ?? 0), 1);
                $reviewCount = (int) ($product->reviews_count ?? 0);
                $priceData = $product->getPrice();
                $currentCurrency = function_exists('current_currency') ? current_currency() : null;
                $currentPrice = $priceData?->value?->amount ?? $product->price;
                $comparePrice = $priceData?->compare?->amount ?? $product->compare_price;
                $discountPercent = $priceData?->percentage ?? null;
                $shortDescription = $product->trans('short_description') ?? $product->short_description;
                $recentHistories = $this->recentHistories;
                $contactUrl = Route::has('contact')
                    ? route('contact', ['locale' => app()->getLocale()])
                    : 'mailto:' . (config('mail.from.address') ?? 'info@example.com');
            @endphp

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
                                @if ($brandLabel)
                                    <span
                                          class="inline-flex items-center rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700">
                                        {{ $brandLabel }}
                                    </span>
                                @endif
                                @foreach ($categoryLabels as $categoryName)
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
                                    <div class="flex items-center gap-1">
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($averageRating >= $i - 0.25)
                                                <x-heroicon-s-star class="h-4 w-4 text-amber-500" />
                                            @else
                                                <x-heroicon-o-star class="h-4 w-4 text-slate-200" />
                                            @endif
                                        @endfor
                                        <span
                                              class="ml-2 font-semibold text-slate-700">{{ number_format($averageRating, 1) }}</span>
                                    </div>
                                    <span class="text-slate-300">•</span>
                                    <span class="font-medium text-slate-600">{{ $reviewCount }}
                                        {{ __('translations.reviews') }}</span>
                                    @if ($product->sku)
                                        <span class="text-slate-300">•</span>
                                        <span class="text-slate-500">{{ __('translations.sku') }}:
                                            {{ $product->sku }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    @if ($currentPrice)
                                        <p class="text-4xl font-semibold text-primary-600">
                                            {{ \Illuminate\Support\Number::currency((float) $currentPrice, $currentCurrency, app()->getLocale()) }}
                                        </p>
                                    @endif
                                    @if ($comparePrice && $currentPrice && $comparePrice > $currentPrice)
                                        <p class="flex items-center gap-2 text-sm text-slate-500">
                                            <span class="line-through">
                                                {{ \Illuminate\Support\Number::currency((float) $comparePrice, $currentCurrency, app()->getLocale()) }}
                                            </span>
                                            @if ($discountPercent)
                                                <span
                                                      class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-600">
                                                    -{{ number_format($discountPercent, 0) }}%
                                                </span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="sm:text-right">
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                        {{ __('translations.available') }}</p>
                                    <p class="text-lg font-semibold text-slate-900">
                                        {{ $product->availableQuantity() }}
                                    </p>
                                </div>
                            </div>

                            @if ($shortDescription)
                                <p class="text-base leading-relaxed text-slate-600">
                                    {{ $shortDescription }}
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
                                    {{ __('Features') }}</span>
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
                                'label' => __('frontend.products.height'),
                                'value' => $product->height
                                    ? \Illuminate\Support\Number::format((float) $product->height) .
                                        ' ' .
                                        ($product->height_unit?->value ?? 'cm')
                                    : null,
                            ],
                            [
                                'label' => __('frontend.products.width'),
                                'value' => $product->width
                                    ? \Illuminate\Support\Number::format((float) $product->width) .
                                        ' ' .
                                        ($product->width_unit?->value ?? 'cm')
                                    : null,
                            ],
                            [
                                'label' => __('frontend.products.depth'),
                                'value' => $product->length
                                    ? \Illuminate\Support\Number::format((float) $product->length) .
                                        ' ' .
                                        ($product->depth_unit?->value ?? 'cm')
                                    : null,
                            ],
                            [
                                'label' => __('translations.weight'),
                                'value' => $this->formatMeasurement(
                                    $product->weight,
                                    $product->weight_unit?->value ?? null,
                                ),
                            ],
                            [
                                'label' => __('Minimum quantity'),
                                'value' => $product->getMinimumQuantity() > 1 ? $product->getMinimumQuantity() : null,
                            ],
                        ];
                    @endphp

                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="space-y-6 p-6 lg:p-8">
                            <h2 class="text-lg font-semibold text-slate-900">{{ __('product_page.technical_details') }}
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
                                    {{ __('product_page.change_history') }}
                                </h2>
                                <a href="{{ route('localized.products.history', ['locale' => app()->getLocale(), 'product' => $product->trans('slug') ?? $product->slug]) }}"
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
                                {{ __('product_page.all_variants_options') }}</h2>
                            @if ($this->variantMatrix->isEmpty())
                                <p class="text-sm text-slate-600">{{ __('product_page.single_configuration') }}</p>
                            @else
                                <div class="space-y-4">
                                    @foreach ($this->variantMatrix as $variant)
                                        <div
                                             class="flex flex-col gap-4 rounded-2xl border border-slate-100 bg-slate-50/70 p-5 sm:flex-row sm:items-center sm:justify-between">
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
                                                                {{ __('translations.sku') }}: {{ $variant['sku'] }}
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
                                                   class="mt-2 text-xs font-medium uppercase tracking-wide {{ $variant['is_out_of_stock'] ? 'text-red-500' : 'text-emerald-500' }}">
                                                    {{ $variant['is_out_of_stock'] ? __('translations.out_of_stock') : __('translations.available') }}
                                                </p>
                                                <p class="text-xs text-slate-400">{{ __('translations.available') }}:
                                                    {{ $variant['available_quantity'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </section>

                    @if ($product->documents && $product->documents->isNotEmpty())
                        <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                            <div class="space-y-6 p-6 lg:p-8">
                                <h2 class="text-lg font-semibold text-slate-900">
                                    {{ __('product_page.downloads_guides') }}</h2>
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
                                                {{ __('translations.download') }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </section>
                    @endif

                    @if ((bool) (config('app-features.features.review') ?? true))
                        <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                            <div class="space-y-6 p-6 lg:p-8">
                                <h2 class="text-lg font-semibold text-slate-900">
                                    {{ __('product_page.customer_feedback') }}</h2>
                                <livewire:components.product.reviews :productId="$product->id" />
                                <livewire:components.product.review-form :productId="$product->id" />
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
                                        {{ __('translations.brand') }}</p>
                                    <p class="text-lg font-semibold text-slate-900">
                                        {{ $brandLabel ?? __('product_page.unknown_brand') }}</p>
                                </div>
                                @if ($currentPrice)
                                    <p class="text-3xl font-semibold text-primary-600">
                                        {{ \Illuminate\Support\Number::currency((float) $currentPrice, $currentCurrency, app()->getLocale()) }}
                                    </p>
                                @endif
                            </div>
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 text-sm text-slate-600">
                                <p class="flex items-center gap-2 text-slate-700">
                                    <x-heroicon-o-shield-check class="h-5 w-5 text-primary-500" />
                                    {{ __('product_page.quality_guarantee') }}
                                </p>
                            </div>

                            <!-- MOCK: Konfigūruojami atributai (varžtas) -->
                            <div x-data="{size:'M5',length:'30 mm',head:'Cilindrinė',drive:'Hex / Allen',material:'A2',finish:'Be dangos',pitch:'Smulkus',pack:'10 vnt.',qty:1}"
                                 class="space-y-5 rounded-2xl border border-slate-100 bg-white p-4">
                                <h3 class="text-sm font-semibold text-slate-900">Konfigūruojami atributai (maketas)</h3>
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400 mb-2">Srieginis dydis</p>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" @click="size='M4'" :class="size==='M4'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-1.5 rounded-full border text-sm transition">M4</button>
                                            <button type="button" @click="size='M5'" :class="size==='M5'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-1.5 rounded-full border text-sm transition">M5</button>
                                            <button type="button" @click="size='M6'" :class="size==='M6'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-1.5 rounded-full border text-sm transition">M6</button>
                                            <button type="button" disabled class="px-3 py-1.5 rounded-full border border-slate-200 text-sm text-slate-400 line-through cursor-not-allowed">M8</button>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400 mb-2">Ilgis</p>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" @click="length='10 mm'" :class="length==='10 mm'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-1.5 rounded-md border text-sm transition">10 mm</button>
                                            <button type="button" @click="length='20 mm'" :class="length==='20 mm'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-1.5 rounded-md border text-sm transition">20 mm</button>
                                            <button type="button" @click="length='30 mm'" :class="length==='30 mm'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-1.5 rounded-md border text-sm transition">30 mm</button>
                                            <button type="button" @click="length='40 mm'" :class="length==='40 mm'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-1.5 rounded-md border text-sm transition">40 mm</button>
                                            <button type="button" @click="length='50 mm'" :class="length==='50 mm'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-1.5 rounded-md border text-sm transition">50 mm</button>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400 mb-2">Galvutės tipas</p>
                                        <div class="grid grid-cols-3 gap-2">
                                            <button type="button" @click="head='Cilindrinė'" :class="head==='Cilindrinė'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-2 rounded-xl border text-sm transition">Cilindrinė</button>
                                            <button type="button" @click="head='Plokščia'" :class="head==='Plokščia'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-2 rounded-xl border text-sm transition">Plokščia</button>
                                            <button type="button" @click="head='Įleista'" :class="head==='Įleista'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-2 rounded-xl border text-sm transition">Įleista</button>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400 mb-2">Įpjova / sukimo tipas</p>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" @click="drive='Hex / Allen'" :class="drive==='Hex / Allen'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-1.5 rounded-full border text-sm transition">Hex / Allen</button>
                                            <button type="button" @click="drive='Phillips (PH2)'" :class="drive==='Phillips (PH2)'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-1.5 rounded-full border text-sm transition">Phillips (PH2)</button>
                                            <button type="button" @click="drive='Torx (T25)'" :class="drive==='Torx (T25)'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-1.5 rounded-full border text-sm transition">Torx (T25)</button>
                                        </div>
                                    </div>
                                    <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-wide text-slate-400 mb-2">Medžiaga</p>
                                            <div class="flex flex-wrap gap-2">
                                                <button type="button" @click="material='A2'" :class="material==='A2'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-1.5 rounded-md border text-sm transition">A2</button>
                                                <button type="button" @click="material='A4'" :class="material==='A4'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-1.5 rounded-md border text-sm transition">A4</button>
                                                <button type="button" @click="material='8.8'" :class="material==='8.8'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-1.5 rounded-md border text-sm transition">8.8</button>
                                                <button type="button" @click="material='10.9'" :class="material==='10.9'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-1.5 rounded-md border text-sm transition">10.9</button>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-wide text-slate-400 mb-2">Paviršiaus danga</p>
                                            <div class="flex flex-wrap gap-2">
                                                <button type="button" @click="finish='Be dangos'" :class="finish==='Be dangos'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-1.5 rounded-md border text-sm transition">Be dangos</button>
                                                <button type="button" @click="finish='Cinkuotas'" :class="finish==='Cinkuotas'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-1.5 rounded-md border text-sm transition">Cinkuotas</button>
                                                <button type="button" @click="finish='Juodintas'" :class="finish==='Juodintas'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-1.5 rounded-md border text-sm transition">Juodintas</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-wide text-slate-400 mb-2">Žingsnis (pitch)</p>
                                            <div class="inline-flex rounded-xl border border-slate-200 bg-white p-1">
                                                <button type="button" @click="pitch='Standartinis'" :class="pitch==='Standartinis'?'rounded-lg text-sm font-semibold text-primary-700 bg-primary-50 border border-primary-300 shadow-sm px-3 py-1.5':'rounded-lg text-sm text-slate-700 hover:bg-slate-50 px-3 py-1.5'">Standartinis</button>
                                                <button type="button" @click="pitch='Smulkus'" :class="pitch==='Smulkus'?'rounded-lg text-sm font-semibold text-primary-700 bg-primary-50 border border-primary-300 shadow-sm px-3 py-1.5':'rounded-lg text-sm text-slate-700 hover:bg-slate-50 px-3 py-1.5'">Smulkus</button>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-xs font medium uppercase tracking-wide text-slate-400 mb-2">Pakuotė</p>
                                            <div class="flex flex-wrap gap-2">
                                                <button type="button" @click="pack='1 vnt.'" :class="pack==='1 vnt.'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-1.5 rounded-full border text-sm transition">1 vnt.</button>
                                                <button type="button" @click="pack='10 vnt.'" :class="pack==='10 vnt.'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-1.5 rounded-full border text-sm transition">10 vnt.</button>
                                                <button type="button" @click="pack='100 vnt.'" :class="pack==='100 vnt.'?'border-primary-300 bg-primary-50 text-primary-700 font-semibold shadow-sm':'border-slate-200 text-slate-700 hover-border-primary-300 hover:bg-primary-50 hover:text-primary-700'" class="px-3 py-1.5 rounded-full border text-sm transition">100 vnt.</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 pt-1">
                                    <div class="flex items-center rounded-xl border border-slate-200">
                                        <button type="button" @click="qty = Math.max(1, qty - 1)" class="px-3 py-2 text-slate-600 hover:bg-slate-50">−</button>
                                        <input type="number" x-model.number="qty" min="1" class="w-16 border-0 text-center text-sm focus:ring-0" />
                                        <button type="button" @click="qty = qty + 1" class="px-3 py-2 text-slate-600 hover:bg-slate-50">＋</button>
                                    </div>
                                    <button type="button" class="flex-1 rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">Pridėti į krepšelį (maketas)</button>
                                </div>
                                <p class="text-xs text-slate-500">Pasirinkta: <span x-text="size"></span>, <span x-text="length"></span>, <span x-text="head"></span>, <span x-text="drive"></span>, <span x-text="material"></span>, <span x-text="finish"></span>, <span x-text="pitch"></span>, <span x-text="pack"></span>. Kiekis: <span x-text="qty"></span>. (Maketas)</p>
                            </div>

                            <!-- MOCK: Import-style single product options (Pavadinimas, Kiekis pakuotėje, Matmenys) -->
                            <div
                                 x-data="{
                                     rows: [
                                         { qty: 1000, dim: '3.5 x 30 mm' },
                                         { qty: 1000, dim: '3.5 x 35 mm' },
                                         { qty: 1000, dim: '3.5 x 40 mm' },
                                         { qty: 500, dim: '3.5 x 50 mm' },
                                         { qty: 1000, dim: '4 x 30 mm' },
                                         { qty: 1000, dim: '4 x 35 mm' },
                                         { qty: 1000, dim: '4 x 40 mm' },
                                         { qty: 500, dim: '4 x 50 mm' },
                                         { qty: 200, dim: '4 x 60 mm' },
                                         { qty: 200, dim: '4 x 70 mm' },
                                         { qty: 200, dim: '4 x 80 mm' },
                                         { qty: 200, dim: '5 x 40 mm' },
                                         { qty: 200, dim: '5 x 50 mm' },
                                         { qty: 200, dim: '5 x 60 mm' },
                                         { qty: 200, dim: '5 x 70 mm' },
                                         { qty: 200, dim: '5 x 80 mm' },
                                         { qty: 200, dim: '5 x 100 mm' },
                                         { qty: 200, dim: '5 x 120 mm' },
                                         { qty: 100, dim: '6 x 100 mm' },
                                         { qty: 100, dim: '6 x 120 mm' },
                                         { qty: 100, dim: '6 x 140 mm' },
                                         { qty: 100, dim: '6 x 200 mm' },
                                         { qty: 100, dim: '6 x 220 mm' },
                                         { qty: 100, dim: '6 x 240 mm' },
                                         { qty: 100, dim: '6 x 260 mm' },
                                         { qty: 100, dim: '6 x 28 mm' },
                                         { qty: 100, dim: '6 x 300 mm' },
                                         { qty: 50, dim: '8 x 80 mm' },
                                         { qty: 50, dim: '8 x 100 mm' },
                                         { qty: 50, dim: '8 x 120 mm' },
                                         { qty: 50, dim: '8 x 140 mm' },
                                         { qty: 50, dim: '8 x 160 mm' },
                                         { qty: 50, dim: '8 x 180 mm' },
                                         { qty: 50, dim: '8 x 200 mm' },
                                         { qty: 50, dim: '8 x 220 mm' },
                                         { qty: 50, dim: '8 x 240 mm' },
                                         { qty: 50, dim: '8 x 260 mm' },
                                         { qty: 50, dim: '8 x 280 mm' },
                                         { qty: 50, dim: '8x 300 mm' },
                                         { qty: 50, dim: '8x 320 mm' },
                                         { qty: 50, dim: '8 x 340 mm' },
                                         { qty: 50, dim: '8 x 360 mm' },
                                         { qty: 50, dim: '8 x 380 mm' },
                                         { qty: 50, dim: '8 x 400 mm' },
                                         { qty: 25, dim: '8x 420 mm' },
                                         { qty: 50, dim: '8 x 420 mm' },
                                         { qty: 25, dim: '8 x 440 mm' },
                                         { qty: 50, dim: '8 x 440 mm' },
                                         { qty: 25, dim: '8 x 460 mm' },
                                         { qty: 50, dim: '8 x 460 mm' },
                                         { qty: 25, dim: '8x 480 mm' },
                                         { qty: 50, dim: '8 x 480 mm' },
                                         { qty: 25, dim: '8 x 500 mm' },
                                         { qty: 50, dim: '8 x 500 mm' },
                                         { qty: 25, dim: '8 x 550 mm' },
                                         { qty: 50, dim: '8 x 500 mm' },
                                     ],
                                     dims: [],
                                     packs: [],
                                     selectedDim: '',
                                     selectedPack: null,
                                     qty: 1,
                                     init() {
                                         this.dims = [...new Set(this.rows.map(r => r.dim))];
                                         this.selectedDim = this.dims[0] ?? '';
                                         this.updatePacks();
                                     },
                                     updatePacks() {
                                         this.packs = this.rows.filter(r => r.dim === this.selectedDim).map(r => r.qty);
                                         this.packs = [...new Set(this.packs)].sort((a,b)=>a-b);
                                         if (!this.packs.includes(this.selectedPack)) this.selectedPack = this.packs[0] ?? null;
                                     },
                                 }"
                                 x-init="init()"
                                 class="space-y-3 rounded-2xl border border-slate-100 bg-white p-4">
                                <h3 class="text-sm font-semibold text-slate-900">Produkto pasirenkami matmenys (maketas)</h3>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                    <div>
                                        <label class="block text-xs font-medium uppercase tracking-wide text-slate-400 mb-1">Matmenys</label>
                                        <select x-model="selectedDim" @change="updatePacks()" class="w-full rounded-lg border-slate-200 text-sm">
                                            <template x-for="d in dims" :key="d">
                                                <option :value="d" x-text="d"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium uppercase tracking-wide text-slate-400 mb-1">Pakuotės dydis</label>
                                        <select x-model.number="selectedPack" class="w-full rounded-lg border-slate-200 text-sm">
                                            <template x-for="p in packs" :key="p">
                                                <option :value="p" x-text="p + ' vnt.'"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="flex items-end justify-start gap-2">
                                        <div class="flex items-center rounded-xl border border-slate-200">
                                            <button type="button" @click="qty = Math.max(1, qty - 1)" class="px-3 py-2 text-slate-600 hover:bg-slate-50">−</button>
                                            <input type="number" x-model.number="qty" min="1" class="w-16 border-0 text-center text-sm focus:ring-0" />
                                            <button type="button" @click="qty = qty + 1" class="px-3 py-2 text-slate-600 hover:bg-slate-50">＋</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs text-slate-500">Pasirinkta: <span class="font-semibold" x-text="selectedDim"></span>, pakuotė: <span class="font-semibold" x-text="(selectedPack||0) + ' vnt.'"></span>, kiekis: <span class="font-semibold" x-text="qty"></span>. (Maketas)</div>
                            </div>

                            <div class="variant-selector-card" wire:loading.class="opacity-50 pointer-events-none">
                                <livewire:components.variants-selector :product="$product" />
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="space-y-4 p-6 lg:p-8">
                            <h2 class="text-base font-semibold text-slate-900">{{ __('product_page.quick_facts') }}
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
                                {{ __('product_page.shipping_service') }}</h2>
                            <div class="space-y-4">
                                <div
                                     class="flex items-start gap-3 rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                    <x-untitledui-globe-05 class="h-6 w-6 text-slate-500" />
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">
                                            {{ __('frontend.products.international_delivery') }}</p>
                                        <p class="text-sm text-slate-600">
                                            {{ __('frontend.products.delivery_eta_2_weeks') }}</p>
                                    </div>
                                </div>
                                <div
                                     class="flex items-start gap-3 rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                    <x-untitledui-gift-02 class="h-6 w-6 text-slate-500" />
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">
                                            {{ __('frontend.products.loyalty_rewards') }}</p>
                                        <p class="text-sm text-slate-600">
                                            {{ __('frontend.products.loyalty_rewards_desc') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="space-y-4 p-6 lg:p-8">
                            <h2 class="text-base font-semibold text-slate-900">
                                {{ __('product_page.need_tailored_offer') }}</h2>
                            <p class="text-sm text-slate-600">{{ __('product_page.tailored_offer_desc') }}</p>
                            <a href="{{ $contactUrl }}"
                               class="inline-flex items-center justify-center rounded-full bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                                <x-heroicon-o-phone class="mr-2 h-4 w-4" />
                                {{ __('translations.contact_us') }}
                            </a>
                        </div>
                    </section>
                </div>
            </div>

            <div class="text-center">
                <a href="{{ route('products.index', ['locale' => app()->getLocale()]) }}"
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
    @php
        $price = $product->getPrice();
        $image =
            $product->getFirstMediaUrl(config('media.storage.collection_name'), 'large') ?:
            $product->getFirstMediaUrl(config('media.storage.collection_name'));
        $brandName = $product->brand?->trans('name') ?? $product->brand?->name;
        $productSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->trans('name') ?? $product->name,
            'image' => $image ? [$image] : [],
            'description' => Str::limit(strip_tags($product->trans('description') ?? $product->description), 300),
        ];
        if ($brandName) {
            $productSchema['brand'] = [
                '@type' => 'Brand',
                'name' => $brandName,
            ];
        }
        if ($price) {
            $productSchema['offers'] = [
                '@type' => 'Offer',
                'priceCurrency' => current_currency(),
                'price' => number_format($price->value->amount, 2, '.', ''),
                'availability' => 'https://schema.org/' . ($product->isPublished() ? 'InStock' : 'OutOfStock'),
                'url' => url()->current(),
            ];
        }
        $recentReviews = $this->recentApprovedReviewsLimited;
        $reviewsSchema = null;
        if ($recentReviews->isNotEmpty()) {
            $reviewsSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'itemListElement' => $recentReviews
                    ->map(function ($r) {
                        return [
                            '@type' => 'Review',
                            'name' => $r->title,
                            'reviewBody' => Str::limit(strip_tags($r->content), 300),
                            'reviewRating' => [
                                '@type' => 'Rating',
                                'ratingValue' => (int) $r->rating,
                                'bestRating' => '5',
                            ],
                            'datePublished' => optional($r->created_at)->toDateString(),
                        ];
                    })
                    ->toArray(),
            ];
        }
    @endphp
    <script type="application/ld+json">{!! json_encode($productSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
    @if ($reviewsSchema)
        <script type="application/ld+json">{!! json_encode($reviewsSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
    @endif
@endpush
