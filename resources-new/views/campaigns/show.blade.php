@extends('components.layouts.base')

@section('title', $campaign->trans('meta_title') ?: $campaign->trans('name'))
@section('description', $campaign->trans('meta_description') ?: $campaign->trans('description'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <nav class="mb-8">
            <ol class="flex items-center space-x-2 text-sm">
                <li>
                    <a href="{{ route('frontend.campaigns.index') }}"
                       class="text-blue-600 dark:text-blue-400 hover:underline">
                        {{ __('campaigns.navigation.campaigns') }}
                    </a>
                </li>
                <li class="text-gray-500 dark:text-gray-400">/</li>
                <li class="text-gray-900 dark:text-white font-medium">
                    {{ $campaign->trans('name') }}
                </li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Campaign Header -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden mb-8">
                    @if ($campaign->banner_image)
                        <div class="aspect-w-16 aspect-h-9">
                            <img src="{{ $campaign->getBannerUrl() }}"
                                 alt="{{ $campaign->trans('banner_alt_text') }}"
                                 class="w-full h-64 object-cover">
                        </div>
                    @endif

                    <div class="p-8">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <span
                                      class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                @if ($campaign->type === 'email') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                @elseif($campaign->type === 'sms') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                @elseif($campaign->type === 'push') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @elseif($campaign->type === 'banner') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                                @elseif($campaign->type === 'popup') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                                    {{ __('campaigns.types.' . $campaign->type) }}
                                </span>

                                @if ($campaign->is_featured)
                                    <span
                                          class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                  d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                            </path>
                                        </svg>
                                        {{ __('campaigns.fields.is_featured') }}
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                                @if ($campaign->total_views > 0)
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                        {{ number_format($campaign->total_views) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                            {{ $campaign->trans('name') }}
                        </h1>

                        @if ($campaign->trans('description'))
                            <div class="prose dark:prose-invert max-w-none mb-6">
                                {!! nl2br(e($campaign->trans('description'))) !!}
                            </div>
                        @endif

                        <!-- Campaign Content -->
                        @if ($campaign->trans('content'))
                            <div class="prose dark:prose-invert max-w-none mb-6">
                                {!! $campaign->trans('content') !!}
                            </div>
                        @endif

                        <!-- Call to Action -->
                        @if ($campaign->trans('cta_text') && $campaign->cta_url)
                            <div class="mt-8">
                                <a href="{{ $campaign->cta_url }}"
                                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
                                   onclick="recordCampaignClick('{{ $campaign->id }}', 'cta', '{{ $campaign->cta_url }}')">
                                    {{ $campaign->trans('cta_text') }}
                                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Campaign Details -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('campaigns.sections.campaign_details') }}
                    </h2>

                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if ($campaign->start_date)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('campaigns.fields.start_date') }}
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $campaign->start_date->format('d.m.Y H:i') }}
                                </dd>
                            </div>
                        @endif

                        @if ($campaign->end_date)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('campaigns.fields.end_date') }}
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $campaign->end_date->format('d.m.Y H:i') }}
                                </dd>
                            </div>
                        @endif

                        @if ($campaign->budget)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('campaigns.fields.budget') }}
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    €{{ number_format($campaign->budget, 2) }}
                                </dd>
                            </div>
                        @endif

                        @if ($campaign->channel)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('campaigns.fields.channel_id') }}
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $campaign->channel->name }}
                                </dd>
                            </div>
                        @endif

                        @if ($campaign->zone)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('campaigns.fields.zone_id') }}
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $campaign->zone->name }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Campaign Stats -->
                @if (
                    $campaign->track_conversions &&
                        ($campaign->total_views > 0 || $campaign->total_clicks > 0 || $campaign->total_conversions > 0))
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ __('campaigns.analytics.performance') }}
                        </h3>

                        <div class="space-y-4">
                            @if ($campaign->total_views > 0)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('campaigns.analytics.views') }}
                                    </span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ number_format($campaign->total_views) }}
                                    </span>
                                </div>
                            @endif

                            @if ($campaign->total_clicks > 0)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('campaigns.analytics.clicks') }}
                                    </span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ number_format($campaign->total_clicks) }}
                                    </span>
                                </div>
                            @endif

                            @if ($campaign->total_conversions > 0)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('campaigns.analytics.conversions') }}
                                    </span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ number_format($campaign->total_conversions) }}
                                    </span>
                                </div>
                            @endif

                            @if ($campaign->total_clicks > 0 && $campaign->total_views > 0)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('campaigns.analytics.ctr') }}
                                    </span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ number_format($campaign->getClickThroughRate(), 2) }}%
                                    </span>
                                </div>
                            @endif

                            @if ($campaign->total_conversions > 0 && $campaign->total_clicks > 0)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('campaigns.analytics.conversion_rate') }}
                                    </span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ number_format($campaign->getConversionRate(), 2) }}%
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Target Categories -->
                @if ($campaign->targetCategories->count() > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ __('campaigns.fields.target_categories') }}
                        </h3>

                        <div class="flex flex-wrap gap-2">
                            @foreach ($campaign->targetCategories as $category)
                                <span
                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ $category->trans('name') }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Target Products -->
                @if ($campaign->targetProducts->count() > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ __('campaigns.fields.target_products') }}
                        </h3>

                        <div class="space-y-2">
                            @foreach ($campaign->targetProducts->take(5) as $product)
                                <a href="{{ route('frontend.products.show', $product) }}"
                                   class="block p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <div class="flex items-center space-x-3">
                                        @if ($product->featured_image)
                                            <img src="{{ $product->getFeaturedImageUrl() }}"
                                                 alt="{{ $product->trans('name') }}"
                                                 class="w-10 h-10 object-cover rounded">
                                        @endif
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                {{ $product->trans('name') }}
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                €{{ number_format($product->price, 2) }}
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            @endforeach

                            @if ($campaign->targetProducts->count() > 5)
                                <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                                    {{ __('campaigns.messages.and_more_products', ['count' => $campaign->targetProducts->count() - 5]) }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Related Campaigns -->
                @if ($relatedCampaigns->count() > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ __('campaigns.sections.related_campaigns') }}
                        </h3>

                        <div class="space-y-4">
                            @foreach ($relatedCampaigns as $relatedCampaign)
                                <a href="{{ route('frontend.campaigns.show', $relatedCampaign) }}"
                                   class="block group">
                                    <div class="flex items-center space-x-3">
                                        @if ($relatedCampaign->banner_image)
                                            <img src="{{ $relatedCampaign->getBannerUrl() }}"
                                                 alt="{{ $relatedCampaign->trans('name') }}"
                                                 class="w-12 h-12 object-cover rounded">
                                        @else
                                            <div
                                                 class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 0h10m-10 0a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2">
                                                    </path>
                                                </svg>
                                            </div>
                                        @endif

                                        <div class="flex-1 min-w-0">
                                            <p
                                               class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 truncate">
                                                {{ $relatedCampaign->trans('name') }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ __('campaigns.types.' . $relatedCampaign->type) }}
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function recordCampaignClick(campaignId, clickType, clickedUrl) {
            fetch('{{ route('frontend.campaigns.click', ':id') }}'.replace(':id', campaignId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    type: clickType,
                    url: clickedUrl
                })
            }).catch(error => {
                console.error('Error recording campaign click:', error);
            });
        }
    </script>
@endpush
