@php
    $dots = 'mx-[1px] inline-block size-1 animate-blink';
@endphp

<span class="inline-flex items-center px-2">
    <span {{ $attributes->merge(['class' => $dots]) }}></span>
    <span {{ $attributes->merge(['class' => $dots . ' animation-delay-[200ms]']) }}></span>
    <span {{ $attributes->merge(['class' => $dots . ' animation-delay-[400ms]']) }}></span>
</span>
