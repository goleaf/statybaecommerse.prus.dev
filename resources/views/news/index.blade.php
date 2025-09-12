@extends('layouts.app')

@section('title', __('news.title'))

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
        <form method="GET" action="{{ route('news.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('news.search_news') }}
                    </label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}"
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
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">{{ __('news.all_categories') }}</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
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
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">{{ __('news.all_tags') }}</option>
                        @foreach($tags as $tag)
                        <option value="{{ $tag->id }}" {{ request('tag') == $tag->id ? 'selected' : '' }}>
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
                               {{ request('featured') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
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
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($news as $article)
        <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
            @if($article->images->where('is_featured', true)->first())
            <div class="aspect-w-16 aspect-h-9">
                <img src="{{ $article->images->where('is_featured', true)->first()->url }}" 
                     alt="{{ $article->images->where('is_featured', true)->first()->alt_text }}"
                     class="w-full h-48 object-cover">
            </div>
            @endif
            <div class="p-6">
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach($article->categories as $category)
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                        {{ $category->name }}
                    </span>
                    @endforeach
                    @if($article->is_featured)
                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">
                        {{ __('news.featured_news') }}
                    </span>
                    @endif
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2 line-clamp-2">
                    <a href="{{ route('news.show', $article->slug) }}" class="hover:text-blue-600">
                        {{ $article->title }}
                    </a>
                </h3>
                <p class="text-gray-600 mb-4 line-clamp-3">{{ $article->summary }}</p>
                <div class="flex items-center justify-between text-sm text-gray-500">
                    <div class="flex items-center space-x-4">
                        <span>{{ $article->author_name }}</span>
                        <span>{{ $article->published_at->format('Y-m-d') }}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span>{{ $article->view_count }} {{ __('news.view_count') }}</span>
                        <span>{{ $article->comments_count }} {{ __('news.comments') }}</span>
                    </div>
                </div>
            </div>
        </article>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $news->appends(request()->query())->links() }}
    </div>
    @else
    <div class="text-center py-12">
        <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('news.no_news_found') }}</h3>
        <p class="text-gray-600">{{ __('news.search_placeholder') }}</p>
    </div>
    @endif
</div>
@endsection
