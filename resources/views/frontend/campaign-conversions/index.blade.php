@extends('frontend.layouts.app')

@section('title', __('campaign_conversions.pages.index.title'))
@section('description', __('campaign_conversions.pages.index.description'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('campaign_conversions.pages.index.title') }}</h1>
            <p class="text-gray-600 mt-2">{{ __('campaign_conversions.pages.index.description') }}</p>
        </div>
        <div class="flex space-x-4">
            <a href="{{ route('frontend.campaign-conversions.create') }}" 
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                {{ __('campaign_conversions.actions.create') }}
            </a>
            <a href="{{ route('frontend.campaign-conversions.export', request()->query()) }}" 
               class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                {{ __('campaign_conversions.actions.export') }}
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form method="GET" action="{{ route('frontend.campaign-conversions.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label for="campaign_id" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('campaign_conversions.filters.campaign') }}
                </label>
                <select name="campaign_id" id="campaign_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">{{ __('campaign_conversions.filters.all_campaigns') }}</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign->id }}" {{ request('campaign_id') == $campaign->id ? 'selected' : '' }}>
                            {{ $campaign->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="conversion_type" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('campaign_conversions.filters.conversion_type') }}
                </label>
                <select name="conversion_type" id="conversion_type" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">{{ __('campaign_conversions.filters.all_types') }}</option>
                    @foreach($conversionTypes as $type)
                        <option value="{{ $type }}" {{ request('conversion_type') == $type ? 'selected' : '' }}>
                            {{ __('campaign_conversions.conversion_types.' . $type) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('campaign_conversions.filters.status') }}
                </label>
                <select name="status" id="status" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">{{ __('campaign_conversions.filters.all_statuses') }}</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ __('campaign_conversions.statuses.' . $status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="device_type" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('campaign_conversions.filters.device_type') }}
                </label>
                <select name="device_type" id="device_type" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">{{ __('campaign_conversions.filters.all_devices') }}</option>
                    @foreach($deviceTypes as $device)
                        <option value="{{ $device }}" {{ request('device_type') == $device ? 'selected' : '' }}>
                            {{ __('campaign_conversions.device_types.' . $device) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="source" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('campaign_conversions.filters.source') }}
                </label>
                <select name="source" id="source" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">{{ __('campaign_conversions.filters.all_sources') }}</option>
                    @foreach($sources as $source)
                        <option value="{{ $source }}" {{ request('source') == $source ? 'selected' : '' }}>
                            {{ $source }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('campaign_conversions.filters.date_from') }}
                </label>
                <input type="date" name="date_from" id="date_from" 
                       value="{{ request('date_from') }}" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>

            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('campaign_conversions.filters.date_to') }}
                </label>
                <input type="date" name="date_to" id="date_to" 
                       value="{{ request('date_to') }}" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    {{ __('campaign_conversions.actions.filter') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Analytics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">{{ __('campaign_conversions.widgets.total_conversions') }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $conversions->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">{{ __('campaign_conversions.widgets.total_value') }}</p>
                    <p class="text-2xl font-bold text-gray-900">€{{ number_format($conversions->sum('conversion_value'), 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">{{ __('campaign_conversions.widgets.average_value') }}</p>
                    <p class="text-2xl font-bold text-gray-900">€{{ number_format($conversions->avg('conversion_value') ?? 0, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">{{ __('campaign_conversions.widgets.conversion_rate') }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format(($conversions->avg('conversion_rate') ?? 0) * 100, 2) }}%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Conversions Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('campaign_conversions.pages.index.conversions_list') }}</h2>
        </div>

        @if($conversions->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('campaign_conversions.table.id') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('campaign_conversions.table.campaign') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('campaign_conversions.table.conversion_type') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('campaign_conversions.table.conversion_value') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('campaign_conversions.table.status') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('campaign_conversions.table.device_type') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('campaign_conversions.table.converted_at') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('campaign_conversions.table.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($conversions as $conversion)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #{{ $conversion->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $conversion->campaign?->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($conversion->conversion_type === 'purchase') bg-green-100 text-green-800
                                        @elseif($conversion->conversion_type === 'signup') bg-blue-100 text-blue-800
                                        @elseif($conversion->conversion_type === 'download') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ __('campaign_conversions.conversion_types.' . $conversion->conversion_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    €{{ number_format($conversion->conversion_value, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($conversion->status === 'completed') bg-green-100 text-green-800
                                        @elseif($conversion->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($conversion->status === 'cancelled') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ __('campaign_conversions.statuses.' . $conversion->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($conversion->device_type)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($conversion->device_type === 'mobile') bg-blue-100 text-blue-800
                                            @elseif($conversion->device_type === 'tablet') bg-yellow-100 text-yellow-800
                                            @elseif($conversion->device_type === 'desktop') bg-green-100 text-green-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ __('campaign_conversions.device_types.' . $conversion->device_type) }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $conversion->converted_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('frontend.campaign-conversions.show', $conversion) }}" 
                                           class="text-blue-600 hover:text-blue-900">
                                            {{ __('campaign_conversions.actions.view') }}
                                        </a>
                                        <a href="{{ route('frontend.campaign-conversions.edit', $conversion) }}" 
                                           class="text-green-600 hover:text-green-900">
                                            {{ __('campaign_conversions.actions.edit') }}
                                        </a>
                                        <form method="POST" action="{{ route('frontend.campaign-conversions.destroy', $conversion) }}" 
                                              class="inline" onsubmit="return confirm('{{ __('campaign_conversions.messages.confirm_delete') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                {{ __('campaign_conversions.actions.delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $conversions->links() }}
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('campaign_conversions.messages.no_conversions') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('campaign_conversions.messages.no_conversions_description') }}</p>
                <div class="mt-6">
                    <a href="{{ route('frontend.campaign-conversions.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        {{ __('campaign_conversions.actions.create_first') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

