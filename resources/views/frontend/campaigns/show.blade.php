@extends('frontend.layouts.app')

@section('title', $campaign->name)
@section('description', $campaign->meta_description ?: $campaign->description)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Campaign Header -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        @if($campaign->banner_image)
            <div class="relative h-64 md:h-96 overflow-hidden">
                <img src="{{ $campaign->getBannerUrl() }}" 
                     alt="{{ $campaign->banner_alt_text ?: $campaign->name }}"
                     class="w-full h-full object-cover">
                
                <div class="absolute inset-0 bg-black bg-opacity-30"></div>
                
                <div class="absolute bottom-6 left-6 text-white">
                    <h1 class="text-3xl md:text-4xl font-bold mb-2">{{ $campaign->name }}</h1>
                    @if($campaign->description)
                        <p class="text-lg opacity-90 max-w-2xl">{{ $campaign->description }}</p>
                    @endif
                </div>
                
                @if($campaign->is_featured)
                    <div class="absolute top-4 right-4">
                        <span class="bg-yellow-400 text-yellow-900 px-3 py-1 rounded-full text-sm font-semibold">
                            <i class="fas fa-star mr-1"></i>{{ __('campaigns.featured') }}
                        </span>
                    </div>
                @endif
            </div>
        @else
            <div class="h-64 md:h-96 bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                <div class="text-center text-white">
                    <i class="fas fa-megaphone text-6xl mb-4"></i>
                    <h1 class="text-3xl md:text-4xl font-bold mb-2">{{ $campaign->name }}</h1>
                    @if($campaign->description)
                        <p class="text-lg opacity-90 max-w-2xl">{{ $campaign->description }}</p>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Campaign Details -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('campaigns.campaign_details') }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-2">{{ __('campaigns.fields.status') }}</h3>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            {{ $campaign->status === 'active' ? 'bg-green-100 text-green-800' : 
                               ($campaign->status === 'scheduled' ? 'bg-yellow-100 text-yellow-800' : 
                               ($campaign->status === 'expired' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                            {{ $campaign->getStatusLabel() }}
                        </span>
                    </div>
                    
                    @if($campaign->starts_at)
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">{{ __('campaigns.fields.starts_at') }}</h3>
                            <p class="text-gray-600">{{ $campaign->starts_at->format('d/m/Y H:i') }}</p>
                        </div>
                    @endif
                    
                    @if($campaign->ends_at)
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">{{ __('campaigns.fields.ends_at') }}</h3>
                            <p class="text-gray-600">{{ $campaign->ends_at->format('d/m/Y H:i') }}</p>
                        </div>
                    @endif
                    
                    @if($campaign->channel)
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">{{ __('campaigns.fields.channel') }}</h3>
                            <p class="text-gray-600">{{ $campaign->channel->name }}</p>
                        </div>
                    @endif
                    
                    @if($campaign->zone)
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">{{ __('campaigns.fields.zone') }}</h3>
                            <p class="text-gray-600">{{ $campaign->zone->name }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Campaign Products -->
            @if($products->count() > 0)
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('campaigns.campaign_products') }}</h2>
                        <a href="{{ route('campaigns.products', $campaign->slug) }}" 
                           class="text-blue-600 hover:text-blue-800 font-medium">
                            {{ __('campaigns.view_all_products') }}
                        </a>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($products->take(6) as $product)
                            @include('frontend.products.partials.product-card', ['product' => $product])
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Campaign Analytics (if user has permission) -->
            @if($campaign->track_conversions && auth()->check() && auth()->user()->can('view', $campaign))
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('campaigns.performance_analytics') }}</h2>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ number_format($campaign->total_views) }}</div>
                            <div class="text-sm text-gray-600">{{ __('campaigns.stats.views') }}</div>
                        </div>
                        
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">{{ number_format($campaign->total_clicks) }}</div>
                            <div class="text-sm text-gray-600">{{ __('campaigns.stats.clicks') }}</div>
                        </div>
                        
                        <div class="text-center p-4 bg-purple-50 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600">{{ number_format($campaign->total_conversions) }}</div>
                            <div class="text-sm text-gray-600">{{ __('campaigns.stats.conversions') }}</div>
                        </div>
                        
                        <div class="text-center p-4 bg-yellow-50 rounded-lg">
                            <div class="text-2xl font-bold text-yellow-600">{{ $campaign->getConversionRate() }}%</div>
                            <div class="text-sm text-gray-600">{{ __('campaigns.stats.conversion_rate') }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- CTA Section -->
            @if($campaign->cta_text && $campaign->cta_url)
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">{{ __('campaigns.take_action') }}</h3>
                    <a href="{{ $campaign->cta_url }}" 
                       class="w-full bg-blue-600 text-white text-center py-3 px-4 rounded-md hover:bg-blue-700 transition-colors duration-200 block text-lg font-semibold"
                       onclick="trackCampaignClick({{ $campaign->id }}, 'cta', '{{ $campaign->cta_url }}')">
                        {{ $campaign->cta_text }}
                    </a>
                </div>
            @endif

            <!-- Campaign Info -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4">{{ __('campaigns.campaign_info') }}</h3>
                
                <div class="space-y-3">
                    @if($campaign->budget_limit)
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ __('campaigns.fields.budget_limit') }}:</span>
                            <span class="font-semibold">€{{ number_format($campaign->budget_limit, 2) }}</span>
                        </div>
                    @endif
                    
                    @if($campaign->max_uses)
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ __('campaigns.fields.max_uses') }}:</span>
                            <span class="font-semibold">{{ number_format($campaign->max_uses) }}</span>
                        </div>
                    @endif
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('campaigns.fields.created_at') }}:</span>
                        <span class="font-semibold">{{ $campaign->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Related Campaigns -->
            @if($relatedCampaigns->count() > 0)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">{{ __('campaigns.related_campaigns') }}</h3>
                    
                    <div class="space-y-4">
                        @foreach($relatedCampaigns as $relatedCampaign)
                            <div class="flex items-center space-x-3">
                                @if($relatedCampaign->banner_image)
                                    <img src="{{ $relatedCampaign->getBannerUrl() }}" 
                                         alt="{{ $relatedCampaign->name }}"
                                         class="w-16 h-16 object-cover rounded-lg">
                                @else
                                    <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-megaphone text-white"></i>
                                    </div>
                                @endif
                                
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 line-clamp-2">
                                        <a href="{{ route('campaigns.show', $relatedCampaign->slug) }}" 
                                           class="hover:text-blue-600 transition-colors duration-200">
                                            {{ $relatedCampaign->name }}
                                        </a>
                                    </h4>
                                    <p class="text-sm text-gray-600">{{ $relatedCampaign->getStatusLabel() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function trackCampaignClick(campaignId, type, url) {
    fetch(`/campaigns/${campaignId}/click`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            type: type,
            url: url
        })
    }).catch(error => console.error('Error tracking click:', error));
}
</script>
@endpush
@endsection
