<x-layouts.base :title="$title ?? null">
    @include('components.hreflang')
    <main>
        <div class="container mx-auto px-4 pt-4">
            @livewire(\App\Livewire\Shared\LanguageSwitcher::class)
        </div>
        {{ $slot }}
    </main>
</x-layouts.base>
