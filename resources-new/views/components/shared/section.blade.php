@props([
    'title' => null,
    'description' => null,
    'icon' => null,
    'iconColor' => 'text-blue-600',
    'titleSize' => 'text-2xl', // text-lg, text-xl, text-2xl, text-3xl
    'centered' => false,
    'spacing' => 'mb-8', // mb-4, mb-6, mb-8, mb-12
])

<div {{ $attributes->merge(['class' => $spacing]) }}>
    @if($title || $description || $icon)
        <div @class(['text-center' => $centered, 'mb-6'])>
            @if($icon || $title)
                <div @class(['flex items-center', 'justify-center' => $centered, 'mb-4'])>
                    @if($icon)
                        <x-dynamic-component :component="$icon" class="h-8 w-8 {{ $iconColor }} mr-3" />
                    @endif
                    @if($title)
                        <h2 class="{{ $titleSize }} font-bold text-gray-900 dark:text-white">{{ $title }}</h2>
                    @endif
                </div>
            @endif
            
            @if($description)
                <p class="text-lg text-gray-600 dark:text-gray-300">{{ $description }}</p>
            @endif
        </div>
    @endif
    
    {{ $slot }}
</div>
