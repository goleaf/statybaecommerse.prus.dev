@php($logo = $brand->getFirstMediaUrl('logo'))

<x-meta
    :title="$brand->name . ' - ' . __('translations.brands')"
    :description="$brand->description ?? ''"
    :og-image="$logo ?: null"
    canonical="{{ url()->current() }}" />

<div class="py-6 lg:py-8" x-data="{ showFilters: false }">
    <x-container>
        <x-breadcrumbs :items="[
            ['label' => __('translations.brands'), 'url' => route('brands.index')],
            ['label' => $brand->name, 'url' => url()->current()],
        ]" />

        <header class="mt-4 mb-6 flex items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                @if($logo)
                    <img src="{{ $logo }}" alt="{{ $brand->name }}" class="h-14 w-14 rounded bg-white object-contain ring-1 ring-gray-200 dark:ring-gray-700" />
                @endif
                <div>
                    <h1 class="text-2xl md:text-3xl font-semibold text-gray-900 dark:text-white">{{ $brand->name }}</h1>
                    @if(!empty($brand->description))
                        <p class="mt-1 text-gray-600 dark:text-gray-300 line-clamp-2">{{ $brand->description }}</p>
                    @endif
                    @if(!empty($brand->website))
                        <p class="mt-2">
                            <a href="{{ $brand->website }}" rel="noopener" target="_blank" class="text-sm text-blue-600 hover:text-blue-700 underline">
                                {{ __('Visit website') }}
                            </a>
                        </p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-sm text-gray-600 dark:text-gray-300 hidden sm:block">
                    @if($products->total() > 0)
                        {{ $products->firstItem() }}–{{ $products->lastItem() }} {{ __('translations.of_total') }} {{ $products->total() }}
                    @else
                        0 {{ __('translations.products') ?? __('Products') }}
                    @endif
                </div>
                <select wire:model.live="sortBy"
                        class="rounded-md border-gray-300 bg-white text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <option value="latest">{{ __('translations.newest') }}</option>
                    <option value="name">{{ __('translations.name') }}</option>
                    <option value="oldest">{{ __('translations.oldest') }}</option>
                    <option value="price_asc">{{ __('translations.price') }} ↑</option>
                    <option value="price_desc">{{ __('translations.price') }} ↓</option>
                </select>
            </div>
        </header>

        @if ($products->count() > 0)
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($products as $product)
                    <x-product.card :product="$product" />
                @endforeach
            </div>

            <div class="mt-8 flex justify-center">
                <x-filament::pagination :paginator="$products" />
            </div>
        @else
            <div class="rounded-lg border border-dashed border-gray-300 p-12 text-center dark:border-gray-700">
                <div class="mx-auto mb-3 h-10 w-10 text-gray-400">
                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-4.35-4.35m1.1-4.4a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('translations.no_products_found') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('translations.try_different_search') }}</p>
                <div class="mt-6 flex items-center justify-center gap-3">
                    <a href="{{ route('brands.index') }}"
                       class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">{{ __('translations.browse_categories') ?? __('Back to brands') }}</a>
                    <a href="{{ route('products.index') }}"
                       class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">{{ __('translations.view_all_products') }}</a>
                </div>
            </div>
        @endif
    </x-container>
</div>

