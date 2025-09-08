@props([
    'title',
    'description' => null,
    'icon' => null,
    'iconColor' => 'text-blue-600',
    'breadcrumbs' => [],
    'actions' => null,
    'centered' => true,
    'background' => 'bg-white dark:bg-gray-800',
])

<div class="{{ $background }} shadow-sm">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Breadcrumbs --}}
        @if(!empty($breadcrumbs))
            <nav class="mb-6" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm">
                    @foreach($breadcrumbs as $index => $breadcrumb)
                        <li class="flex items-center">
                            @if($index > 0)
                                <svg class="h-4 w-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            @endif
                            
                            @if(isset($breadcrumb['url']) && $index < count($breadcrumbs) - 1)
                                <a href="{{ $breadcrumb['url'] }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                    {{ $breadcrumb['title'] }}
                                </a>
                            @else
                                <span class="text-gray-900 dark:text-white font-medium">{{ $breadcrumb['title'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif

        {{-- Header Content --}}
        <div @class(['text-center' => $centered, 'flex items-center justify-between' => $actions && !$centered])>
            <div @class(['flex-1' => $actions && !$centered])>
                @if($icon || $title)
                    <div @class(['flex items-center', 'justify-center' => $centered, 'mb-4'])>
                        @if($icon)
                            <x-dynamic-component :component="$icon" class="h-8 w-8 {{ $iconColor }} mr-3" />
                        @endif
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                            {{ $title }}
                        </h1>
                    </div>
                @endif
                
                @if($description)
                    <p @class([
                        'text-lg text-gray-600 dark:text-gray-300',
                        'mx-auto mt-4 max-w-2xl' => $centered,
                        'mt-2' => !$centered
                    ])>
                        {{ $description }}
                    </p>
                @endif
            </div>

            {{-- Actions --}}
            @if($actions)
                <div class="flex items-center space-x-4">
                    {{ $actions }}
                </div>
            @endif
        </div>

        {{-- Additional Content --}}
        @if($slot->isNotEmpty())
            <div class="mt-8">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
