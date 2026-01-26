@extends('frontend.layouts.app')

@php
    $defaultTitle = __('messages.frontend_legal);
    $pageTitle = $legal?->getTranslatedSeoTitle() ?? $legal?->getTranslatedTitle() ?? $defaultTitle;
    $pageDescription = $legal?->getTranslatedSeoDescription() ?? __('frontend.legal.descriptions.shipping');
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
                'emptyMessage' => __('messages.frontend_legal),
                'fallbackKey' => 'shipping',
            ])
        </div>
    </section>
@endsection
