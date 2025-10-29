@extends('components.layouts.base')

@section('title', __('customers.title'))
@section('description', __('customers.description'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ __('customers.title') }}
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    {{ __('customers.description') }}
                </p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('customers.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    {{ __('customers.create') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Advanced Filters Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                {{ __('customers.advanced_filters') }}
            </h3>
            <button type="button" 
                    onclick="toggleAdvancedFilters()"
                    class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                {{ __('customers.toggle_filters') }}
            </button>
        </div>

        <form method="GET" action="{{ route('customers.index') }}" class="space-y-6" id="filtersForm">
            <!-- Basic Filters -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('customers.search') }}
                    </label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="{{ __('customers.search_placeholder') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('customers.filters.status') }}
                    </label>
                    <select id="status" 
                            name="status"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">{{ __('customers.filters.all') }}</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                            {{ __('customers.filters.active') }}
                        </option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                            {{ __('customers.filters.inactive') }}
                        </option>
                    </select>
                </div>

                <!-- Language Filter -->
                <div>
                    <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('customers.filters.language') }}
                    </label>
                    <select id="language" 
                            name="language"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">{{ __('customers.filters.all_languages') }}</option>
                        <option value="lt" {{ request('language') === 'lt' ? 'selected' : '' }}>
                            {{ __('customers.filters.lithuanian') }}
                        </option>
                        <option value="en" {{ request('language') === 'en' ? 'selected' : '' }}>
                            {{ __('customers.filters.english') }}
                        </option>
                        <option value="de" {{ request('language') === 'de' ? 'selected' : '' }}>
                            {{ __('customers.filters.german') }}
                        </option>
                    </select>
                </div>

                <!-- Customer Group Filter -->
                <div>
                    <label for="customer_group" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('customers.filters.customer_group') }}
                    </label>
                    <select id="customer_group" 
                            name="customer_group"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">{{ __('customers.filters.all_groups') }}</option>
                        @foreach($customerGroups as $group)
                            <option value="{{ $group->id }}" {{ request('customer_group') == $group->id ? 'selected' : '' }}>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Advanced Filters (Collapsible) -->
            <div id="advancedFilters" class="hidden space-y-4">
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">
                        {{ __('customers.advanced_options') }}
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Total Spent Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('customers.filters.total_spent_range') }}
                            </label>
                            <div class="flex space-x-2">
                                <input type="number" 
                                       name="min_spent" 
                                       value="{{ request('min_spent') }}"
                                       placeholder="{{ __('customers.filters.min_spent') }}"
                                       class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <input type="number" 
                                       name="max_spent" 
                                       value="{{ request('max_spent') }}"
                                       placeholder="{{ __('customers.filters.max_spent') }}"
                                       class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>

                        <!-- Orders Count Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('customers.filters.orders_count_range') }}
                            </label>
                            <div class="flex space-x-2">
                                <input type="number" 
                                       name="min_orders" 
                                       value="{{ request('min_orders') }}"
                                       placeholder="{{ __('customers.filters.min_orders') }}"
                                       class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <input type="number" 
                                       name="max_orders" 
                                       value="{{ request('max_orders') }}"
                                       placeholder="{{ __('customers.filters.max_orders') }}"
                                       class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>

                        <!-- Customer Segments -->
                        <div>
                            <label for="segment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('customers.filters.customer_segments') }}
                            </label>
                            <select id="segment" 
                                    name="segment[]"
                                    multiple
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="new" {{ in_array('new', (array) request('segment', [])) ? 'selected' : '' }}>
                                    {{ __('customers.filters.new_customers') }}
                                </option>
                                <option value="regular" {{ in_array('regular', (array) request('segment', [])) ? 'selected' : '' }}>
                                    {{ __('customers.filters.regular_customers') }}
                                </option>
                                <option value="vip" {{ in_array('vip', (array) request('segment', [])) ? 'selected' : '' }}>
                                    {{ __('customers.filters.vip_customers') }}
                                </option>
                                <option value="inactive" {{ in_array('inactive', (array) request('segment', [])) ? 'selected' : '' }}>
                                    {{ __('customers.filters.inactive_customers') }}
                                </option>
                                <option value="high_value" {{ in_array('high_value', (array) request('segment', [])) ? 'selected' : '' }}>
                                    {{ __('customers.filters.high_value_customers') }}
                                </option>
                                <option value="frequent_buyers" {{ in_array('frequent_buyers', (array) request('segment', [])) ? 'selected' : '' }}>
                                    {{ __('customers.filters.frequent_buyers') }}
                                </option>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('customers.filters.created_date_range') }}
                            </label>
                            <div class="flex space-x-2">
                                <input type="date" 
                                       name="created_from" 
                                       value="{{ request('created_from') }}"
                                       class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <input type="date" 
                                       name="created_to" 
                                       value="{{ request('created_to') }}"
                                       class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>

                        <!-- Has Orders -->
                        <div>
                            <label for="has_orders" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('customers.filters.has_orders') }}
                            </label>
                            <select id="has_orders" 
                                    name="has_orders"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="">{{ __('customers.filters.all') }}</option>
                                <option value="yes" {{ request('has_orders') === 'yes' ? 'selected' : '' }}>
                                    {{ __('customers.filters.with_orders') }}
                                </option>
                                <option value="no" {{ request('has_orders') === 'no' ? 'selected' : '' }}>
                                    {{ __('customers.filters.without_orders') }}
                                </option>
                            </select>
                        </div>

                        <!-- Email Verified -->
                        <div>
                            <label for="email_verified" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('customers.filters.email_verified') }}
                            </label>
                            <select id="email_verified" 
                                    name="email_verified"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="">{{ __('customers.filters.all') }}</option>
                                <option value="yes" {{ request('email_verified') === 'yes' ? 'selected' : '' }}>
                                    {{ __('customers.filters.verified') }}
                                </option>
                                <option value="no" {{ request('email_verified') === 'no' ? 'selected' : '' }}>
                                    {{ __('customers.filters.unverified') }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4">
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    {{ __('customers.filter') }}
                </button>
                
                <a href="{{ route('customers.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('customers.clear_filters') }}
                </a>

                <button type="button" 
                        onclick="exportCustomers()"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    {{ __('customers.export') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Customers Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        @if($customers->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('customers.table.name') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('customers.table.email') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('customers.table.phone') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('customers.table.language') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('customers.table.orders') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('customers.table.total_spent') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('customers.table.status') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('customers.table.created_at') }}
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('customers.table.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($customers as $customer)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                                                <span class="text-sm font-medium text-white">
                                                    {{ strtoupper(substr($customer->name, 0, 2)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $customer->name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ $customer->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $customer->phone ?? __('customers.table.no_phone') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $customer->preferred_locale === 'lt' ? 'bg-blue-100 text-blue-800' : 
                                           ($customer->preferred_locale === 'en' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ __('customers.locales.' . $customer->preferred_locale) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $customer->orders_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    €{{ number_format($customer->total_spent, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $customer->is_active ? __('customers.table.active') : __('customers.table.inactive') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $customer->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('customers.show', $customer) }}" 
                                           class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            {{ __('customers.actions.view') }}
                                        </a>
                                        <a href="{{ route('customers.edit', $customer) }}" 
                                           class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            {{ __('customers.actions.edit') }}
                                        </a>
                                        <form method="POST" action="{{ route('customers.destroy', $customer) }}" 
                                              class="inline" 
                                              onsubmit="return confirm('{{ __('customers.confirm_delete') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                {{ __('customers.actions.delete') }}
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
            <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                {{ $customers->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                    {{ __('customers.no_customers') }}
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('customers.no_customers_description') }}
                </p>
                <div class="mt-6">
                    <a href="{{ route('customers.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        {{ __('customers.create_first') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('customers.stats.total_customers') }}</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total_customers'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('customers.stats.active_customers') }}</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['active_customers'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('customers.stats.total_revenue') }}</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">€{{ number_format($stats['total_revenue'] ?? 0, 2) }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('customers.stats.new_this_month') }}</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['new_this_month'] ?? 0 }}</p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function toggleAdvancedFilters() {
    const advancedFilters = document.getElementById('advancedFilters');
    const toggleButton = document.querySelector('[onclick="toggleAdvancedFilters()"]');
    
    if (advancedFilters.classList.contains('hidden')) {
        advancedFilters.classList.remove('hidden');
        toggleButton.textContent = '{{ __("customers.hide_filters") }}';
    } else {
        advancedFilters.classList.add('hidden');
        toggleButton.textContent = '{{ __("customers.show_filters") }}';
    }
}

function exportCustomers() {
    const form = document.getElementById('filtersForm');
    const formData = new FormData(form);
    
    // Add export parameter
    formData.append('export', '1');
    
    // Create a temporary form for export
    const exportForm = document.createElement('form');
    exportForm.method = 'GET';
    exportForm.action = '{{ route("customers.index") }}';
    exportForm.target = '_blank';
    
    // Add all form data as hidden inputs
    for (let [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        exportForm.appendChild(input);
    }
    
    document.body.appendChild(exportForm);
    exportForm.submit();
    document.body.removeChild(exportForm);
}

// Auto-submit form on filter change
document.addEventListener('DOMContentLoaded', function() {
    const filterInputs = document.querySelectorAll('#filtersForm select, #filtersForm input[type="date"]');
    
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Add a small delay to prevent too many requests
            setTimeout(() => {
                document.getElementById('filtersForm').submit();
            }, 300);
        });
    });
});
</script>
@endpush
