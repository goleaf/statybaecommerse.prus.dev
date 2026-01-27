@props([
    'actions' => [],
    'fullWidth' => false,
    'alignment' => null,
])

<x-filament::actions
    :actions="$actions"
    :alignment="$alignment"
    :full-width="$fullWidth"
/>
