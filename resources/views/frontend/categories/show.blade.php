@extends('frontend.layouts.app')

@section('title', $category->getTranslatedSeoTitle())
@section('description', strip_tags((string) $category->getTranslatedDescription()))

@section('content')
    <div class="bg-gray-50 py-12">
        <div class="mx-auto max-w-7xl space-y-12 px-4 sm:px-6 lg:px-8">
            <div class="space-y-4">
                <nav class="text-sm text-gray-500" aria-label="{{ __('frontend.navigation.breadcrumbs') }}">
                    <ol class="flex flex-wrap items-center gap-2">
                        <li><a href="{{ route('home') }}" class="text-blue-600 hover:text-blue-700">{{ __('frontend.home', 'Home') }}</a></li>
                        <li>/</li>
                        <li><a href="{{ route('frontend.categories.index') }}" class="text-blue-600 hover:text-blue-700">{{ __('messages.categories') }}</a></li>
                        <li>/</li>
                        <li class="text-gray-700">{{ $category->name }}</li>
                    </ol>
                </nav>
                <div class="flex flex-col gap-6 rounded-3xl border border-gray-200 bg-white p-8 shadow-sm sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-3">
                        <span class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700">
                            <x-untitledui-tag class="h-4 w-4" />
                            {{ __('ui.category_spotlight') }}
                        </span>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">{{ $category->name }}</h1>
                        <p class="text-gray-600">{{ $category->description }}</p>
                        @if($category->parent)
                            <p class="text-sm text-gray-500">
                                {{ __('ui.part_of_parent', ['parent' => $category->parent->name]) }}
                            </p>
                        @endif
                    </div>
                    <div class="flex flex-col gap-3 rounded-3xl bg-indigo-600 p-6 text-sm text-white/80 sm:bg-white/10">
                        <div class="flex items-center justify-between">
                            <span>{{ __('ui.products_live') }}</span>
                            <span class="text-lg font-semibold text-white">{{ number_format($products->total()) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>{{ __('messages.subcategories') }}</span>
                            <span class="text-lg font-semibold text-white">{{ number_format($relatedCategories->count()) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>{{ __('ui.featured_brands') }}</span>
                            <span class="text-lg font-semibold text-white">{{ number_format($highlightedBrands->count()) }}</span>
                        </div>
                    </div>
                </div>
            </header>

            <section class="mt-10 space-y-8">
                <div class="flex flex-col gap-4 rounded-3xl border border-gray-200 bg-gray-50 p-6 sm:flex-row sm:items-center sm:justify-between">
                    <form method="get" class="flex flex-wrap items-center gap-3 text-sm font-medium text-gray-700">
                        <span class="text-gray-500">{{ __('ui.quick_filters') }}</span>
                        @foreach ($availableFilters as $key => $label)
                            <label class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-3 py-1">
                                <input type="radio" name="filter" value="{{ $key }}" @checked($activeFilter === $key) class="h-4 w-4 text-indigo-600" />
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                        <label class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-3 py-1">
                            <input type="radio" name="filter" value="" @checked(! $activeFilter) class="h-4 w-4 text-indigo-600" />
                            <span>{{ __('ui.all_products') }}</span>
                        </label>
                        <button type="submit" class="rounded-full bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow-sm hover:bg-indigo-700">{{ __('messages.apply') }}</button>
                    </form>

                    <form method="get" class="flex items-center gap-3 text-sm">
                        @foreach (request()->except('sort') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}" />
                        @endforeach
                        <label for="sort" class="text-gray-500">{{ __('messages.sort_by') }}</label>
                        <select id="sort" name="sort" class="rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 focus:border-indigo-500 focus:outline-none">
                            @foreach ($availableSorts as $key => $label)
                                <option value="{{ $key }}" @selected($activeSort === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="rounded-full border border-indigo-500 px-4 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-50">{{ __('messages.update') }}</button>
                    </form>
                </div>

                @include('frontend.products.partials.product-grid', ['products' => $products, 'emptyMessage' => __('ui.no_products_available_for_this_category_yet')])
            </section>

            <section class="mt-12 grid gap-8 lg:grid-cols-2">
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-semibold text-gray-900">{{ __('ui.explore_related_categories') }}</h2>
                    <ul class="mt-4 grid gap-3 text-sm text-gray-700 sm:grid-cols-2">
                        @forelse ($relatedCategories as $related)
                            <li class="rounded-2xl border border-gray-200 bg-gray-50 p-4 transition hover:border-indigo-500 hover:bg-white">
                                <a href="{{ route('frontend.categories.show', $related) }}" class="font-semibold text-gray-900 hover:text-indigo-600">{{ $related->name }}</a>
                                @if ($related->description)
                                    <p class="mt-2 text-xs text-gray-600">{!! str($related->description)->stripTags()->limit(100) !!}</p>
                                @endif
                            </li>
                        @empty
                            <li class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-500">{{ __('ui.additional_categories_will_appear_here_as_soon_as_they_are_available') }}</li>
                        @endforelse
                    </ul>
                </div>

                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-semibold text-gray-900">{{ __('ui.featured_brands_in_this_category') }}</h2>
                    <ul class="mt-4 space-y-3 text-sm text-gray-700">
                        @forelse ($highlightedBrands as $brand)
                            <li class="flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                                <a href="{{ route('frontend.brands.show', $brand) }}" class="font-semibold text-gray-900 hover:text-indigo-600">{{ $brand->name }}</a>
                                <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-600">{{ number_format($brand->published_products_count ?? $brand->products_count ?? 0) }}</span>
                            </li>
                        @empty
                            <li class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-500">{{ __('ui.brand_highlights_will_appear_as_soon_as_products_are_published') }}</li>
                        @endforelse
                    </ul>
                </div>
            </section>
        </div>
    </div>
@endsection
