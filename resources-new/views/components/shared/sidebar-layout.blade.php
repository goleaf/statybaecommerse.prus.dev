@props([
    'sidebarWidth' => 'w-80',
    'contentWidth' => 'flex-1',
    'sidebarSticky' => true,
    'sidebarClass' => '',
    'contentClass' => '',
])

<div class="flex flex-col lg:flex-row gap-6">
    {{-- Sidebar --}}
    <aside class="lg:{{ $sidebarWidth }} {{ $sidebarSticky ? 'lg:sticky lg:top-6 lg:self-start' : '' }} {{ $sidebarClass }}">
        {{ $sidebar }}
    </aside>
    
    {{-- Main Content --}}
    <main class="{{ $contentWidth }} {{ $contentClass }}">
        {{ $slot }}
    </main>
</div>
