<x-layouts.base :title="$title ?? null">
    @include('components.hreflang')
    @include('components.canonical')

    <main>
        {{ $slot }}
    </main>
</x-layouts.base>
