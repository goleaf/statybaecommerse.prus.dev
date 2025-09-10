{{-- Enhanced Header with Modern Navigation --}}
<header class="sticky top-0 z-20 border-b border-gray-200 bg-white/90 backdrop-blur-xl" role="banner"
        aria-label="{{ __('nav_toggle') }}">
    <x-banner />

    {{-- Primary navigation (logo, search, actions) + secondary links --}}
    <livewire:components.navigation-menu />
</header>
