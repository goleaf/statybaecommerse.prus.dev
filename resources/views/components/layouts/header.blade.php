{{-- Enhanced Header with Modern Navigation --}}
<header class="sticky top-0 z-30 border-b border-gray-200/50 bg-white/95 backdrop-blur-xl shadow-soft" role="banner"
        aria-label="{{ __('nav_toggle') }}">
    <x-banner />

    {{-- Primary navigation (logo, search, actions) + secondary links --}}
    <livewire:components.navigation-menu />
</header>
