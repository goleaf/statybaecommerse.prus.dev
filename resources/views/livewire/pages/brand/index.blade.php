@section('meta')
    <x-meta
        :title="__('frontend/brands.meta.title') . ' - ' . config('app.name')"
        :description="__('frontend/brands.meta.description')"
        canonical="{{ url()->current() }}" />
@endsection

@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
    $paginator = $this->brands;
    $totalBrands = $paginator->total();
    $totalProducts = $paginator->sum('products_count');
    $activeFilterCount = collect([
        filled($search ?? ''),
        ($sortBy ?? 'name') !== 'name',
    ])->filter()->count();
    $featuredBrands = $paginator->where('is_featured', true);
@endphp

<div class="bg-slate-50 pb-16">
    <x-shared.page-header
        :title="__('frontend/brands.hero.title')"
        :description="__('frontend/brands.hero.description')"
        icon="untitledui-globe-05"
        icon-color="text-blue-600"
        :breadcrumbs="[
            ['title' => __('frontend.navigation.home'), 'url' => route('localized.home', ['locale' => app()->getLocale()])],
            ['title' => __('shared.brands')],
        ]"
        background="bg-white"
    >
        <x-slot name="actions">
            <x-shared.button
                :href="route('localized.collections.index', ['locale' => app()->getLocale()])"
                variant="primary"
                size="sm"
                icon="untitledui-arrow-narrow-right"
                icon-position="right"
            >
                {{ __('frontend/brands.hero.cta') }}
            </x-shared.button>
        </x-slot>
    </x-shared.page-header>

    <x-container class="space-y-10 pt-10">
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">{{ __('frontend/brands.stats.brands.caption') }}</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($totalBrands) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">{{ __('frontend/brands.stats.products.caption') }}</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($totalProducts) }}</p>
            </div>
            <div class="rounded-2xl border border-blue-100 bg-blue-50 p-6 shadow-sm">
                <p class="text-sm font-medium text-blue-700">{{ __('frontend/brands.stats.promise.label') }}</p>
                <p class="mt-2 text-3xl font-semibold text-blue-900">{{ __('frontend/brands.stats.promise.caption') }}</p>
            </div>
        </div>

        <div class="grid gap-10 lg:grid-cols-[300px,minmax(0,1fr)]">
            <aside class="space-y-6">
                <x-shared.filter-sidebar
                    :title="__('frontend/brands.filters.title')"
                    :description="__('frontend/brands.filters.description')"
                >
                    <form wire:submit.prevent class="space-y-6">
                        <div class="space-y-5">
                            {{ $this->form }}
                        </div>
                    </form>

                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        @if($activeFilterCount > 0)
                            <span class="font-semibold text-slate-900">
                                {{ trans_choice('frontend/brands.filters.status.some', $activeFilterCount, ['count' => $activeFilterCount]) }}
                            </span>
                            <span class="mt-1 block text-xs text-slate-500">
                                {{ __('frontend/brands.filters.status.some_hint') }}
                            </span>
                        @else
                            <span class="font-semibold text-slate-900">
                                {{ __('frontend/brands.filters.status.none') }}
                            </span>
                            <span class="mt-1 block text-xs text-slate-500">
                                {{ __('frontend/brands.filters.status.none_hint') }}
                            </span>
                        @endif
                    </div>

                    <x-slot name="footer">
                        <div class="flex items-center justify-between gap-3">
                            <x-shared.button
                                type="button"
                                variant="secondary"
                                size="sm"
                                wire:click="clearFilters"
                                icon="untitledui-x"
                            >
                                {{ __('shared.clear_filters') }}
                            </x-shared.button>

                            <span class="text-xs text-slate-500">
                                {{ __('frontend/brands.filters.sync_notice') }}
                            </span>
                        </div>
                    </x-slot>

                    <x-slot name="actions">
                        <div class="grid gap-2">
                            <button
                                type="button"
                                wire:click="$set('sortBy', 'featured')"
                                class="flex items-center justify-between rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-blue-300 hover:text-blue-700"
                            >
                                <span>{{ __('frontend/brands.filters.quick.featured') }}</span>
                                <x-untitledui-star-07 class="h-4 w-4 text-blue-500" />
                            </button>
                            <button
                                type="button"
                                wire:click="$set('sortBy', 'products_count')"
                                class="flex items-center justify-between rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-blue-300 hover:text-blue-700"
                            >
                                <span>{{ __('frontend/brands.filters.quick.products') }}</span>
                                <x-untitledui-collection class="h-4 w-4 text-blue-500" />
                            </button>
                        </div>
                    </x-slot>
                </x-shared.filter-sidebar>
            </aside>

            <div class="space-y-10">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-2xl font-semibold text-slate-900">{{ __('frontend/brands.list.title') }}</h2>
                            <p class="text-sm text-slate-600">
                                {{ __('frontend/brands.list.description') }}
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-xs font-medium text-slate-600">
                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1">
                                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                {{ trans_choice('frontend/brands.list.badges.brands', $totalBrands, ['count' => number_format($totalBrands)]) }}
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                {{ trans_choice('frontend/brands.list.badges.products', $totalProducts, ['count' => number_format($totalProducts)]) }}
                            </span>
                        </div>
                    </div>
                </div>

                @if($paginator->count() > 0)
                    @if($featuredBrands->count() > 0)
                        <section class="space-y-6">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 class="text-xl font-semibold text-slate-900">{{ __('frontend/brands.list.featured.title') }}</h3>
                                    <p class="text-sm text-slate-600">{{ __('frontend/brands.list.featured.subtitle') }}</p>
                                </div>
                                <div class="text-sm text-slate-500">
                                    {{ trans_choice('frontend/brands.list.featured.count', $featuredBrands->count(), ['count' => number_format($featuredBrands->count())]) }}
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach($featuredBrands->take(6) as $brand)
                                    <x-shared.card class="flex flex-col overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                                        <div class="relative flex h-44 items-center justify-center bg-blue-50">
                                            @if($brand->getFirstMediaUrl('logo'))
                                                <img
                                                    src="{{ $brand->getFirstMediaUrl('logo') }}"
                                                    alt="{{ $brand->name }}"
                                                    loading="lazy"
                                                    class="max-h-24 object-contain"
                                                />
                                            @else
                                                <span class="text-2xl font-semibold text-blue-700">
                                                    {{ mb_strtoupper(mb_substr($brand->name ?? '', 0, 2)) }}
                                                </span>
                                            @endif

                                            <div class="absolute left-4 top-4">
                                                <x-shared.badge variant="primary" size="sm" class="bg-blue-600 text-white shadow-sm">
                                                    {{ __('frontend/brands.list.badges.featured') }}
                                                </x-shared.badge>
                                            </div>
                                        </div>

                                        <div class="space-y-4 p-6">
                                            <div class="space-y-1">
                                                <h4 class="text-lg font-semibold text-slate-900">{{ $brand->name }}</h4>
                                                <p class="text-sm text-slate-600 line-clamp-3">
                                                    {{ \Illuminate\Support\Str::limit($brand->description ?? '', 110) }}
                                                </p>
                                            </div>

                                            <div class="flex items-center justify-between text-sm text-slate-600">
                                                <span>{{ trans_choice('frontend/brands.list.badges.products', $brand->products_count, ['count' => number_format($brand->products_count)]) }}</span>
                                                <a href="{{ route('localized.brands.show', ['locale' => app()->getLocale(), 'slug' => $brand->slug]) }}"
                                                   class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700">
                                                    {{ __('frontend/brands.list.visit') }}
                                                    <x-untitledui-arrow-narrow-right class="h-4 w-4" />
                                                </a>
                                            </div>
                                        </div>
                                    </x-shared.card>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <section class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach($paginator as $brand)
                                <x-shared.card class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                                    <div class="flex h-40 items-center justify-center bg-slate-50">
                                        @if($brand->getFirstMediaUrl('logo'))
                                            <img
                                                src="{{ $brand->getFirstMediaUrl('logo') }}"
                                                alt="{{ $brand->name }}"
                                                loading="lazy"
                                                class="max-h-20 object-contain"
                                            />
                                        @else
                                            <div class="flex h-full w-full items-center justify-center" aria-hidden="true">
                                                <div class="text-center">
                                                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-200 text-base font-semibold text-slate-600">
                                                        {{ mb_strtoupper(mb_substr($brand->name ?? '', 0, 2)) }}
                                                    </span>
                                                    <p class="mt-2 text-xs text-slate-500">
                                                        {{ __('frontend/brands.list.fallback_logo', ['name' => $brand->name]) }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="space-y-4 p-6">
                                        <div class="space-y-1">
                                            <h4 class="text-lg font-semibold text-slate-900">{{ $brand->name }}</h4>
                                            <p class="text-sm text-slate-600 line-clamp-3">
                                                {{ \Illuminate\Support\Str::limit($brand->description ?? '', 120) }}
                                            </p>
                                        </div>

                                        <div class="flex items-center justify-between text-sm text-slate-600">
                                            <span>{{ trans_choice('frontend/brands.list.badges.products', $brand->products_count, ['count' => number_format($brand->products_count)]) }}</span>
                                            <a href="{{ route('localized.brands.show', ['locale' => app()->getLocale(), 'slug' => $brand->slug]) }}"
                                               class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700">
                                                {{ __('frontend/brands.list.visit') }}
                                                <x-untitledui-arrow-narrow-right class="h-4 w-4" />
                                            </a>
                                        </div>
                                    </div>
                                </x-shared.card>
                            @endforeach
                        </div>

                        <x-shared.pagination :paginator="$paginator" />
                    </section>
                @else
                    <x-shared.empty-state
                        icon="untitledui-info-circle"
                        :title="__('frontend/brands.list.empty.title')"
                        :description="__('frontend/brands.list.empty.description')"
                    />
                @endif
            </div>
        </div>
    </x-container>
</div>
