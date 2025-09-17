@extends('layouts.app')

@section('title', $attributeValue->getDisplayValue())

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
                    <span class="text-gray-900 dark:text-white">{{ $attributeValue->getDisplayValue() }}</span>
                </li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                                {{ $attributeValue->getDisplayValue() }}
                            </h1>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('attributes.attribute') }}:
                                <a href="{{ route('attribute-values.by-attribute', $attributeValue->attribute) }}"
                                   class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ $attributeValue->attribute->getDisplayName() }}
                                </a>
                            </div>
                        </div>

                        <!-- Color indicator -->
                        @if ($attributeValue->color_code)
                            <div class="w-16 h-16 rounded-lg border-2 border-gray-200 dark:border-gray-600"
                                 style="background-color: {{ $attributeValue->color_code }}"></div>
                        @endif
                    </div>

                    <!-- Description -->
                    @if ($attributeValue->getDisplayDescription())
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                {{ __('attributes.description') }}
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                {{ $attributeValue->getDisplayDescription() }}
                            </p>
                        </div>
                    @endif

                    <!-- Meta Information -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                {{ $attributeValue->products()->count() }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('attributes.products') }}
                            </div>
                        </div>

                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                {{ $attributeValue->variants()->count() }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('attributes.variants') }}
                            </div>
                        </div>

                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                {{ $attributeValue->sort_order }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('attributes.sort_order') }}
                            </div>
                        </div>

                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                                {{ $attributeValue->translations()->count() }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('translations.translations') }}
                            </div>
                        </div>
                    </div>

                    <!-- Status Badges -->
                    <div class="flex flex-wrap gap-2 mb-6">
                        @if ($attributeValue->is_enabled)
                            <span
                                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                {{ __('attributes.enabled') }}
                            </span>
                        @endif

                        @if ($attributeValue->is_required)
                            <span
                                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                {{ __('attributes.required') }}
                            </span>
                        @endif

                        @if ($attributeValue->is_default)
                            <span
                                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                {{ __('attributes.default') }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Related Products -->
                @if ($attributeValue->products()->count() > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                            {{ __('attributes.related_products') }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($attributeValue->products()->limit(6)->get() as $product)
                                <div
                                     class="flex items-center space-x-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg">
                                    <div class="flex-shrink-0">
                                        @if ($product->featured_image)
                                            <img src="{{ Storage::url($product->featured_image) }}"
                                                 alt="{{ $product->getDisplayName() }}"
                                                 class="w-12 h-12 rounded-lg object-cover">
                                        @else
                                            <div
                                                 class="w-12 h-12 bg-gray-200 dark:bg-gray-600 rounded-lg flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                    </path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ $product->getDisplayName() }}
                                        </h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $product->sku }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if ($attributeValue->products()->count() > 6)
                            <div class="mt-4 text-center">
                                <a href="{{ localized_route('products.index', ['attribute_value_id' => $attributeValue->id]) }}"
                                   class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
                                    {{ __('attributes.view_all_products') }}
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Translations -->
                @if ($attributeValue->translations()->count() > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ __('translations.translations') }}
                        </h3>
                        <div class="space-y-3">
                            @foreach ($attributeValue->translations as $translation)
                                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-3">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ match ($translation->locale) {
                                                'lt' => __('translations.lithuanian'),
                                                'en' => __('translations.english'),
                                                'de' => __('translations.german'),
                                                default => $translation->locale,
                                            } }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $translation->locale }}
                                        </span>
                                    </div>
                                    @if ($translation->value)
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            <strong>{{ __('attributes.value') }}:</strong> {{ $translation->value }}
                                        </p>
                                    @endif
                                    @if ($translation->description)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            <strong>{{ __('attributes.description') }}:</strong>
                                            {{ $translation->description }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Meta Data -->
                @if ($attributeValue->meta_data && count($attributeValue->meta_data) > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ __('attributes.meta_data') }}
                        </h3>
                        <div class="space-y-2">
                            @foreach ($attributeValue->meta_data as $key => $value)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">{{ $key }}:</span>
                                    <span
                                          class="text-gray-900 dark:text-white">{{ is_array($value) ? json_encode($value) : $value }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('attributes.actions') }}
                    </h3>
                    <div class="space-y-2">
                        <a href="{{ route('attribute-values.by-attribute', $attributeValue->attribute) }}"
                           class="w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-4 rounded-md text-sm font-medium transition duration-200 block">
                            {{ __('attributes.view_all_values') }}
                        </a>
                        <a href="{{ route('attribute-values.index') }}"
                           class="w-full bg-gray-600 hover:bg-gray-700 text-white text-center py-2 px-4 rounded-md text-sm font-medium transition duration-200 block">
                            {{ __('attributes.back_to_list') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
