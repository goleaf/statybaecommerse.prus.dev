@extends('frontend.layouts.app')

@section('title', __('attributes.attributes'))
@section('description', __('attributes.attributes_description'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ __('attributes.attributes') }}</h1>
            <p class="text-gray-600">{{ __('attributes.attributes_description') }}</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <form method="GET" action="{{ route('frontend.attributes.index') }}"
                  class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search"
                           class="block text-sm font-medium text-gray-700 mb-2">{{ __('attributes.search') }}</label>
                    <input type="text"
                           id="search"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="{{ __('attributes.search_placeholder') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="type"
                           class="block text-sm font-medium text-gray-700 mb-2">{{ __('attributes.type') }}</label>
                    <select id="type" name="type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">{{ __('attributes.all_types') }}</option>
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>
                                {{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="group"
                           class="block text-sm font-medium text-gray-700 mb-2">{{ __('attributes.group_name') }}</label>
                    <select id="group" name="group"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">{{ __('attributes.all_groups') }}</option>
                        @foreach ($groups as $value => $label)
                            <option value="{{ $value }}" {{ request('group') == $value ? 'selected' : '' }}>
                                {{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit"
                            class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        {{ __('attributes.filter') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Attributes Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($attributes as $attribute)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="p-6">
                        <!-- Attribute Header -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                @if ($attribute->icon)
                                    <div class="flex-shrink-0">
                                        <x-heroicon-o-adjustments-horizontal class="h-6 w-6 text-gray-400" />
                                    </div>
                                @endif
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $attribute->name }}</h3>
                                    <p class="text-sm text-gray-500">{{ __('attributes.' . $attribute->type) }}</p>
                                </div>
                            </div>
                            @if ($attribute->color)
                                <div class="w-4 h-4 rounded-full" style="background-color: {{ $attribute->color }}"></div>
                            @endif
                        </div>

                        <!-- Attribute Description -->
                        @if ($attribute->description)
                            <p class="text-gray-600 text-sm mb-4">{{ Str::limit($attribute->description, 100) }}</p>
                        @endif

                        <!-- Attribute Properties -->
                        <div class="flex flex-wrap gap-2 mb-4">
                            @if ($attribute->is_required)
                                <span
                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ __('attributes.required') }}
                                </span>
                            @endif
                            @if ($attribute->is_filterable)
                                <span
                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ __('attributes.filterable') }}
                                </span>
                            @endif
                            @if ($attribute->is_searchable)
                                <span
                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ __('attributes.searchable') }}
                                </span>
                            @endif
                        </div>

                        <!-- Attribute Values -->
                        @if ($attribute->values->count() > 0)
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">{{ __('attributes.available_values') }}
                                </h4>
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($attribute->values->take(5) as $value)
                                        <span
                                              class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $value->display_value ?: $value->value }}
                                        </span>
                                    @endforeach
                                    @if ($attribute->values->count() > 5)
                                        <span
                                              class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-200 text-gray-600">
                                            +{{ $attribute->values->count() - 5 }} {{ __('attributes.more') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Action Button -->
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">
                                {{ __('attributes.values_count') }}: {{ $attribute->values->count() }}
                            </span>
                            <a href="{{ route('frontend.attributes.show', $attribute) }}"
                               class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ __('attributes.view_details') }}
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <div class="text-gray-400 mb-4">
                        <x-heroicon-o-adjustments-horizontal class="h-16 w-16 mx-auto" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('attributes.no_attributes_found') }}</h3>
                    <p class="text-gray-500">{{ __('attributes.no_attributes_description') }}</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($attributes->hasPages())
            <div class="mt-8">
                {{ $attributes->links() }}
            </div>
        @endif
    </div>
@endsection
