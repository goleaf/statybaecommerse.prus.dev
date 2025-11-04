@props([
    'title',
    'description' => null,
    'icon' => 'heroicon-o-cube',
    'actionText' => null,
    'actionUrl' => null,
    'actionWire' => null,
])

<div class="text-center py-16">
    <svg class="mx-auto h-24 w-24 text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        @if($icon === 'heroicon-o-cube')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        @elseif($icon === 'heroicon-o-shopping-cart')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
        @elseif($icon === 'heroicon-o-magnifying-glass')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        @elseif($icon === 'heroicon-o-heart')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        @elseif($icon === 'heroicon-o-document')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        @else
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        @endif
    </svg>
    
    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">{{ $title }}</h3>
    
    @if($description)
        <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">{{ $description }}</p>
    @endif
    
    @if($actionText && ($actionUrl || $actionWire))
        @if($actionUrl)
            <x-shared.button 
                href="{{ $actionUrl }}"
                variant="primary"
                size="lg"
            >
                {{ $actionText }}
            </x-shared.button>
        @elseif($actionWire)
            <x-shared.button 
                wire:click="{{ $actionWire }}"
                variant="primary"
                size="lg"
            >
                {{ $actionText }}
            </x-shared.button>
        @endif
    @endif
    
    {{ $slot }}
</div>
