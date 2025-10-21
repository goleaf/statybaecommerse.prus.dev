@extends('frontend.layouts.app')

@php
    $defaultTitle = __('Terms & Conditions');
    $pageTitle = $legal?->getTranslatedSeoTitle() ?? $legal?->getTranslatedTitle() ?? $defaultTitle;
    $pageDescription = $legal?->getTranslatedSeoDescription() ?? __('Review the terms and conditions that govern your use of our storefront.');
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
                'emptyMessage' => __('Our terms are currently unavailable. Please contact support if you need assistance.'),
            ])
        </div>
    </section>
@endsection
