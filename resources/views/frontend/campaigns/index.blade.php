@extends('frontend.layouts.app')

@section('title', __('campaigns.navigation.campaigns'))
@section('description', __('campaigns.meta.description'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                {{ __('campaigns.navigation.campaigns') }}
            </h1>
            <p class="text-gray-600 dark:text-gray-300">
                {{ __('campaigns.index.description') }}
            </p>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('campaigns.filters.search') }}
                    </label>
                    <input type="text"
                           id="search"
                           name="search"
                           value="{{ request('search') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                           placeholder="{{ __('campaigns.filters.search_placeholder') }}">
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('campaigns.fields.type') }}
                    </label>
                    <select id="type"
                            name="type"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">{{ __('campaigns.filters.all_types') }}</option>
                        @foreach (__('campaigns.types') as $key => $label)
                            <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('campaigns.fields.target_categories') }}
                    </label>
                    <select id="category"
                            name="category"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">{{ __('campaigns.filters.all_categories') }}</option>
                        @foreach ($categories ?? [] as $category)
                            <option value="{{ $category->slug }}"
                                    {{ request('category') === $category->slug ? 'selected' : '' }}>
                                {{ $category->trans('name') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit"
                            class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        {{ __('campaigns.filters.apply') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Campaigns Grid -->
        @if ($campaigns->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach ($campaigns as $campaign)
                    <div
                         class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                        @if ($campaign->banner_image)
                            <div class="aspect-w-16 aspect-h-9">
                                <img src="{{ $campaign->getBannerUrl() }}"
                                     alt="{{ $campaign->trans('banner_alt_text') }}"
                                     class="w-full h-48 object-cover">
                            </div>
                        @endif

                        <div class="p-6">
                            <div class="flex items-center justify-between mb-3">
                                <span
                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
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
                                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        {{ __('campaigns.fields.is_featured') }}
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                <a href="{{ route('frontend.campaigns.show', $campaign) }}"
                                   class="hover:text-blue-600 dark:hover:text-blue-400">
                                    {{ $campaign->trans('name') }}
                                </a>
                            </h3>

                            @if ($campaign->trans('description'))
                                <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 line-clamp-3">
                                    {{ Str::limit($campaign->trans('description'), 120) }}
                                </p>
                            @endif

                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    @if ($campaign->start_date)
                                        <div>{{ __('campaigns.fields.start_date') }}:
                                            {{ $campaign->start_date->format('d.m.Y') }}</div>
                                    @endif
                                    @if ($campaign->end_date)
                                        <div>{{ __('campaigns.fields.end_date') }}:
                                            {{ $campaign->end_date->format('d.m.Y') }}</div>
                                    @endif
                                </div>

                                <a href="{{ route('frontend.campaigns.show', $campaign) }}"
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    {{ __('campaigns.actions.view_details') }}
                                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $campaigns->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div class="mx-auto h-12 w-12 text-gray-400">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                        </path>
                    </svg>
                </div>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                    {{ __('campaigns.messages.no_campaigns') }}
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('campaigns.messages.no_campaigns_description') }}
                </p>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-submit form on filter change
            const form = document.querySelector('form');
            const selects = form.querySelectorAll('select');

            selects.forEach(select => {
                select.addEventListener('change', function() {
                    form.submit();
                });
            });
        });
    </script>
@endpush
