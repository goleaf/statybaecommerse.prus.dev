@php
    $page = is_array($page ?? null) ? $page : [];
    $sectionLabel = $page['section'] ?? null;
    $title = $page['title'] ?? __('messages.frontend');
    $description = $page['description'] ?? null;
    $highlights = is_array($page['highlights'] ?? null) ? $page['highlights'] : [];
    $sections = is_array($page['sections'] ?? null) ? $page['sections'] : [];
    $faqs = is_array($page['faqs'] ?? null) ? $page['faqs'] : [];
    $actions = is_array($actions ?? null) ? $actions : [];
    $relatedPages = is_array($relatedPages ?? null) ? $relatedPages : [];
    $documentMeta = is_array($documentMeta ?? null) ? $documentMeta : [];
    $contentHtml = is_string($contentHtml ?? null) ? trim($contentHtml) : null;
    $emptyMessage = $emptyMessage ?? __('frontend.legal.document_unavailable', ['document' => mb_strtolower((string) $title)]);
    $contents = collect($sections)
        ->map(static fn (array $section): ?string => isset($section['title']) && is_string($section['title']) ? $section['title'] : null)
        ->filter()
        ->values()
        ->all();
@endphp

<section class="relative overflow-hidden bg-sage py-10 md:py-14 text-dark">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -top-24 left-0 h-56 w-56 rounded-full bg-white/45 blur-3xl"></div>
        <div class="absolute right-0 top-12 h-64 w-64 rounded-full bg-white/30 blur-3xl"></div>
    </div>

    <div class="relative container mx-auto px-4 space-y-8">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1.25fr)_minmax(300px,0.75fr)]">
            <article class="rounded-3xl border border-ash bg-white/95 p-6 shadow-xl sm:p-8">
                <div class="space-y-6">
                    @if ($sectionLabel)
                        <span class="inline-flex items-center rounded-full border border-ash bg-brand-light px-4 py-1.5 text-sm font-semibold text-dark">
                            {{ $sectionLabel }}
                        </span>
                    @endif

                    <div class="space-y-4">
                        <h1 class="max-w-4xl text-3xl font-bold tracking-tight text-dark md:text-5xl">
                            {{ $title }}
                        </h1>

                        @if ($description)
                            <p class="max-w-3xl text-base leading-8 text-stone md:text-lg">
                                {{ $description }}
                            </p>
                        @endif
                    </div>

                    @if ($highlights !== [])
                        <div class="grid gap-4 md:grid-cols-3">
                            @foreach ($highlights as $highlight)
                                <article class="rounded-2xl border border-ash bg-sage p-5 shadow-soft">
                                    <h2 class="text-lg font-semibold text-dark">
                                        {{ $highlight['title'] ?? '' }}
                                    </h2>
                                    <p class="mt-2 text-sm leading-6 text-stone">
                                        {{ $highlight['description'] ?? '' }}
                                    </p>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            </article>

            <aside class="rounded-3xl border border-ash bg-white/80 p-6 shadow-xl backdrop-blur-sm">
                @if ($contents !== [])
                    <div>
                        <p class="text-sm font-semibold text-dark">{{ __('info_pages.shared.on_this_page') }}</p>
                        <ul class="mt-4 space-y-3">
                            @foreach ($contents as $contentTitle)
                                <li class="rounded-2xl border border-ash bg-brand-light px-4 py-3 text-sm leading-6 text-dark">
                                    {{ $contentTitle }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

            </aside>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-6">
                @if ($documentMeta !== [])
                    <article class="rounded-3xl border border-ash bg-white/80 p-6 shadow-soft">
                        <div class="flex flex-wrap items-center gap-3">
                            @foreach ($documentMeta as $meta)
                                @if (filled($meta))
                                    <span class="inline-flex items-center rounded-full border border-ash bg-brand-light px-4 py-2 text-sm font-medium text-dark">
                                        {{ $meta }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </article>
                @endif

                @if (filled($contentHtml))
                    <article class="rounded-3xl border border-ash bg-white/95 p-6 shadow-xl md:p-8">
                        <div class="prose prose-lg max-w-none text-stone">
                            {!! $contentHtml !!}
                        </div>
                    </article>
                @elseif ($sections !== [])
                    @foreach ($sections as $section)
                        <article class="rounded-3xl border border-ash bg-white/95 p-6 shadow-xl md:p-8">
                            @if (filled($section['title'] ?? null))
                                <h2 class="text-2xl font-semibold text-dark">
                                    {{ $section['title'] }}
                                </h2>
                            @endif

                            @foreach (($section['paragraphs'] ?? []) as $paragraph)
                                <p class="mt-4 text-base leading-8 text-stone">
                                    {{ $paragraph }}
                                </p>
                            @endforeach

                            @if (! empty($section['list'] ?? []))
                                <ul class="mt-5 space-y-3">
                                    @foreach ($section['list'] as $item)
                                        <li class="flex gap-3 text-base leading-7 text-stone">
                                            <span class="mt-2 h-2.5 w-2.5 shrink-0 rounded-full bg-stone"></span>
                                            <span>{{ $item }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @if (filled($section['note'] ?? null))
                                <div class="mt-5 rounded-2xl border border-ash bg-brand-light px-5 py-4 text-sm leading-6 text-dark">
                                    {{ $section['note'] }}
                                </div>
                            @endif
                        </article>
                    @endforeach
                @elseif ($faqs !== [])
                    <article class="rounded-3xl border border-ash bg-white/95 p-6 shadow-xl md:p-8">
                        <div class="space-y-4">
                            @foreach ($faqs as $faq)
                                <details class="group rounded-2xl border border-ash bg-brand-light p-5 transition">
                                    <summary class="cursor-pointer list-none pr-8 text-lg font-semibold text-dark marker:hidden">
                                        {{ $faq['question'] ?? '' }}
                                    </summary>
                                    <p class="mt-4 text-base leading-8 text-stone">
                                        {{ $faq['answer'] ?? '' }}
                                    </p>
                                </details>
                            @endforeach
                        </div>
                    </article>
                @else
                    <article class="rounded-3xl border border-ash bg-white/95 p-6 shadow-soft">
                        <p class="text-base leading-7 text-stone">{{ $emptyMessage }}</p>
                    </article>
                @endif
            </div>

            <aside class="space-y-6">
                <article class="rounded-3xl border border-ash bg-white/80 p-6 shadow-soft backdrop-blur-sm">
                    <h2 class="text-xl font-semibold text-dark">{{ __('info_pages.shared.need_help_title') }}</h2>
                    <p class="mt-3 text-sm leading-7 text-stone">
                        {{ __('info_pages.shared.need_help_description') }}
                    </p>

                    @if ($actions !== [])
                        <div class="mt-5 flex flex-wrap gap-3">
                            @foreach ($actions as $action)
                                <a
                                    href="{{ $action['url'] }}"
                                    class="{{ $action['style'] === 'secondary'
                                        ? 'inline-flex items-center rounded-full border border-ash bg-white px-4 py-2.5 text-sm font-medium text-dark transition hover:bg-brand-light'
                                        : 'inline-flex items-center rounded-full border border-ash bg-brand-light px-4 py-2.5 text-sm font-semibold text-dark transition hover:bg-sage' }}"
                                >
                                    {{ $action['label'] }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </article>

                @if ($relatedPages !== [])
                    <article class="rounded-3xl border border-ash bg-white/95 p-6 shadow-xl">
                        <h2 class="text-xl font-semibold text-dark">{{ __('info_pages.shared.related_pages') }}</h2>
                        <div class="mt-5 space-y-4">
                            @foreach ($relatedPages as $relatedPage)
                                <a href="{{ $relatedPage['url'] }}" class="block rounded-2xl border border-ash bg-brand-light px-5 py-4 transition hover:bg-sage">
                                    <p class="text-base font-semibold text-dark">{{ $relatedPage['title'] }}</p>
                                    @if (filled($relatedPage['description']))
                                        <p class="mt-2 text-sm leading-6 text-stone">{{ $relatedPage['description'] }}</p>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </article>
                @endif
            </aside>
        </div>
    </div>
</section>
