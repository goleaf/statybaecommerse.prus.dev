@foreach($newsItems as $article)
    @php($primaryImage = $article->primaryImage())
    <article class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100" data-infinite-scroll-item>
        @if($primaryImage)
            <img
                src="{{ $primaryImage->url }}"
                alt="{{ $primaryImage->alt_text ?: $article->title }}"
                class="w-full h-48 object-cover"
            >
        @endif
        <div class="p-5 space-y-4">
            <div class="flex items-center gap-2 text-xs text-gray-500">
                @if($article->is_featured)
                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-medium">{{ __('messages.featured') }}</span>
                @endif
                @if($article->is_breaking)
                    <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-medium">{{ __('admin.news.is_breaking') }}</span>
                @endif
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                    <a href="{{ route('frontend.news.show', ['slug' => $article->slug]) }}" class="hover:text-cyan-700">
                        {{ $article->title }}
                    </a>
                </h3>
                @if($article->summary)
                    <p class="text-gray-600 text-sm line-clamp-3">{{ $article->summary }}</p>
                @endif
            </div>
            <div class="flex items-center justify-between text-sm text-gray-500">
                <span>{{ $article->author_name ?: '-' }}</span>
                <span>{{ optional($article->published_at)->format('Y-m-d') }}</span>
            </div>
        </div>
    </article>
@endforeach
