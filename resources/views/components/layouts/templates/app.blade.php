<x-layouts.base :title="$title ?? null">
    @include('components.hreflang')
    @include('components.canonical')
    <x-layouts.header />

    <main>
        {{ $slot }}
    </main>

    <x-layouts.footer />
</x-layouts.base>
