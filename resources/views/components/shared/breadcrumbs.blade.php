@props([
    'items' => [],
    'separator' => 'chevron', // chevron, slash, dot
    'showHome' => true,
])

@if(!empty($items) || $showHome)
    <nav class="flex" aria-label="{{ __('Breadcrumb') }}">
        <ol class="flex items-center space-x-2 text-sm">
            @if($showHome)
                <li>
                    <a href="{{ localized_route('home') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors duration-200">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span class="sr-only">{{ __('shared.home') }}</span>
                    </a>
                </li>
                
                @if(!empty($items))
                    <li class="flex items-center">
                        @if($separator === 'chevron')
                            <svg class="h-4 w-4 text-gray-400 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        @elseif($separator === 'slash')
                            <span class="text-gray-400 mx-2">/</span>
                        @else
                            <span class="text-gray-400 mx-2">•</span>
                        @endif
                    </li>
                @endif
            @endif

            @foreach($items as $index => $item)
                <li class="flex items-center">
                    @if(isset($item['url']) && $index < count($items) - 1)
                        <a href="{{ $item['url'] }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors duration-200">
                            {{ $item['title'] }}
                        </a>
                    @else
                        <span class="text-gray-900 dark:text-white font-medium">{{ $item['title'] }}</span>
                    @endif
                    
                    @if($index < count($items) - 1)
                        @if($separator === 'chevron')
                            <svg class="h-4 w-4 text-gray-400 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        @elseif($separator === 'slash')
                            <span class="text-gray-400 mx-2">/</span>
                        @else
                            <span class="text-gray-400 mx-2">•</span>
                        @endif
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif


