@extends('layouts.app')

@section('title', $news->title)

@section('meta')
<meta name="description" content="{{ $news->seo_description ?? $news->summary }}">
<meta name="keywords" content="{{ $news->tags->pluck('name')->join(', ') }}">
<meta property="og:title" content="{{ $news->seo_title ?? $news->title }}">
<meta property="og:description" content="{{ $news->seo_description ?? $news->summary }}">
<meta property="og:type" content="article">
<meta property="og:url" content="{{ localized_route('news.show', $news->slug) }}">
@if($news->images->where('is_featured', true)->first())
<meta property="og:image" content="{{ $news->images->where('is_featured', true)->first()->url }}">
@endif
<meta property="article:author" content="{{ $news->author_name }}">
<meta property="article:published_time" content="{{ $news->published_at->toISOString() }}">
<meta property="article:section" content="{{ $news->categories->pluck('name')->join(', ') }}">
<meta property="article:tag" content="{{ $news->tags->pluck('name')->join(', ') }}">
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-6">
        <ol class="flex items-center space-x-2 text-sm text-gray-500">
            <li><a href="{{ localized_route('news.index') }}" class="hover:text-blue-600">{{ __('news.title') }}</a></li>
            <li class="flex items-center">
                <svg class="w-4 h-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-gray-900">{{ $news->title }}</span>
            </li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-3">
            <article class="bg-white rounded-lg shadow-md overflow-hidden">
                <!-- Featured Image -->
                @if($news->images->where('is_featured', true)->first())
                <div class="aspect-w-16 aspect-h-9">
                    <img src="{{ $news->images->where('is_featured', true)->first()->url }}" 
                         alt="{{ $news->images->where('is_featured', true)->first()->alt_text }}"
                         class="w-full h-64 md:h-96 object-cover">
                </div>
                @endif

                <div class="p-6 md:p-8">
                    <!-- Categories and Tags -->
                    <div class="flex flex-wrap gap-2 mb-4">
                        @foreach($news->categories as $category)
                        <a href="{{ localized_route('news.category', $category->slug) }}" 
                           class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full hover:bg-blue-200">
                            {{ $category->name }}
                        </a>
                        @endforeach
                        @if($news->is_featured)
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm rounded-full">
                            {{ __('news.featured_news') }}
                        </span>
                        @endif
                    </div>

                    <!-- Title -->
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ $news->title }}</h1>

                    <!-- Meta Information -->
                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 mb-6">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                            </svg>
                            <span>{{ $news->author_name }}</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                            <span>{{ $news->published_at->format('Y-m-d H:i') }}</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span>{{ $news->view_count }} {{ __('news.view_count') }}</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"></path>
                            </svg>
                            <span>{{ $news->comments->count() }} {{ __('news.comments') }}</span>
                        </div>
                    </div>

                    <!-- Summary -->
                    @if($news->summary)
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <p class="text-lg text-gray-700 italic">{{ $news->summary }}</p>
                    </div>
                    @endif

                    <!-- Content -->
                    <div class="prose prose-lg max-w-none">
                        {!! $news->content !!}
                    </div>

                    <!-- Tags -->
                    @if($news->tags->count() > 0)
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-sm font-medium text-gray-900 mb-3">{{ __('news.tags') }}:</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($news->tags as $tag)
                            <a href="{{ localized_route('news.tag', $tag->slug) }}" 
                               class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full hover:bg-gray-200">
                                #{{ $tag->name }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Share Section -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-sm font-medium text-gray-900 mb-3">{{ __('news.share_this_news') }}:</h3>
                        <div class="flex space-x-4">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(localized_route('news.show', $news->slug)) }}" 
                               target="_blank" 
                               class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                                Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(localized_route('news.show', $news->slug)) }}&text={{ urlencode($news->title) }}" 
                               target="_blank" 
                               class="flex items-center px-4 py-2 bg-blue-400 text-white rounded-md hover:bg-blue-500">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                </svg>
                                Twitter
                            </a>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Comments Section -->
            @if($news->comments->count() > 0)
            <div class="mt-8 bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-6">{{ __('news.comments') }} ({{ $news->comments->count() }})</h3>
                
                <div class="space-y-6">
                    @foreach($news->comments as $comment)
                    <div class="border-l-4 border-blue-500 pl-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <span class="font-medium text-gray-900">{{ $comment->author_name }}</span>
                                <span class="text-sm text-gray-500">{{ $comment->created_at->format('Y-m-d H:i') }}</span>
                            </div>
                        </div>
                        <p class="text-gray-700">{{ $comment->content }}</p>
                        
                        @if($comment->replies->count() > 0)
                        <div class="mt-4 ml-4 space-y-4">
                            @foreach($comment->replies as $reply)
                            <div class="border-l-2 border-gray-300 pl-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-medium text-gray-900">{{ $reply->author_name }}</span>
                                        <span class="text-sm text-gray-500">{{ $reply->created_at->format('Y-m-d H:i') }}</span>
                                    </div>
                                </div>
                                <p class="text-gray-700">{{ $reply->content }}</p>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Add Comment Form -->
            <div class="mt-8 bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-6">{{ __('news.add_comment') }}</h3>
                
                <form action="{{ localized_route('news.comments.store', $news->slug) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="author_name" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('news.comment_name') }} *
                            </label>
                            <input type="text" 
                                   id="author_name" 
                                   name="author_name" 
                                   value="{{ old('author_name') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('author_name') border-red-500 @enderror"
                                   required>
                            @error('author_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="author_email" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('news.comment_email') }} *
                            </label>
                            <input type="email" 
                                   id="author_email" 
                                   name="author_email" 
                                   value="{{ old('author_email') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('author_email') border-red-500 @enderror"
                                   required>
                            @error('author_email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('news.comment_content') }} *
                        </label>
                        <textarea id="content" 
                                  name="content" 
                                  rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('content') border-red-500 @enderror"
                                  required>{{ old('content') }}</textarea>
                        @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            {{ __('news.submit_comment') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Related News -->
            @if($relatedNews->count() > 0)
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('news.related_news') }}</h3>
                <div class="space-y-4">
                    @foreach($relatedNews as $related)
                    <article class="flex space-x-3">
                        @if($related->images->where('is_featured', true)->first())
                        <div class="flex-shrink-0">
                            <img src="{{ $related->images->where('is_featured', true)->first()->url }}" 
                                 alt="{{ $related->images->where('is_featured', true)->first()->alt_text }}"
                                 class="w-16 h-16 object-cover rounded">
                        </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-gray-900 line-clamp-2">
                                <a href="{{ localized_route('news.show', $related->slug) }}" class="hover:text-blue-600">
                                    {{ $related->title }}
                                </a>
                            </h4>
                            <p class="text-xs text-gray-500 mt-1">{{ $related->published_at->format('Y-m-d') }}</p>
                        </div>
                    </article>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Newsletter Signup -->
            <div class="bg-blue-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('news.newsletter') }}</h3>
                <p class="text-sm text-gray-600 mb-4">{{ __('news.subscribe_newsletter') }}</p>
                <form class="space-y-3">
                    <input type="email" 
                           placeholder="{{ __('news.newsletter_email') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit" 
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        {{ __('news.newsletter_subscribe') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

