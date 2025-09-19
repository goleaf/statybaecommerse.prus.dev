@props([
    'type' => 'spinner', // spinner, skeleton, pulse, dots
    'size' => 'md', // sm, md, lg, xl
    'text' => null,
    'overlay' => false,
])

@php
$sizeClasses = match($size) {
    'sm' => 'h-4 w-4',
    'md' => 'h-6 w-6',
    'lg' => 'h-8 w-8',
    'xl' => 'h-12 w-12',
    default => 'h-6 w-6',
};
@endphp

@if($overlay)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg p-6 shadow-xl dark:bg-gray-800">
            @include('components.shared.loading-content')
        </div>
    </div>
@else
    @include('components.shared.loading-content')
@endif

@php
function renderLoadingContent($type, $sizeClasses, $text) {
    switch($type) {
        case 'spinner':
            return '
                <div class="flex items-center justify-center">
                    <svg class="animate-spin ' . $sizeClasses . ' text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    ' . ($text ? '<span class="ml-2 text-sm text-gray-600 dark:text-gray-300">' . $text . '</span>' : '') . '
                </div>
            ';
        
        case 'skeleton':
            return '
                <div class="animate-pulse">
                    <div class="bg-gray-200 rounded ' . $sizeClasses . ' dark:bg-gray-700"></div>
                    ' . ($text ? '<div class="mt-2 bg-gray-200 h-4 w-24 rounded dark:bg-gray-700"></div>' : '') . '
                </div>
            ';
        
        case 'pulse':
            return '
                <div class="flex items-center justify-center">
                    <div class="animate-pulse bg-blue-600 rounded-full ' . $sizeClasses . '"></div>
                    ' . ($text ? '<span class="ml-2 text-sm text-gray-600 dark:text-gray-300">' . $text . '</span>' : '') . '
                </div>
            ';
        
        case 'dots':
            return '
                <div class="flex items-center justify-center space-x-1">
                    <div class="animate-bounce bg-blue-600 rounded-full w-2 h-2"></div>
                    <div class="animate-bounce bg-blue-600 rounded-full w-2 h-2 delay" data-delay="0.1"></div>
                    <div class="animate-bounce bg-blue-600 rounded-full w-2 h-2 delay" data-delay="0.2"></div>
                    ' . ($text ? '<span class="ml-3 text-sm text-gray-600 dark:text-gray-300">' . $text . '</span>' : '') . '
                </div>
            ';
        
        default:
            return renderLoadingContent('spinner', $sizeClasses, $text);
    }
}
@endphp

{{-- Loading Content Template --}}
@if($type === 'spinner')
    <div class="flex items-center justify-center">
        <svg class="animate-spin {{ $sizeClasses }} text-blue-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        @if($text)
            <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">{{ $text }}</span>
        @endif
    </div>
@elseif($type === 'skeleton')
    <div class="animate-pulse">
        <div class="bg-gray-200 rounded {{ $sizeClasses }} dark:bg-gray-700"></div>
        @if($text)
            <div class="mt-2 bg-gray-200 h-4 w-24 rounded dark:bg-gray-700"></div>
        @endif
    </div>
@elseif($type === 'pulse')
    <div class="flex items-center justify-center">
        <div class="animate-pulse bg-blue-600 rounded-full {{ $sizeClasses }}"></div>
        @if($text)
            <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">{{ $text }}</span>
        @endif
    </div>
@elseif($type === 'dots')
    <div class="flex items-center justify-center space-x-1">
        <div class="animate-bounce bg-blue-600 rounded-full w-2 h-2"></div>
        <div class="animate-bounce bg-blue-600 rounded-full w-2 h-2 delay" data-delay="0.1"></div>
        <div class="animate-bounce bg-blue-600 rounded-full w-2 h-2 delay" data-delay="0.2"></div>
        @if($text)
            <span class="ml-3 text-sm text-gray-600 dark:text-gray-300">{{ $text }}</span>
        @endif
    </div>
@endif
