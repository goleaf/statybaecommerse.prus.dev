{{-- Enhanced Header with Modern Navigation --}}
<header class="sticky top-0 z-20 border-b border-gray-200 bg-white bg-opacity-80 backdrop-blur-xl backdrop-filter">
    <x-banner />
    
    {{-- Use Enhanced Navigation Component --}}
    <livewire:components.enhanced-navigation />
    
    <x-container class="px-4 py-2">
        @includeIf('components.store-badge')
    </x-container>
</header>
