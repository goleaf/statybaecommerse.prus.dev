@extends('components.layouts.base')

@section('title', __('posts.title'))
@section('meta_description', __('posts.title'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ __('posts.title') }}</h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    {{ __('posts.index.description', 'Discover the latest news and updates from our blog.') }}
                </p>
            </div>

            <!-- Filters -->
            <div class="mb-8">
                <div class="flex flex-wrap gap-4 justify-center">
                    <a href="{{ route('posts.index') }}"
                       class="px-6 py-2 rounded-full {{ request()->routeIs('posts.index') && !request()->featured ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                        {{ __('posts.filters.all_posts') }}
                    </a>
                    <a href="{{ route('posts.featured') }}"
                       class="px-6 py-2 rounded-full {{ request()->routeIs('posts.featured') ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                        {{ __('posts.filters.featured_only') }}
                    </a>
                </div>
            </div>

            <!-- Search -->
            <div class="mb-8">
                <form action="{{ route('posts.search') }}" method="GET" class="max-w-md mx-auto">
                    <div class="relative">
                        <input type="text"
                               name="q"
                               value="{{ request('q') }}"
                               placeholder="{{ __('posts.search.placeholder', 'Search posts...') }}"
                               class="w-full px-4 py-3 pl-10 pr-4 text-gray-700 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Posts Grid -->
            @if ($posts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($posts as $post)
                        <article
                                 class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                            @if ($post->getFirstMediaUrl('images'))
                                <div class="aspect-w-16 aspect-h-9">
                                    <img src="{{ $post->getFirstMediaUrl('images', 'medium') }}"
                                         alt="{{ $post->getTranslatedTitle() }}"
                                         class="w-full h-48 object-cover">
                                </div>
                            @endif

                            <div class="p-6">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm text-gray-500">
                                        {{ $post->published_at?->format('d/m/Y') }}
                                    </span>
                                    @if ($post->featured)
                                        <span
                                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                      d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                                </path>
                                            </svg>
                                            {{ __('posts.fields.featured') }}
                                        </span>
                                    @endif
                                </div>

                                <h2 class="text-xl font-semibold text-gray-900 mb-3 line-clamp-2">
                                    <a href="{{ route('posts.show', $post) }}"
                                       class="hover:text-blue-600 transition-colors">
                                        {{ $post->getTranslatedTitle() }}
                                    </a>
                                </h2>

                                @if ($post->getTranslatedExcerpt())
                                    <p class="text-gray-600 mb-4 line-clamp-3">
                                        {{ $post->getTranslatedExcerpt() }}
                                    </p>
                                @endif

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center text-sm text-gray-500">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                        {{ $post->user->name }}
                                    </div>

                                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                            {{ $post->views_count }}
                                        </span>

                                        @if ($post->likes_count > 0)
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                                    </path>
                                                </svg>
                                                {{ $post->likes_count }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-12">
                    <x-perfect-pagination :paginator="$posts" />
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('posts.no_posts.title', 'No posts found') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ __('posts.no_posts.description', 'There are no posts available at the moment.') }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection

