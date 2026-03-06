@extends('frontend.layouts.app')

@php
    $page = \App\Support\Frontend\InfoPages::get('returns') ?? [];
    $defaultTitle = $page['title'] ?? __('frontend.legal.return_policy');
    $pageTitle = $legal?->getTranslatedSeoTitle() ?? $legal?->getTranslatedTitle() ?? $defaultTitle;
    $pageDescription = $legal?->getTranslatedSeoDescription() ?? $page['description'] ?? __('frontend.legal.descriptions.returns');
    $contentHtml = $legal?->getTranslatedContent();
    $emptyMessage = __('frontend.legal.document_unavailable', [
        'document' => \Illuminate\Support\Str::lower($defaultTitle),
    ]);
@endphp

@section('title', $pageTitle)

@section('meta')
    <meta name="description" content="{{ $pageDescription }}">
@endsection

@section('content')
    @include('frontend.info.partials.page', [
        'page' => array_merge($page, [
            'title' => $legal?->getTranslatedTitle() ?? $defaultTitle,
            'description' => $pageDescription,
        ]),
        'relatedPages' => \App\Support\Frontend\InfoPages::resolveRelatedPages($page['related_pages'] ?? []),
        'actions' => \App\Support\Frontend\InfoPages::resolveActions($page['actions'] ?? []),
        'contentHtml' => $contentHtml,
        'documentMeta' => array_filter([
            \App\Models\Legal::getTypes()[$legal?->type ?? ''] ?? null,
            $legal?->is_required ? __('messages.is_required') : null,
        ]),
        'emptyMessage' => $emptyMessage,
    ])
@endsection
