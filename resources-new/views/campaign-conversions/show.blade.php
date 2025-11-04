@extends('components.layouts.base')

@section('title', __('campaign_conversions.pages.show.title'))
@section('description', __('campaign_conversions.pages.show.description'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('campaign_conversions.pages.show.title') }}</h1>
                <p class="text-gray-600 mt-2">{{ __('campaign_conversions.pages.show.description') }}</p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('frontend.campaign-conversions.index') }}"
                   class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                    {{ __('campaign_conversions.actions.back_to_list') }}
                </a>
                <a href="{{ route('frontend.campaign-conversions.edit', $campaignConversion) }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    {{ __('campaign_conversions.actions.edit') }}
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        {{ __('campaign_conversions.sections.basic_information') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                   class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.id') }}</label>
                            <p class="mt-1 text-sm text-gray-900">#{{ $campaignConversion->id }}</p>
                        </div>
                        <div>
                            <label
                                   class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.campaign') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $campaignConversion->campaign?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <label
                                   class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.conversion_type') }}</label>
                            <span
                                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if ($campaignConversion->conversion_type === 'purchase') bg-green-100 text-green-800
                            @elseif($campaignConversion->conversion_type === 'signup') bg-blue-100 text-blue-800
                            @elseif($campaignConversion->conversion_type === 'download') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800 @endif">
                                {{ __('campaign_conversions.conversion_types.' . $campaignConversion->conversion_type) }}
                            </span>
                        </div>
                        <div>
                            <label
                                   class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.conversion_value') }}</label>
                            <p class="mt-1 text-sm font-bold text-gray-900">
                                €{{ number_format($campaignConversion->conversion_value, 2) }}</p>
                        </div>
                        <div>
                            <label
                                   class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.status') }}</label>
                            <span
                                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if ($campaignConversion->status === 'completed') bg-green-100 text-green-800
                            @elseif($campaignConversion->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($campaignConversion->status === 'cancelled') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                                {{ __('campaign_conversions.statuses.' . $campaignConversion->status) }}
                            </span>
                        </div>
                        <div>
                            <label
                                   class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.converted_at') }}</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $campaignConversion->converted_at->format('Y-m-d H:i:s') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        {{ __('campaign_conversions.sections.customer_information') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                   class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.customer') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $campaignConversion->customer?->email ?? '-' }}</p>
                        </div>
                        <div>
                            <label
                                   class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.order') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $campaignConversion->order?->id ?? '-' }}</p>
                        </div>
                        <div>
                            <label
                                   class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.session_id') }}</label>
                            <p class="mt-1 text-sm text-gray-900 font-mono">{{ $campaignConversion->session_id ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tracking Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        {{ __('campaign_conversions.sections.tracking_information') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                   class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.source') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $campaignConversion->source ?? '-' }}</p>
                        </div>
                        <div>
                            <label
                                   class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.medium') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $campaignConversion->medium ?? '-' }}</p>
                        </div>
                        <div>
                            <label
                                   class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.campaign_name') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $campaignConversion->campaign_name ?? '-' }}</p>
                        </div>
                        <div>
                            <label
                                   class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.utm_content') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $campaignConversion->utm_content ?? '-' }}</p>
                        </div>
                        <div>
                            <label
                                   class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.utm_term') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $campaignConversion->utm_term ?? '-' }}</p>
                        </div>
                        <div>
                            <label
                                   class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.referrer') }}</label>
                            <p class="mt-1 text-sm text-gray-900">
                                @if ($campaignConversion->referrer)
                                    <a href="{{ $campaignConversion->referrer }}" target="_blank"
                                       class="text-blue-600 hover:text-blue-800">
                                        {{ Str::limit($campaignConversion->referrer, 50) }}
                                    </a>
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Device Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        {{ __('campaign_conversions.sections.device_information') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                   class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.device_type') }}</label>
                            @if ($campaignConversion->device_type)
                                <span
                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if ($campaignConversion->device_type === 'mobile') bg-blue-100 text-blue-800
                                @elseif($campaignConversion->device_type === 'tablet') bg-yellow-100 text-yellow-800
                                @elseif($campaignConversion->device_type === 'desktop') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800 @endif">
                                    {{ __('campaign_conversions.device_types.' . $campaignConversion->device_type) }}
                                </span>
                            @else
                                <p class="mt-1 text-sm text-gray-900">-</p>
                            @endif
                        </div>
                        <div>
                            <label
                                   class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.browser') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $campaignConversion->browser ?? '-' }}</p>
                        </div>
                        <div>
                            <label
                                   class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.os') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $campaignConversion->os ?? '-' }}</p>
                        </div>
                        <div>
                            <label
                                   class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.country') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $campaignConversion->country ?? '-' }}</p>
                        </div>
                        <div>
                            <label
                                   class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.city') }}</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $campaignConversion->city ?? '-' }}</p>
                        </div>
                        <div>
                            <label
                                   class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.ip_address') }}</label>
                            <p class="mt-1 text-sm text-gray-900 font-mono">{{ $campaignConversion->ip_address ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                @if ($campaignConversion->roi || $campaignConversion->roas || $campaignConversion->conversion_rate)
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">
                            {{ __('campaign_conversions.sections.performance_metrics') }}</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @if ($campaignConversion->roi)
                                <div>
                                    <label
                                           class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.roi') }}</label>
                                    <p
                                       class="mt-1 text-sm font-bold text-gray-900
                            @if ($campaignConversion->roi > 0) text-green-600
                            @elseif($campaignConversion->roi < 0) text-red-600
                            @else text-gray-600 @endif">
                                        {{ number_format($campaignConversion->roi * 100, 2) }}%
                                    </p>
                                </div>
                            @endif
                            @if ($campaignConversion->roas)
                                <div>
                                    <label
                                           class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.roas') }}</label>
                                    <p class="mt-1 text-sm font-bold text-gray-900">
                                        {{ number_format($campaignConversion->roas * 100, 2) }}%</p>
                                </div>
                            @endif
                            @if ($campaignConversion->conversion_rate)
                                <div>
                                    <label
                                           class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.conversion_rate') }}</label>
                                    <p class="mt-1 text-sm font-bold text-gray-900">
                                        {{ number_format($campaignConversion->conversion_rate * 100, 2) }}%</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Additional Information -->
                @if ($campaignConversion->notes || $campaignConversion->tags || $campaignConversion->custom_attributes)
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">
                            {{ __('campaign_conversions.sections.additional_information') }}</h2>
                        @if ($campaignConversion->notes)
                            <div class="mb-4">
                                <label
                                       class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.notes') }}</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $campaignConversion->notes }}</p>
                            </div>
                        @endif
                        @if ($campaignConversion->tags)
                            <div class="mb-4">
                                <label
                                       class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.tags') }}</label>
                                <div class="mt-1 flex flex-wrap gap-2">
                                    @foreach ($campaignConversion->tags as $tag)
                                        <span
                                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if ($campaignConversion->custom_attributes)
                            <div>
                                <label
                                       class="block text-sm font-medium text-gray-700">{{ __('campaign_conversions.fields.custom_attributes') }}</label>
                                <div class="mt-1 bg-gray-50 rounded-lg p-4">
                                    <pre class="text-sm text-gray-900">{{ json_encode($campaignConversion->custom_attributes, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Stats -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ __('campaign_conversions.sections.quick_stats') }}</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span
                                  class="text-sm text-gray-600">{{ __('campaign_conversions.fields.conversion_value') }}</span>
                            <span
                                  class="text-sm font-bold text-gray-900">€{{ number_format($campaignConversion->conversion_value, 2) }}</span>
                        </div>
                        @if ($campaignConversion->page_views)
                            <div class="flex justify-between">
                                <span
                                      class="text-sm text-gray-600">{{ __('campaign_conversions.fields.page_views') }}</span>
                                <span class="text-sm font-bold text-gray-900">{{ $campaignConversion->page_views }}</span>
                            </div>
                        @endif
                        @if ($campaignConversion->time_on_site)
                            <div class="flex justify-between">
                                <span
                                      class="text-sm text-gray-600">{{ __('campaign_conversions.fields.time_on_site') }}</span>
                                <span
                                      class="text-sm font-bold text-gray-900">{{ gmdate('H:i:s', $campaignConversion->time_on_site) }}</span>
                            </div>
                        @endif
                        @if ($campaignConversion->bounce_rate)
                            <div class="flex justify-between">
                                <span
                                      class="text-sm text-gray-600">{{ __('campaign_conversions.fields.bounce_rate') }}</span>
                                <span
                                      class="text-sm font-bold text-gray-900">{{ number_format($campaignConversion->bounce_rate * 100, 2) }}%</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('campaign_conversions.sections.actions') }}
                    </h3>
                    <div class="space-y-3">
                        <a href="{{ route('frontend.campaign-conversions.edit', $campaignConversion) }}"
                           class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-center block">
                            {{ __('campaign_conversions.actions.edit') }}
                        </a>
                        <form method="POST"
                              action="{{ route('frontend.campaign-conversions.destroy', $campaignConversion) }}"
                              onsubmit="return confirm('{{ __('campaign_conversions.messages.confirm_delete') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                                {{ __('campaign_conversions.actions.delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
