@extends('layouts.app')

@section('title', $attribute->getDisplayName() . ' - ' . __('attributes.attribute_values'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <nav class="mb-8">
            <ol class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                <li><a href="{{ route('attribute-values.index') }}"
                       class="hover:text-gray-700 dark:hover:text-gray-300">{{ __('attributes.attribute_values') }}</a></li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                              clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-gray-900 dark:text-white">{{ $attribute->getDisplayName() }}</span>
                </li>
            </ol>
        </nav>

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                {{ $attribute->getDisplayName() }} - {{ __('attributes.attribute_values') }}
            </h1>
            @if ($attribute->getDisplayDescription())
                <p class="text-gray-600 dark:text-gray-400">
                    {{ $attribute->getDisplayDescription() }}
                </p>
            @endif
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

                            <!-- Status Badges -->
                            <div class="flex flex-wrap gap-1 mb-4">
                                @if ($attributeValue->is_required)
                                    <span
                                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                        {{ __('attributes.required') }}
                                    </span>
                                @endif

                                @if ($attributeValue->is_default)
                                    <span
                                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                        {{ __('attributes.default') }}
                                    </span>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="flex space-x-2">
                                <a href="{{ route('attribute-values.show', $attributeValue) }}"
                                   class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-3 rounded-md text-sm font-medium transition duration-200">
                                    {{ __('attributes.view') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $attributeValues->links() }}
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
                    {{ __('attributes.no_values_for_attribute') }}
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    {{ __('attributes.no_values_for_attribute_description') }}
                </p>
                <a href="{{ route('attribute-values.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition duration-200">
                    {{ __('attributes.back_to_all_values') }}
                </a>
            </div>
        @endif
    </div>
@endsection
