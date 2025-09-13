@props([
    'type' => 'info',
    'title' => null,
    'message' => null,
    'dismissible' => true,
    'show' => false,
])

@php
    $types = [
        'success' => [
            'bg' => 'bg-green-50',
            'border' => 'border-green-200',
            'text' => 'text-green-800',
            'icon' => 'M5 13l4 4L19 7',
            'iconColor' => 'text-green-400',
        ],
        'error' => [
            'bg' => 'bg-red-50',
            'border' => 'border-red-200',
            'text' => 'text-red-800',
            'icon' => 'M6 18L18 6M6 6l12 12',
            'iconColor' => 'text-red-400',
        ],
        'warning' => [
            'bg' => 'bg-yellow-50',
            'border' => 'border-yellow-200',
            'text' => 'text-yellow-800',
            'icon' =>
                'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z',
            'iconColor' => 'text-yellow-400',
        ],
        'info' => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-200',
            'text' => 'text-blue-800',
            'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'iconColor' => 'text-blue-400',
        ],
    ];

    $config = $types[$type] ?? $types['info'];
@endphp

<div x-data="{ show: @js($show) }"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform scale-100"
     x-transition:leave-end="opacity-0 transform scale-95"
     class="fixed top-4 right-4 z-50 max-w-sm w-full {{ $config['bg'] }} {{ $config['border'] }} border rounded-xl shadow-large"
     style="display: none;">
    <div class="p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 {{ $config['iconColor'] }}" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['icon'] }}">
                    </path>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                @if ($title)
                    <h3 class="text-sm font-semibold {{ $config['text'] }}">{{ $title }}</h3>
                @endif
                @if ($message)
                    <p class="mt-1 text-sm {{ $config['text'] }}">{{ $message }}</p>
                @endif
            </div>
            @if ($dismissible)
                <div class="ml-4 flex-shrink-0">
                    <button @click="show = false"
                            class="inline-flex {{ $config['text'] }} hover:opacity-75 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 rounded-md">
                        <span class="sr-only">{{ __('Close') }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

