@foreach($newsItems as $article)
    <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow" data-infinite-scroll-item>
        @php
            $featuredImage = $article->images->where('is_featured', true)->first();
        @endphp
        @if($featuredImage)
            <div class="aspect-w-16 aspect-h-9">
                <img src="{{ $featuredImage->url }}"
                     alt="{{ $featuredImage->alt_text }}"
                     class="w-full h-48 object-cover">
            </div>
        @endif
        <div class="p-6 space-y-4">
            <div class="flex flex-wrap gap-2">
                @foreach($article->categories as $category)
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                        {{ $category->name }}
                    </span>
                @endforeach
                @if($article->is_featured)
                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">
                        {{ __('messages.news') }}
                    </span>
                @endif
            </div>
            <div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2 line-clamp-2">
                    <a href="{{ route('news.show', $article->slug) }}" class="hover:text-blue-600">
                        {{ $article->title }}
                    </a>
                </h3>
                <p class="text-gray-600 line-clamp-3">{{ $article->summary }}</p>
            </div>
            <div class="flex items-center justify-between text-sm text-gray-500">
                <div class="flex items-center space-x-4">
                    <span>{{ $article->author_name }}</span>
                    <span>{{ $article->published_at->format('Y-m-d') }}</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span>{{ $article->view_count }} {{ __('messages.news') }}</span>
                    <span>{{ $article->comments_count }} {{ __('messages.news') }}</span>
                </div>
            </div>
        </div>
    </article>
@endforeach
