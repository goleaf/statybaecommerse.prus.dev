@props(['campaign', 'featured' => false])

<div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 {{ $featured ? 'ring-2 ring-yellow-400' : '' }}">
    <!-- Campaign Banner -->
    @if($campaign->banner_image)
        <div class="relative h-48 overflow-hidden">
            <img src="{{ $campaign->getBannerUrl() }}" 
                 alt="{{ $campaign->banner_alt_text ?: $campaign->name }}"
                 class="w-full h-full object-cover">
            
            @if($featured)
                <div class="absolute top-2 right-2">
                    <span class="bg-yellow-400 text-yellow-900 px-2 py-1 rounded-full text-xs font-semibold">
                        <i class="fas fa-star mr-1"></i>{{ __('campaigns.featured') }}
                    </span>
                </div>
            @endif
            
            @if($campaign->isActive())
                <div class="absolute top-2 left-2">
                    <span class="bg-green-500 text-white px-2 py-1 rounded-full text-xs font-semibold">
                        {{ __('campaigns.status.active') }}
                    </span>
                </div>
            @endif
        </div>
    @else
        <div class="h-48 bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
            <div class="text-center text-white">
                <i class="fas fa-megaphone text-4xl mb-2"></i>
                <h3 class="font-semibold">{{ $campaign->name }}</h3>
            </div>
        </div>
    @endif

    <!-- Campaign Content -->
    <div class="p-6">
        <div class="flex items-start justify-between mb-3">
            <h3 class="text-lg font-semibold text-gray-900 line-clamp-2">
                <a href="{{ route('campaigns.show', $campaign->slug) }}" 
                   class="hover:text-blue-600 transition-colors duration-200">
                    {{ $campaign->name }}
                </a>
            </h3>
            
            @if($campaign->display_priority > 0)
                <div class="flex items-center text-yellow-500 ml-2">
                    <i class="fas fa-star text-sm"></i>
                    <span class="text-xs ml-1">{{ $campaign->display_priority }}</span>
                </div>
            @endif
        </div>

        @if($campaign->description)
            <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ $campaign->description }}</p>
        @endif

        <!-- Campaign Stats -->
        @if($campaign->track_conversions && ($campaign->total_views > 0 || $campaign->total_clicks > 0))
            <div class="grid grid-cols-3 gap-2 mb-4 text-center">
                <div class="bg-gray-50 rounded-lg p-2">
                    <div class="text-lg font-semibold text-gray-900">{{ number_format($campaign->total_views) }}</div>
                    <div class="text-xs text-gray-600">{{ __('campaigns.stats.views') }}</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-2">
                    <div class="text-lg font-semibold text-gray-900">{{ number_format($campaign->total_clicks) }}</div>
                    <div class="text-xs text-gray-600">{{ __('campaigns.stats.clicks') }}</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-2">
                    <div class="text-lg font-semibold text-gray-900">{{ $campaign->getConversionRate() }}%</div>
                    <div class="text-xs text-gray-600">{{ __('campaigns.stats.conversion_rate') }}</div>
                </div>
            </div>
        @endif

        <!-- Campaign Dates -->
        <div class="text-sm text-gray-500 mb-4">
            @if($campaign->starts_at)
                <div class="flex items-center mb-1">
                    <i class="fas fa-play text-green-500 mr-2"></i>
                    <span>{{ __('campaigns.starts_at') }}: {{ $campaign->starts_at->format('d/m/Y') }}</span>
                </div>
            @endif
            
            @if($campaign->ends_at)
                <div class="flex items-center">
                    <i class="fas fa-stop text-red-500 mr-2"></i>
                    <span>{{ __('campaigns.ends_at') }}: {{ $campaign->ends_at->format('d/m/Y') }}</span>
                </div>
            @endif
        </div>

        <!-- CTA Button -->
        @if($campaign->cta_text && $campaign->cta_url)
            <a href="{{ $campaign->cta_url }}" 
               class="w-full bg-blue-600 text-white text-center py-2 px-4 rounded-md hover:bg-blue-700 transition-colors duration-200 block"
               onclick="trackCampaignClick({{ $campaign->id }}, 'cta', '{{ $campaign->cta_url }}')">
                {{ $campaign->cta_text }}
            </a>
        @else
            <a href="{{ route('campaigns.show', $campaign->slug) }}" 
               class="w-full bg-gray-600 text-white text-center py-2 px-4 rounded-md hover:bg-gray-700 transition-colors duration-200 block">
                {{ __('campaigns.view_details') }}
            </a>
        @endif
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












