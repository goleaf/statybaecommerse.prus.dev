@extends('frontend.layouts.app')

@section('title', $attribute->name)
@section('description', $attribute->description ?: __('attributes.attribute_details'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('frontend.home') }}"
                       class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <x-heroicon-o-home class="w-4 h-4 mr-2" />
                        {{ __('common.home') }}
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <x-heroicon-o-chevron-right class="w-4 h-4 text-gray-400" />
                        <a href="{{ route('frontend.attributes.index') }}"
                           class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">
                            {{ __('attributes.attributes') }}
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <x-heroicon-o-chevron-right class="w-4 h-4 text-gray-400" />
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $attribute->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Attribute Header -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-4">
                            @if ($attribute->icon)
                                <div class="flex-shrink-0">
                                    <x-heroicon-o-adjustments-horizontal class="h-8 w-8 text-gray-400" />
                                </div>
                            @endif
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">{{ $attribute->name }}</h1>
                                <p class="text-gray-600">{{ __('attributes.' . $attribute->type) }}</p>
                            </div>
                        </div>
                        @if ($attribute->color)
                            <div class="w-8 h-8 rounded-full border-2 border-gray-200"
                                 style="background-color: {{ $attribute->color }}"></div>
                        @endif
                    </div>

                    @if ($attribute->description)
                        <div class="prose max-w-none">
                            <p class="text-gray-700">{{ $attribute->description }}</p>
                        </div>
                    @endif

                    <!-- Attribute Properties -->
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ __('attributes.attribute_properties') }}
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <div class="text-sm text-gray-500">{{ __('attributes.type') }}</div>
                                <div class="font-medium">{{ __('attributes.' . $attribute->type) }}</div>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <div class="text-sm text-gray-500">{{ __('attributes.required') }}</div>
                                <div class="font-medium">
                                    @if ($attribute->is_required)
                                        <span class="text-green-600">{{ __('common.yes') }}</span>
                                    @else
                                        <span class="text-gray-500">{{ __('common.no') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <div class="text-sm text-gray-500">{{ __('attributes.filterable') }}</div>
                                <div class="font-medium">
                                    @if ($attribute->is_filterable)
                                        <span class="text-green-600">{{ __('common.yes') }}</span>
                                    @else
                                        <span class="text-gray-500">{{ __('common.no') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <div class="text-sm text-gray-500">{{ __('attributes.searchable') }}</div>
                                <div class="font-medium">
                                    @if ($attribute->is_searchable)
                                        <span class="text-green-600">{{ __('common.yes') }}</span>
                                    @else
                                        <span class="text-gray-500">{{ __('common.no') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attribute Values -->
                @if ($attribute->values->count() > 0)
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('attributes.available_values') }}</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($attribute->values as $value)
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="font-medium text-gray-900">{{ $value->display_value ?: $value->value }}
                                        </h3>
                                        @if ($value->color)
                                            <div class="w-4 h-4 rounded-full border border-gray-200"
                                                 style="background-color: {{ $value->color }}"></div>
                                        @endif
                                    </div>
                                    @if ($value->description)
                                        <p class="text-sm text-gray-600">{{ $value->description }}</p>
                                    @endif
                                    <div class="mt-2 text-xs text-gray-500">
                                        {{ __('attributes.sort_order') }}: {{ $value->sort_order }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Products Using This Attribute -->
                @if ($attribute->products->count() > 0)
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">
                            {{ __('attributes.products_using_this_attribute') }} ({{ $attribute->products->count() }})
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($attribute->products->take(6) as $product)
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                                    <div class="flex items-center space-x-3">
                                        @if ($product->getFirstMediaUrl('images'))
                                            <img src="{{ $product->getFirstMediaUrl('images', 'thumb') }}"
                                                 alt="{{ $product->name }}"
                                                 class="w-12 h-12 object-cover rounded">
                                        @else
                                            <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                                <x-heroicon-o-photo class="w-6 h-6 text-gray-400" />
                                            </div>
                                        @endif
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-sm font-medium text-gray-900 truncate">
                                                <a href="{{ route('frontend.products.show', $product) }}"
                                                   class="hover:text-blue-600">
                                                    {{ $product->name }}
                                                </a>
                                            </h3>
                                            <p class="text-sm text-gray-500">{{ $product->brand?->name }}</p>
                                            <p class="text-sm font-medium text-gray-900">
                                                €{{ number_format($product->price, 2) }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if ($attribute->products->count() > 6)
                            <div class="mt-4 text-center">
                                <a href="{{ route('frontend.products.index', ['attribute' => $attribute->id]) }}"
                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200">
                                    {{ __('attributes.view_all_products') }}
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Related Attributes -->
                @if ($relatedAttributes->count() > 0)
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('attributes.related_attributes') }}</h3>
                        <div class="space-y-3">
                            @foreach ($relatedAttributes as $relatedAttribute)
                                <div class="border border-gray-200 rounded-lg p-3 hover:border-blue-300 transition-colors">
                                    <a href="{{ route('frontend.attributes.show', $relatedAttribute) }}" class="block">
                                        <h4 class="font-medium text-gray-900 hover:text-blue-600">
                                            {{ $relatedAttribute->name }}</h4>
                                        <p class="text-sm text-gray-500">{{ __('attributes.' . $relatedAttribute->type) }}
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            {{ __('attributes.values_count') }}: {{ $relatedAttribute->values->count() }}
                                        </p>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Attribute Info -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('attributes.additional_info') }}</h3>
                    <dl class="space-y-3">
                        @if ($attribute->group_name)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('attributes.group_name') }}</dt>
                                <dd class="text-sm text-gray-900">{{ __('attributes.' . $attribute->group_name) }}</dd>
                            </div>
                        @endif
                        @if ($attribute->placeholder)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('attributes.placeholder') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $attribute->placeholder }}</dd>
                            </div>
                        @endif
                        @if ($attribute->help_text)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('attributes.help_text') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $attribute->help_text }}</dd>
                            </div>
                        @endif
                        @if ($attribute->default_value)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('attributes.default_value') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $attribute->default_value }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('attributes.sort_order') }}</dt>
                            <dd class="text-sm text-gray-900">{{ $attribute->sort_order }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
