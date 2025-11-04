@props([
    'href' => null,
])

@if ($href)
    <x-link
            :$href
            {{ $attributes->merge(['class' => 'inline-flex items-center justify-center px-6 py-3 bg-sage text-dark text-sm font-medium rounded-lg transition-all duration-300 ease-out hover:bg-sage/90 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-sage focus:ring-offset-2 text-center']) }}>
        {{ $slot }}
    </x-link>
@else
    <button
            {{ $attributes->merge(['class' => 'inline-flex items-center justify-center px-6 py-3 bg-sage text-dark text-sm font-medium rounded-lg transition-all duration-300 ease-out hover:bg-sage/90 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-sage focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 text-center']) }}>
        {{ $slot }}
    </button>
@endif
