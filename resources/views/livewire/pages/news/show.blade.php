<article class="container mx-auto px-4 py-8 prose max-w-none">
    <nav class="mb-6 text-sm text-gray-600">
        <a href="{{ app()->getLocale() === 'lt' ? url('/lt/naujienos') : url('/en/news') }}"
           class="underline">{{ __('frontend.navigation.news') }}</a>
        <span class="mx-2">/</span>
        <span>{{ $record->title ?? $record->trans('title') }}</span>
    </nav>

    <h1 class="text-3xl font-bold">{{ $record->title ?? $record->trans('title') }}</h1>
    <p class="text-gray-500 mt-2">{{ optional($record->published_at)->format('Y-m-d') }} — {{ $record->author_name }}</p>

    <div class="mt-6">{!! $record->content ?? $record->trans('content') !!}</div>
</article>
