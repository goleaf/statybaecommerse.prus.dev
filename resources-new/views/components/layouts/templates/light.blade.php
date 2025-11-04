<x-layouts.base :title="$title ?? null">
    <div class="container mx-auto px-4 pt-4">
        @livewire(\App\Livewire\Shared\LanguageSwitcher::class)
    </div>
    {{ $slot }}
</x-layouts.base>
