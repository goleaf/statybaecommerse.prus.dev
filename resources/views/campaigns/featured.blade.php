@extends('components.layouts.base')

@section('title', __('campaigns.featured.title'))
@section('description', __('campaigns.featured.description'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                {{ __('campaigns.featured.title') }}
            </h1>
            <p class="text-gray-600 dark:text-gray-300">
                {{ __('campaigns.featured.description') }}
            </p>
        </div>

        @include('campaigns.partials.campaign-grid', [
            'campaigns' => $campaigns,
            'showPagination' => false,
            'emptyTitle' => __('campaigns.featured.empty_title'),
            'emptyDescription' => __('campaigns.featured.empty_description'),
        ])
    </div>
@endsection
