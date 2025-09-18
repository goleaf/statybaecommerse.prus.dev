@extends('components.layouts.base')

@section('title', __('campaign_clicks.click_details'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('campaign_clicks.click_details') }}</h1>

            <a href="{{ route('campaign-clicks.index') }}"
               class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                {{ __('campaign_clicks.back_to_list') }}
            </a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">{{ __('campaign_clicks.basic_information') }}</h3>
            </div>

            <div class="px-6 py-4">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('campaign_clicks.id') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $campaignClick->id }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('campaign_clicks.campaign') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $campaignClick->campaign->name ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('campaign_clicks.customer') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $campaignClick->customer->name ?? __('campaign_clicks.guest') }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('campaign_clicks.click_type') }}</dt>
                        <dd class="mt-1">
                            <span
                                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $campaignClick->click_type_label }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('campaign_clicks.clicked_url') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if ($campaignClick->clicked_url)
                                <a href="{{ $campaignClick->clicked_url }}" target="_blank"
                                   class="text-blue-600 hover:text-blue-900">
                                    {{ Str::limit($campaignClick->clicked_url, 50) }}
                                </a>
                            @else
                                -
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('campaign_clicks.clicked_at') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $campaignClick->clicked_at->format('Y-m-d H:i:s') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="mt-6 bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">{{ __('campaign_clicks.device_information') }}</h3>
            </div>

            <div class="px-6 py-4">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('campaign_clicks.device_type') }}</dt>
                        <dd class="mt-1">
                            <span
                                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                {{ $campaignClick->device_type_label }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('campaign_clicks.browser') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $campaignClick->browser_label }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('campaign_clicks.os') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $campaignClick->os_label }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('campaign_clicks.ip_address') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $campaignClick->ip_address }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="mt-6 bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">{{ __('campaign_clicks.location_information') }}</h3>
            </div>

            <div class="px-6 py-4">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('campaign_clicks.country') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $campaignClick->country ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('campaign_clicks.city') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $campaignClick->city ?? '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        @if ($campaignClick->utm_source || $campaignClick->utm_medium || $campaignClick->utm_campaign)
            <div class="mt-6 bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('campaign_clicks.utm_parameters') }}</h3>
                </div>

                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('campaign_clicks.utm_source') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $campaignClick->utm_source ?? '-' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('campaign_clicks.utm_medium') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $campaignClick->utm_medium ?? '-' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('campaign_clicks.utm_campaign') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $campaignClick->utm_campaign ?? '-' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('campaign_clicks.utm_term') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $campaignClick->utm_term ?? '-' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('campaign_clicks.utm_content') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $campaignClick->utm_content ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        @endif

        <div class="mt-6 bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">{{ __('campaign_clicks.conversion_tracking') }}</h3>
            </div>

            <div class="px-6 py-4">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('campaign_clicks.converted') }}</dt>
                        <dd class="mt-1">
                            <span
                                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $campaignClick->is_converted ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $campaignClick->is_converted ? __('campaign_clicks.yes') : __('campaign_clicks.no') }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('campaign_clicks.conversion_value') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">€{{ number_format($campaignClick->conversion_value, 2) }}
                        </dd>
                    </div>

                    @if ($campaignClick->conversions->count() > 0)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">{{ __('campaign_clicks.conversions') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <div class="space-y-2">
                                    @foreach ($campaignClick->conversions as $conversion)
                                        <div class="border rounded-lg p-3">
                                            <div class="flex justify-between items-center">
                                                <span class="font-medium">{{ $conversion->conversion_type }}</span>
                                                <span
                                                      class="text-green-600 font-semibold">€{{ number_format($conversion->conversion_value, 2) }}</span>
                                            </div>
                                            <div class="text-sm text-gray-500 mt-1">
                                                {{ $conversion->converted_at->format('Y-m-d H:i:s') }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
@endsection
