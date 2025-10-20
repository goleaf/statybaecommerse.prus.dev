@extends('components.layouts.base')

@php
    $hasQuery = filled($query);
    $pageTitle = $hasQuery
        ? __('campaigns.search.title', ['query' => $query])
        : __('campaigns.search.title_default');
    $pageDescription = $hasQuery
        ? __('campaigns.search.description', ['query' => $query])
        : __('campaigns.search.description_default');
@endphp

@section('title', $pageTitle)
@section('description', $pageDescription)

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                {{ $pageTitle }}
            </h1>
            <p class="text-gray-600 dark:text-gray-300">
                {{ $pageDescription }}
            </p>
        </div>

        @include('campaigns.partials.campaign-grid', [
            'campaigns' => $campaigns,
            'showPagination' => true,
            'emptyTitle' => $hasQuery
                ? __('campaigns.search.empty_title', ['query' => $query])
                : __('campaigns.search.empty_title_default'),
            'emptyDescription' => $hasQuery
                ? __('campaigns.search.empty_description', ['query' => $query])
                : __('campaigns.search.empty_description_default'),
        ])
    </div>
@endsection
