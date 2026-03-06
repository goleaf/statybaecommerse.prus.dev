@extends('frontend.layouts.app')

@php
    $pageTitle = $page['seo_title'] ?? $page['title'] ?? __('messages.frontend');
    $pageDescription = $page['seo_description'] ?? $page['description'] ?? null;
@endphp

@section('title', $pageTitle)

@section('meta')
    @if ($pageDescription)
        <meta name="description" content="{{ $pageDescription }}">
    @endif
@endsection

@section('content')
    @include('frontend.info.partials.page', [
        'page' => $page,
        'actions' => $actions,
        'relatedPages' => $relatedPages,
    ])
@endsection
