@extends('frontend.layouts.app')

@section('title', $category->name)

@section('content')
    <div class="container mx-auto px-4">
        <nav class="mb-6 text-sm text-gray-500 dark:text-gray-400" aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center gap-1">
                <li><a href="{{ route('home') }}" class="hover:text-blue-600 dark:hover:text-blue-300">{{ __('frontend.navigation.home') }}</a></li>
                <li aria-hidden="true" class="px-1">/</li>
                <li><a href="{{ route('frontend.categories.index') }}" class="hover:text-blue-600 dark:hover:text-blue-300">{{ __('frontend.navigation.categories') }}</a></li>
                <li aria-hidden="true" class="px-1">/</li>
                <li class="font-semibold text-gray-900 dark:text-white">{{ $category->name }}</li>
            </ol>
        </nav>

        <div class="mb-8 rounded-2xl bg-white/80 p-6 shadow-sm ring-1 ring-gray-100 dark:bg-slate-900/70 dark:ring-slate-800">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-3">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $category->name }}</h1>
                    @if ($category->description)
                        <div class="prose max-w-none text-gray-600 dark:prose-invert dark:text-gray-300">
                            {!! $category->description !!}
                        </div>
                    @else
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            {{ __('frontend.categories.show.description_fallback') }}
                        </p>
                    @endif
                </div>
                <div class="rounded-xl bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:bg-blue-900/40 dark:text-blue-200">
                    {{ trans_choice('frontend.categories.show.product_count', $products->total(), ['count' => $products->total()]) }}
                </div>
            </div>
        </div>

        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('frontend.categories.show.available_products') }}</h2>
            </div>
            <form method="GET" action="{{ url()->current() }}" class="grid w-full gap-4 sm:grid-cols-2 lg:flex lg:w-auto lg:items-end lg:gap-4">
                <div class="flex flex-col">
                    <label for="q" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('frontend.categories.show.search_label') }}</label>
                    <input type="text" id="q" name="q" value="{{ $filters['search'] ?? '' }}"
                           class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                           placeholder="{{ __('frontend.categories.show.search_placeholder') }}">
                </div>
                <div class="flex flex-col">
                    <label for="brand" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('frontend.categories.show.brand_label') }}</label>
                    <select id="brand" name="brand" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                        <option value="">{{ __('frontend.categories.show.all_brands') }}</option>
                        @foreach ($availableBrands as $brand)
                            <option value="{{ $brand->slug }}" @selected(($filters['brands'][0] ?? null) === $brand->slug)>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col">
                    <label for="sort" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('frontend.categories.show.sort_by') }}</label>
                    <select id="sort" name="sort" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                        <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>{{ __('frontend.categories.show.sort.latest') }}</option>
                        <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>{{ __('frontend.categories.show.sort.price_low_high') }}</option>
                        <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>{{ __('frontend.categories.show.sort.price_high_low') }}</option>
                        <option value="name_asc" @selected(($filters['sort'] ?? '') === 'name_asc')>{{ __('frontend.categories.show.sort.name_a_z') }}</option>
                        <option value="name_desc" @selected(($filters['sort'] ?? '') === 'name_desc')>{{ __('frontend.categories.show.sort.name_z_a') }}</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-5 py-2.5 font-semibold text-white shadow hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500">
                        {{ __('frontend.categories.show.update_results') }}
                    </button>
                </div>
            </form>
        </div>

        @if ($category->children->isNotEmpty())
            <div class="mb-10 rounded-2xl bg-white/60 p-6 ring-1 ring-gray-100 dark:bg-slate-900/50 dark:ring-slate-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('frontend.categories.show.related_subcategories') }}</h3>
                <div class="mt-4 flex flex-wrap gap-3">
                    @foreach ($category->children as $child)
                        <a href="{{ route('frontend.categories.show', $child) }}" class="rounded-full border border-blue-200 px-4 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50 dark:border-blue-900 dark:text-blue-300 dark:hover:bg-blue-900/40">
                            {{ $child->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @include('products.partials.grid', ['products' => $products])
    </div>
@endsection
