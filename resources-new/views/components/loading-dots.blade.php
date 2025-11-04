@php
    $baseClasses = 'inline-block size-1 rounded-full';
    $animationClasses = 'animate-pulse';
@endphp

<span class="inline-flex items-center gap-1 hidden" {{ $attributes }}>
    <span class="{{ $baseClasses }} {{ $animationClasses }}"></span>
    <span class="{{ $baseClasses }} {{ $animationClasses }} animation-delay-200"></span>
    <span class="{{ $baseClasses }} {{ $animationClasses }} animation-delay-400"></span>
</span>
