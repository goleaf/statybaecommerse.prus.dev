<article class="container mx-auto px-4 py-8 prose max-w-none">
    <nav class="mb-6 text-sm text-gray-600">
        <a href="{{ app()->getLocale() === 'lt' ? url('/lt/naujienos') : url('/en/news') }}"
           class="underline">{{ __('messages.frontend) }}</a>
        <span class="mx-2">/</span>
        <span>{{ $record->title ?? $record->trans('title') }}</span>
    </nav>

    <h1 class="text-3xl font-bold">{{ $record->title ?? $record->trans('title') }}</h1>
    <p class="text-gray-500 mt-2">{{ optional($record->published_at)->format('Y-m-d') }} — {{ $record->author_name }}</p>

    <div class="mt-6">{!! $record->content ?? $record->trans('content') !!}</div>

    @php
        $podcastPlayerUrl = $record->getPodcastPlayerUrl();
        $podcastShareUrl = $record->getPodcastShareUrl();
    @endphp

    @if ($podcastPlayerUrl)
        <section class="mt-10 bg-gray-50 border border-gray-200 rounded-xl p-6 shadow-sm">
            <h2 class="text-2xl font-semibold text-gray-900">{{ __('news.podcast.section_title') }}</h2>
            <p class="mt-2 text-gray-600">{{ __('news.podcast.section_description') }}</p>
            <div class="mt-4">
                <iframe
                    src="{{ $podcastPlayerUrl }}"
                    title="{{ __('news.podcast.embed_title') }}"
                    loading="lazy"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    class="w-full h-48 rounded-lg border border-gray-200"
                ></iframe>
            </div>
            @if ($podcastShareUrl)
                <div class="mt-4">
                    <a href="{{ $podcastShareUrl }}" target="_blank" rel="noopener" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition duration-200">
                        <x-heroicon-o-play class="w-4 h-4 mr-2" />
                        {{ __('news.podcast.listen_cta') }}
                    </a>
                </div>
            @endif
        </section>
    @endif

    <!-- Back Button -->
    <div class="mt-8 text-center">
        <a href="{{ route('news.index', ['locale' => app()->getLocale()]) }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-md transition duration-200">
            <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
            {{ __('frontend.buttons.back_to_news') }}
        </a>
    </div>
</article>
