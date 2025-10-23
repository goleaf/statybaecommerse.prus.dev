<x-layouts.base title="{{ __('Home') }}">
    <div class="max-w-7xl mx-auto px-4 py-10 space-y-12">
        <section>
            <h1 class="text-3xl font-semibold mb-4">{{ __('Featured products') }}</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @forelse ($featuredProducts as $product)
                    <div class="p-4 border border-gray-200 rounded-xl bg-white shadow-sm dark:bg-gray-900 dark:border-white/10">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $product->brand?->name }}</p>
                        <p class="mt-2 text-primary-600 font-semibold">{{ app_money_format($product->sale_price ?? $product->price ?? 0) }}</p>
                        <a class="mt-3 inline-flex items-center text-sm text-primary-700 hover:text-primary-800"
                           href="{{ route('frontend.products.show', $product) }}">{{ __('View product') }}</a>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">{{ __('No featured products available.') }}</p>
                @endforelse
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-semibold mb-4">{{ __('Latest arrivals') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @forelse ($latestProducts as $product)
                    <div class="p-4 rounded-xl border border-gray-200 bg-white dark:bg-gray-900 dark:border-white/10">
                        <h3 class="text-base font-semibold">{{ $product->name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $product->brand?->name }}</p>
                        <div class="mt-2 text-primary-600 font-medium">{{ app_money_format($product->sale_price ?? $product->price ?? 0) }}</div>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">{{ __('Products will appear here soon.') }}</p>
                @endforelse
            </div>
        </section>

        <section class="relative bg-gray-50 py-16 sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-16">
                    <div class="space-y-4 text-center">
                        <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                            {{ __('frontend/home.sections.featured.title') }}
                        </h2>
                        <p class="text-lg text-gray-600">
                            {{ __('frontend/home.sections.featured.subtitle') }}
                        </p>
                    </div>

                    @include('frontend.products.partials.product-grid', [
                        'products' => $featuredProducts,
                        'emptyMessage' => __('frontend/home.messages.no_featured_products'),
                    ])
                </div>
            </div>
        </section>

        <section class="relative bg-white py-16 sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-[1.2fr_1fr] lg:items-center">
                    <div class="space-y-8">
                        <div class="space-y-4">
                            <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                                {{ __('frontend/home.sections.catalogue.title') }}
                            </h2>
                            <p class="text-lg text-gray-600">
                                {{ __('frontend/home.sections.catalogue.subtitle') }}
                            </p>
                        </div>
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                                <div class="flex items-center gap-3">
                                    <x-untitledui-folder-open class="h-10 w-10 text-indigo-500" />
                                    <div>
                                        <h3 class="text-xl font-semibold text-gray-900">{{ __('frontend/home.sections.catalogue.cards.categories.title') }}</h3>
                                        <p class="mt-1 text-sm text-gray-600">{{ __('frontend/home.sections.catalogue.cards.categories.subtitle') }}</p>
                                    </div>
                                </div>
                                <ul class="mt-6 space-y-3 text-sm text-gray-700">
                                    @foreach ($topCategories->take(5) as $category)
                                        <li class="flex items-center justify-between">
                                            <a href="{{ route('frontend.categories.show', $category) }}" class="hover:text-indigo-600">{{ $category->name }}</a>
                                            <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-600">{{ number_format($category->published_products_count ?? 0) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                <a href="{{ route('frontend.categories.index') }}"
                                   class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                                    {{ __('frontend/home.sections.catalogue.cards.categories.link') }}
                                    <x-untitledui-arrow-narrow-right class="h-4 w-4" />
                                </a>
                            </div>
                            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                                <div class="flex items-center gap-3">
                                    <x-untitledui-briefcase class="h-10 w-10 text-rose-500" />
                                    <div>
                                        <h3 class="text-xl font-semibold text-gray-900">{{ __('frontend/home.sections.catalogue.cards.brands.title') }}</h3>
                                        <p class="mt-1 text-sm text-gray-600">{{ __('frontend/home.sections.catalogue.cards.brands.subtitle') }}</p>
                                    </div>
                                </div>
                                <ul class="mt-6 space-y-3 text-sm text-gray-700">
                                    @foreach ($highlightedBrands->take(5) as $brand)
                                        <li class="flex items-center justify-between">
                                            <a href="{{ route('frontend.brands.show', $brand) }}" class="hover:text-rose-600">{{ $brand->name }}</a>
                                            <span class="rounded-full bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-600">{{ number_format($brand->published_products_count ?? 0) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                <a href="{{ route('frontend.brands.index') }}"
                                   class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-rose-600 hover:text-rose-700">
                                    {{ __('frontend/home.sections.catalogue.cards.brands.link') }}
                                    <x-untitledui-arrow-narrow-right class="h-4 w-4" />
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-3xl border border-gray-200 bg-gray-50 p-6 shadow-inner">
                        <livewire:home.collections-showcase />
                    </div>
                </div>
            </div>
        </section>

        <section class="relative bg-slate-950 py-16 text-slate-100 sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-12">
                    <div class="space-y-4 text-center">
                        <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                            {{ __('frontend/home.sections.highlights.title') }}
                        </h2>
                        <p class="text-lg text-slate-300">
                            {{ __('frontend/home.sections.highlights.subtitle') }}
                        </p>
                    </div>

                    <div class="space-y-16">
                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-2xl font-semibold text-white">{{ __('frontend/home.sections.highlights.blocks.latest.title') }}</h3>
                                    <p class="text-sm text-slate-300">{{ __('frontend/home.sections.highlights.blocks.latest.subtitle') }}</p>
                                </div>
                                <a href="{{ route('frontend.products.index', ['sort' => 'latest']) }}" class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20">
                                    {{ __('frontend/home.sections.highlights.blocks.latest.link') }}
                                    <x-untitledui-arrow-up-right class="h-4 w-4" />
                                </a>
                            </div>
                            @include('frontend.products.partials.product-grid', [
                                'products' => $latestProducts,
                                'emptyMessage' => __('frontend/home.messages.no_latest_products'),
                            ])
                        </div>

                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-2xl font-semibold text-white">{{ __('frontend/home.sections.highlights.blocks.trending.title') }}</h3>
                                    <p class="text-sm text-slate-300">{{ __('frontend/home.sections.highlights.blocks.trending.subtitle') }}</p>
                                </div>
                                <a href="{{ route('frontend.products.index', ['sort' => 'bestsellers']) }}" class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20">
                                    {{ __('frontend/home.sections.highlights.blocks.trending.link') }}
                                    <x-untitledui-arrow-up-right class="h-4 w-4" />
                                </a>
                            </div>
                            @include('frontend.products.partials.product-grid', [
                                'products' => $trendingProducts,
                                'emptyMessage' => __('frontend/home.messages.no_trending_products'),
                            ])
                        </div>

                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-2xl font-semibold text-white">{{ __('frontend/home.sections.highlights.blocks.sale.title') }}</h3>
                                    <p class="text-sm text-slate-300">{{ __('frontend/home.sections.highlights.blocks.sale.subtitle') }}</p>
                                </div>
                                <a href="{{ route('frontend.products.index', ['filter' => 'sale']) }}" class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20">
                                    {{ __('frontend/home.sections.highlights.blocks.sale.link') }}
                                    <x-untitledui-arrow-up-right class="h-4 w-4" />
                                </a>
                            </div>
                            @include('frontend.products.partials.product-grid', [
                                'products' => $saleProducts,
                                'emptyMessage' => __('frontend/home.messages.no_sale_products'),
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="relative bg-white py-16 sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-[1.2fr_1fr] lg:items-center">
                    <div class="space-y-6">
                        <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                            {{ __('frontend/home.sections.discovery.title') }}
                        </h2>
                        <p class="text-lg text-gray-600">
                            {{ __('frontend/home.sections.discovery.subtitle') }}
                        </p>
                        <ul class="grid gap-4 sm:grid-cols-2">
                            <li class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                                <x-untitledui-bulb class="mt-1 h-6 w-6 text-amber-500" />
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ __('frontend/home.sections.discovery.items.recommendations.title') }}</p>
                                    <p class="mt-1 text-sm text-gray-600">{{ __('frontend/home.sections.discovery.items.recommendations.subtitle') }}</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                                <x-untitledui-shield-tick class="mt-1 h-6 w-6 text-emerald-500" />
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ __('frontend/home.sections.discovery.items.security.title') }}</p>
                                    <p class="mt-1 text-sm text-gray-600">{{ __('frontend/home.sections.discovery.items.security.subtitle') }}</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                                <x-untitledui-credit-card-check class="mt-1 h-6 w-6 text-blue-500" />
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ __('frontend/home.sections.discovery.items.payments.title') }}</p>
                                    <p class="mt-1 text-sm text-gray-600">{{ __('frontend/home.sections.discovery.items.payments.subtitle') }}</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                                <x-untitledui-truck class="mt-1 h-6 w-6 text-indigo-500" />
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ __('frontend/home.sections.discovery.items.delivery.title') }}</p>
                                    <p class="mt-1 text-sm text-gray-600">{{ __('frontend/home.sections.discovery.items.delivery.subtitle') }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="rounded-3xl border border-gray-200 bg-gray-50 p-6 shadow-inner">
                        <livewire:home.product-catalogue />
                    </div>
                </div>
            </div>
        </section>

        <section class="relative bg-gray-900 py-16 text-white sm:py-20">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-[1.2fr_1fr] lg:items-center">
                    <div class="space-y-6">
                        <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">
                            {{ __('frontend/home.sections.cta.title') }}
                        </h2>
                        <p class="text-lg text-gray-300">{{ __('frontend/home.sections.cta.subtitle') }}</p>
                        <div class="flex flex-wrap items-center gap-4">
                            <a href="{{ route('frontend.news.index') }}"
                               class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 text-base font-semibold text-gray-900 transition hover:bg-gray-200">
                                <x-untitledui-newsletter class="h-5 w-5" />
                                <span>{{ __('frontend/home.sections.cta.primary') }}</span>
                            </a>
                            <span class="text-sm text-gray-500">{{ trans_choice('{0}No products|{1}1 product|[2,*]:count products', $category->products_count, ['count' => $category->products_count]) }}</span>
                        </li>
                    @empty
                        <li class="text-gray-500 dark:text-gray-400">{{ __('No categories available yet.') }}</li>
                    @endforelse
                </ul>
            </div>
            <div>
                <h2 class="text-2xl font-semibold mb-4">{{ __('Brands customers love') }}</h2>
                <ul class="space-y-3">
                    @forelse ($popularBrands as $brand)
                        <li class="flex items-center justify-between">
                            <a href="{{ route('frontend.brands.show', $brand) }}" class="text-primary-700 hover:text-primary-800">
                                {{ $brand->name }}
                            </a>
                            <span class="text-sm text-gray-500">{{ trans_choice('{0}No products|{1}1 product|[2,*]:count products', $brand->products_count, ['count' => $brand->products_count]) }}</span>
                        </li>
                    @empty
                        <li class="text-gray-500 dark:text-gray-400">{{ __('No brands available yet.') }}</li>
                    @endforelse
                </ul>
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-semibold mb-4">{{ __('Active discounts') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @forelse ($activeDiscounts as $discount)
                    <article class="p-4 rounded-xl border border-dashed border-primary-200 bg-primary-50 dark:bg-primary-900/10">
                        <h3 class="text-lg font-semibold text-primary-900 dark:text-primary-200">{{ $discount->name }}</h3>
                        <p class="text-sm text-primary-700 dark:text-primary-300">{{ $discount->description }}</p>
                        <p class="mt-2 text-sm text-primary-600">{{ __('Value: :value', ['value' => $discount->value]) }}</p>
                    </article>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">{{ __('There are no active discounts right now.') }}</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.base>
