@extends('components.layouts.base')

@section('title', __('attributes.attribute_values'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                {{ __('attributes.attribute_values') }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                {{ __('attributes.attribute_values_description') }}
            </p>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
            <form method="GET" action="{{ route('attribute-values.index') }}"
                  class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="attribute_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('attributes.attribute') }}
                    </label>
                    <select name="attribute_id" id="attribute_id"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">{{ __('attributes.all_attributes') }}</option>
                        @foreach ($attributes as $attribute)
                            <option value="{{ $attribute->id }}"
                                    {{ request('attribute_id') == $attribute->id ? 'selected' : '' }}>
                                {{ $attribute->getDisplayName() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('attributes.search') }}
                    </label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="{{ __('attributes.search_placeholder') }}"
                           class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>

                <div class="flex items-end space-x-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="with_color" value="1"
                               {{ request('with_color') ? 'checked' : '' }}
                               class="rounded border-gray-300 dark:border-gray-600">
                        <span
                              class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('attributes.with_color') }}</span>
                    </label>
                </div>

                <div class="flex items-end">
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                        {{ __('attributes.filter') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Attribute Values Grid -->
        @if ($attributeValues->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($attributeValues as $attributeValue)
                    <div
                         class="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200">
                        <div class="p-6">
                            <!-- Color indicator -->
                            @if ($attributeValue->color_code)
                                <div class="w-full h-4 rounded mb-4"
                                     style="background-color: {{ $attributeValue->color_code }}"></div>
                            @endif

                            <!-- Attribute name -->
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                {{ $attributeValue->attribute->getDisplayName() }}
                            </div>

                            <!-- Value -->
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                {{ $attributeValue->getDisplayValue() }}
                            </h3>

                            <!-- Description -->
                            @if ($attributeValue->getDisplayDescription())
                                <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
                                    {{ Str::limit($attributeValue->getDisplayDescription(), 100) }}
                                </p>
                            @endif

                            <!-- Stats -->
                            <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400 mb-4">
                                <span>{{ $attributeValue->products()->count() }} {{ __('attributes.products') }}</span>
                                <span>{{ $attributeValue->variants()->count() }} {{ __('attributes.variants') }}</span>
                            </div>

                            <!-- Actions -->
                            <div class="flex space-x-2">
                                <a href="{{ route('attribute-values.show', $attributeValue) }}"
                                   class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-3 rounded-md text-sm font-medium transition duration-200">
                                    {{ __('attributes.view') }}
                                </a>
                                <a href="{{ route('attribute-values.by-attribute', $attributeValue->attribute) }}"
                                   class="bg-gray-600 hover:bg-gray-700 text-white py-2 px-3 rounded-md text-sm font-medium transition duration-200">
                                    {{ __('attributes.all_values') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $attributeValues->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-gray-400 dark:text-gray-500 mb-4">
                    <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    {{ __('attributes.no_values_found') }}
                </h3>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ __('attributes.no_values_found_description') }}
                </p>
            </div>
        @endif
    </div>
@endsection
