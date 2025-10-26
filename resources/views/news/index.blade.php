@extends('components.layouts.base')

@section('title', __('news.title'))

@include('components.scripts.debounced-search-form')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ __('news.title') }}</h1>
        <p class="text-lg text-gray-600">{{ __('news.latest_news') }}</p>
    </div>

    <!-- Featured News Section -->
    @if($featuredNews->count() > 0)
    <section class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('news.featured_news') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($featuredNews as $featured)
            <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                @if($featured->images->where('is_featured', true)->first())
                <div class="aspect-w-16 aspect-h-9">
                    <img src="{{ $featured->images->where('is_featured', true)->first()->url }}" 
                         alt="{{ $featured->images->where('is_featured', true)->first()->alt_text }}"
                         class="w-full h-48 object-cover">
                </div>
                @endif
                <div class="p-6">
                    <div class="flex flex-wrap gap-2 mb-3">
                        @foreach($featured->categories as $category)
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                            {{ $category->name }}
                        </span>
                        @endforeach
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2 line-clamp-2">
                        <a href="{{ route('news.show', $featured->slug) }}" class="hover:text-blue-600">
                            {{ $featured->title }}
                        </a>
                    </h3>
                    <p class="text-gray-600 mb-4 line-clamp-3">{{ $featured->summary }}</p>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>{{ $featured->author_name }}</span>
                        <span>{{ $featured->published_at->format('Y-m-d') }}</span>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Filters Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form
            method="GET"
            action="{{ route('news.index') }}"
            class="space-y-4"
            x-data="debouncedSearchForm({
                initialQuery: @js($searchTerm),
                delay: 500,
                minLength: 2,
                maxLength: 120,
                autoSubmit: true,
                allowEmptyManualSubmit: true,
            })"
            @submit.prevent="manualSubmit()"
        >
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('news.search_news') }}
                    </label>
                    <input type="text"
                           id="search"
                           name="search"
                           x-ref="queryField"
                           x-model="term"
                           @input="handleInput()"
                           value="{{ $searchTerm }}"
                           placeholder="{{ __('news.search_placeholder') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Category Filter -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('news.filter_by_category') }}
                    </label>
                    <select id="category"
                            name="category"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="manualSubmit()">
                        <option value="">{{ __('news.all_categories') }}</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (string) $selectedCategory === (string) $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tag Filter -->
                <div>
                    <label for="tag" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('news.filter_by_tag') }}
                    </label>
                    <select id="tag"
                            name="tag"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="manualSubmit()">
                        <option value="">{{ __('news.all_tags') }}</option>
                        @foreach($tags as $tag)
                        <option value="{{ $tag->id }}" {{ (string) $selectedTag === (string) $tag->id ? 'selected' : '' }}>
                            {{ $tag->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Featured Filter -->
                <div class="flex items-end">
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="featured"
                               value="1"
                               {{ $featuredOnly ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                               @change="manualSubmit()">
                        <span class="ml-2 text-sm text-gray-700">{{ __('news.featured_news') }}</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    {{ __('news.search_news') }}
                </button>
            </div>
        </form>
    </div>

    <!-- News Grid -->
    @if($news->count() > 0)
    @php
        $hasMoreNews = $news->hasMorePages();
    @endphp
    <section
        data-infinite-scroll
        data-next-page-url="{{ $news->nextPageUrl() ? e($news->nextPageUrl()) : '' }}"
        data-infinite-scroll-context="news"
        class="space-y-8"
    >
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" data-infinite-scroll-items>
            @include('news.partials.grid-items', ['newsItems' => $news])
        </div>

        <div class="flex flex-col items-center gap-4" data-infinite-scroll-controls>
            <div class="flex items-center gap-3 text-sm text-gray-500" data-infinite-scroll-loader hidden>
                <span class="h-5 w-5 rounded-full border-2 border-blue-500 border-t-transparent animate-spin"></span>
                <span>{{ __('news.loading_more') }}</span>
            </div>

            <button
                type="button"
                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition disabled:opacity-50 disabled:cursor-not-allowed {{ $hasMoreNews ? '' : 'hidden' }}"
                data-infinite-scroll-trigger
            >
                {{ __('news.load_more') }}
            </button>

            <p class="text-sm text-gray-500 hidden" data-infinite-scroll-end>
                {{ __('news.end_of_results') }}
            </p>

            <div data-infinite-scroll-fallback class="w-full">
                <x-perfect-pagination :paginator="$news->appends(request()->query())" />
            </div>

            <div class="sr-only" aria-live="polite" data-infinite-scroll-status></div>
        </div>
    </section>
    @else
    <div class="rounded-lg border border-dashed border-blue-300 bg-blue-50/50 px-8 py-12 text-center dark:border-blue-900/60 dark:bg-gray-900">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 text-blue-600">
            <x-heroicon-o-newspaper class="h-7 w-7" />
        </div>
        <h3 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">{{ __('news.no_news_found') }}</h3>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('news.empty_state.description') }}</p>

        @if($categories->isNotEmpty())
            <div class="mt-6 flex flex-wrap justify-center gap-2">
                @foreach($categories->take(3) as $category)
                    <span class="rounded-full bg-white px-4 py-2 text-sm font-medium text-blue-700 shadow-sm dark:bg-gray-800 dark:text-blue-200">
                        {{ $category->name }}
                    </span>
                @endforeach
            </div>
        @endif

        <ul class="mt-6 space-y-3 text-sm text-left sm:mx-auto sm:max-w-xl sm:text-center">
            <li class="flex items-start gap-3 sm:justify-center">
                <span class="mt-1 h-2 w-2 flex-none rounded-full bg-blue-400"></span>
                <span>{{ __('news.empty_state.tip_keywords') }}</span>
            </li>
            <li class="flex items-start gap-3 sm:justify-center">
                <span class="mt-1 h-2 w-2 flex-none rounded-full bg-blue-400"></span>
                <span>{{ __('news.empty_state.tip_filters') }}</span>
            </li>
            <li class="flex items-start gap-3 sm:justify-center">
                <span class="mt-1 h-2 w-2 flex-none rounded-full bg-blue-400"></span>
                <span>{{ __('news.empty_state.tip_latest') }}</span>
            </li>
        </ul>

        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('news.index') }}" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                {{ __('news.empty_state.cta') }}
            </a>
            <a href="{{ route('frontend.news.index') }}" class="inline-flex items-center rounded-md border border-blue-200 px-4 py-2 text-sm font-medium text-blue-700 transition hover:border-blue-300 hover:bg-blue-50">
                {{ __('frontend.buttons.back_to_news') }}
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
