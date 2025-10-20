@extends('frontend.layouts.app')

@section('title', __('frontend/home.meta.title'))
@section('description', __('frontend/home.meta.description'))
@section('meta_description', __('frontend/home.meta.description'))

@section('content')
    <div class="relative overflow-hidden bg-white">
        <section class="relative isolate">
            <div class="absolute inset-0 -z-10 bg-gradient-to-br from-blue-50 via-white to-indigo-100"></div>
            <div class="mx-auto flex max-w-7xl flex-col gap-12 px-4 pb-16 pt-12 sm:gap-16 sm:px-6 lg:flex-row lg:items-center lg:px-8 lg:pt-20">
                <div class="flex-1 space-y-8">
                    <div class="inline-flex items-center gap-3 rounded-full border border-blue-100 bg-white px-4 py-1 text-sm font-medium text-blue-600 shadow-sm">
                        <x-untitledui-sparkles class="h-4 w-4" />
                        <span>{{ __('frontend/home.hero.eyebrow') }}</span>
                    </div>

                    <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl lg:text-6xl">
                        {{ __('frontend/home.hero.title') }}
                    </h1>

                    <p class="text-lg leading-8 text-gray-600 sm:max-w-xl">
                        {{ __('frontend/home.hero.subtitle') }}
                    </p>

                    <div class="flex flex-wrap items-center gap-4">
                        <a href="{{ route('frontend.products.index') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-full bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-lg transition hover:bg-blue-700">
                            <x-untitledui-shopping-bag class="h-5 w-5" />
                            <span>{{ __('frontend/home.hero.cta_primary') }}</span>
                        </a>
                        <a href="{{ route('frontend.products.index', ['sort' => 'latest']) }}"
                           class="inline-flex items-center justify-center gap-2 rounded-full border border-blue-200 bg-white px-6 py-3 text-base font-semibold text-blue-700 transition hover:bg-blue-50">
                            <x-untitledui-trending-up class="h-5 w-5" />
                            <span>{{ __('frontend/home.hero.cta_secondary') }}</span>
                        </a>
                    </div>

                    <dl class="grid grid-cols-2 gap-6 sm:grid-cols-4">
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 text-center shadow-sm">
                            <dt class="text-sm font-medium text-gray-500">{{ __('frontend/home.stats.products.label') }}</dt>
                            <dd class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($stats['products_count']) }}</dd>
                            <dd class="mt-1 text-xs text-gray-500">{{ __('frontend/home.stats.products.caption') }}</dd>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 text-center shadow-sm">
                            <dt class="text-sm font-medium text-gray-500">{{ __('frontend/home.stats.categories.label') }}</dt>
                            <dd class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($stats['categories_count']) }}</dd>
                            <dd class="mt-1 text-xs text-gray-500">{{ __('frontend/home.stats.categories.caption') }}</dd>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 text-center shadow-sm">
                            <dt class="text-sm font-medium text-gray-500">{{ __('frontend/home.stats.brands.label') }}</dt>
                            <dd class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($stats['brands_count']) }}</dd>
                            <dd class="mt-1 text-xs text-gray-500">{{ __('frontend/home.stats.brands.caption') }}</dd>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 text-center shadow-sm">
                            <dt class="text-sm font-medium text-gray-500">{{ __('frontend/home.stats.reviews.label') }}</dt>
                            <dd class="mt-2 text-2xl font-semibold text-gray-900">
                                {{ number_format($stats['reviews_count']) }}
                            </dd>
                            <dd class="mt-1 text-xs text-gray-500">
                                {{ __('frontend/home.stats.reviews.caption', ['rating' => number_format($stats['avg_rating'], 1)]) }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="relative flex-1 rounded-[2.5rem] border border-blue-100 bg-gradient-to-br from-indigo-500 via-purple-500 to-blue-500 p-1 shadow-xl">
                    <div class="rounded-[2.35rem] bg-white p-6">
                        <div class="grid gap-4">
                            <div class="rounded-3xl bg-gradient-to-br from-slate-900 to-slate-700 p-6 text-white shadow-lg">
                                <p class="text-sm uppercase tracking-wide text-white/70">{{ __('frontend/home.hero.featured_card.badge') }}</p>
                                <p class="mt-3 text-2xl font-semibold leading-snug">
                                    {{ __('frontend/home.hero.featured_card.title') }}
                                </p>
                                <p class="mt-3 text-sm text-white/80">
                                    {{ __('frontend/home.hero.featured_card.subtitle') }}
                                </p>
                                <a href="{{ route('frontend.products.index', ['filter' => 'featured']) }}"
                                   class="mt-6 inline-flex items-center gap-2 text-sm font-medium text-white transition hover:text-blue-100">
                                    <span>{{ __('frontend/home.hero.featured_card.link') }}</span>
                                    <x-untitledui-arrow-up-right class="h-4 w-4" />
                                </a>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="rounded-3xl border border-gray-100 bg-white p-5 text-gray-900 shadow-sm">
                                    <p class="text-xs font-medium uppercase tracking-wide text-indigo-500">{{ __('frontend/home.hero.secondary_cards.new.badge') }}</p>
                                    <p class="mt-2 text-lg font-semibold leading-tight">{{ __('frontend/home.hero.secondary_cards.new.title') }}</p>
                                    <p class="mt-2 text-sm text-gray-500">{{ __('frontend/home.hero.secondary_cards.new.subtitle') }}</p>
                                    <a href="{{ route('frontend.products.index', ['sort' => 'latest']) }}"
                                       class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                                        {{ __('frontend/home.hero.secondary_cards.new.link') }}
                                        <x-untitledui-arrow-narrow-right class="h-4 w-4" />
                                    </a>
                                </div>
                                <div class="rounded-3xl border border-gray-100 bg-white p-5 text-gray-900 shadow-sm">
                                    <p class="text-xs font-medium uppercase tracking-wide text-rose-500">{{ __('frontend/home.hero.secondary_cards.sale.badge') }}</p>
                                    <p class="mt-2 text-lg font-semibold leading-tight">{{ __('frontend/home.hero.secondary_cards.sale.title') }}</p>
                                    <p class="mt-2 text-sm text-gray-500">{{ __('frontend/home.hero.secondary_cards.sale.subtitle') }}</p>
                                    <a href="{{ route('frontend.products.index', ['filter' => 'sale']) }}"
                                       class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-rose-600 hover:text-rose-700">
                                        {{ __('frontend/home.hero.secondary_cards.sale.link') }}
                                        <x-untitledui-arrow-narrow-right class="h-4 w-4" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="relative bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <livewire:home-slider />
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

                    @include('frontend.catalogue.product-grid', ['products' => $featuredProducts, 'columns' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4'])
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
                                <div class="flex items-center justify-between">
                                    <h3 class="text-xl font-semibold text-gray-900">{{ __('frontend/home.sections.catalogue.lists.categories.title') }}</h3>
                                    <a href="{{ route('frontend.categories.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">{{ __('frontend/home.sections.catalogue.lists.categories.link') }}</a>
                                </div>
                                <p class="mt-2 text-sm text-gray-600">{{ __('frontend/home.sections.catalogue.lists.categories.subtitle') }}</p>
                                <ul class="mt-4 space-y-3">
                                    @forelse($popularCategories as $popularCategory)
                                        <li class="flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ $popularCategory->name }}</p>
                                                @if($popularCategory->visible_products_count ?? false)
                                                    <p class="text-xs text-gray-500">{{ __('frontend/home.sections.catalogue.lists.categories.item_count', ['count' => $popularCategory->visible_products_count]) }}</p>
                                                @endif
                                            </div>
                                            <x-untitledui-arrow-up-right class="h-4 w-4 text-indigo-500" />
                                        </li>
                                    @empty
                                        <li class="rounded-2xl border border-dashed border-gray-200 bg-white px-4 py-6 text-sm text-gray-500">
                                            {{ __('frontend/home.sections.catalogue.lists.categories.empty') }}
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-xl font-semibold text-gray-900">{{ __('frontend/home.sections.catalogue.lists.brands.title') }}</h3>
                                    <a href="{{ route('frontend.brands.index') }}" class="text-sm font-semibold text-rose-600 hover:text-rose-700">{{ __('frontend/home.sections.catalogue.lists.brands.link') }}</a>
                                </div>
                                <p class="mt-2 text-sm text-gray-600">{{ __('frontend/home.sections.catalogue.lists.brands.subtitle') }}</p>
                                <ul class="mt-4 space-y-3">
                                    @forelse($topBrands as $topBrand)
                                        <li class="flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ $topBrand->name }}</p>
                                                @if($topBrand->visible_products_count ?? false)
                                                    <p class="text-xs text-gray-500">{{ __('frontend/home.sections.catalogue.lists.brands.item_count', ['count' => $topBrand->visible_products_count]) }}</p>
                                                @endif
                                            </div>
                                            <x-untitledui-arrow-up-right class="h-4 w-4 text-rose-500" />
                                        </li>
                                    @empty
                                        <li class="rounded-2xl border border-dashed border-gray-200 bg-white px-4 py-6 text-sm text-gray-500">
                                            {{ __('frontend/home.sections.catalogue.lists.brands.empty') }}
                                        </li>
                                    @endforelse
                                </ul>
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

                    <div class="space-y-12">
                        <div class="space-y-4">
                            <h3 class="text-xl font-semibold text-white">{{ __('frontend/home.sections.highlights.latest.title') }}</h3>
                            @include('frontend.catalogue.product-grid', ['products' => $latestProducts, 'columns' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4', 'emptyMessage' => __('frontend/home.sections.highlights.latest.empty')])
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach($topBrands as $brand)
                                <article class="rounded-2xl border border-white/10 bg-white/10 p-5 backdrop-blur-sm">
                                    <h3 class="text-lg font-semibold text-white">{{ $brand->name }}</h3>
                                    <p class="mt-2 text-sm text-slate-200">{{ \Illuminate\Support\Str::limit(strip_tags($brand->description ?? __('frontend/home.sections.highlights.brands.fallback_description')), 90) }}</p>
                                    <a href="{{ route('frontend.brands.show', $brand) }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-emerald-200 hover:text-white">
                                        {{ __('frontend/home.sections.highlights.brands.cta') }}
                                        <x-untitledui-arrow-up-right class="h-4 w-4" />
                                    </a>
                                </article>
                            @endforeach
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
                            <a href="{{ route('frontend.contact.index') }}"
                               class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-transparent px-6 py-3 text-base font-semibold text-white transition hover:bg-white/10">
                                <x-untitledui-chat-bubble class="h-5 w-5" />
                                <span>{{ __('frontend/home.sections.cta.secondary') }}</span>
                            </a>
                        </div>
                    </div>
                    <div class="space-y-4 rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl backdrop-blur">
                        <p class="text-sm uppercase tracking-wide text-white/70">{{ __('frontend/home.sections.cta.review_badge') }}</p>
                        <p class="text-4xl font-semibold">{{ number_format($stats['avg_rating'], 1) }}<span class="text-lg text-white/70">/5</span></p>
                        <p class="text-sm text-white/80">
                            {{ __('frontend/home.sections.cta.review_copy') }}
                        </p>
                        <div class="flex items-center gap-1 text-amber-300">
                            @for ($i = 0; $i < 5; $i++)
                                <x-untitledui-star class="h-5 w-5" />
                            @endfor
                        </div>
                        <p class="text-xs text-white/60">{{ __('frontend/home.sections.cta.review_caption', ['count' => number_format($stats['reviews_count'])]) }}</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
