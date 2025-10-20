@extends('frontend.layouts.app')

@php
    $defaultTitle = __('Cookie Policy');
    $pageTitle = $legal?->getTranslatedSeoTitle() ?? $legal?->getTranslatedTitle() ?? $defaultTitle;
    $pageDescription = $legal?->getTranslatedSeoDescription() ?? __('Learn how we use cookies and similar technologies to improve your experience.');
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
                'emptyMessage' => __('Our cookie policy is currently unavailable. Please contact support if you have any questions.'),
            ])
        </div>
    </section>
@endsection
