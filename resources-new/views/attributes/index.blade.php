@extends('components.layouts.base')

@section('title', __('attributes.title'))
@section('description', __('attributes.description'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
            {{ __('attributes.title') }}
        </h1>
        <p class="text-gray-600 dark:text-gray-300">
            {{ __('attributes.description') }}
        </p>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('attributes.filters.search') }}
                </label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="{{ __('attributes.placeholders.search') }}"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('attributes.filters.type') }}
                </label>
                <select id="type" 
                        name="type"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">{{ __('attributes.filters.all_types') }}</option>
                    <option value="text" {{ request('type') === 'text' ? 'selected' : '' }}>
                        {{ __('attributes.text') }}
                    </option>
                    <option value="number" {{ request('type') === 'number' ? 'selected' : '' }}>
                        {{ __('attributes.number') }}
                    </option>
                    <option value="boolean" {{ request('type') === 'boolean' ? 'selected' : '' }}>
                        {{ __('attributes.boolean') }}
                    </option>
                    <option value="select" {{ request('type') === 'select' ? 'selected' : '' }}>
                        {{ __('attributes.select') }}
                    </option>
                    <option value="multiselect" {{ request('type') === 'multiselect' ? 'selected' : '' }}>
                        {{ __('attributes.multiselect') }}
                    </option>
                    <option value="color" {{ request('type') === 'color' ? 'selected' : '' }}>
                        {{ __('attributes.color') }}
                    </option>
                    <option value="date" {{ request('type') === 'date' ? 'selected' : '' }}>
                        {{ __('attributes.date') }}
                    </option>
                </select>
            </div>

            <div>
                <label for="group" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('attributes.filters.group_name') }}
                </label>
                <select id="group" 
                        name="group"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">{{ __('attributes.filters.all_groups') }}</option>
                    @foreach($attributes->pluck('group_name')->filter()->unique() as $group)
                        <option value="{{ $group }}" {{ request('group') === $group ? 'selected' : '' }}>
                            {{ $group }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('attributes.filters.properties') }}
                </label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="required" value="1" {{ request('required') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('attributes.required') }}</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="filterable" value="1" {{ request('filterable') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('attributes.filterable') }}</span>
                    </label>
                </div>
            </div>

            <div class="flex items-end">
                <button type="submit" 
                        class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    {{ __('attributes.actions.filter') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Attributes Grid -->
    @if($attributes->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($attributes as $attribute)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-semibold
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
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $attribute->getTranslatedName() }}
                                </h3>
                            </div>
                            @if($attribute->is_required)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    {{ __('attributes.required') }}
                                </span>
                            @endif
                        </div>

                        @if($attribute->getTranslatedDescription())
                            <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 line-clamp-2">
                                {{ Str::limit($attribute->getTranslatedDescription(), 100) }}
                            </p>
                        @endif

                        <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400 mb-4">
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
                            <span>
                                <i class="fas fa-list mr-1"></i>
                                {{ $attribute->getValuesCount() }} {{ __('attributes.values') }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-4">
                            <div class="flex space-x-4">
                                @if($attribute->is_filterable)
                                    <span class="flex items-center">
                                        <i class="fas fa-filter mr-1"></i>
                                        {{ __('attributes.filterable') }}
                                    </span>
                                @endif
                                @if($attribute->is_searchable)
                                    <span class="flex items-center">
                                        <i class="fas fa-search mr-1"></i>
                                        {{ __('attributes.searchable') }}
                                    </span>
                                @endif
                            </div>
                            @if($attribute->group_name)
                                <span class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                    {{ $attribute->group_name }}
                                </span>
                            @endif
                        </div>

                        <a href="{{ route('attributes.show', $attribute) }}" 
                           class="w-full bg-blue-600 text-white text-center px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors inline-block">
                            {{ __('attributes.actions.view') }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $attributes->appends(request()->query())->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <div class="text-gray-400 dark:text-gray-500 text-6xl mb-4">
                <i class="fas fa-adjustments-horizontal"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                {{ __('attributes.empty_states.no_attributes') }}
            </h3>
            <p class="text-gray-500 dark:text-gray-400">
                {{ __('attributes.empty_states.no_attributes_description') }}
            </p>
        </div>
    @endif
</div>
@endsection