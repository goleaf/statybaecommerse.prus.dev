{{-- Local fallback for the Filament grid view so third-party widgets continue to render. --}}
@props([
    'default' => 1,
    'sm' => null,
    'md' => null,
    'lg' => null,
    'xl' => null,
    'twoXl' => null,
])

@php
    $columns = [
        'default' => $default,
        'sm' => $sm,
        'md' => $md,
        'lg' => $lg,
        'xl' => $xl,
        '2xl' => $twoXl,
    ];

    $classes = ['fi-grid', 'grid'];

    foreach ($columns as $breakpoint => $count) {
        if (empty($count)) {
            continue;
        }

        $prefix = $breakpoint === 'default' ? '' : ($breakpoint === '2xl' ? '2xl:' : $breakpoint . ':');
        $classes[] = $prefix . 'grid-cols-' . $count;
    }

    $preparedAttributes = \Filament\Support\prepare_inherited_attributes($attributes)->class(implode(' ', $classes));
@endphp

<div {{ $preparedAttributes }}>
    {{ $slot }}
</div>
