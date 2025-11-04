@props([
    'size' => 'md',
    'variant' => 'default', // default, minimal, colored
    'showLabels' => false,
    'platforms' => ['facebook', 'instagram', 'twitter', 'youtube', 'linkedin'],
])

@php
    $sizes = [
        'sm' => 'w-8 h-8',
        'md' => 'w-10 h-10',
        'lg' => 'w-12 h-12',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];

    $socialLinks = [
        'facebook' => [
            'url' => app_setting('social_facebook') ?? '#',
            'icon' =>
                '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
            'label' => 'Facebook',
            'color' => 'hover:text-blue-600',
        ],
        'instagram' => [
            'url' => app_setting('social_instagram') ?? '#',
            'icon' =>
                '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.62 5.367 11.987 11.988 11.987 6.62 0 11.987-5.367 11.987-11.987C24.014 5.367 18.637.001 12.017.001zM8.449 16.988c-1.297 0-2.448-.49-3.323-1.297C4.198 14.895 3.708 13.744 3.708 12.447s.49-2.448 1.297-3.323c.875-.807 2.026-1.297 3.323-1.297s2.448.49 3.323 1.297c.807.875 1.297 2.026 1.297 3.323s-.49 2.448-1.297 3.323c-.875.807-2.026 1.297-3.323 1.297zm7.83-9.281H7.721c-.807 0-1.297.49-1.297 1.297v8.449c0 .807.49 1.297 1.297 1.297h8.449c.807 0 1.297-.49 1.297-1.297V9.004c0-.807-.49-1.297-1.297-1.297z"/></svg>',
            'label' => 'Instagram',
            'color' => 'hover:text-pink-600',
        ],
        'twitter' => [
            'url' => app_setting('social_twitter') ?? '#',
            'icon' =>
                '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>',
            'label' => 'Twitter',
            'color' => 'hover:text-blue-400',
        ],
        'youtube' => [
            'url' => app_setting('social_youtube') ?? '#',
            'icon' =>
                '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
            'label' => 'YouTube',
            'color' => 'hover:text-red-600',
        ],
        'linkedin' => [
            'url' => app_setting('social_linkedin') ?? '#',
            'icon' =>
                '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
            'label' => 'LinkedIn',
            'color' => 'hover:text-blue-700',
        ],
    ];
@endphp

<div class="flex items-center {{ $showLabels ? 'flex-col space-y-2' : 'space-x-3' }}">
    @foreach ($platforms as $platform)
        @if (isset($socialLinks[$platform]))
            @php
                $link = $socialLinks[$platform];
                $baseClasses =
                    'flex items-center justify-center rounded-full transition-all duration-200 transform hover:scale-110';

                if ($variant === 'minimal') {
                    $classes = $baseClasses . ' ' . $sizeClass . ' text-gray-400 hover:text-gray-600 hover:bg-gray-100';
                } elseif ($variant === 'colored') {
                    $classes = $baseClasses . ' ' . $sizeClass . ' text-white ' . $link['color'];
                } else {
                    $classes = $baseClasses . ' ' . $sizeClass . ' text-gray-400 hover:text-white hover:bg-gray-800';
                }
            @endphp

            <a href="{{ $link['url'] }}"
               target="_blank"
               rel="noopener noreferrer"
               class="{{ $classes }}"
               aria-label="{{ $link['label'] }}">
                {!! $link['icon'] !!}
            </a>

            @if ($showLabels)
                <span class="text-xs text-gray-600">{{ $link['label'] }}</span>
            @endif
        @endif
    @endforeach
</div>

