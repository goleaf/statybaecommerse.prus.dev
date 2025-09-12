@extends('frontend.layouts.app')

@section('title', __('campaigns.page_title'))
@section('description', __('campaigns.page_description'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ __('campaigns.page_title') }}</h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">{{ __('campaigns.page_description') }}</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form method="GET" action="{{ route('campaigns.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">{{ __('campaigns.filters.search') }}</label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="{{ __('campaigns.filters.search_placeholder') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label for="channel" class="block text-sm font-medium text-gray-700 mb-2">{{ __('campaigns.filters.channel') }}</label>
                <select id="channel" name="channel" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('campaigns.filters.all_channels') }}</option>
                    @foreach($channels as $channel)
                        <option value="{{ $channel->id }}" {{ request('channel') == $channel->id ? 'selected' : '' }}>
                            {{ $channel->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="zone" class="block text-sm font-medium text-gray-700 mb-2">{{ __('campaigns.filters.zone') }}</label>
                <select id="zone" name="zone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('campaigns.filters.all_zones') }}</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone->id }}" {{ request('zone') == $zone->id ? 'selected' : '' }}>
                            {{ $zone->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                    {{ __('campaigns.filters.apply') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Featured Campaigns -->
    @if($featuredCampaigns->count() > 0)
    <div class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('campaigns.featured_campaigns') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($featuredCampaigns as $campaign)
                @include('frontend.campaigns.partials.campaign-card', ['campaign' => $campaign, 'featured' => true])
            @endforeach
        </div>
    </div>
    @endif

    <!-- All Campaigns -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('campaigns.all_campaigns') }}</h2>
        
        @if($campaigns->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($campaigns as $campaign)
                    @include('frontend.campaigns.partials.campaign-card', ['campaign' => $campaign])
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-8">
                {{ $campaigns->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">
                    <i class="fas fa-megaphone"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('campaigns.no_campaigns_found') }}</h3>
                <p class="text-gray-600">{{ __('campaigns.no_campaigns_description') }}</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form on filter change
    const filterForm = document.querySelector('form');
    const selects = filterForm.querySelectorAll('select');
    
    selects.forEach(select => {
        select.addEventListener('change', function() {
            filterForm.submit();
        });
    });
});
</script>
@endpush
@endsection
