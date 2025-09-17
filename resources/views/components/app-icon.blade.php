@props(['name'])

@php
    $componentName = is_string($name) ? $name : '';
    $viewName = 'components.' . str_replace(':', '.', $componentName);
@endphp

@if ($componentName !== '' && view()->exists($viewName))
    <x-dynamic-component :component="$componentName" {{ $attributes }} />
@else
    <x-untitledui-shopping-bag {{ $attributes }} />
@endif
