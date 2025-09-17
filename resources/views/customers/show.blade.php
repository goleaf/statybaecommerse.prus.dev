@extends('layouts.app')

@section('title', $customer->name)
@section('description', __('customers.view_description', ['name' => $customer->name]))

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="flex items-center">
                <a href="{{ route('customers.index') }}" 
                   class="mr-4 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $customer->name }}
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        {{ $customer->email }}
                    </p>
                </div>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <a href="{{ route('customers.edit', $customer) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    {{ __('customers.actions.edit') }}
                </a>
                <form method="POST" action="{{ route('customers.destroy', $customer) }}" 
                      class="inline" 
                      onsubmit="return confirm('{{ __('customers.confirm_delete') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        {{ __('customers.actions.delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Customer Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ __('customers.sections.basic_information') }}
                </h2>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('customers.fields.name') }}
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $customer->name }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('customers.fields.email') }}
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $customer->email }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('customers.fields.phone') }}
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $customer->phone ?? __('customers.fields.no_phone') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('customers.fields.preferred_language') }}
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ __('customers.locales.' . $customer->preferred_locale) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('customers.fields.email_verified_at') }}
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            @if($customer->email_verified_at)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ __('customers.fields.verified') }} - {{ $customer->email_verified_at->format('d/m/Y H:i') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ __('customers.fields.not_verified') }}
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('customers.fields.last_login_at') }}
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $customer->last_login_at ? $customer->last_login_at->format('d/m/Y H:i') : __('customers.fields.never_logged_in') }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Account Status -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ __('customers.sections.account_status') }}
                </h2>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('customers.fields.is_active') }}
                        </dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $customer->is_active ? __('customers.fields.active') : __('customers.fields.inactive') }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('customers.fields.email_notifications') }}
                        </dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $customer->email_notifications ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $customer->email_notifications ? __('customers.fields.enabled') : __('customers.fields.disabled') }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('customers.fields.sms_notifications') }}
                        </dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $customer->sms_notifications ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $customer->sms_notifications ? __('customers.fields.enabled') : __('customers.fields.disabled') }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('customers.fields.marketing_consent') }}
                        </dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $customer->marketing_consent ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $customer->marketing_consent ? __('customers.fields.enabled') : __('customers.fields.disabled') }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Customer Groups -->
            @if($customer->customerGroups->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        {{ __('customers.sections.customer_groups') }}
                    </h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($customer->customerGroups as $group)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {{ $group->name }}
                                @if($group->discount_percentage > 0)
                                    <span class="ml-1 text-xs">({{ $group->discount_percentage }}%)</span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Partners -->
            @if($customer->partners->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        {{ __('customers.sections.partners') }}
                    </h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($customer->partners as $partner)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                {{ $partner->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Statistics -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ __('customers.sections.statistics') }}
                </h2>
                <dl class="space-y-4">
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('customers.fields.total_orders') }}
                        </dt>
                        <dd class="text-sm text-gray-900 dark:text-white">
                            {{ $customer->orders_count }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('customers.fields.total_spent') }}
                        </dt>
                        <dd class="text-sm text-gray-900 dark:text-white">
                            €{{ number_format($customer->total_spent, 2) }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('customers.fields.average_order_value') }}
                        </dt>
                        <dd class="text-sm text-gray-900 dark:text-white">
                            €{{ number_format($customer->average_order_value, 2) }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('customers.fields.reviews_written') }}
                        </dt>
                        <dd class="text-sm text-gray-900 dark:text-white">
                            {{ $customer->reviews_count }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- System Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ __('customers.sections.system_information') }}
                </h2>
                <dl class="space-y-4">
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('customers.fields.created_at') }}
                        </dt>
                        <dd class="text-sm text-gray-900 dark:text-white">
                            {{ $customer->created_at->format('d/m/Y H:i') }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('customers.fields.updated_at') }}
                        </dt>
                        <dd class="text-sm text-gray-900 dark:text-white">
                            {{ $customer->updated_at->format('d/m/Y H:i') }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ __('customers.sections.quick_actions') }}
                </h2>
                <div class="space-y-3">
                    <a href="{{ route('orders.index', ['customer' => $customer->id]) }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        {{ __('customers.actions.view_orders') }}
                    </a>
                    <a href="{{ route('reviews.index', ['customer' => $customer->id]) }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                        {{ __('customers.actions.view_reviews') }}
                    </a>
                    <a href="{{ route('addresses.index', ['customer' => $customer->id]) }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        {{ __('customers.actions.view_addresses') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


