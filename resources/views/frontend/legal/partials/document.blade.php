@php
    $heading = $heading ?? __('legal.single');
    $description = $description ?? null;
    $emptyMessage = $emptyMessage ?? __('frontend.legal.document_unavailable');
    $updatedAt = optional($legal?->updated_at)->format('Y-m-d');
    $content = $legal?->getTranslatedContent();
    $fallbackKey = $fallbackKey ?? null;
    $fallbackSections = [];

    if ((! $legal || blank($content)) && $fallbackKey) {
        $sectionsKey = sprintf('frontend.legal.defaults.%s.sections', $fallbackKey);

        if (\Illuminate\Support\Facades\Lang::has($sectionsKey)) {
            $resolvedSections = trans($sectionsKey);
            $fallbackSections = is_array($resolvedSections) ? $resolvedSections : [];
        }
    }
@endphp

<div class="bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl">
    <div class="px-6 md:px-10 py-8 border-b border-gray-200 dark:border-gray-700">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $heading }}</h1>

        @if ($description)
            <p class="mt-4 text-gray-600 dark:text-gray-300">{{ $description }}</p>
        @endif

        @if ($legal)
            <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 text-sm text-gray-500 dark:text-gray-400">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-200">
                        {{ \App\Models\Legal::getTypes()[$legal->type] ?? $legal->type }}
                    </span>

                    @if ($legal->is_required)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-200">
                            {{ __('frontend.legal.required') }}
                        </span>
                    @endif
                </div>

                @if ($updatedAt)
                    <span>{{ __('frontend.legal.last_updated') }}: {{ $updatedAt }}</span>
                @endif
            </div>
        @endif
    </div>

    <div class="px-6 md:px-10 py-10">
        @if ($legal && filled($content))
            <div class="prose prose-lg max-w-none dark:prose-invert">
                {!! $content !!}
            </div>
        @elseif (filled($fallbackSections))
            <div class="prose prose-lg max-w-none dark:prose-invert">
                @foreach ($fallbackSections as $section)
                    @php
                        $title = $section['title'] ?? null;
                        $paragraphs = $section['paragraphs'] ?? [];
                        $listItems = $section['list'] ?? [];
                    @endphp

                    @if (filled($title))
                        <h2>{{ $title }}</h2>
                    @endif

                    @foreach ($paragraphs as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach

                    @if (is_array($listItems) && filled($listItems))
                        <ul>
                            @foreach ($listItems as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    @endif
                @endforeach
            </div>
        @else
            <div class="text-center py-10">
                <p class="text-gray-600 dark:text-gray-300">{{ $emptyMessage }}</p>
            </div>
        @endif
    </div>
</div>
