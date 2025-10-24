@extends('frontend.layouts.app')

@php
    $defaultTitleKey = 'frontend.legal.privacy_policy';
    $defaultTitle = __($defaultTitleKey);
    if ($defaultTitle === $defaultTitleKey) {
        $defaultTitle = 'Privacy Policy';
    }

    $pageTitle = $legal?->getTranslatedSeoTitle() ?? $legal?->getTranslatedTitle() ?? $defaultTitle;
    $defaultDescriptionKey = 'frontend.legal.descriptions.privacy';
    $pageDescription = $legal?->getTranslatedSeoDescription() ?? __($defaultDescriptionKey);
    if ($pageDescription === $defaultDescriptionKey) {
        $pageDescription = 'Review how we collect, use, and protect your personal information.';
    }

    $documentName = \Illuminate\Support\Str::lower($defaultTitle);
    if ($documentName === 'frontend.legal.privacy_policy') {
        $documentName = 'privacy policy';
    }

    $emptyMessage = __('frontend.legal.document_unavailable', [
        'document' => $documentName,
    ]);
    if ($emptyMessage === 'frontend.legal.document_unavailable') {
        $emptyMessage = sprintf('Our %s is currently unavailable.', $documentName);
    }
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
