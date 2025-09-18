@props([
    'title' => __('Filters'),
    'description' => null,
    'badge' => null,
    'sticky' => true,
    'icon' => null,
])

@php
    $iconMarkup = $icon;
    if ($icon === null) {
        $iconMarkup = <<<'SVG'
<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
</svg>
SVG;
    }
    $containerClasses = trim(($sticky ? 'lg:sticky lg:top-28' : '') . ' space-y-6');
    $hasHeaderActions = isset($headerActions);
@endphp

<div {{ $attributes->class($containerClasses) }}>
    <x-shared.card :hover="false" padding="p-6" class="shadow-lg shadow-blue-100/50 dark:shadow-none">
        <x-slot name="header">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-2">
                    <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 text-blue-600 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.35em]">
                        {!! $iconMarkup !!}
                        {{ __('Filters') }}
                    </span>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $title }}</h2>
                    @if($description)
                        <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                            {{ $description }}
                        </p>
                    @endif
                </div>

                @if($hasHeaderActions)
                    <div class="shrink-0">
                        {{ $headerActions }}
                    </div>
                @elseif($badge)
                    <span class="rounded-full bg-gray-900/5 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-white/10 dark:text-gray-200">
                        {{ $badge }}
                    </span>
                @endif
            </div>
        </x-slot>

        <div class="space-y-6">
            {{ $slot }}
        </div>

        @isset($footer)
            <div class="mt-6 border-t border-dashed border-gray-200 pt-6 dark:border-gray-700">
                {{ $footer }}
            </div>
        @endisset
    </x-shared.card>

    @isset($actions)
        <div class="space-y-3">
            {{ $actions }}
        </div>
    @endisset
</div>
