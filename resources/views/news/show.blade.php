@extends('components.layouts.base')

@section('title', $news->title)

@section('meta')
    <meta name="description" content="{{ $news->seo_description }}">
    <meta property="og:title" content="{{ $news->seo_title }}">
    <meta property="og:description" content="{{ $news->seo_description }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ route('frontend.news.show', ['slug' => $news->slug]) }}">
    @if($news->primaryImage())
        <meta property="og:image" content="{{ $news->primaryImage()->url }}">
    @endif
    <meta property="article:author" content="{{ $news->author_name }}">
    @if($news->published_at)
        <meta property="article:published_time" content="{{ $news->published_at->toISOString() }}">
    @endif
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    @php($primaryImage = $news->primaryImage())

    <nav class="mb-6 text-sm text-gray-500">
        <a href="{{ route('frontend.news.index') }}" class="hover:text-cyan-700">{{ __('messages.news') }}</a>
        <span class="mx-2">/</span>
        <span class="text-gray-900">{{ $news->title }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-3">
            <article class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                @if($primaryImage)
                    <img
                        src="{{ $primaryImage->url }}"
                        alt="{{ $primaryImage->alt_text ?: $news->title }}"
                        class="w-full h-72 md:h-96 object-cover"
                    >
                @endif

                <div class="p-6 md:p-8">
                    <div class="flex flex-wrap gap-2 mb-4">
                        @if($news->is_featured)
                            <span class="px-3 py-1 bg-amber-100 text-amber-700 text-sm rounded-full">{{ __('messages.featured') }}</span>
                        @endif
                        @if($news->is_breaking)
                            <span class="px-3 py-1 bg-red-100 text-red-700 text-sm rounded-full">{{ __('admin.news.is_breaking') }}</span>
                        @endif
                    </div>

                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ $news->title }}</h1>

                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 mb-6">
                        <span>{{ $news->author_name ?: '-' }}</span>
                        @if($news->published_at)
                            <span>{{ $news->published_at->format('Y-m-d H:i') }}</span>
                        @endif
                        <span>{{ $news->view_count }} {{ __('admin.news.view_count') }}</span>
                    </div>

                    @if($news->summary)
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <p class="text-gray-700 italic">{{ $news->summary }}</p>
                        </div>
                    @endif

                    <div class="prose prose-lg max-w-none">
                        {!! $news->content !!}
                    </div>
                </div>
            </article>
        </div>

        <aside class="lg:col-span-1 space-y-6">
            @if($relatedNews->isNotEmpty())
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.related_news') }}</h2>
                    <div class="space-y-4">
                        @foreach($relatedNews as $related)
                            @php($relatedImage = $related->primaryImage())
                            <article class="flex gap-3">
                                @if($relatedImage)
                                    <img
                                        src="{{ $relatedImage->url }}"
                                        alt="{{ $relatedImage->alt_text ?: $related->title }}"
                                        class="w-16 h-16 object-cover rounded"
                                    >
                                @endif
                                <div class="min-w-0">
                                    <h3 class="text-sm font-medium text-gray-900 line-clamp-2">
                                        <a href="{{ route('frontend.news.show', ['slug' => $related->slug]) }}" class="hover:text-cyan-700">
                                            {{ $related->title }}
                                        </a>
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-1">{{ optional($related->published_at)->format('Y-m-d') }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>
    </div>
</div>
@endsection
