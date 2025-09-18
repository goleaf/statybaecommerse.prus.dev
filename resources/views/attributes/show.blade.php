@extends('components.layouts.base')

@section('title', $attribute->getTranslatedName())
@section('description', $attribute->getTranslatedDescription())

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                    <i class="fas fa-home mr-2"></i>
                    {{ __('common.home') }}
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                    <a href="{{ route('attributes.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2 dark:text-gray-400 dark:hover:text-white">
                        {{ __('attributes.title') }}
                    </a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">
                        {{ $attribute->getTranslatedName() }}
                    </span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Attribute Header -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-8">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-lg font-semibold
                            {{ match($attribute->type) {
                                'text' => 'bg-gray-500',
                                'number' => 'bg-blue-500',
                                'boolean' => 'bg-green-500',
                                'select' => 'bg-yellow-500',
                                'multiselect' => 'bg-orange-500',
                                'color' => 'bg-purple-500',
                                'date' => 'bg-red-500',
                                'textarea' => 'bg-indigo-500',
                                'file' => 'bg-pink-500',
                                'image' => 'bg-rose-500',
                                default => 'bg-gray-500'
                            } }}">
                            <i class="fas {{ $attribute->getTypeIconAttribute() }}"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                                {{ $attribute->getTranslatedName() }}
                            </h1>
                            <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ match($attribute->type) {
                                        'text' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                                        'number' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                        'boolean' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        'select' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        'multiselect' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                                        'color' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                                        'date' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
                                    } }}">
                                    {{ __('attributes.' . $attribute->type) }}
                                </span>
                                @if($attribute->is_required)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        {{ __('attributes.required') }}
                                    </span>
                                @endif
                                <span>
                                    <i class="fas fa-list mr-1"></i>
                                    {{ $attribute->getValuesCount() }} {{ __('attributes.values') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($attribute->getTranslatedDescription())
                    <div class="prose dark:prose-invert max-w-none">
                        <p class="text-gray-700 dark:text-gray-300">
                            {{ $attribute->getTranslatedDescription() }}
                        </p>
                    </div>
                @endif
            </div>

            <!-- Attribute Values Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                    {{ __('attributes.values_in_attribute') }}
                </h2>

                @if($attribute->values->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($attribute->values as $value)
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $value->value }}
                                    </h3>
                                    @if($value->is_enabled)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            {{ __('attributes.enabled') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                            {{ __('attributes.disabled') }}
                                        </span>
                                    @endif
                                </div>

                                @if($value->description)
                                    <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">
                                        {{ Str::limit($value->description, 80) }}
                                    </p>
                                @endif

                                <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                    <span>
                                        <i class="fas fa-sort mr-1"></i>
                                        {{ $value->sort_order }}
                                    </span>
                                    @if($value->usage_count ?? 0 > 0)
                                        <span>
                                            <i class="fas fa-chart-bar mr-1"></i>
                                            {{ $value->usage_count ?? 0 }} {{ __('attributes.usage') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="text-gray-400 dark:text-gray-500 text-4xl mb-4">
                            <i class="fas fa-list"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                            {{ __('attributes.empty_states.no_values') }}
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400">
                            {{ __('attributes.empty_states.no_values_description') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Attribute Info -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('attributes.attribute_info') }}
                </h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">{{ __('attributes.type') }}:</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ __('attributes.' . $attribute->type) }}
                        </span>
                    </div>
                    
                    @if($attribute->group_name)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-300">{{ __('attributes.group_name') }}:</span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ $attribute->group_name }}
                            </span>
                        </div>
                    @endif
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">{{ __('attributes.values_count') }}:</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ $attribute->getValuesCount() }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">{{ __('attributes.usage_count') }}:</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ $attribute->getUsageCount() }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">{{ __('attributes.popularity_score') }}:</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ $attribute->getPopularityScore() }}/100
                        </span>
                    </div>
                </div>
            </div>

            <!-- Attribute Properties -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('attributes.attribute_properties') }}
                </h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">{{ __('attributes.required') }}:</span>
                        <span class="font-medium {{ $attribute->is_required ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $attribute->is_required ? __('common.yes') : __('common.no') }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">{{ __('attributes.filterable') }}:</span>
                        <span class="font-medium {{ $attribute->is_filterable ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $attribute->is_filterable ? __('common.yes') : __('common.no') }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">{{ __('attributes.searchable') }}:</span>
                        <span class="font-medium {{ $attribute->is_searchable ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $attribute->is_searchable ? __('common.yes') : __('common.no') }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">{{ __('attributes.visible') }}:</span>
                        <span class="font-medium {{ $attribute->is_visible ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $attribute->is_visible ? __('common.yes') : __('common.no') }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">{{ __('attributes.editable') }}:</span>
                        <span class="font-medium {{ $attribute->is_editable ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $attribute->is_editable ? __('common.yes') : __('common.no') }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">{{ __('attributes.sortable') }}:</span>
                        <span class="font-medium {{ $attribute->is_sortable ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $attribute->is_sortable ? __('common.yes') : __('common.no') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Related Attributes -->
            @if($relatedAttributes->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('attributes.related_attributes') }}
                    </h3>
                    
                    <div class="space-y-4">
                        @foreach($relatedAttributes as $relatedAttribute)
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-semibold
                                    {{ match($relatedAttribute->type) {
                                        'text' => 'bg-gray-500',
                                        'number' => 'bg-blue-500',
                                        'boolean' => 'bg-green-500',
                                        'select' => 'bg-yellow-500',
                                        'multiselect' => 'bg-orange-500',
                                        'color' => 'bg-purple-500',
                                        'date' => 'bg-red-500',
                                        default => 'bg-gray-500'
                                    } }}">
                                    <i class="fas {{ $relatedAttribute->getTypeIconAttribute() }}"></i>
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $relatedAttribute->getTranslatedName() }}
                                    </h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $relatedAttribute->getValuesCount() }} {{ __('attributes.values') }}
                                    </p>
                                </div>
                                
                                <a href="{{ route('attributes.show', $relatedAttribute) }}" 
                                   class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection