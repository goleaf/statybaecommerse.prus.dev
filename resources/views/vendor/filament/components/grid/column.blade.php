{{-- Column helper to complement the custom Filament grid fallback. --}}
@props([
    'default' => 1,
    'sm' => null,
    'md' => null,
    'lg' => null,
    'xl' => null,
    'twoXl' => null,
    'defaultStart' => null,
    'smStart' => null,
    'mdStart' => null,
    'lgStart' => null,
    'xlStart' => null,
    'twoXlStart' => null,
])

@php
    $spans = [
        'default' => $default,
        'sm' => $sm,
        'md' => $md,
        'lg' => $lg,
        'xl' => $xl,
        '2xl' => $twoXl,
    ];

    $starts = [
        'default' => $defaultStart,
        'sm' => $smStart,
        'md' => $mdStart,
        'lg' => $lgStart,
        'xl' => $xlStart,
        '2xl' => $twoXlStart,
    ];

    $classes = ['fi-grid-column'];

    foreach ($spans as $breakpoint => $span) {
        if (empty($span)) {
            continue;
        }

        $prefix = $breakpoint === 'default' ? '' : ($breakpoint === '2xl' ? '2xl:' : $breakpoint . ':');
        $classes[] = $prefix . 'col-span-' . $span;
    }

    foreach ($starts as $breakpoint => $start) {
        if (empty($start)) {
            continue;
        }

        $prefix = $breakpoint === 'default' ? '' : ($breakpoint === '2xl' ? '2xl:' : $breakpoint . ':');
        $classes[] = $prefix . 'col-start-' . $start;
    }

    if (! in_array('col-span-1', $classes, true) && empty($spans['default'])) {
        $classes[] = 'col-span-1';
    }

    $preparedAttributes = \Filament\Support\prepare_inherited_attributes($attributes)->class(implode(' ', $classes));
@endphp

<div {{ $preparedAttributes }}>
    {{ $slot }}
</div>
