@props([
    'content' => '',
    'tag' => 'div',
])

{{-- Render sanitized markup within a configurable wrapper tag. --}}
@php
    /** @var \App\Support\Html\HtmlSanitizer $sanitizer */
    $sanitizer = app(\App\Support\Html\HtmlSanitizer::class);
    $prepared = is_string($content) ? $content : ($content ?? '');
    $sanitized = $prepared === '' ? '' : $sanitizer->sanitize($prepared);
@endphp

<{{ $tag }} {{ $attributes }}>
    {!! $sanitized !!}
</{{ $tag }}>
