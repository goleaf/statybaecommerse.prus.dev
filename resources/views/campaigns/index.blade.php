@extends('components.layouts.base')

@section('title', __('messages.campaigns_section'))
@section('description', __('campaigns.index.description'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                {{ __('messages.campaigns') }}
            </h1>
            <p class="text-gray-600 dark:text-gray-300">
                {{ __('campaigns.index.description') }}
            </p>
        </div>

        @include('campaigns.partials.campaign-grid', [
            'campaigns' => $campaigns,
            'showPagination' => true,
        ])
    </div>
@endsection
