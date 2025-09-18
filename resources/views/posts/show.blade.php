@extends('components.layouts.base')

@section('title', $post->getTranslatedMetaTitle() ?: $post->getTranslatedTitle())
@section('meta_description', $post->getTranslatedMetaDescription() ?: $post->getTranslatedExcerpt())

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="mb-8">
                <ol class="flex items-center space-x-2 text-sm text-gray-500">
                    <li><a href="{{ route('posts.index') }}" class="hover:text-blue-600">{{ __('posts.title') }}</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li class="text-gray-900">{{ $post->getTranslatedTitle() }}</li>
                </ol>
            </nav>

            <article class="bg-white rounded-lg shadow-lg overflow-hidden">
                <!-- Featured Image -->
                @if ($post->getFirstMediaUrl('images'))
                    <div class="aspect-w-16 aspect-h-9">
                        <img src="{{ $post->getFirstMediaUrl('images') }}"
                             alt="{{ $post->getTranslatedTitle() }}"
                             class="w-full h-64 md:h-96 object-cover">
                    </div>
                @endif

                <div class="p-8">
                    <!-- Header -->
                    <header class="mb-8">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    {{ $post->published_at?->format('d/m/Y H:i') }}
                                </span>

                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                        </path>
                                    </svg>
                                    {{ $post->user->name }}
                                </span>

                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                    {{ $post->views_count }} {{ __('posts.views', 'views') }}
                                </span>
                            </div>

                            @if ($post->featured)
                                <span
                                      class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                              d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                    {{ __('posts.fields.featured') }}
                                </span>
                            @endif
                        </div>

                        <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $post->getTranslatedTitle() }}</h1>

                        @if ($post->getTranslatedExcerpt())
                            <p class="text-xl text-gray-600 leading-relaxed">{{ $post->getTranslatedExcerpt() }}</p>
                        @endif
                    </header>

                    <!-- Content -->
                    <div class="prose prose-lg max-w-none mb-8">
                        {!! $post->getTranslatedContent() !!}
                    </div>

                    <!-- Tags -->
                    @if ($post->getTranslatedTags())
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ __('posts.tags', 'Tags') }}</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach (explode(',', $post->getTranslatedTags()) as $tag)
                                    <span
                                          class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                        {{ trim($tag) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-8 border-t border-gray-200">
                        <div class="flex items-center space-x-4">
                            <button
                                    class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                    </path>
                                </svg>
                                {{ __('posts.like', 'Like') }}
                            </button>

                            <button
                                    class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z">
                                    </path>
                                </svg>
                                {{ __('posts.share', 'Share') }}
                            </button>
                        </div>

                        <div class="text-sm text-gray-500">
                            {{ __('posts.last_updated', 'Last updated') }}: {{ $post->updated_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </article>

            <!-- Gallery -->
            @if ($post->getMedia('gallery')->count() > 0)
                <div class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('posts.gallery', 'Gallery') }}</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach ($post->getMedia('gallery') as $image)
                            <div class="aspect-square">
                                <img src="{{ $image->getUrl('medium') }}"
                                     alt="{{ $post->getTranslatedTitle() }}"
                                     class="w-full h-full object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                                     onclick="openImageModal('{{ $image->getUrl() }}')">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Related Posts -->
            @if ($relatedPosts->count() > 0)
                <div class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('posts.related_posts', 'Related Posts') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach ($relatedPosts as $relatedPost)
                            <article
                                     class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                                @if ($relatedPost->getFirstMediaUrl('images'))
                                    <div class="aspect-w-16 aspect-h-9">
                                        <img src="{{ $relatedPost->getFirstMediaUrl('images', 'medium') }}"
                                             alt="{{ $relatedPost->getTranslatedTitle() }}"
                                             class="w-full h-32 object-cover">
                                    </div>
                                @endif

                                <div class="p-4">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                                        <a href="{{ route('posts.show', $relatedPost) }}"
                                           class="hover:text-blue-600 transition-colors">
                                            {{ $relatedPost->getTranslatedTitle() }}
                                        </a>
                                    </h3>

                                    <div class="flex items-center justify-between text-sm text-gray-500">
                                        <span>{{ $relatedPost->published_at?->format('d/m/Y') }}</span>
                                        <span>{{ $relatedPost->views_count }} {{ __('posts.views', 'views') }}</span>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-50 flex items-center justify-center p-4">
        <div class="relative max-w-4xl max-h-full">
            <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
            <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain">
        </div>
    </div>

    <script>
        function openImageModal(imageUrl) {
            document.getElementById('modalImage').src = imageUrl;
            document.getElementById('imageModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });

        // Close modal on background click
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });
    </script>
@endsection

