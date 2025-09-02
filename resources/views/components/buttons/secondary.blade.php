@props([
    'href' => null,
])

@if ($href)
    <x-link
        :$href
        {{ $attributes->merge(['class' => 'group relative py-2.5 inline-flex border border-gray-300 text-sm font-medium text-gray-700 shadow-sm focus:outline-none']) }}
    >
        <span
            class="absolute inset-0 z-0 transform border-2 border-gray-300 transition-transform group-hover:translate-x-1 group-hover:translate-y-1 group-focus:-translate-y-1 group-focus:translate-x-1"
        ></span>
        <span class="absolute inset-0 bg-gray-100 z-0"></span>
        <span class="relative w-full inline-flex items-center gap-2 justify-center">
            {{ $slot }}
        </span>
    </x-link>
@else
    <button
        {{ $attributes->merge(['class' => 'group relative py-2.5 inline-flex border border-gray-300 text-sm font-medium text-gray-700 shadow-sm focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed']) }}
    >
        <span
            class="absolute inset-0 z-0 transform border-2 border-gray-300 p-1 transition-transform group-hover:translate-x-1 group-hover:translate-y-1 group-focus:-translate-y-1 group-focus:translate-x-1"
        ></span>
        <span class="absolute inset-0 bg-gray-100 z-0"></span>
        <span class="relative w-full inline-flex items-center gap-2 justify-center">
            {{ $slot }}
        </span>
    </button>
@endif
