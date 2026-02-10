@extends('components.layouts.base')

@php
    $typeLabel = __(sprintf('campaigns.types.%s', $type));
@endphp

@section('title', __('campaigns.by_type.title', ['type' => $typeLabel]))
@section('description', __('campaigns.by_type.description', ['type' => $typeLabel]))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                {{ __('campaigns.by_type.heading', ['type' => $typeLabel]) }}
            </h1>
            <p class="text-gray-600 dark:text-gray-300">
                {{ __('campaigns.by_type.description', ['type' => $typeLabel]) }}
            </p>
        </div>

        @include('campaigns.partials.campaign-grid', [
            'campaigns' => $campaigns,
            'showPagination' => true,
            'emptyTitle' => __('campaigns.by_type.empty_title', ['type' => $typeLabel]),
            'emptyDescription' => __('campaigns.by_type.empty_description', ['type' => $typeLabel]),
        ])
    </div>
@endsection
