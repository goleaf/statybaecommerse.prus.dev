@extends('frontend.layouts.app')

@php
    $defaultTitle = __('frontend.legal.privacy_policy');
    $pageTitle = $legal?->getTranslatedSeoTitle() ?? $legal?->getTranslatedTitle() ?? $defaultTitle;
    $pageDescription = $legal?->getTranslatedSeoDescription() ?? __('frontend.legal.descriptions.privacy');

    $documentName = \Illuminate\Support\Str::lower($defaultTitle);
    $emptyMessage = __('frontend.legal.document_unavailable', [
        'document' => $documentName,
    ]);
@endphp

@section('title', $pageTitle)

@section('meta')
    <meta name="description" content="{{ $pageDescription }}">
@endsection

@section('content')
    <section class="bg-gray-50 dark:bg-gray-950 py-12 md:py-16">
        <div class="container mx-auto px-4">
            @include('frontend.legal.partials.document', [
                'legal' => $legal,
                'heading' => $legal?->getTranslatedTitle() ?? $defaultTitle,
                'description' => $pageDescription,
                'emptyMessage' => $emptyMessage,
                'fallbackKey' => 'privacy',
            ])
        </div>
    </section>
@endsection
