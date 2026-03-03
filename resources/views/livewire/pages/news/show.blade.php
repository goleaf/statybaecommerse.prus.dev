<article class="container mx-auto px-4 py-8 prose max-w-none">
    @php
        $newsIndexUrl = \Illuminate\Support\Facades\Route::has('localized.news.index')
            ? route('localized.news.index', ['locale' => app()->getLocale()])
            : route('frontend.news.index');
    @endphp

    <nav class="mb-6 text-sm text-gray-600">
        <a href="{{ app()->getLocale() === 'lt' ? url('/lt/naujienos') : url('/en/news') }}"
           class="underline">{{ __('messages.frontend') }}</a>
        <span class="mx-2">/</span>
        <span>{{ $record->title ?? $record->trans('title') }}</span>
    </nav>

    <h1 class="text-3xl font-bold">{{ $record->title ?? $record->trans('title') }}</h1>
    <p class="text-gray-500 mt-2">{{ optional($record->published_at)->format('Y-m-d') }} — {{ $record->author_name }}</p>

    <div class="mt-6">{!! $record->content ?? $record->trans('content') !!}</div>

    <!-- Back Button -->
    <div class="mt-8 text-center">
        <a href="{{ $newsIndexUrl }}"
           class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-md transition duration-200">
            <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
            {{ __('frontend.buttons.back_to_news') }}
        </a>
    </div>
</article>
