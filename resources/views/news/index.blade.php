@extends('components.layouts.base')

@section('title', __('messages.news'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">{{ __('messages.news') }}</h1>
        <p class="text-gray-600">{{ __('frontend.news_page.subtitle') }}</p>
    </div>

    @if($featuredNews->isNotEmpty())
        <section class="mb-10">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">{{ __('messages.featured') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($featuredNews as $featured)
                    @php($featuredImage = $featured->primaryImage())
                    <article class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100">
                        @if($featuredImage)
                            <img
                                src="{{ $featuredImage->url }}"
                                alt="{{ $featuredImage->alt_text ?: $featured->title }}"
                                class="w-full h-48 object-cover"
                            >
                        @endif
                        <div class="p-5">
                            <h3 class="font-semibold text-lg text-gray-900 line-clamp-2">
                                <a href="{{ route('frontend.news.show', ['slug' => $featured->slug]) }}" class="hover:text-cyan-700">
                                    {{ $featured->title }}
                                </a>
                            </h3>
                            @if($featured->summary)
                                <p class="mt-2 text-sm text-gray-600 line-clamp-3">{{ $featured->summary }}</p>
                            @endif
                            <div class="mt-4 text-xs text-gray-500 flex items-center justify-between">
                                <span>{{ $featured->author_name ?: '-' }}</span>
                                <span>{{ optional($featured->published_at)->format('Y-m-d') }}</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if($news->isNotEmpty())
        @php($hasMoreNews = $news->hasMorePages())
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
                    <span class="h-5 w-5 rounded-full border-2 border-cyan-600 border-t-transparent animate-spin"></span>
                    <span>{{ __('frontend.news_page.loading') }}</span>
                </div>

                <button
                    type="button"
                    class="px-6 py-2 bg-cyan-700 text-white rounded-md hover:bg-cyan-800 focus:outline-none focus:ring-2 focus:ring-cyan-700 focus:ring-offset-2 transition disabled:opacity-50 disabled:cursor-not-allowed {{ $hasMoreNews ? '' : 'hidden' }}"
                    data-infinite-scroll-trigger
                >
                    {{ __('frontend.news_page.load_more') }}
                </button>

                <p class="text-sm text-gray-500 hidden" data-infinite-scroll-end>
                    {{ __('frontend.news_page.no_more_results') }}
                </p>

                <div data-infinite-scroll-fallback class="w-full">
                    <x-perfect-pagination :paginator="$news->appends(request()->query())" />
                </div>

                <div class="sr-only" aria-live="polite" data-infinite-scroll-status></div>
            </div>
        </section>
    @else
        <div class="rounded-lg border border-dashed border-gray-300 px-8 py-12 text-center bg-white">
            <h3 class="text-xl font-semibold text-gray-900">{{ __('messages.news') }}</h3>
            <p class="mt-2 text-sm text-gray-600">{{ __('frontend.news_page.empty_description') }}</p>
            <a
                href="{{ route('frontend.news.index') }}"
                class="inline-flex mt-6 items-center rounded-md bg-cyan-700 px-4 py-2 text-sm font-semibold text-white hover:bg-cyan-800"
            >
                {{ __('messages.news') }}
            </a>
        </div>
    @endif
</div>
@endsection
