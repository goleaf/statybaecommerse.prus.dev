@props([
    'content' => null,
    'tag' => null,
])

@php
    $html = $content instanceof \Illuminate\Support\HtmlString ? $content->toHtml() : ($content ?? '');
@endphp

@if (blank($html))
    {{-- No sanitized content to render. --}}
@elseif ($tag)
    <{{ $tag }} {!! $attributes !!}>{!! $html !!}</{{ $tag }}>
@elseif ($attributes->isNotEmpty())
    <div {!! $attributes !!}>{!! $html !!}</div>
@else
    {!! $html !!}
@endif
